using System.Data.Odbc;
using System.Diagnostics;
using System.Globalization;
using System.Net.Sockets;
using System.Runtime.InteropServices;
using System.Collections.Concurrent;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using System.Text.Json.Nodes;
using System.Text.RegularExpressions;
using Microsoft.Data.Sqlite;
using Microsoft.Extensions.FileProviders;
using Microsoft.AspNetCore.Http.Features;
using WinForms = System.Windows.Forms;

Encoding.RegisterProvider(CodePagesEncodingProvider.Instance);
var root = AppContext.BaseDirectory;
var dataRoot = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "PersianTicketing", "FoodTicket");
Directory.CreateDirectory(dataRoot);
static string? FindFileUpward(string startPath, string fileName)
{
    var directory = new DirectoryInfo(Path.GetFullPath(startPath));
    while (directory is not null)
    {
        var candidate = Path.Combine(directory.FullName, fileName);
        if (File.Exists(candidate))
            return candidate;
        directory = directory.Parent;
    }
    return null;
}
var config = new ConfigStore(FindFileUpward(root, "food_ticket_config.json") ?? Path.Combine(root, "food_ticket_config.json"));
config.LoadSsoKeyFromPhp(FindFileUpward(root, "config.php") ?? FindFileUpward(Directory.GetCurrentDirectory(), "config.php") ?? "");
var data = new FoodTicketData(config, dataRoot);
data.Initialize();

const int MaxConsecutiveAccessFailures = 12;
_ = Task.Run(async () =>
{
    var consecutiveFailures = 0;
    while (true)
    {
        try
        {
            data.ProcessSource();
            consecutiveFailures = 0;
        }
        catch (Exception error)
        {
            data.SetLastError(error);
            Console.Error.WriteLine($"[food-ticket] poll failed: {error.Message}");
            consecutiveFailures++;
            if (consecutiveFailures == MaxConsecutiveAccessFailures)
                Console.Error.WriteLine("[food-ticket] monitoring is still running; the source database has failed repeatedly, printing retries will continue on the next cycle.");
        }
        await Task.Delay(TimeSpan.FromSeconds(Math.Clamp(config.Current.PollSeconds, 1, 120)));
    }
});

var builder = WebApplication.CreateBuilder(args);
builder.WebHost.UseUrls(Environment.GetEnvironmentVariable("FOOD_TICKET_URL") ?? "http://127.0.0.1:8080");
var app = builder.Build();
var publicFiles = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
{
    "index.html",
    "assets/food-ticket-theme.css",
    "assets/food-brand.js",
    "assets/food-ticket-persistence.js",
};
app.UseStaticFiles(new StaticFileOptions {
    FileProvider = new PhysicalFileProvider(root),
    OnPrepareResponse = ctx => {
        var relativePath = Path.GetRelativePath(root, ctx.File.PhysicalPath).Replace('\\', '/');
        if (!publicFiles.Contains(relativePath)) {
            ctx.Context.Response.StatusCode = 404;
            ctx.Context.Response.ContentLength = 0;
            ctx.Context.Response.Body = Stream.Null;
        }
    }
});
var sessions = new ConcurrentDictionary<string, FoodSession>();

async Task ServeIndex(HttpContext context)
{
    var candidates = new[]
    {
        Path.Combine(root, "index.html"),
        Path.Combine(app.Environment.ContentRootPath, "index.html"),
    };
    var indexPath = candidates.FirstOrDefault(File.Exists);
    if (indexPath is null)
    {
        context.Response.StatusCode = StatusCodes.Status500InternalServerError;
        await context.Response.WriteAsync("فایل index.html در پوشه سرویس پیدا نشد.");
        return;
    }
    context.Response.ContentType = "text/html; charset=utf-8";
    context.Response.Headers.CacheControl = "no-store, no-cache, must-revalidate";
    await context.Response.SendFileAsync(indexPath);
}

bool TryGetSessionRole(HttpContext context, out string role)
{
    role = "";
    var session = context.Request.Cookies["food_ticket_auth"];
    if (string.IsNullOrWhiteSpace(session) || !sessions.TryGetValue(session, out var value) || value.Expires <= DateTimeOffset.UtcNow)
        return false;
    role = value.Role;
    return true;
}

bool IsPrimaryAdminRole(string role) => string.Equals(role, "admin", StringComparison.OrdinalIgnoreCase) || string.Equals(role, "primary_admin", StringComparison.OrdinalIgnoreCase) || role.Contains("ادمین اصلی", StringComparison.OrdinalIgnoreCase);
bool IsSupportManagerRole(string role) => string.Equals(role, "manager", StringComparison.OrdinalIgnoreCase) || string.Equals(role, "support_manager", StringComparison.OrdinalIgnoreCase) || role.Contains("مدیر پشتیبانی", StringComparison.OrdinalIgnoreCase);
bool IsAuthorized(HttpContext context) => TryGetSessionRole(context, out _);
bool CanUseRoute(HttpContext context, string route)
{
    if (!TryGetSessionRole(context, out var role))
        return false;
    if (IsPrimaryAdminRole(role))
        return true;
    return IsSupportManagerRole(role) && route is ("dashboard" or "monitoring" or "absent" or "orders" or "employees" or "process" or "reprint-errors" or "health" or "reports/export");
}

string SessionRole(HttpContext context) => TryGetSessionRole(context, out var role) ? role : "";

IResult UnauthorizedResponse() => Results.Json(new { error = "ورود مدیر اصلی یا مدیر پشتیبانی لازم است." }, statusCode: 401);
IResult ForbiddenResponse() => Results.Json(new { error = "این نقش به این بخش از پنل چاپ فیش دسترسی ندارد." }, statusCode: 403);

void SetSession(HttpContext context, string role)
{
    var session = Convert.ToBase64String(RandomNumberGenerator.GetBytes(32));
    sessions[session] = new FoodSession(role, DateTimeOffset.UtcNow.AddHours(8));
    context.Response.Cookies.Append("food_ticket_auth", session, new CookieOptions
    {
        HttpOnly = true,
        SameSite = SameSiteMode.Strict,
        Secure = context.Request.IsHttps,
        MaxAge = TimeSpan.FromHours(8),
    });
}

bool TryValidateSso(string token, out string role)
{
    role = "";
    if (string.IsNullOrWhiteSpace(config.Current.SsoKey))
        return false;
    var parts = token.Split('.', 2);
    if (parts.Length != 2)
        return false;
    try
    {
        var payloadBytes = Base64UrlDecode(parts[0]);
        var expected = HMACSHA256.HashData(Encoding.UTF8.GetBytes(config.Current.SsoKey), Encoding.UTF8.GetBytes(parts[0]));
        var provided = Convert.FromHexString(parts[1]);
        if (!CryptographicOperations.FixedTimeEquals(expected, provided))
            return false;
        using var document = JsonDocument.Parse(payloadBytes);
        var root = document.RootElement;
        role = root.GetProperty("role").GetString() ?? "";
        var group = root.GetProperty("service_group").GetString() ?? "";
        var expires = root.GetProperty("exp").GetInt64();
        return expires >= DateTimeOffset.UtcNow.ToUnixTimeSeconds()
            && (((role == "admin" || role == "primary_admin") && root.GetProperty("user_id").GetInt32() > 0)
                || ((role == "manager" || role == "support_manager") && group == "support"));
    }
    catch
    {
        return false;
    }
}

static byte[] Base64UrlDecode(string value)
{
    var base64 = value.Replace('-', '+').Replace('_', '/');
    base64 = base64.PadRight(base64.Length + (4 - base64.Length % 4) % 4, '=');
    return Convert.FromBase64String(base64);
}

app.MapGet("/", ServeIndex);
app.MapGet("/index.html", ServeIndex);
app.MapGet("/api/health", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "health") ? ForbiddenResponse() : Results.Ok(data.Health()));
app.MapPost("/api/self-test", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "self-test") ? ForbiddenResponse() : Results.Ok(data.SelfTest()));
app.MapPost("/api/test-print", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "test-print") ? ForbiddenResponse() : Results.Ok(data.TestPrint()));
app.MapPost("/api/login", async (HttpContext context) =>
{
    var request = await JsonSerializer.DeserializeAsync<LoginRequest>(context.Request.Body, new JsonSerializerOptions { PropertyNameCaseInsensitive = true });
    var account = request is null || string.IsNullOrWhiteSpace(request.Password) ? null : config.Current.Admins.FirstOrDefault(item => string.Equals(item.Username, request.Username, StringComparison.OrdinalIgnoreCase) && item.Password == request.Password);
    if (account is null)
        return Results.Json(new { error = "نام کاربری یا رمز عبور مدیر صحیح نیست." }, statusCode: 401);
    SetSession(context, account.Role);
    return Results.Ok(new { ok = true, role = account.Role });
});
app.MapPost("/api/sso-login", (HttpContext context, SsoRequest request) =>
{
    if (!TryValidateSso(request.Token, out var role))
        return UnauthorizedResponse();
    SetSession(context, role);
    return Results.Ok(new { ok = true, role });
});
app.MapGet("/api/session", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : Results.Ok(new { role = SessionRole(context) }));
app.MapGet("/api/printers", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "printers") ? ForbiddenResponse() : Results.Ok(new { items = data.ListPrinters() }));
app.MapGet("/api/config", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "config") ? ForbiddenResponse() : Results.Ok(config.Public()));
app.MapPost("/api/config", async (HttpContext context, HttpRequest request) =>
{
    if (!IsAuthorized(context)) return UnauthorizedResponse();
    if (!CanUseRoute(context, "config")) return ForbiddenResponse();
    using var document = await JsonDocument.ParseAsync(request.Body);
    config.Merge(document.RootElement);
    config.Save();
    return Results.Ok(new { ok = true });
});
app.MapPost("/api/database-file", async (HttpContext context, HttpRequest request) =>
{
    if (!IsAuthorized(context)) return UnauthorizedResponse();
    if (!CanUseRoute(context, "config")) return ForbiddenResponse();
    string? destination = null;
    try
    {
        var maxBody = context.Features.Get<IHttpMaxRequestBodySizeFeature>();
        if (maxBody is { IsReadOnly: false }) maxBody.MaxRequestBodySize = 129L * 1024 * 1024;
        var form = await request.ReadFormAsync(new FormOptions { MultipartBodyLengthLimit = 129L * 1024 * 1024 });
        var file = form.Files.GetFile("file");
        var kind = form["kind"].ToString().Trim().ToLowerInvariant();
        if (kind is not ("attendance" or "orders")) return Results.BadRequest(new { error = "نوع فایل دیتابیس مشخص نیست." });
        if (file is null || file.Length < 1) return Results.BadRequest(new { error = "فایل انتخاب یا دریافت نشد." });
        if (file.Length > 128L * 1024 * 1024) return Results.StatusCode(413);
        var extension = Path.GetExtension(file.FileName).ToLowerInvariant();
        if (extension is not (".mdb" or ".accdb")) return Results.BadRequest(new { error = "فقط فایل‌های .mdb و .accdb مجاز هستند." });
        var directory = Path.Combine(dataRoot, "private", "access-databases");
        Directory.CreateDirectory(directory);
        destination = Path.Combine(directory, kind + "-" + Guid.NewGuid().ToString("N") + extension);
        await using (var output = new FileStream(destination, FileMode.CreateNew, FileAccess.Write, FileShare.None))
            await file.CopyToAsync(output);
        if (kind == "attendance") config.Current.Attendance.Path = destination;
        else config.Current.Orders.Path = destination;
        config.Save();
        return Results.Ok(new { ok = true, kind, path = destination, message = "فایل بارگذاری شد و مسیر سرور ذخیره شد." });
    }
    catch (Exception error)
    {
        if (!string.IsNullOrEmpty(destination)) try { File.Delete(destination); } catch { }
        return Results.Json(new { error = "بارگذاری فایل دیتابیس ناموفق بود: " + error.Message }, statusCode: 500);
    }
});
app.MapPost("/api/browse-file", async (HttpContext context) =>
{
    if (!IsAuthorized(context)) return UnauthorizedResponse();
    if (!CanUseRoute(context, "browse-file")) return ForbiddenResponse();
    try { return Results.Ok(new { path = await Browse.FileAsync() }); }
    catch (Exception error) { return Results.Json(new { error = "باز کردن انتخاب‌گر فایل ناموفق بود: " + error.Message }, statusCode: 500); }
});
app.MapPost("/api/browse-folder", async (HttpContext context) =>
{
    if (!IsAuthorized(context)) return UnauthorizedResponse();
    if (!CanUseRoute(context, "browse-folder")) return ForbiddenResponse();
    try { return Results.Ok(new { path = await Browse.FolderAsync() }); }
    catch (Exception error) { return Results.Json(new { error = "باز کردن انتخاب‌گر پوشه ناموفق بود: " + error.Message }, statusCode: 500); }
});
app.MapGet("/api/orders", (HttpContext context, string? date) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "orders") ? ForbiddenResponse() : Results.Ok(new { items = data.Orders(date) }));
app.MapGet("/api/monitoring", (HttpContext context, string? from, string? to) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "monitoring") ? ForbiddenResponse() : Results.Ok(new { items = data.Monitoring(from, to) }));
app.MapGet("/api/absent", (HttpContext context, string? from, string? to) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "absent") ? ForbiddenResponse() : Results.Ok(data.Absent(from, to)));
app.MapGet("/api/employees", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "employees") ? ForbiddenResponse() : Results.Ok(new { items = data.Employees() }));
app.MapPost("/api/employees", (HttpContext context, EmployeeRequest request) =>
{
    if (!IsAuthorized(context)) return UnauthorizedResponse();
    if (!CanUseRoute(context, "employees")) return ForbiddenResponse();
    try { data.SaveEmployee(request); return Results.Ok(new { ok = true }); }
    catch (ArgumentException error) { return Results.BadRequest(new { error = error.Message }); }
});
app.MapGet("/api/guest-cards", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "guests") ? ForbiddenResponse() : Results.Ok(new { items = data.GuestCards() }));
app.MapPost("/api/guest-cards", (HttpContext context, GuestCardRequest request) =>
{
    if (!IsAuthorized(context)) return UnauthorizedResponse();
    if (!CanUseRoute(context, "guests")) return ForbiddenResponse();
    try { data.SaveGuestCard(request); return Results.Ok(new { ok = true }); }
    catch (ArgumentException error) { return Results.BadRequest(new { error = error.Message }); }
});
app.MapPost("/api/process", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "process") ? ForbiddenResponse() : Results.Ok(data.ProcessSource()));
app.MapPost("/api/reprint-errors", (HttpContext context) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "reprint-errors") ? ForbiddenResponse() : Results.Ok(data.RetryFailedPrints()));
app.MapPost("/api/reports/export", (HttpContext context, ExportRequest request) => !IsAuthorized(context) ? UnauthorizedResponse() : !CanUseRoute(context, "reports/export") ? ForbiddenResponse() : Results.Ok(data.Export(request)));

