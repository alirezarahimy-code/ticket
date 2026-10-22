param(
    [string] $ComputerName = ''
)

$ErrorActionPreference = 'Stop'
$utf8Encoding = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false
[Console]::OutputEncoding = $utf8Encoding
$script:CimSession = $null
$script:InventoryCollectorStatus = @{}

function Get-InventoryRows {
    param(
        [Parameter(Mandatory = $true)][string] $ClassName,
        [string] $Namespace = 'root/cimv2',
        [string[]] $Properties = @('*'),
        [string] $Filter = ''
    )

    $statusKey = $Namespace + '|' + $ClassName
    try {
        $options = @{
            ClassName = $ClassName
            Namespace = $Namespace
            OperationTimeoutSec = 30
            ErrorAction = 'Stop'
        }
        if (-not [string]::IsNullOrWhiteSpace($Filter)) {
            $options.Filter = $Filter
        }
        if ($script:CimSession) {
            $options.CimSession = $script:CimSession
        }
        $items = Get-CimInstance @options
        if ($Properties -and $Properties -notcontains '*') {
            $items = $items | Select-Object -Property $Properties
        }
        $rows = @($items)
        $script:InventoryCollectorStatus[$statusKey] = @{ ok = $true; count = $rows.Count }
        return $rows
    } catch {
        $script:InventoryCollectorStatus[$statusKey] = @{ ok = $false; count = 0 }
        throw
    }
}

function Get-OptionalInventoryRows {
    param(
        [Parameter(Mandatory = $true)][string] $ClassName,
        [string] $Namespace = 'root/cimv2',
        [string[]] $Properties = @('*'),
        [string] $Filter = ''
    )
    try {
        return @(Get-InventoryRows -ClassName $ClassName -Namespace $Namespace -Properties $Properties -Filter $Filter)
    } catch {
        return @()
    }
}

function Invoke-RegistryMethod {
    param(
        [Parameter(Mandatory = $true)][string] $MethodName,
        [Parameter(Mandatory = $true)][hashtable] $Arguments
    )
    $options = @{
        Namespace = 'root/default'
        ClassName = 'StdRegProv'
        MethodName = $MethodName
        Arguments = $Arguments
        ErrorAction = 'Stop'
    }
    if ($script:CimSession) {
        $options.CimSession = $script:CimSession
    }
    return Invoke-CimMethod @options
}

function Get-InstalledSoftware {
    $software = @()
    $clock = [System.Diagnostics.Stopwatch]::StartNew()
    $hklm = [uint32] 2147483650
    $paths = @(
        'SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall',
        'SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall'
    )
    foreach ($path in $paths) {
        try {
            $keys = Invoke-RegistryMethod -MethodName 'EnumKey' -Arguments @{ hDefKey = $hklm; sSubKeyName = $path }
            foreach ($key in @($keys.sNames)) {
                if ($software.Count -ge 500 -or $clock.Elapsed.TotalSeconds -gt 45) { break }
                $subKey = $path + '\' + [string] $key
                $nameResult = Invoke-RegistryMethod -MethodName 'GetStringValue' -Arguments @{ hDefKey = $hklm; sSubKeyName = $subKey; sValueName = 'DisplayName' }
                $name = [string] $nameResult.sValue
                if ([string]::IsNullOrWhiteSpace($name)) { continue }
                $versionResult = Invoke-RegistryMethod -MethodName 'GetStringValue' -Arguments @{ hDefKey = $hklm; sSubKeyName = $subKey; sValueName = 'DisplayVersion' }
                $publisherResult = Invoke-RegistryMethod -MethodName 'GetStringValue' -Arguments @{ hDefKey = $hklm; sSubKeyName = $subKey; sValueName = 'Publisher' }
                $software += [pscustomobject]@{
                    name = $name.Trim()
                    version = [string] $versionResult.sValue
                    publisher = [string] $publisherResult.sValue
                }
            }
        } catch {
            continue
        }
    }
    return @($software)
}