app.Run();

static bool OdbcCheck()
{
    try { return Type.GetType("System.Data.Odbc.OdbcConnection, System.Data.Odbc") != null; } catch { return false; }
}

public sealed record ExportRequest(string From, string To, string Type);
public sealed record LoginRequest(string Username, string Password);
public sealed record SsoRequest(string Token);
public sealed record EmployeeRequest(int Id, string? Pc, string? Nat, string? First, string? Last, bool Active, string? Action);
public sealed record GuestCardRequest(int Id, string? CardNumber, string? GuestName, int DailyLimit, bool Active, string? Action);
public sealed record EmployeeView(int Id, string Pc, string Nat, string First, string Last, bool Active);
public sealed record GuestCardView(string Id, int RecordId, string CardNumber, string Name, int DailyLimit, bool Active);
public sealed record FoodSession(string Role, DateTimeOffset Expires);

public sealed class AppConfig
{
    /// <summary>نام جدول تراکنش‌های حضور و غیاب در فایل Access شما (مقدار محیطی — در config.json قابل تغییر است).</summary>
    public static readonly string DefaultSourceTable = System.Text.Encoding.UTF8.GetString(System.Convert.FromBase64String("VEVOVEVS"));
    public string AccessMode { get; set; } = "integrated";
    public string SsoKey { get; set; } = "";
    public string BrandName { get; set; } = "سامانه چاپ فیش غذا";
    public string BrandLogo { get; set; } = "";
    public List<AdminAccount> Admins { get; set; } = new();
    public DatabaseConfig Attendance { get; set; } = new() { Table = DefaultSourceTable, Columns = new() { ["uid"] = "L_UID", ["card"] = "C_Card", ["name"] = "C_Name", ["date"] = "C_Date", ["time"] = "C_Time" } };
    public DatabaseConfig Orders { get; set; } = new() { Table = "food_fish", Columns = new() { ["national"] = "cod_meli", ["first"] = "naam", ["last"] = "famili", ["food"] = "nahar_entekhabi", ["food_date"] = "tarikh_entekhabi", ["reserve_date"] = "d", ["reserve_time"] = "s" } };
    public PrinterConfig Printer { get; set; } = new();
    public string ExportPath { get; set; } = "";
    public int PollSeconds { get; set; } = 5;
    public string GuestCardUIDs { get; set; } = "";
    public int MaxGuestTicketsPerDay { get; set; } = 20;
    public string GuestFoodType { get; set; } = "مهمان";
    public bool CutFromSource { get; set; } = true;
}

public sealed class AdminAccount
{
    public string Username { get; set; } = "";
    public string Password { get; set; } = "";
    public string Role { get; set; } = "مدیر پشتیبانی";
}

public sealed class DatabaseConfig
{
    public string Path { get; set; } = "";
    public string Password { get; set; } = "";
    public string Table { get; set; } = "";
    public string Mode { get; set; } = "live-odbc";
    public bool CutSourceRows { get; set; } = true;
    public Dictionary<string, string> Columns { get; set; } = new();
}

public sealed class PrinterConfig
{
    public string Mode { get; set; } = "windows-spooler";
    public string Name { get; set; } = "";
    public string Host { get; set; } = "";
    public int Port { get; set; } = 9100;
    public string Encoding { get; set; } = "CP864";
    public string Company { get; set; } = "نام سازمان";
    public string Title { get; set; } = "فیش غذای پرسنل";
    public string Footer { get; set; } = "نوش جان";
    public bool Cut { get; set; } = true;
}

public sealed class ConfigStore
{
    private readonly string file;
    /// <summary>کلید قدیمی بخش تنظیمات منبع تردد در config.json نصب‌های قبلی (کدشده؛ فقط برای مهاجرت خودکار).</summary>
    private static readonly string LegacySourceKey = System.Text.Encoding.UTF8.GetString(System.Convert.FromBase64String("dW5pcw=="));

    private readonly JsonSerializerOptions options = new() { PropertyNameCaseInsensitive = true, WriteIndented = true };
    public AppConfig Current { get; private set; }

    public ConfigStore(string file)
    {
        this.file = file;
        if (!File.Exists(file))
        {
            Current = new AppConfig();
            return;
        }
        var saved = JsonNode.Parse(File.ReadAllText(file))?.AsObject() ?? new JsonObject();
        CopyAlias(saved, "access_mode", "accessMode");
        CopyAlias(saved, "export_path", "exportPath");
        CopyAlias(saved, "poll_seconds", "pollSeconds");
        // سازگاری: فایل config.json نصب‌های قبلی بخش منبع تردد را با کلید قدیمی ذخیره کرده است
        if (saved[LegacySourceKey] is JsonObject legacySource && saved["attendance"] is null) saved["attendance"] = legacySource;
        if (saved["attendance"] is JsonObject savedAttendance) CopyAlias(savedAttendance, "cut_source_rows", "cutSourceRows");
        Current = saved.Deserialize<AppConfig>(options) ?? new AppConfig();
        if (string.IsNullOrWhiteSpace(Current.Attendance.Table)) Current.Attendance.Table = DefaultSourceTable;
        var migrateOrderColumns = NormalizeOrderColumns(Current);
        var migrateSecrets = HasUnprotectedSecrets(Current);
        DecryptSecrets(Current);
        if (migrateSecrets || migrateOrderColumns)
        {
            try { Save(); } catch { }
        }
    }

    public void Save()
    {
        var snapshot = JsonNode.Parse(JsonSerializer.Serialize(Current, options))!.AsObject();
        ProtectSecrets(snapshot);
        var temporary = file + ".tmp";
        File.WriteAllText(temporary, JsonSerializer.Serialize(snapshot, options), Encoding.UTF8);
        File.Move(temporary, file, true);
    }

    public void LoadSsoKeyFromPhp(string phpConfigFile)
    {
        if (Current.SsoKey.Length >= 32 && !Current.SsoKey.Contains("app.key", StringComparison.OrdinalIgnoreCase))
            return;
        if (!File.Exists(phpConfigFile))
            return;
        var source = File.ReadAllText(phpConfigFile);
        var match = Regex.Match(source, "['\"]key['\"]\\s*=>\\s*['\"](?<key>[A-Za-z0-9]+)['\"]");
        if (match.Success)
            Current.SsoKey = match.Groups["key"].Value;
    }

    public object Public() => new
    {
        accessMode = Current.AccessMode,
        brandName = Current.BrandName,
        brandLogo = Current.BrandLogo,
        attendancePath = Current.Attendance.Path,
        attendanceTable = Current.Attendance.Table,
        ordersPath = Current.Orders.Path,
        ordersTable = Current.Orders.Table,
        printer = Current.Printer,
        exportPath = Current.ExportPath,
        pollSeconds = Current.PollSeconds,
        guestCardUIDs = Current.GuestCardUIDs,
        maxGuestTicketsPerDay = Current.MaxGuestTicketsPerDay,
        guestFoodType = Current.GuestFoodType,
        cutFromSource = Current.CutFromSource
    };

    public void Merge(JsonElement patch)
    {
        var current = JsonNode.Parse(JsonSerializer.Serialize(Current, options))!.AsObject();
        var patchObject = JsonNode.Parse(patch.GetRawText())!.AsObject();
        CopyAlias(patchObject, "poll_seconds", "pollSeconds");
        CopyAlias(patchObject, "export_path", "exportPath");
        CopyAlias(patchObject, "access_mode", "accessMode");
        if (patchObject[LegacySourceKey] is JsonObject legacySource && patchObject["attendance"] is null) patchObject["attendance"] = legacySource;
        if (patchObject["attendance"] is JsonObject attendancePatch) CopyAlias(attendancePatch, "cut_source_rows", "cutSourceRows");
        RemoveBlankSecret(patchObject, LegacySourceKey);
        RemoveBlankSecret(patchObject, "attendance");
        RemoveBlankSecret(patchObject, "orders");
        MergeObject(current, patchObject);
        Current = current.Deserialize<AppConfig>(options) ?? new AppConfig();
        NormalizeOrderColumns(Current);
    }

    private static bool NormalizeOrderColumns(AppConfig value)
    {
        var columns = value.Orders.Columns;
        var changed = false;
        if (columns.GetValueOrDefault("food_date") == "roz_entekhabi")
        {
            columns["food_date"] = "tarikh_entekhabi";
            changed = true;
        }
        if (columns.GetValueOrDefault("reserve_date") == "tarikh_entekhabi")
        {
            columns["reserve_date"] = "d";
            changed = true;
        }
        if (columns.GetValueOrDefault("reserve_time") == "reserve_time")
        {
            columns["reserve_time"] = "s";
            changed = true;
        }
        if (columns.GetValueOrDefault("food") == "sham_entekhabi")
        {
            columns["food"] = "nahar_entekhabi";
            changed = true;
        }
        changed |= columns.Remove("dinner_food");
        return changed;
    }

    private static bool HasUnprotectedSecrets(AppConfig value)
    {
        return value.Admins.Any(item => !string.IsNullOrWhiteSpace(item.Password) && !item.Password.StartsWith("dpapi:", StringComparison.Ordinal))
            || (!string.IsNullOrWhiteSpace(value.Attendance.Password) && !value.Attendance.Password.StartsWith("dpapi:", StringComparison.Ordinal))
            || (!string.IsNullOrWhiteSpace(value.Orders.Password) && !value.Orders.Password.StartsWith("dpapi:", StringComparison.Ordinal));
    }