try {
    if (-not [string]::IsNullOrWhiteSpace($ComputerName)) {
        $scanUser = [Environment]::GetEnvironmentVariable('ITSM_SCAN_USER')
        $scanPass = [Environment]::GetEnvironmentVariable('ITSM_SCAN_PASS')
        $credential = $null
        if (-not [string]::IsNullOrWhiteSpace($scanUser) -and $null -ne $scanPass) {
            $securePassword = ConvertTo-SecureString $scanPass -AsPlainText -Force
            $credential = New-Object -TypeName System.Management.Automation.PSCredential -ArgumentList $scanUser, $securePassword
        }
        $sessionErrors = @()
        # ترتیب پروتکل‌ها از متغیر محیطی ITSM_SCAN_PROTOCOLS خوانده می‌شود (مثلاً «Wsman,Dcom»)
        # تا اگر DCOM روی شبکه بسته است، مسیر WinRM اول امتحان شود.
        $protocolEnv = [Environment]::GetEnvironmentVariable('ITSM_SCAN_PROTOCOLS')
        $protocolList = @()
        if (-not [string]::IsNullOrWhiteSpace($protocolEnv)) {
            $protocolList = @($protocolEnv -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ -in @('Dcom', 'Wsman') })
        }
        if ($protocolList.Count -eq 0) { $protocolList = @('Dcom', 'Wsman') }
        foreach ($protocol in $protocolList) {
            $candidate = $null
            try {
                $sessionOptions = New-CimSessionOption -Protocol $protocol
                $sessionArgs = @{ ComputerName = $ComputerName; SessionOption = $sessionOptions; OperationTimeoutSec = 30; ErrorAction = 'Stop' }
                if ($credential) { $sessionArgs.Credential = $credential }
                $candidate = New-CimSession @sessionArgs
                # New-CimSession is lazy: run a tiny query to be sure the session really works
                $null = Get-CimInstance -CimSession $candidate -ClassName Win32_ComputerSystem -OperationTimeoutSec 20 -ErrorAction Stop
                $script:CimSession = $candidate
                break
            } catch {
                $sessionErrors += ($protocol + ': ' + $_.Exception.Message)
                if ($candidate) { Remove-CimSession -CimSession $candidate -ErrorAction SilentlyContinue; $candidate = $null }
            }
        }
        if (-not $script:CimSession) {
            throw ('ALL_PROTOCOLS_FAILED: ' + ($sessionErrors -join ' | '))
        }
    }

    $computer = Get-InventoryRows -ClassName 'Win32_ComputerSystem' -Properties @('Manufacturer', 'Model', 'UserName', 'TotalPhysicalMemory', 'Domain', 'Name')
    $bios = Get-OptionalInventoryRows -ClassName 'Win32_BIOS' -Properties @('SerialNumber', 'Manufacturer', 'SMBIOSBIOSVersion', 'ReleaseDate')
    $processor = Get-OptionalInventoryRows -ClassName 'Win32_Processor' -Properties @('Name', 'NumberOfCores', 'NumberOfLogicalProcessors', 'ProcessorId', 'MaxClockSpeed', 'CurrentClockSpeed', 'Manufacturer', 'SocketDesignation', 'L2CacheSize', 'L3CacheSize', 'DataWidth')
    $os = Get-OptionalInventoryRows -ClassName 'Win32_OperatingSystem' -Properties @('Caption', 'Version', 'BuildNumber', 'OSArchitecture', 'SerialNumber', 'InstallDate', 'WindowsDirectory', 'SystemDrive', 'LastBootUpTime')
    $motherboard = Get-OptionalInventoryRows -ClassName 'Win32_BaseBoard' -Properties @('Manufacturer', 'Product', 'SerialNumber', 'Version')
    $systemProduct = Get-OptionalInventoryRows -ClassName 'Win32_ComputerSystemProduct' -Properties @('Vendor', 'Name', 'Version', 'IdentifyingNumber', 'UUID')
    $enclosure = Get-OptionalInventoryRows -ClassName 'Win32_SystemEnclosure' -Properties @('Manufacturer', 'Model', 'SerialNumber', 'SMBIOSAssetTag', 'ChassisTypes')
    $memory = Get-OptionalInventoryRows -ClassName 'Win32_PhysicalMemory' -Properties @('BankLabel', 'Capacity', 'Manufacturer', 'PartNumber', 'SerialNumber', 'Speed', 'ConfiguredClockSpeed', 'DataWidth', 'TotalWidth', 'DeviceLocator', 'FormFactor', 'MemoryType', 'SMBIOSMemoryType')
    $memoryArrays = Get-OptionalInventoryRows -ClassName 'Win32_PhysicalMemoryArray' -Properties @('Location', 'Use', 'MemoryDevices', 'MaxCapacity', 'MaxCapacityEx', 'MemoryErrorCorrection')
    $physicalDisks = Get-OptionalInventoryRows -ClassName 'Win32_DiskDrive' -Properties @('Index', 'Model', 'Size', 'SerialNumber', 'MediaType', 'InterfaceType', 'FirmwareRevision', 'BytesPerSector', 'Partitions', 'Status')
    $graphics = Get-OptionalInventoryRows -ClassName 'Win32_VideoController' -Properties @('Name', 'AdapterRAM', 'DriverVersion', 'VideoModeDescription')
    $sound = Get-OptionalInventoryRows -ClassName 'Win32_SoundDevice' -Properties @('Name', 'Manufacturer', 'PNPDeviceID')
    $opticalDrives = Get-OptionalInventoryRows -ClassName 'Win32_CDROMDrive' -Properties @('Caption', 'Drive', 'Manufacturer')
    $networkAdapters = Get-OptionalInventoryRows -ClassName 'Win32_NetworkAdapter' -Properties @('Name', 'MACAddress', 'AdapterType', 'Speed', 'NetConnectionStatus', 'PhysicalAdapter', 'Manufacturer')
    $modems = Get-OptionalInventoryRows -ClassName 'Win32_POTSModem' -Properties @('Name', 'Manufacturer', 'Status')
    $users = Get-OptionalInventoryRows -ClassName 'Win32_UserProfile' -Properties @('LocalPath', 'SID', 'LastUseTime', 'Loaded')
    $disks = Get-OptionalInventoryRows -ClassName 'Win32_LogicalDisk' -Properties @('DeviceID', 'VolumeName', 'FileSystem', 'Size', 'FreeSpace', 'DriveType') | Where-Object { [int] $_.DriveType -eq 3 }
    $network = Get-OptionalInventoryRows -ClassName 'Win32_NetworkAdapterConfiguration' -Properties @('Description', 'MACAddress', 'IPAddress', 'IPSubnet', 'DefaultIPGateway', 'DNSDomain') | Where-Object { $_.IPAddress -or $_.MACAddress }
    $printers = Get-OptionalInventoryRows -ClassName 'Win32_Printer' -Properties @('Name', 'DriverName', 'PortName', 'Default', 'Network', 'Shared')
    $pnp = Get-OptionalInventoryRows -ClassName 'Win32_PnPEntity' -Properties @('Name', 'Manufacturer', 'PNPClass', 'DeviceID', 'Status') -Filter "PNPClass='Image' OR PNPClass='Monitor' OR PNPClass='Keyboard' OR PNPClass='Mouse'"
    $scanners = @($pnp | Where-Object { $_.PNPClass -eq 'Image' })
    $monitorsPnp = @($pnp | Where-Object { $_.PNPClass -eq 'Monitor' })
    $keyboards = @($pnp | Where-Object { $_.PNPClass -eq 'Keyboard' })
    $mice = @($pnp | Where-Object { $_.PNPClass -eq 'Mouse' })
    $monitors = Get-OptionalInventoryRows -ClassName 'Win32_DesktopMonitor' -Properties @('Name', 'MonitorManufacturer', 'MonitorType', 'ScreenHeight', 'ScreenWidth')
    $antivirus = Get-OptionalInventoryRows -ClassName 'AntiVirusProduct' -Namespace 'root/SecurityCenter2' -Properties @('displayName', 'productState', 'pathToSignedProductExe')
    $software = Get-InstalledSoftware
    # اطلاعات تکمیلیِ قابل‌دریافت با WMI/CIM؛ هر کلاس اختیاری است تا نبودن آن در
    # نسخه‌های قدیمی ویندوز، نسخه‌های Server یا مقصدهای محدودشده، اسکن پایه را متوقف نکند.
    $allDevices = @(Get-OptionalInventoryRows -ClassName 'Win32_PnPEntity' -Properties @('Name', 'Manufacturer', 'PNPClass', 'DeviceID', 'Status', 'ConfigManagerErrorCode', 'Service') | Select-Object -First 3000)
    $drivers = @(Get-OptionalInventoryRows -ClassName 'Win32_PnPSignedDriver' -Properties @('DeviceName', 'DeviceID', 'DriverProviderName', 'DriverVersion', 'DriverDate', 'InfName', 'IsSigned') | Select-Object -First 2000)
    $services = @(Get-OptionalInventoryRows -ClassName 'Win32_Service' -Properties @('Name', 'DisplayName', 'State', 'Status', 'StartMode', 'StartName', 'ProcessId') | Select-Object -First 1500)
    $processes = @(Get-OptionalInventoryRows -ClassName 'Win32_Process' -Properties @('Name', 'ProcessId', 'ExecutablePath', 'CreationDate') | Select-Object -First 1500)
    $startup = @(Get-OptionalInventoryRows -ClassName 'Win32_StartupCommand' -Properties @('Name', 'Location', 'User', 'Caption', 'Description') | Select-Object -First 500)
    $shares = @(Get-OptionalInventoryRows -ClassName 'Win32_Share' -Properties @('Name', 'Path', 'Type', 'Description') | Select-Object -First 1000)
    $accounts = @(Get-OptionalInventoryRows -ClassName 'Win32_UserAccount' -Properties @('Name', 'Domain', 'SID', 'Disabled', 'Lockout', 'LocalAccount', 'Status') | Select-Object -First 2000)
    $updates = @(Get-OptionalInventoryRows -ClassName 'Win32_QuickFixEngineering' -Properties @('HotFixID', 'Description', 'InstalledBy', 'InstalledOn') | Select-Object -First 1000)
    # فقط نام و محدودهٔ متغیرهای محیطی نگه‌داری می‌شود؛ مقادیر ممکن است رمز/توکن باشند.
    $environment = @(Get-OptionalInventoryRows -ClassName 'Win32_Environment' -Properties @('Name', 'UserName', 'SystemVariable') | Select-Object -First 1000)
    $firewall = Get-OptionalInventoryRows -ClassName 'MSFT_NetFirewallProfile' -Namespace 'root/StandardCimv2' -Properties @('Name', 'Enabled', 'DefaultInboundAction', 'DefaultOutboundAction')
    $firewalls = Get-OptionalInventoryRows -ClassName 'FirewallProduct' -Namespace 'root/SecurityCenter2' -Properties @('displayName', 'productState', 'pathToSignedProductExe')
    $antispyware = Get-OptionalInventoryRows -ClassName 'AntiSpywareProduct' -Namespace 'root/SecurityCenter2' -Properties @('displayName', 'productState', 'pathToSignedProductExe')
    $storageDevices = Get-OptionalInventoryRows -ClassName 'MSFT_PhysicalDisk' -Namespace 'root/Microsoft/Windows/Storage' -Properties @('FriendlyName', 'SerialNumber', 'MediaType', 'HealthStatus', 'OperationalStatus', 'Size', 'BusType', 'FirmwareVersion')
    $storageReliability = Get-OptionalInventoryRows -ClassName 'MSFT_StorageReliabilityCounter' -Namespace 'root/Microsoft/Windows/Storage' -Properties @('Temperature', 'TemperatureMax', 'Wear', 'ReadErrorsTotal', 'ReadErrorsUncorrected', 'WriteErrorsTotal', 'WriteErrorsUncorrected', 'PowerOnHours')
    $smartStatus = Get-OptionalInventoryRows -ClassName 'MSStorageDriver_FailurePredictStatus' -Namespace 'root/wmi' -Properties @('InstanceName', 'PredictFailure', 'Reason')
    $monitorIds = Get-OptionalInventoryRows -ClassName 'WmiMonitorID' -Namespace 'root/wmi' -Properties @('InstanceName', 'ManufacturerName', 'ProductCodeID', 'SerialNumberID', 'UserFriendlyName', 'UserFriendlyNameLength', 'SerialNumberIDLength', 'Active')

    if ($computer.Count -eq 0) {
        throw 'NO_COMPUTER_INFO: target returned no computer information (check WMI/DCOM access and firewall).'
    }
    $result = [ordered]@{
        computer = @($computer)
        collection_status = $script:InventoryCollectorStatus
        system_product = @($systemProduct)
        enclosure = @($enclosure)
        bios = @($bios)
        processor = @($processor)
        os = @($os)
        motherboard = @($motherboard)
        memory_modules = @($memory)
        memory_arrays = @($memoryArrays)
        physical_disks = @($physicalDisks)
        graphics = @($graphics)
        sound = @($sound)
        optical_drives = @($opticalDrives)
        network_adapters = @($networkAdapters)
        modems = @($modems)
        os_users = @($users)
        disks = @($disks)
        network = @($network)
        printers = @($printers)
        scanners = @($scanners)
        monitors = @($monitors)
        monitors_pnp = @($monitorsPnp)
        keyboards = @($keyboards)
        mice = @($mice)
        antivirus = @($antivirus)
        firewall_products = @($firewalls)
        antispyware_products = @($antispyware)
        firewall_profiles = @($firewall)
        pnp_devices = @($allDevices)
        signed_drivers = @($drivers)
        services = @($services)
        processes = @($processes)
        startup_items = @($startup)
        shares = @($shares)
        user_accounts = @($accounts)
        windows_updates = @($updates)
        environment_names = @($environment)
        storage_devices = @($storageDevices)
        storage_reliability = @($storageReliability)
        smart_status = @($smartStatus)
        monitor_ids = @($monitorIds)
        software = @($software)
    }
    $result | ConvertTo-Json -Depth 8 -Compress
} catch {
    [Console]::Error.WriteLine($_.Exception.Message)
    exit 1
} finally {
    if ($script:CimSession) {
        Remove-CimSession -CimSession $script:CimSession -ErrorAction SilentlyContinue
    }
}