    private static void DecryptSecrets(AppConfig value)
    {
        foreach (var admin in value.Admins)
            admin.Password = UnprotectSecret(admin.Password);
        value.Attendance.Password = UnprotectSecret(value.Attendance.Password);
        value.Orders.Password = UnprotectSecret(value.Orders.Password);
    }

    private static void ProtectSecrets(JsonObject value)
    {
        if (value["admins"] is JsonArray admins)
        {
            foreach (var node in admins.OfType<JsonObject>())
            {
                var password = node["password"]?.GetValue<string>();
                if (password is not null) node["password"] = ProtectSecret(password);
            }
        }
        foreach (var name in new[] { "attendance", "orders" })
        {
            if (value[name] is not JsonObject database) continue;
            var password = database["password"]?.GetValue<string>();
            if (password is not null) database["password"] = ProtectSecret(password);
        }
    }

    private static void RemoveBlankSecret(JsonObject value, string section)
    {
        if (value[section] is JsonObject child && child["password"]?.GetValue<string>() is string password && password.Length == 0)
            child.Remove("password");
    }

    private static string ProtectSecret(string value)
    {
        if (value.Length == 0 || value.StartsWith("dpapi:", StringComparison.Ordinal)) return value;
        var protectedValue = ProtectedData.Protect(Encoding.UTF8.GetBytes(value), null, DataProtectionScope.LocalMachine);
        return "dpapi:" + Convert.ToBase64String(protectedValue);
    }

    private static string UnprotectSecret(string value)
    {
        if (!value.StartsWith("dpapi:", StringComparison.Ordinal)) return value;
        try
        {
            var protectedValue = Convert.FromBase64String(value[6..]);
            var plain = ProtectedData.Unprotect(protectedValue, null, DataProtectionScope.LocalMachine);
            return Encoding.UTF8.GetString(plain);
        }
        catch
        {
            return "";
        }
    }

    private static void CopyAlias(JsonObject source, string oldName, string newName)
    {
        if (source[newName] is null && source[oldName] is JsonNode value)
            source[newName] = value.DeepClone();
        source.Remove(oldName);
    }

    private static void MergeObject(JsonObject target, JsonObject patch)
    {
        foreach (var item in patch)
        {
            if (item.Value is JsonObject child && target[item.Key] is JsonObject existing) MergeObject(existing, child);
            else target[item.Key] = item.Value?.DeepClone();
        }
    }
}

public sealed class FoodTicketData
{
    private readonly ConfigStore config;
    private readonly string database;
    private readonly object gate = new();
    private string lastError = "";
    public string LastError => lastError;

    public FoodTicketData(ConfigStore config, string root)
    {
        this.config = config;
        database = Path.Combine(root, "food_ticket_monitoring.db");
    }

    public void Initialize()
    {
        using var connection = OpenSqlite();
        using var command = connection.CreateCommand();
        command.CommandText = "CREATE TABLE IF NOT EXISTS monitoring (id INTEGER PRIMARY KEY AUTOINCREMENT,event_key TEXT UNIQUE,pc_code TEXT,national_code TEXT,full_name TEXT,food_type TEXT,food_date TEXT,reserved_date TEXT,reserved_time TEXT,attendance_date TEXT,attendance_time TEXT,result TEXT,print_status TEXT,source_deleted INTEGER,created_at TEXT)";
        command.ExecuteNonQuery();
        command.CommandText = "CREATE TABLE IF NOT EXISTS staff (id INTEGER PRIMARY KEY AUTOINCREMENT,personnel_code TEXT NOT NULL UNIQUE,national_code TEXT NOT NULL,first_name TEXT NOT NULL,last_name TEXT NOT NULL,is_active INTEGER NOT NULL DEFAULT 1)";
        command.ExecuteNonQuery();
        command.CommandText = "CREATE TABLE IF NOT EXISTS guest_cards (id INTEGER PRIMARY KEY AUTOINCREMENT,card_number TEXT NOT NULL UNIQUE,name TEXT NOT NULL,daily_limit INTEGER NOT NULL DEFAULT 0,is_active INTEGER NOT NULL DEFAULT 1)";
        command.ExecuteNonQuery();
        foreach (var column in new[] { ("raw_card", "TEXT"), ("raw_date", "TEXT"), ("raw_time", "TEXT"), ("first_name", "TEXT"), ("last_name", "TEXT") })
        {
            try
            {
                command.CommandText = $"ALTER TABLE monitoring ADD COLUMN {column.Item1} {column.Item2}";
                command.ExecuteNonQuery();
            }
                catch (SqliteException) { }
        }
        SeedGuestCardsFromConfig();
        try { EnsureAccessLiveSchema(); } catch (Exception error) { SetLastError(error); }
    }

    public void SetLastError(Exception error) => lastError = $"{DateTime.Now:s} {error.Message}";

    public List<Dictionary<string, object?>> Monitoring(string? from = null, string? to = null)
    {
        using var connection = OpenSqlite();
        using var command = connection.CreateCommand();
        if (from is null && to is null)
        {
            command.CommandText = "SELECT * FROM monitoring ORDER BY id DESC LIMIT 500";
        }
        else
        {
            var range = NormalizeReportRange(from, to);
            command.CommandText = "SELECT * FROM monitoring WHERE attendance_date >= $from AND attendance_date <= $to ORDER BY id DESC";
            command.Parameters.AddWithValue("$from", range.From);
            command.Parameters.AddWithValue("$to", range.To);
        }
        using var reader = command.ExecuteReader();
        return ReadDictionaries(reader);
    }

    public List<EmployeeView> Employees()
    {
        lock (gate)
        {
            SyncAccessEmployees();
            using var connection = OpenSqlite(); using var command = connection.CreateCommand();
            command.CommandText = "SELECT id,personnel_code,national_code,first_name,last_name,is_active FROM staff ORDER BY personnel_code";
            using var reader = command.ExecuteReader();
            var items = new List<EmployeeView>();
            while (reader.Read()) items.Add(new EmployeeView(reader.GetInt32(0), reader.GetString(1), reader.GetString(2), reader.GetString(3), reader.GetString(4), reader.GetInt32(5) != 0));
            return items;
        }
    }

    public void SaveEmployee(EmployeeRequest request)
    {
        lock (gate)
        {
            if (string.Equals(request.Action, "deactivate", StringComparison.OrdinalIgnoreCase))
            {
                using var connection = OpenSqlite(); using var command = connection.CreateCommand();
                command.CommandText = "UPDATE staff SET is_active=0 WHERE id=$id"; command.Parameters.AddWithValue("$id", request.Id);
                if (command.ExecuteNonQuery() != 1) throw new ArgumentException("کارمند برای غیرفعال‌سازی پیدا نشد.");
                return;
            }
            var pc = request.Pc?.Trim() ?? ""; var national = request.Nat?.Trim() ?? ""; var first = request.First?.Trim() ?? ""; var last = request.Last?.Trim() ?? "";
            if (pc.Length == 0 || first.Length == 0 || last.Length == 0 || !Regex.IsMatch(national, @"^\d{10}$"))
                throw new ArgumentException("کد پرسنلی، نام، نام خانوادگی و کد ملی ۱۰ رقمی لازم است.");
            using var connection = OpenSqlite(); using var command = connection.CreateCommand();
            if (request.Id > 0)
            {
                command.CommandText = "UPDATE staff SET personnel_code=$pc,national_code=$nat,first_name=$first,last_name=$last,is_active=$active WHERE id=$id";
                command.Parameters.AddWithValue("$id", request.Id);
            }
            else
            {
                command.CommandText = "INSERT INTO staff(personnel_code,national_code,first_name,last_name,is_active) VALUES($pc,$nat,$first,$last,$active) ON CONFLICT(personnel_code) DO UPDATE SET national_code=excluded.national_code,first_name=excluded.first_name,last_name=excluded.last_name,is_active=excluded.is_active";
            }
            command.Parameters.AddWithValue("$pc", pc); command.Parameters.AddWithValue("$nat", national); command.Parameters.AddWithValue("$first", first); command.Parameters.AddWithValue("$last", last); command.Parameters.AddWithValue("$active", request.Active ? 1 : 0);
            if (command.ExecuteNonQuery() != 1) throw new ArgumentException("ذخیرهٔ اطلاعات کارمند انجام نشد.");
        }
    }

    public List<GuestCardView> GuestCards()
    {
        lock (gate)
        {
            using (var check = OpenSqlite())
            using (var checkCommand = check.CreateCommand())
            {
                checkCommand.CommandText = "SELECT COUNT(*) FROM guest_cards";
                if (Convert.ToInt32(checkCommand.ExecuteScalar()) == 0) SeedGuestCardsFromConfig();
            }
            using var connection = OpenSqlite(); using var command = connection.CreateCommand();
            command.CommandText = "SELECT id,card_number,name,daily_limit,is_active FROM guest_cards ORDER BY id DESC";
            using var reader = command.ExecuteReader();
            var items = new List<GuestCardView>();
            while (reader.Read()) items.Add(new GuestCardView(reader.GetString(1), reader.GetInt32(0), reader.GetString(1), reader.GetString(2), reader.GetInt32(3), reader.GetInt32(4) != 0));
            return items;
        }
    }

    public void SaveGuestCard(GuestCardRequest request)
    {
        lock (gate)
        {
            using var connection = OpenSqlite(); using var command = connection.CreateCommand();
            if (string.Equals(request.Action, "delete", StringComparison.OrdinalIgnoreCase))
            {
                if (request.Id > 0)
                {
                    command.CommandText = "DELETE FROM guest_cards WHERE id=$id";
                    command.Parameters.AddWithValue("$id", request.Id);
                }
                else
                {
                    var delCard = NormGuestCard(request.CardNumber ?? "");
                    if (delCard.Length == 0) throw new ArgumentException("کارت مهمان پیدا نشد.");
                    command.CommandText = "DELETE FROM guest_cards WHERE card_number=$card";
                    command.Parameters.AddWithValue("$card", delCard);
                }
                if (command.ExecuteNonQuery() < 1) throw new ArgumentException("کارت مهمان پیدا نشد.");
                SyncGuestCardConfig();
                return;
            }
            var card = NormGuestCard(request.CardNumber ?? "");
            if (card.Length == 0 || request.DailyLimit < 0) throw new ArgumentException("شماره کارت معتبر و سقف روزانهٔ صفر یا بیشتر لازم است.");
            var name = string.IsNullOrWhiteSpace(request.GuestName) ? "مهمان" : request.GuestName.Trim();
            if (request.Id > 0)
            {
                command.CommandText = "UPDATE guest_cards SET card_number=$card,name=$name,daily_limit=$limit,is_active=$active WHERE id=$id";
                command.Parameters.AddWithValue("$id", request.Id);
            }
            else
            {
                command.CommandText = "INSERT INTO guest_cards(card_number,name,daily_limit,is_active) VALUES($card,$name,$limit,$active) ON CONFLICT(card_number) DO UPDATE SET name=excluded.name,daily_limit=excluded.daily_limit,is_active=excluded.is_active";
            }
            command.Parameters.AddWithValue("$card", card); command.Parameters.AddWithValue("$name", name); command.Parameters.AddWithValue("$limit", request.DailyLimit); command.Parameters.AddWithValue("$active", request.Active ? 1 : 0);
            if (command.ExecuteNonQuery() != 1) throw new ArgumentException("ذخیرهٔ کارت مهمان انجام نشد.");
            SyncGuestCardConfig();
        }
    }

    private void SyncGuestCardConfig()
    {
        config.Current.GuestCardUIDs = string.Join(",", GuestCards().Where(card => card.Active).Select(card => card.CardNumber));
        config.Save();
    }

    private void SeedGuestCardsFromConfig()
    {
        var accessCards = AccessSettings().GetValueOrDefault("GuestCardUIDs", "");
        var cards = GuestCards(config.Current.GuestCardUIDs).Concat(GuestCards(accessCards)).Distinct(StringComparer.OrdinalIgnoreCase).ToHashSet(StringComparer.OrdinalIgnoreCase);
        if (cards.Count == 0) return;
        using var connection = OpenSqlite(); using var command = connection.CreateCommand();
        command.CommandText = "SELECT COUNT(*) FROM guest_cards";
        if (Convert.ToInt32(command.ExecuteScalar()) > 0) return;
        foreach (var card in cards)
        {
            command.Parameters.Clear(); command.CommandText = "INSERT OR IGNORE INTO guest_cards(card_number,name,daily_limit,is_active) VALUES($card,'مهمان',$limit,1)";
            command.Parameters.AddWithValue("$card", card); command.Parameters.AddWithValue("$limit", Math.Max(0, config.Current.MaxGuestTicketsPerDay)); command.ExecuteNonQuery();
        }
    }

    public List<Dictionary<string, string>> Orders(string? date)
    {
        var rows = ReadAccess(config.Current.Orders);
        var availableColumns = rows.Count > 0 ? rows.SelectMany(row => row.Keys).Distinct(StringComparer.OrdinalIgnoreCase).ToList() : AccessTableColumns(config.Current.Orders, config.Current.Orders.Table);
        var columns = config.Current.Orders.Columns;
        var national = ResolveColumn(availableColumns, columns, "national", "national_code", "NationalCode", "NationalID", "cod_meli", "کد ملی", "کدملی");
        var first = ResolveColumn(availableColumns, columns, "first", "first_name", "FirstName", "naam", "نام");
        var last = ResolveColumn(availableColumns, columns, "last", "last_name", "LastName", "famili", "نام خانوادگی", "فامیل");
        var food = ResolveColumn(availableColumns, columns, "food", "food_type", "food_name", "FoodType", "FoodName", "nahar_entekhabi", "نوع غذا", "نام غذا");
        var foodDate = ResolveColumn(availableColumns, columns, "food_date", "tarikh_entekhabi", "food_date", "FoodDate", "OrderDate", "تاریخ غذا", "تاريخ_غذا");
        var reserveDate = ResolveColumn(availableColumns, columns, "reserve_date", "d", "reserve_date", "ReserveDate", "تاریخ رزرو");
        var reserveTime = ResolveColumn(availableColumns, columns, "reserve_time", "s", "reserve_time", "ReserveTime", "ساعت رزرو");
        if (national == null || food == null || foodDate == null) throw new InvalidOperationException("ستون‌های کد ملی، انتخاب ناهار و تاریخ غذا در جدول سفارش پیدا نشدند.");
        return rows.Select(row =>
        {
            return new Dictionary<string, string>
            {
                ["nat"] = Raw(row, national), ["first"] = Raw(row, first), ["last"] = Raw(row, last), ["food"] = Raw(row, food), ["foodDate"] = DateValue(Raw(row, foodDate)), ["reserveDate"] = DateValue(Raw(row, reserveDate)), ["reserveTime"] = TimeValue(Raw(row, reserveTime))
            };
        }).Where(row => string.IsNullOrWhiteSpace(date) || row["foodDate"] == DateValue(date)).ToList();
    }

    public object ProcessSource()
    {
        lock (gate)
        {
            if (string.IsNullOrWhiteSpace(config.Current.Attendance.Path) || string.IsNullOrWhiteSpace(config.Current.Orders.Path))
                return new { processed = 0, printed = 0, deleted = 0, skipped = 0, errors = 0, message = "مسیر دیتابیس‌ها کامل نیست" };

            var settings = AccessSettings();
            var orders = Orders(null);
            var employees = LoadEmployees().GroupBy(x => NormIdentity(x.PersonnelCode)).Where(x => x.Key.Length > 0).ToDictionary(x => x.Key, x => x.First());
            var guestRows = GuestCards();
            var guests = guestRows.Count > 0
                ? guestRows.Where(item => item.Active).Select(item => NormGuestCard(item.CardNumber)).ToHashSet(StringComparer.OrdinalIgnoreCase)
                : GuestCards(Setting(settings, "GuestCardUIDs", config.Current.GuestCardUIDs));
            var maxGuest = ParseInt(Setting(settings, "MaxGuestTicketsPerDay", config.Current.MaxGuestTicketsPerDay.ToString()), 20);
            var guestFood = Setting(settings, "GuestFoodType", config.Current.GuestFoodType);
            var printer = EffectivePrinter(settings);
            var cut = ParseBool(Setting(settings, "CutFromSource", config.Current.CutFromSource.ToString())) && config.Current.Attendance.CutSourceRows;
            var guestToday = GuestCountToday();
            var processed = 0; var printed = 0; var deleted = 0; var skipped = 0; var errors = 0;
            var columns = config.Current.Attendance.Columns;

            foreach (var row in ReadAccess(config.Current.Attendance).Take(80))
            {
                try
                {
                    var uid = NormUid(Value(row, columns, "uid"));
                    var rawCard = RawValue(row, columns, "card");
                    var card = NormGuestCard(rawCard);
                    if (uid.Length == 0 && card.Length == 0) continue;
                    var rawDate = RawValue(row, columns, "date");
                    var rawTime = RawValue(row, columns, "time");
                    var punch = ParsePunchDate(rawDate, rawTime) ?? DateTime.Now;
                    var attendanceDate = punch.ToString("yyyy-MM-dd");
                    var attendanceTime = punch.ToString("HH:mm:ss");
                    var key = HashKey($"{uid}|{card}|{rawDate}|{rawTime}");
                    var existing = FindMonitoring(key);
                    if (existing != null)
                    {
                        skipped++;
                        if (IsPendingPrint(existing))
                        {
                            var retry = RetryRecord(existing, printer, cut);
                            if (retry.Printed) printed++;
                            if (retry.Deleted) deleted++;
                            if (retry.Failed) errors++;
                        }
                    else if (cut && !IsTrue(existing.GetValueOrDefault("source_deleted")))
                    {
                        var removal = DeleteAccessRow(config.Current.Attendance, row);
                        if (removal.Deleted) { UpdateDeleted(key); deleted++; }
                        else if (removal.Failed) errors++;
                    }
                        continue;
                    }

                    var result = ""; var food = ""; var national = ""; var first = ""; var last = ""; var fullName = Value(row, columns, "name"); var shouldPrint = false;
                    // پرسنل: فقط با L_UID (شماره کاربری). کارت RFID پرسنل مانع تشخیص پرسنل نمی‌شود.
                    EmployeeRecord employee = null;
                    if (uid.Length > 0 && uid is not "-1" and not "0")
                        employees.TryGetValue(NormIdentity(uid), out employee);

                    // مهمان فقط اگر پرسنل نبود و شماره کارت در فرم کارت مهمان تعریف شده باشد
                    GuestCardView guestCardRow = null;
                    if (employee is null)
                    {
                        guestCardRow = guestRows.Where(item => item.Active).FirstOrDefault(item =>
                            (card.Length > 0 && IsGuestCard(card, new HashSet<string>(StringComparer.OrdinalIgnoreCase) { NormGuestCard(item.CardNumber) }))
                            || (uid.Length > 0 && IsGuestCard(NormGuestCard(uid), new HashSet<string>(StringComparer.OrdinalIgnoreCase) { NormGuestCard(item.CardNumber) })));
                    }
                    var isGuest = employee is null && (guestCardRow != null
                        || (card.Length > 0 && IsGuestCard(card, guests))
                        || (uid.Length > 0 && IsGuestCard(NormGuestCard(uid), guests)));

                    if (isGuest)
                    {
                        if (HasRecentPunch(uid, card, punch)) result = "تردد مجدد";
                        else if ((maxGuest > 0 && guestToday >= maxGuest) || (guestCardRow != null && guestCardRow.DailyLimit > 0 && GuestCountToday(card.Length > 0 ? card : NormGuestCard(uid)) >= guestCardRow.DailyLimit)) result = "سقف مهمان";
                        else { result = "فیش مهمان"; food = guestFood; fullName = $"مهمان {(card.Length > 0 ? card : uid)}"; guestToday++; shouldPrint = true; }
                    }
                    else if (employee is null) result = $"کاربر ناشناس ({(uid.Length > 0 ? uid : card)})";
                    else
                    {
                        national = employee.NationalCode; first = employee.FirstName; last = employee.LastName; fullName = $"{first} {last}".Trim();
                        if (!employee.Active) result = "کاربر غیرفعال";
                        else
                        {
                            var order = FindOrder(orders, national, punch.Date);
                            food = order?["food"] ?? "";
                            if (food.Length == 0) result = "غذا ندارد";
                            else if (HasRecentPunch(uid, card, punch)) result = "تردد مجدد";
                            else { result = "فیش چاپ شد"; shouldPrint = true; }
                        }
                    }
                    if (result == "فیش چاپ شد" && string.IsNullOrWhiteSpace(food)) { result = "غذا ندارد"; shouldPrint = false; }

                    var record = new Dictionary<string, object?>
                    {
                        ["event_key"] = key, ["pc_code"] = uid, ["national_code"] = national, ["full_name"] = fullName,
                        ["first_name"] = first, ["last_name"] = last,
                        ["food_type"] = food, ["food_date"] = FindOrder(orders, national, punch.Date)?["foodDate"] ?? "",
                        ["reserved_date"] = FindOrder(orders, national, punch.Date)?["reserveDate"] ?? "", ["reserved_time"] = FindOrder(orders, national, punch.Date)?["reserveTime"] ?? "",
                        ["attendance_date"] = attendanceDate, ["attendance_time"] = attendanceTime, ["result"] = result,
                        ["print_status"] = shouldPrint ? "pending" : "not_printed", ["source_deleted"] = 0, ["created_at"] = DateTime.Now.ToString("s"),
                        ["raw_card"] = rawCard, ["raw_date"] = rawDate, ["raw_time"] = rawTime
                    };
                    if (!InsertMonitoring(record)) continue;
                    var accessWritten = true;
                    try { WriteAccessMonitoring(record); } catch (Exception error) { accessWritten = false; SetLastError(error); }
                    if (!accessWritten) { UpdatePrint(key, "sync_error", "خطای ثبت مانیتورینگ Access"); errors++; continue; }
                    processed++;
                    var printSucceeded = !shouldPrint;
                    if (shouldPrint)
                    {
                        var ok = Printer.Write(printer, record);
                        printSucceeded = ok;
                        var finalResult = ok ? (result == "فیش مهمان" ? "فیش مهمان" : "فیش چاپ شد") : "خطا در چاپ";
                        UpdatePrint(key, ok ? "printed" : "print_error", finalResult);
                        try { UpdateAccessMonitoringResult(record, finalResult); }
                        catch (Exception error) { SetLastError(error); errors++; }
                        if (ok) printed++;
                        else
                        {
                            SetLastError(new InvalidOperationException("ارسال فیش به چاپگر ناموفق بود؛ رکورد SOURCE_TABLE برای تلاش مجدد باقی ماند."));
                            errors++;
                            continue;
                        }
                    }
                    if (cut && printSucceeded)
                    {
                        var removal = DeleteAccessRow(config.Current.Attendance, row);
                        if (removal.Deleted) { UpdateDeleted(key); deleted++; }
                        else if (removal.Failed) errors++;
                    }
                }
                catch (Exception error) { errors++; SetLastError(error); }
            }
            lastError = errors == 0 ? "" : lastError;
            return new { processed, printed, deleted, skipped, errors };
        }
    }

    private sealed record EmployeeRecord(string PersonnelCode, string NationalCode, string FirstName, string LastName, bool Active);

    private List<EmployeeRecord> LoadEmployees()
    {
        SyncAccessEmployees();
        using var connection = OpenSqlite(); using var command = connection.CreateCommand();
        command.CommandText = "SELECT personnel_code,national_code,first_name,last_name,is_active FROM staff";
        using var reader = command.ExecuteReader(); var items = new List<EmployeeRecord>();
        while (reader.Read()) items.Add(new EmployeeRecord(reader.GetString(0), reader.GetString(1), reader.GetString(2), reader.GetString(3), reader.GetInt32(4) != 0));
        return items;
    }

    private void SyncAccessEmployees()
    {
        var rows = ReadAccess(config.Current.Orders, "tblEmployees");
        var availableColumns = rows.Count > 0 ? rows.SelectMany(row => row.Keys).Distinct(StringComparer.OrdinalIgnoreCase).ToList() : AccessTableColumns(config.Current.Orders, "tblEmployees");
        var pc = FindColumn(availableColumns, "personnel_code", "PersonnelCode", "EmpCode", "UserCode", "کد پرسنلی", "شماره کاربری");
        var nat = FindColumn(availableColumns, "national_code", "NationalCode", "NationalID", "کد ملی", "کدملی");
        var first = FindColumn(availableColumns, "first_name", "FirstName", "FName", "نام");
        var last = FindColumn(availableColumns, "last_name", "LastName", "Family", "نام خانوادگی", "فامیل");
        var active = FindColumn(availableColumns, "is_active", "IsActive", "Active", "فعال");
        if (pc == null || nat == null || first == null || last == null) throw new InvalidOperationException("ستون‌های لازم در جدول tblEmployees پیدا نشدند.");
        using var connection = OpenSqlite(); using var transaction = connection.BeginTransaction();
        foreach (var row in rows)
        {
            var code = Raw(row, pc); if (code.Length == 0) continue;
            using var command = connection.CreateCommand(); command.Transaction = transaction;
            command.CommandText = "INSERT OR IGNORE INTO staff(personnel_code,national_code,first_name,last_name,is_active) VALUES($pc,$nat,$first,$last,$active)";
            command.Parameters.AddWithValue("$pc", code); command.Parameters.AddWithValue("$nat", Raw(row, nat)); command.Parameters.AddWithValue("$first", Raw(row, first)); command.Parameters.AddWithValue("$last", Raw(row, last)); command.Parameters.AddWithValue("$active", active == null || ParseBool(Raw(row, active)) ? 1 : 0);
            command.ExecuteNonQuery();
        }
        transaction.Commit();
    }

    private Dictionary<string, string> AccessSettings()
    {
        try
        {
            var rows = ReadAccess(config.Current.Orders, "tblSettings");
            var key = FindColumn(rows, "SettingKey", "setting_key"); var value = FindColumn(rows, "SettingValue", "setting_value");
            return rows.Where(row => key != null && value != null).ToDictionary(row => Raw(row, key), row => Raw(row, value), StringComparer.OrdinalIgnoreCase);
        }
        catch { return new(StringComparer.OrdinalIgnoreCase); }
    }

    private static string Setting(Dictionary<string, string> settings, string key, string fallback) => settings.TryGetValue(key, out var value) && !string.IsNullOrWhiteSpace(value) ? value : fallback;
    private static string? ResolveColumn(List<string> columns, Dictionary<string, string> configured, string key, params string[] candidates)
    {
        if (configured.TryGetValue(key, out var selected) && columns.Any(column => string.Equals(column, selected, StringComparison.OrdinalIgnoreCase))) return columns.First(column => string.Equals(column, selected, StringComparison.OrdinalIgnoreCase));
        return FindColumn(columns, candidates);
    }
    private static string? FindColumn(IEnumerable<string> columns, params string[] candidates) => candidates.Select(candidate => columns.FirstOrDefault(column => string.Equals(column, candidate, StringComparison.OrdinalIgnoreCase))).FirstOrDefault(value => value != null);
    private static string? FindColumn(IEnumerable<Dictionary<string, object?>> rows, params string[] candidates) => candidates.Select(candidate => rows.SelectMany(row => row.Keys).FirstOrDefault(key => string.Equals(key, candidate, StringComparison.OrdinalIgnoreCase))).FirstOrDefault(value => value != null);
    private static string Raw(Dictionary<string, object?> row, string? column) => column != null && row.TryGetValue(column, out var value) ? Convert.ToString(value)?.Trim() ?? "" : "";
    private static string RawValue(Dictionary<string, object?> row, Dictionary<string, string> columns, string key) => columns.TryGetValue(key, out var column) ? Raw(row, column) : "";
    private static string NormUid(string value) => NormIdentity(value);
    private static string NormIdentity(string value)
    {
        var identity = (value ?? "").Trim()
            .Replace('۰', '0').Replace('۱', '1').Replace('۲', '2').Replace('۳', '3').Replace('۴', '4')
            .Replace('۵', '5').Replace('۶', '6').Replace('۷', '7').Replace('۸', '8').Replace('۹', '9')
            .Replace('٠', '0').Replace('١', '1').Replace('٢', '2').Replace('٣', '3').Replace('٤', '4')
            .Replace('٥', '5').Replace('٦', '6').Replace('٧', '7').Replace('٨', '8').Replace('٩', '9');
        if (Regex.IsMatch(identity, @"^\d+\.0+$")) identity = identity[..identity.IndexOf('.')];
        return identity.ToUpperInvariant();
    }
    private static string NormGuestCard(string value) => new string((value ?? "").Trim().ToUpperInvariant().Where(char.IsLetterOrDigit).ToArray());
    private static HashSet<string> GuestCards(string raw) => raw.Replace(";", ",").Replace("،", ",").Split(',', StringSplitOptions.RemoveEmptyEntries).Select(NormGuestCard).Where(x => x.Length > 0).ToHashSet(StringComparer.OrdinalIgnoreCase);
    private static bool IsGuestCard(string card, HashSet<string> guests) => card.Length > 0 && guests.Any(x => card == x || card.EndsWith(x, StringComparison.OrdinalIgnoreCase) || x.EndsWith(card, StringComparison.OrdinalIgnoreCase));
    private static int ParseInt(string value, int fallback) => int.TryParse(value, out var number) ? number : fallback;
    private static bool ParseBool(string value) => !new[] { "0", "false", "no", "off", "غیرفعال" }.Contains((value ?? "").Trim(), StringComparer.OrdinalIgnoreCase);
    private static string HashKey(string value) => Convert.ToHexString(System.Security.Cryptography.SHA256.HashData(Encoding.UTF8.GetBytes(value)));

    private void EnsureAccessLiveSchema()
    {
        if (string.IsNullOrWhiteSpace(config.Current.Orders.Path)) return;
        using var connection = OpenAccess(config.Current.Orders); using var command = connection.CreateCommand();
        try { command.CommandText = "SELECT TOP 1 * FROM [tblLiveMonitoring]"; using var reader = command.ExecuteReader(); return; }
        catch (OdbcException) { }
        command.CommandText = "CREATE TABLE [tblLiveMonitoring] ([LiveID] COUNTER, [PersonnelCode] TEXT(50), [NationalCode] TEXT(20), [FirstName] TEXT(50), [LastName] TEXT(50), [FullName] TEXT(100), [MonitorDateTime] DATETIME, [PunchDate] TEXT(20), [PunchTime] TEXT(20), [Result] TEXT(100), [FoodType] TEXT(50))";
        command.ExecuteNonQuery();
    }
    private static string? PickAccessColumn(List<string> columns, params string[] names) => names.Select(name => columns.FirstOrDefault(column => string.Equals(column, name, StringComparison.OrdinalIgnoreCase))).FirstOrDefault(column => column != null);
    private static List<string> AccessTableColumns(OdbcConnection connection, string table)
    {
        using var command = connection.CreateCommand(); command.CommandText = $"SELECT TOP 0 * FROM [{Safe(table)}]"; using var reader = command.ExecuteReader(); return Enumerable.Range(0, reader.FieldCount).Select(index => reader.GetName(index)).ToList();
    }
    private static List<string> AccessTableColumns(DatabaseConfig source, string table)
    {
        using var connection = OpenAccess(source);
        return AccessTableColumns(connection, table);
    }
    private void WriteAccessMonitoring(Dictionary<string, object?> record)
    {
        using var connection = OpenAccess(config.Current.Orders); var columns = AccessTableColumns(connection, "tblLiveMonitoring");
        var values = new Dictionary<string, object?>
        {
            ["PersonnelCode"] = record.GetValueOrDefault("pc_code"), ["NationalCode"] = record.GetValueOrDefault("national_code"), ["FirstName"] = record.GetValueOrDefault("first_name"), ["LastName"] = record.GetValueOrDefault("last_name"), ["FullName"] = record.GetValueOrDefault("full_name"),
            ["MonitorDateTime"] = ParsePunchDate(Convert.ToString(record.GetValueOrDefault("attendance_date")) ?? "", Convert.ToString(record.GetValueOrDefault("attendance_time")) ?? "") ?? DateTime.Now,
            ["PunchDate"] = record.GetValueOrDefault("raw_date"), ["PunchTime"] = record.GetValueOrDefault("raw_time"), ["Result"] = record.GetValueOrDefault("result"), ["FoodType"] = record.GetValueOrDefault("food_type")
        };
        var fields = new List<string>(); var parameters = new List<string>(); using var command = connection.CreateCommand();
        foreach (var value in values)
        {
            var column = PickAccessColumn(columns, value.Key); if (column == null) continue;
            fields.Add($"[{Safe(column)}]"); var parameter = "p" + parameters.Count; parameters.Add("?"); command.Parameters.AddWithValue(parameter, value.Value ?? "");
        }
        if (fields.Count == 0) return;
        command.CommandText = $"INSERT INTO [tblLiveMonitoring] ({string.Join(",", fields)}) VALUES ({string.Join(",", parameters)})"; command.ExecuteNonQuery();
    }
    private void UpdateAccessMonitoringResult(Dictionary<string, object?> record, string result)
    {
        using var connection = OpenAccess(config.Current.Orders); var columns = AccessTableColumns(connection, "tblLiveMonitoring");
        var resultColumn = PickAccessColumn(columns, "Result", "result"); var pcColumn = PickAccessColumn(columns, "PersonnelCode", "personnel_code", "UID"); var dateColumn = PickAccessColumn(columns, "PunchDate", "punch_date"); var timeColumn = PickAccessColumn(columns, "PunchTime", "punch_time");
        if (resultColumn == null || pcColumn == null) return;
        using var command = connection.CreateCommand(); var where = new List<string>(); command.Parameters.AddWithValue("p0", result); where.Add($"[{Safe(pcColumn)}] = ?"); command.Parameters.AddWithValue("p" + command.Parameters.Count, record.GetValueOrDefault("pc_code") ?? "");
        if (dateColumn != null) { where.Add($"CStr([{Safe(dateColumn)}]) = ?"); command.Parameters.AddWithValue("p" + command.Parameters.Count, record.GetValueOrDefault("raw_date") ?? ""); }
        if (timeColumn != null) { where.Add($"CStr([{Safe(timeColumn)}]) = ?"); command.Parameters.AddWithValue("p" + command.Parameters.Count, record.GetValueOrDefault("raw_time") ?? ""); }
        command.CommandText = $"UPDATE [tblLiveMonitoring] SET [{Safe(resultColumn)}] = ? WHERE {string.Join(" AND ", where)}"; command.ExecuteNonQuery();
    }

    private static Dictionary<string, string>? FindOrder(List<Dictionary<string, string>> orders, string national, DateTime day) => orders.FirstOrDefault(order => NormIdentity(order["nat"]) == NormIdentity(national) && DateValue(order["foodDate"]) == day.ToString("yyyy-MM-dd"));
    private static DateTime? ParseDate(string value)
    {
        if (string.IsNullOrWhiteSpace(value)) return null;
        var digits = new string(value.Where(char.IsDigit).ToArray());
        if (digits.Length == 8 && int.TryParse(digits[..4], out var year))
        {
            var month = int.Parse(digits.Substring(4, 2)); var day = int.Parse(digits.Substring(6, 2));
            if (year >= 1700) try { return new DateTime(year, month, day); } catch { }
            if (year >= 1200) return DateTime.Parse(JalaliToGregorian(year, month, day), CultureInfo.InvariantCulture);
        }
        if (DateTime.TryParse(value, CultureInfo.InvariantCulture, DateTimeStyles.AllowWhiteSpaces, out var parsed)) return parsed.Date;
        if (double.TryParse(value, NumberStyles.Any, CultureInfo.InvariantCulture, out var serial) && serial > 30000 && serial < 60000) return new DateTime(1899, 12, 30).AddDays(serial).Date;
        return null;
    }
    private static TimeSpan? ParseTime(string value)
    {
        if (string.IsNullOrWhiteSpace(value)) return null;
        if (DateTime.TryParse(value, CultureInfo.InvariantCulture, DateTimeStyles.AllowWhiteSpaces, out var dateTime)) return dateTime.TimeOfDay;
        if (TimeSpan.TryParse(value, CultureInfo.InvariantCulture, out var parsed)) return parsed;
        if (double.TryParse(value, NumberStyles.Any, CultureInfo.InvariantCulture, out var number))
        {
            if (number >= 0 && number < 1) return TimeSpan.FromDays(number);
            var n = (int)number; if (n >= 10000) return new TimeSpan(n / 10000, n / 100 % 100, n % 100); if (n >= 100) return new TimeSpan(n / 100, n % 100, 0);
        }
        var digits = new string(value.Where(char.IsDigit).ToArray());
        if (digits.Length == 6) return new TimeSpan(int.Parse(digits[..2]), int.Parse(digits.Substring(2, 2)), int.Parse(digits.Substring(4, 2)));
        if (digits.Length == 4) return new TimeSpan(int.Parse(digits[..2]), int.Parse(digits.Substring(2, 2)), 0);
        return null;
    }
    private static DateTime? ParsePunchDate(string date, string time) { var day = ParseDate(date); if (day == null) return null; var part = ParseTime(time); return day.Value.Date + (part ?? TimeSpan.Zero); }
    private int GuestCountToday(string? card = null)
    {
        var local = Monitoring().Count(row =>
            ParsePunchDate(DateValue(row, "attendance_date"), Convert.ToString(row.GetValueOrDefault("attendance_time")) ?? "")?.Date == DateTime.Today
            && (Convert.ToString(row.GetValueOrDefault("result")) ?? "").Contains("مهمان")
            && (card == null || NormGuestCard(Convert.ToString(row.GetValueOrDefault("raw_card")) ?? "") == NormGuestCard(card)));
        if (card != null) return local;
        try
        {
            var rows = ReadAccess(config.Current.Orders, "tblLiveMonitoring"); var result = FindColumn(rows, "Result", "result"); var dateTime = FindColumn(rows, "MonitorDateTime", "monitor_datetime", "PunchDateTime");
            var access = rows.Count(row => (Raw(row, result)).Contains("مهمان") && dateTime != null && DateTime.TryParse(Raw(row, dateTime), out var value) && value.Date == DateTime.Today);
            return Math.Max(local, access);
        }
        catch { return local; }
    }
    private PrinterConfig EffectivePrinter(Dictionary<string, string> settings)
    {
        var current = config.Current.Printer;
        return new PrinterConfig { Mode = current.Mode, Name = string.IsNullOrWhiteSpace(current.Name) ? Setting(settings, "PrinterName", "") : current.Name, Host = current.Host, Port = current.Port, Encoding = current.Encoding, Company = current.Company, Title = current.Title, Footer = current.Footer, Cut = current.Cut };
    }
    private Dictionary<string, object?>? FindMonitoring(string key)
    {
        using var connection = OpenSqlite(); using var command = connection.CreateCommand(); command.CommandText = "SELECT * FROM monitoring WHERE event_key=$key LIMIT 1"; command.Parameters.AddWithValue("$key", key); using var reader = command.ExecuteReader(); return ReadDictionaries(reader).FirstOrDefault();
    }
    private static bool IsPendingPrint(Dictionary<string, object?> row) => new[] { "pending", "print_error", "sync_error" }.Contains(Convert.ToString(row.GetValueOrDefault("print_status")) ?? "", StringComparer.OrdinalIgnoreCase);
    private static bool IsTrue(object? value)
    {
        if (value is bool flag) return flag;
        if (value is string text) return ParseBool(text);
        return long.TryParse(Convert.ToString(value), out var number) && number != 0;
    }
    private bool HasRecentPunch(string uid, string card, DateTime punch)
    {
        bool Match(Dictionary<string, object?> row, string? pcColumn, string? cardColumn, string? dateColumn, string? timeColumn, string? dateTimeColumn)
        {
            var sameIdentity = uid.Length > 0 ? NormIdentity(Raw(row, pcColumn)) == NormIdentity(uid) : NormGuestCard(Raw(row, cardColumn)) == card;
            var existing = StoredPunchDate(row, dateColumn, timeColumn, dateTimeColumn);
            return sameIdentity && existing.HasValue && existing.Value.Date == punch.Date;
        }
        if (Monitoring().Any(row => Match(row, "pc_code", "raw_card", "attendance_date", "attendance_time", null))) return true;
        try
        {
            var rows = ReadAccess(config.Current.Orders, "tblLiveMonitoring");
            return rows.Any(row => Match(row, FindColumn(rows, "PersonnelCode", "personnel_code", "UID"), FindColumn(rows, "CardNo", "card_no", "C_Card"), FindColumn(rows, "PunchDate", "punch_date"), FindColumn(rows, "PunchTime", "punch_time"), FindColumn(rows, "MonitorDateTime", "monitor_datetime", "PunchDateTime")));
        }
        catch { return false; }
    }
    private static DateTime? StoredPunchDate(Dictionary<string, object?> row, string? dateColumn, string? timeColumn, string? dateTimeColumn)
    {
        if (dateTimeColumn != null && DateTime.TryParse(Raw(row, dateTimeColumn), CultureInfo.InvariantCulture, DateTimeStyles.AllowWhiteSpaces, out var dateTime)) return dateTime;
        return ParsePunchDate(Raw(row, dateColumn), Raw(row, timeColumn));
    }
    private Dictionary<string, object?> SourceRow(Dictionary<string, object?> row)
    {
        var source = new Dictionary<string, object?>(); var columns = config.Current.Attendance.Columns;
        if (columns.TryGetValue("uid", out var uid)) source[uid] = row.GetValueOrDefault("pc_code");
        if (columns.TryGetValue("card", out var card)) source[card] = row.GetValueOrDefault("raw_card");
        if (columns.TryGetValue("date", out var date)) source[date] = row.GetValueOrDefault("raw_date");
        if (columns.TryGetValue("time", out var time)) source[time] = row.GetValueOrDefault("raw_time");
        if (columns.TryGetValue("name", out var name)) source[name] = row.GetValueOrDefault("full_name");
        return source;
    }
    private (bool Printed, bool Deleted, bool Failed) RetryRecord(Dictionary<string, object?> row, PrinterConfig printer, bool cut)
    {
        var key = Convert.ToString(row.GetValueOrDefault("event_key")) ?? ""; var printed = false; var deleted = false; var failed = false;
        var status = Convert.ToString(row.GetValueOrDefault("print_status")) ?? "";
        if (status == "sync_error")
        {
            try { WriteAccessMonitoring(row); UpdatePrint(key, "pending", Convert.ToString(row.GetValueOrDefault("result")) ?? "فیش چاپ شد"); status = "pending"; }
            catch (Exception error) { SetLastError(error); return (false, false, true); }
        }
        if (status != "printed")
        {
            if (!Printer.Write(printer, row))
            {
                UpdatePrint(key, "print_error", "خطا در چاپ");
                try { UpdateAccessMonitoringResult(row, "خطا در چاپ"); } catch (Exception error) { SetLastError(error); }
                SetLastError(new InvalidOperationException("ارسال مجدد فیش به چاپگر ناموفق بود؛ رکورد SOURCE_TABLE برای تلاش بعدی باقی ماند."));
                return (false, false, true);
            }
            var finalResult = Convert.ToString(row.GetValueOrDefault("result")) == "فیش مهمان" ? "فیش مهمان" : "فیش چاپ شد";
            UpdatePrint(key, "printed", finalResult);
            try { UpdateAccessMonitoringResult(row, finalResult); } catch (Exception error) { SetLastError(error); failed = true; }
            printed = true;
        }
        if (cut && !IsTrue(row.GetValueOrDefault("source_deleted")))
        {
            var removal = DeleteAccessRow(config.Current.Attendance, SourceRow(row));
            if (removal.Deleted) { UpdateDeleted(key); deleted = true; }
            else if (removal.Failed) return (printed, false, true);
        }
        return (printed, deleted, failed);
    }
    public object RetryFailedPrints()
    {
        lock (gate)
        {
            var settings = AccessSettings(); var printer = EffectivePrinter(settings); var cut = ParseBool(Setting(settings, "CutFromSource", config.Current.CutFromSource.ToString())) && config.Current.Attendance.CutSourceRows; var printed = 0; var deleted = 0; var errors = 0;
            foreach (var row in Monitoring().Where(IsPendingPrint).Concat(Monitoring().Where(row => Convert.ToString(row.GetValueOrDefault("print_status")) == "printed" && !IsTrue(row.GetValueOrDefault("source_deleted"))).ToList()))
            {
                try
                {
                    var result = RetryRecord(row, printer, cut);
                    if (result.Printed) printed++;
                    if (result.Deleted) deleted++;
                    if (result.Failed) errors++;
                }
                catch (Exception error) { SetLastError(error); errors++; }
            }
            if (errors == 0) lastError = "";
            return new { printed, deleted, errors };
        }
    }

    public List<string> ListPrinters() => Printer.List();
    public object Export(ExportRequest request)
    {
        var range = NormalizeReportRange(request.From, request.To);
        var rows = Monitoring(range.From, range.To).Where(row =>
        {
            var date = DateValue(row, "attendance_date");
            return string.Compare(date, range.From, StringComparison.Ordinal) >= 0
                && string.Compare(date, range.To, StringComparison.Ordinal) <= 0
                && ReportMatch(row, request.Type);
        }).ToList();
        if (string.IsNullOrWhiteSpace(config.Current.ExportPath)) throw new InvalidOperationException("مسیر استخراج Excel در تنظیمات تعیین نشده است");
        Directory.CreateDirectory(config.Current.ExportPath);
        var outputPath = Path.Combine(config.Current.ExportPath, $"food-ticket-{DateTime.Now:yyyyMMdd-HHmmss}.xlsx");
        Xlsx.Write(outputPath, rows);
        return new { path = outputPath, count = rows.Count };
    }

    public object Absent(string? from, string? to)
    {
        var range = NormalizeReportRange(from, to);
        var printed = Monitoring(range.From, range.To)
            .Where(row => string.Equals(Convert.ToString(row.GetValueOrDefault("print_status")), "printed", StringComparison.OrdinalIgnoreCase))
            .Select(row => DateValue(row, "attendance_date") + "|" + NormIdentity(Convert.ToString(row.GetValueOrDefault("national_code")) ?? ""))
            .ToHashSet(StringComparer.Ordinal);
        var items = new List<Dictionary<string, string>>();
        foreach (var order in Orders(null).Where(row => row["foodDate"] >= range.From && row["foodDate"] <= range.To))
        {
            var nat = NormIdentity(order["nat"]);
            if (nat.Length == 0 || printed.Contains(order["foodDate"] + "|" + nat)) continue;
            var first = order["first"];
            var last = order["last"];
            items.Add(new Dictionary<string, string>
            {
                ["nat"] = order["nat"], ["pc"] = "", ["first"] = first, ["last"] = last,
                ["full_name"] = (first + " " + last).Trim(), ["food"] = order["food"],
                ["foodDate"] = order["foodDate"], ["status"] = "غایب — سفارش دارد، فیش چاپ نشده"
            });
        }
        return new { items, from = range.From, to = range.To };
    }

    private bool InsertMonitoring(Dictionary<string, object?> row)
    {
        using var connection = OpenSqlite(); using var command = connection.CreateCommand();
        command.CommandText = "INSERT OR IGNORE INTO monitoring(event_key,pc_code,national_code,full_name,first_name,last_name,food_type,food_date,reserved_date,reserved_time,attendance_date,attendance_time,result,print_status,source_deleted,created_at,raw_card,raw_date,raw_time) VALUES($event_key,$pc_code,$national_code,$full_name,$first_name,$last_name,$food_type,$food_date,$reserved_date,$reserved_time,$attendance_date,$attendance_time,$result,$print_status,$source_deleted,$created_at,$raw_card,$raw_date,$raw_time)";
        foreach (var item in row) command.Parameters.AddWithValue("$" + item.Key, item.Value ?? "");
        return command.ExecuteNonQuery() > 0;
    }

    public object Health()
    {
        var printer = EffectivePrinter(AccessSettings());
        var items = Monitoring();
        var pending = items.Count(IsPendingPrint);
        var failed = items.Count(row => string.Equals(Convert.ToString(row.GetValueOrDefault("print_status")), "print_error", StringComparison.OrdinalIgnoreCase));
        var lastEventAt = items.Count > 0 ? Convert.ToString(items[0].GetValueOrDefault("created_at")) ?? "" : "";
        var sourceConfigured = !string.IsNullOrWhiteSpace(config.Current.Attendance.Path) && !string.IsNullOrWhiteSpace(config.Current.Orders.Path);
        var monitorPath = database;
        var monitorWritable = true;
        var dataDirectory = Path.GetDirectoryName(database)!;
        var probePath = Path.Combine(dataDirectory, ".write-probe");
        try { using (var probe = new FileStream(probePath, FileMode.OpenOrCreate, FileAccess.Write, FileShare.ReadWrite)) { } File.Delete(probePath); }
        catch { monitorWritable = false; }
        return new
        {
            service = "ready",
            dotnet = true,
            database = new { path = monitorPath, writable = monitorWritable, records = items.Count },
            access = new { configured = sourceConfigured, path = config.Current.Attendance.Path, orders = config.Current.Orders.Path, live = OdbcCheck() },
            printer = new { mode = printer.Mode, name = printer.Name, host = printer.Host, port = printer.Port, ready = printer.Mode == "socket" ? !string.IsNullOrWhiteSpace(printer.Host) : !string.IsNullOrWhiteSpace(printer.Name) },
            queue = new { pending, print_error = failed, last_event_at = lastEventAt },
            sso = config.Current.SsoKey.Length >= 32,
            last_error = lastError
        };
    }

    public object SelfTest()
    {
        var checks = new List<object>();
        var settings = AccessSettings();
        var printer = EffectivePrinter(settings);

        bool sqliteOk;
        string sqliteMessage;
        try
        {
            using var connection = OpenSqlite();
            using var command = connection.CreateCommand();
            command.CommandText = "SELECT COUNT(*) FROM monitoring";
            var count = Convert.ToInt64(command.ExecuteScalar());
            sqliteOk = true;
            sqliteMessage = $"مانیتورینگ محلی قابل خواندن است؛ {count} رکورد.";
        }
        catch (Exception error) { sqliteOk = false; sqliteMessage = error.Message; }
        checks.Add(new { name = "دیتابیس داخلی", ok = sqliteOk, message = sqliteMessage });

        bool attendanceOk = false; string attendanceMessage;
        try
        {
            if (string.IsNullOrWhiteSpace(config.Current.Attendance.Path)) throw new InvalidOperationException("مسیر منبع تردد تنظیم نشده است.");
            var rows = ReadAccess(config.Current.Attendance, config.Current.Attendance.Table);
            attendanceOk = true; attendanceMessage = $"منبع تردد خوانده شد؛ {rows.Count} رکورد.";
        }
        catch (Exception error) { attendanceMessage = error.Message; }
        checks.Add(new { name = "منبع تردد (SOURCE_TABLE)", ok = attendanceOk, message = attendanceMessage });

        bool ordersOk = false; string ordersMessage;
        try
        {
            if (string.IsNullOrWhiteSpace(config.Current.Orders.Path)) throw new InvalidOperationException("مسیر سفارش غذا تنظیم نشده است.");
            var rows = ReadAccess(config.Current.Orders, config.Current.Orders.Table);
            ordersOk = true; ordersMessage = $"سفارش غذا خوانده شد؛ {rows.Count} رکورد در {config.Current.Orders.Table}.";
        }
        catch (Exception error) { ordersMessage = error.Message; }
        checks.Add(new { name = "سفارش غذا", ok = ordersOk, message = ordersMessage });

        bool employeesOk = false; string employeesMessage;
        try
        {
            var rows = ReadAccess(config.Current.Orders, "tblEmployees");
            employeesOk = rows.Count > 0; employeesMessage = $"کارکنان خوانده شد؛ {rows.Count} نفر.";
        }
        catch (Exception error) { employeesMessage = error.Message; }
        checks.Add(new { name = "کارکنان", ok = employeesOk, message = employeesMessage });

        bool printOk = false; string printMessage;
        try
        {
            if (printer.Mode == "socket" && string.IsNullOrWhiteSpace(printer.Host)) throw new InvalidOperationException("آدرس چاپگر شبکه تنظیم نشده است.");
            if (printer.Mode != "socket" && string.IsNullOrWhiteSpace(printer.Name)) throw new InvalidOperationException("چاپگر نصب‌شده در Windows انتخاب نشده است.");
            var sample = new Dictionary<string, object?>
            {
                ["full_name"] = "آزمون چاپ موتور فیش",
                ["food_type"] = "فیش آزمایشی",
                ["food_date"] = DateTime.Now.ToString("yyyy-MM-dd"),
                ["attendance_time"] = DateTime.Now.ToString("HH:mm:ss")
            };
            printOk = Printer.Write(printer, sample);
            printMessage = printOk ? "فیش آزمایشی به چاپگر ارسال شد." : "ارسال فیش آزمایشی به چاپگر ناموفق بود؛ نام چاپگر و Encoding را بررسی کنید.";
        }
        catch (Exception error) { printMessage = error.Message; }
        checks.Add(new { name = "چاپگر", ok = printOk, message = printMessage });

        var allOk = checks.All(item => (bool)item.GetType().GetProperty("ok")!.GetValue(item)!);
        return new { ok = allOk, checks, at = DateTime.Now.ToString("s") };
    }

    public object TestPrint()
    {
        var printer = EffectivePrinter(AccessSettings());
        var sample = new Dictionary<string, object?>
        {
            ["full_name"] = "آزمون چاپ موتور فیش",
            ["food_type"] = "فیش آزمایشی",
            ["food_date"] = DateTime.Now.ToString("yyyy-MM-dd"),
            ["attendance_time"] = DateTime.Now.ToString("HH:mm:ss")
        };
        var ok = Printer.Write(printer, sample);
        return new { ok, message = ok ? "فیش آزمایشی به چاپگر ارسال شد." : "ارسال فیش آزمایشی ناموفق بود؛ تنظیمات و اتصال چاپگر را بررسی کنید." };
    }

    private void UpdatePrint(string key, string status, string result) => Execute("UPDATE monitoring SET print_status=$status,result=$result WHERE event_key=$key", ("$status", status), ("$result", result), ("$key", key));
    private void UpdateDeleted(string key) => Execute("UPDATE monitoring SET source_deleted=1 WHERE event_key=$key", ("$key", key));
    private void Execute(string sql, params (string, object)[] values) { using var connection = OpenSqlite(); using var command = connection.CreateCommand(); command.CommandText = sql; foreach (var value in values) command.Parameters.AddWithValue(value.Item1, value.Item2); command.ExecuteNonQuery(); }
    private SqliteConnection OpenSqlite() { var c = new SqliteConnection($"Data Source={database}"); c.Open(); return c; }

    private List<Dictionary<string, object?>> ReadAccess(DatabaseConfig source)
    {
        return ReadAccess(source, source.Table);
    }
    private List<Dictionary<string, object?>> ReadAccess(DatabaseConfig source, string table)
    {
        using var connection = OpenAccess(source);
        using var command = connection.CreateCommand(); command.CommandText = $"SELECT * FROM [{Safe(table)}]";
        using var reader = command.ExecuteReader(); return ReadDictionaries(reader);
    }
    private static OdbcConnection OpenAccess(DatabaseConfig source)
    {
        var baseString = $"DRIVER={{Microsoft Access Driver (*.mdb, *.accdb)}};DBQ={source.Path};Exclusive=0;";
        var attempts = new List<string> { baseString };
        if (!string.IsNullOrWhiteSpace(source.Password)) attempts.Add(baseString + $"PWD={source.Password};");
        Exception? last = null;
        foreach (var connectionString in attempts)
        {
            try
            {
                var connection = new OdbcConnection(connectionString);
                connection.Open();
                return connection;
            }
            catch (Exception error) { last = error; }
        }
        throw new InvalidOperationException($"اتصال به Access برقرار نشد: {last?.Message}", last);
    }
    private static List<Dictionary<string, object?>> ReadDictionaries(System.Data.Common.DbDataReader reader) { var result = new List<Dictionary<string, object?>>(); while (reader.Read()) { var row = new Dictionary<string, object?>(StringComparer.OrdinalIgnoreCase); for (var i = 0; i < reader.FieldCount; i++) row[reader.GetName(i)] = reader.IsDBNull(i) ? "" : reader.GetValue(i); result.Add(row); } return result; }
    private (bool Deleted, bool Failed) DeleteAccessRow(DatabaseConfig source, Dictionary<string, object?> row)
    {
        if (!source.CutSourceRows) return (false, false);
        var matchKeys = new List<string>();
        foreach (var key in new[] { "uid", "card" })
        {
            if (!source.Columns.TryGetValue(key, out var column) || !row.TryGetValue(column, out var value)) continue;
            var text = Convert.ToString(value)?.Trim() ?? "";
            if (text.Length == 0 || key == "uid" && text is "0" or "-1") continue;
            matchKeys.Add(key);
        }
        if (matchKeys.Count == 0)
        {
            SetLastError(new InvalidOperationException("برای حذف SOURCE_TABLE کد پرسنلی یا کارت معتبر در دسترس نیست."));
            return (false, true);
        }
        foreach (var key in new[] { "date", "time" })
        {
            if (!source.Columns.TryGetValue(key, out var column) || !row.TryGetValue(column, out var value) || string.IsNullOrWhiteSpace(Convert.ToString(value)))
            {
                SetLastError(new InvalidOperationException("تاریخ و ساعت تردد برای حذف دقیق SOURCE_TABLE الزامی است؛ رکورد باقی ماند."));
                return (false, true);
            }
            matchKeys.Add(key);
        }

        using var connection = OpenAccess(source);
        using var command = connection.CreateCommand();
        var terms = new List<string>();
        foreach (var key in matchKeys)
        {
            var column = Safe(source.Columns[key]);
            var value = row[source.Columns[key]];
            terms.Add($"([{column}] = ? OR CStr([{column}]) = ?)");
            command.Parameters.AddWithValue("p" + command.Parameters.Count, value);
            command.Parameters.AddWithValue("p" + command.Parameters.Count, Convert.ToString(value) ?? "");
        }
        command.CommandText = $"SELECT COUNT(*) FROM [{Safe(source.Table)}] WHERE {string.Join(" AND ", terms)}";
        var matches = Convert.ToInt32(command.ExecuteScalar());
        if (matches == 0) return (true, false);
        if (matches > 1)
        {
            SetLastError(new InvalidOperationException("تردد SOURCE_TABLE تکراری با همان کد و زمان پیدا شد؛ رکوردها برای بررسی باقی ماندند."));
            return (false, true);
        }
        command.CommandText = $"DELETE FROM [{Safe(source.Table)}] WHERE {string.Join(" AND ", terms)}";
        if (command.ExecuteNonQuery() == 1) return (true, false);
        SetLastError(new InvalidOperationException("حذف دقیق رکورد SOURCE_TABLE انجام نشد؛ رکورد برای بررسی باقی ماند."));
        return (false, true);
    }

    private static string Value(Dictionary<string, object?> row, Dictionary<string, string> columns, string key) => columns.TryGetValue(key, out var column) && row.TryGetValue(column, out var value) ? Convert.ToString(value)?.Trim() ?? "" : "";
    private static string DateValue(Dictionary<string, object?> row, Dictionary<string, string> columns, string key) => DateValue(Value(row, columns, key));
    private static string DateValue(Dictionary<string, object?> row, string key) => row.TryGetValue(key, out var value) ? DateValue(Convert.ToString(value) ?? "") : "";
    private static string DateValue(string value) { var digits = new string(value.Trim().Where(char.IsDigit).ToArray()); if (digits.Length == 8) { var year = int.Parse(digits[..4]); return year < 1700 ? JalaliToGregorian(year, int.Parse(digits.Substring(4, 2)), int.Parse(digits.Substring(6, 2))) : $"{digits[..4]}-{digits.Substring(4, 2)}-{digits.Substring(6, 2)}"; } if (double.TryParse(value, NumberStyles.Any, CultureInfo.InvariantCulture, out var serial) && serial > 30000 && serial < 60000) return new DateTime(1899, 12, 30).AddDays(serial).ToString("yyyy-MM-dd"); if (DateTime.TryParse(value, out var date)) return date.ToString("yyyy-MM-dd"); return value.Trim(); }
    private static (string From, string To) NormalizeReportRange(string? from, string? to)
    {
        var first = DateValue(from ?? "");
        var last = DateValue(to ?? from ?? "");
        if (!DateTime.TryParseExact(first, "yyyy-MM-dd", CultureInfo.InvariantCulture, DateTimeStyles.None, out _)
            || !DateTime.TryParseExact(last, "yyyy-MM-dd", CultureInfo.InvariantCulture, DateTimeStyles.None, out _)
            || string.Compare(first, last, StringComparison.Ordinal) > 0)
            throw new ArgumentException("بازهٔ تاریخ گزارش معتبر نیست.");
        return (first, last);
    }
    private static string TimeValue(string value) { var digits = new string(value.Where(char.IsDigit).ToArray()); return digits.Length == 6 ? $"{digits[..2]}:{digits.Substring(2, 2)}:{digits.Substring(4, 2)}" : value; }
    private static string TimeValue(Dictionary<string, object?> row, Dictionary<string, string> columns, string key) => TimeValue(Value(row, columns, key));
    private static string Safe(string? value) => System.Text.RegularExpressions.Regex.IsMatch(value ?? "", "^[A-Za-z_][A-Za-z0-9_]*$") ? value ?? "" : throw new InvalidOperationException("نام جدول/ستون نامعتبر است");
    private static bool ReportMatch(Dictionary<string, object?> row, string type) { var food = Convert.ToString(row.GetValueOrDefault("food_type")) ?? ""; var result = Convert.ToString(row.GetValueOrDefault("result")) ?? ""; var print = Convert.ToString(row.GetValueOrDefault("print_status")) ?? ""; return type switch { "orders" => food.Length > 0, "guest" => food.Contains("مهمان") || result.Contains("مهمان"), "errors" => print == "print_error" || result.Contains("خطا"), _ => true }; }
    private static string JalaliToGregorian(int jy, int jm, int jd) { var gy = jy <= 979 ? 621 : 1600; var year = jy <= 979 ? jy : jy - 979; var days = 365 * year + year / 33 * 8 + (year % 33 + 3) / 4 + 78 + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186); gy += days / 146097 * 400; days %= 146097; if (days > 36524) { gy += (days - 1) / 36524 * 100; days = (days - 1) % 36524; if (days >= 365) days++; } gy += days / 1461 * 4; days %= 1461; if (days > 365) { gy += (days - 1) / 365; days = (days - 1) % 365; } var gd = days + 1; var leap = gy % 4 == 0 && gy % 100 != 0 || gy % 400 == 0; var monthDays = new[] { 31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 }; var gm = 1; while (gd > monthDays[gm - 1]) { gd -= monthDays[gm - 1]; gm++; } return $"{gy:0000}-{gm:00}-{gd:00}"; }
}

public static class Printer
{
    public static List<string> List() { try { using var process = Process.Start(new ProcessStartInfo("powershell.exe", "-NoProfile -Command \"Get-Printer | Select-Object -ExpandProperty Name\"") { RedirectStandardOutput = true, UseShellExecute = false, CreateNoWindow = true }); var output = process?.StandardOutput.ReadToEnd() ?? ""; process?.WaitForExit(5000); return output.Split(Environment.NewLine, StringSplitOptions.RemoveEmptyEntries | StringSplitOptions.TrimEntries).Distinct(StringComparer.OrdinalIgnoreCase).OrderBy(x => x).ToList(); } catch { return new(); } }
    public static bool Write(PrinterConfig printer, Dictionary<string, object?> record)
    {
        try
        {
            var encoding = Encoding.GetEncoding(printer.Encoding);
            var lines = new List<string>();
            if (!string.IsNullOrWhiteSpace(printer.Company)) lines.Add(printer.Company.Trim());
            if (!string.IsNullOrWhiteSpace(printer.Title)) lines.Add(printer.Title.Trim());
            if (lines.Count > 0) lines.Add(new string('-', 32));
            lines.Add($"نام و نام خانوادگی: {record.GetValueOrDefault("full_name")}");
            lines.Add($"نوع غذا: {record.GetValueOrDefault("food_type")}");
            lines.Add($"تاریخ غذا: {record.GetValueOrDefault("food_date", record.GetValueOrDefault("attendance_date"))}");
            lines.Add($"ساعت تردد: {record.GetValueOrDefault("attendance_time")}");
            if (!string.IsNullOrWhiteSpace(printer.Footer)) lines.Add(printer.Footer.Trim());
            var payload = encoding.GetBytes("\x1b@" + string.Join("\n", lines) + "\n\n" + (printer.Cut ? "\x1dV\x00" : ""));
            if (printer.Mode == "socket")
            {
                using var client = new TcpClient();
                if (!client.ConnectAsync(printer.Host, printer.Port).Wait(TimeSpan.FromSeconds(5)))
                    throw new TimeoutException($"اتصال به چاپگر {printer.Host}:{printer.Port} در ۵ ثانیه برقرار نشد.");
                using var stream = client.GetStream();
                stream.WriteTimeout = 5000;
                stream.Write(payload, 0, payload.Length);
                stream.Flush();
                return true;
            }
            return RawWindowsPrint(printer.Name, printer.Title, payload);
        }
        catch (Exception error)
        {
            Console.Error.WriteLine($"[food-ticket] print failed: {error.Message}");
            return false;
        }
    }
    private static bool RawWindowsPrint(string name, string title, byte[] data)
    {
        if (!OpenPrinter(name, out var handle, IntPtr.Zero)) return false;
        try
        {
            var info = new DOCINFO { pDocName = title, pDataType = "RAW" };
            if (StartDocPrinter(handle, 1, ref info) == 0) return false;
            var pageStarted = StartPagePrinter(handle);
            var written = 0;
            var writeOk = pageStarted && WritePrinter(handle, data, data.Length, out written) && written == data.Length;
            var pageEnded = pageStarted && EndPagePrinter(handle);
            var documentEnded = EndDocPrinter(handle);
            return pageStarted && writeOk && pageEnded && documentEnded;
        }
        finally { ClosePrinter(handle); }
    }
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)] private struct DOCINFO { public string pDocName; public string? pOutputFile; public string pDataType; }
    [DllImport("winspool.drv", CharSet = CharSet.Unicode, SetLastError = true)] private static extern bool OpenPrinter(string name, out IntPtr handle, IntPtr defaults);
    [DllImport("winspool.drv", SetLastError = true)] private static extern bool ClosePrinter(IntPtr handle);
    [DllImport("winspool.drv", CharSet = CharSet.Unicode, SetLastError = true)] private static extern int StartDocPrinter(IntPtr handle, int level, ref DOCINFO info);
    [DllImport("winspool.drv", SetLastError = true)] private static extern bool EndDocPrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError = true)] private static extern bool StartPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError = true)] private static extern bool EndPagePrinter(IntPtr handle);
    [DllImport("winspool.drv", SetLastError = true)] private static extern bool WritePrinter(IntPtr handle, byte[] data, int length, out int written);
}

public static class Browse
{
    public static Task<string?> FileAsync() => Run(() => { using var dialog = new WinForms.OpenFileDialog { Filter = "Access database|*.mdb;*.accdb|All files|*.*", CheckFileExists = true }; return dialog.ShowDialog() == WinForms.DialogResult.OK ? dialog.FileName : null; });
    public static Task<string?> FolderAsync() => Run(() => { using var dialog = new WinForms.FolderBrowserDialog(); return dialog.ShowDialog() == WinForms.DialogResult.OK ? dialog.SelectedPath : null; });
    private static Task<string?> Run(Func<string?> action)
    {
        var source = new TaskCompletionSource<string?>(TaskCreationOptions.RunContinuationsAsynchronously);
        var thread = new Thread(() =>
        {
            try
            {
                WinForms.Application.SetHighDpiMode(WinForms.HighDpiMode.SystemAware);
                WinForms.Application.EnableVisualStyles();
                WinForms.Application.SetCompatibleTextRenderingDefault(false);
                source.SetResult(action());
            }
            catch (Exception error)
            {
                source.SetException(error);
            }
        }) { IsBackground = true };
        thread.SetApartmentState(ApartmentState.STA);
        thread.Start();
        return source.Task;
    }
}

public static class Xlsx
{
    public static void Write(string filename, List<Dictionary<string, object?>> rows)
    {
        var headers = new[] { "تاریخ تردد", "ساعت تردد", "کد پرسنلی", "کد ملی", "نام", "نوع غذا", "تاریخ غذا", "تاریخ رزرو", "ساعت رزرو", "نتیجه", "وضعیت چاپ", "حذف از SOURCE_TABLE" };
        var keys = new[] { "attendance_date", "attendance_time", "pc_code", "national_code", "full_name", "food_type", "food_date", "reserved_date", "reserved_time", "result", "print_status", "source_deleted" };
        static string Escape(string value) => System.Security.SecurityElement.Escape(value) ?? "";
        static string Column(int value) { var result = ""; while (value > 0) { value--; result = (char)('A' + value % 26) + result; value /= 26; } return result; }
        var rowsXml = new StringBuilder(); for (var i = 0; i <= rows.Count; i++) { var values = i == 0 ? headers.Cast<object?>().ToArray() : keys.Select(key => rows[i - 1].GetValueOrDefault(key)).ToArray(); rowsXml.Append($"<row r=\"{i + 1}\">"); for (var c = 0; c < values.Length; c++) rowsXml.Append($"<c r=\"{Column(c + 1)}{i + 1}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">{Escape(Convert.ToString(values[c]) ?? "")}</t></is></c>"); rowsXml.Append("</row>"); }
        using var archive = System.IO.Compression.ZipFile.Open(filename, System.IO.Compression.ZipArchiveMode.Create); Add(archive, "[Content_Types].xml", "<?xml version=\"1.0\"?><Types xmlns=\"http://schemas.openxmlformats.org/package/2006/content-types\"><Default Extension=\"rels\" ContentType=\"application/vnd.openxmlformats-package.relationships+xml\"/><Default Extension=\"xml\" ContentType=\"application/xml\"/><Override PartName=\"/xl/workbook.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml\"/><Override PartName=\"/xl/worksheets/sheet1.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/></Types>"); Add(archive, "_rels/.rels", "<?xml version=\"1.0\"?><Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\"><Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument\" Target=\"xl/workbook.xml\"/></Relationships>"); Add(archive, "xl/workbook.xml", "<?xml version=\"1.0\"?><workbook xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\"><sheets><sheet name=\"گزارش فیش غذا\" sheetId=\"1\" r:id=\"rId1\"/></sheets></workbook>"); Add(archive, "xl/_rels/workbook.xml.rels", "<?xml version=\"1.0\"?><Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\"><Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet1.xml\"/></Relationships>"); Add(archive, "xl/worksheets/sheet1.xml", $"<?xml version=\"1.0\"?><worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\"><sheetData>{rowsXml}</sheetData></worksheet>");
    }
    private static void Add(System.IO.Compression.ZipArchive archive, string name, string content) { using var writer = new StreamWriter(archive.CreateEntry(name).Open(), Encoding.UTF8); writer.Write(content); }
}
