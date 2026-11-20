// قرارداد استقرار نسخهٔ نهایی ۱٫۶۳٫۳ — اجرا: node tests/js/food-absent-lunch-tab.test.js
'use strict';
const fs = require('fs'), path = require('path'), assert = require('assert');
const root = path.resolve(__dirname, '../..');
const read = f => fs.readFileSync(path.join(root, f), 'utf8');
const html = read('food-ticket-web/index.html');
const php = read('food-ticket.php');
const guide = read('help-guide.php');
const bot = read('chatbot.php');
// فقط استایل/ماژول تزریقشدهٔ ۱٫۶۳٫۳ ملاک است؛ خود پنل نمودارها و برچسب «وعده» دیگری هم دارد.
const cssStart = html.indexOf('/* ۱٫۶۳٫۳');
const injected = html.slice(cssStart, html.indexOf('</style>', cssStart));
const moduleCode = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(m => m[1]).find(x => x.includes('window.AbsentLunchReport=')) || '';
const bundle = injected + moduleCode;
const A = (c, m) => assert(c, m);

A(html.includes("['absent-lunch','غایبین ناهار']"), 'تب تازه در REPORT_TABS');
A(html.includes("['reconciliation','تطبیق خرید و توزیع'],['absent-lunch','غایبین ناهار']]"), 'آرایهٔ REPORT_TABS معتبر است');
A(html.includes("tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html()"), 'reportView محتوای تب را رندر میکند');
A(html.includes('window.AbsentLunchReport.load(false)'), 'render لودر تب را صدا میزند');
A(!html.includes('option value="absent"'), 'گزینهٔ قدیمی نوع گزارش حذف شده');
A(!html.includes("['absent-report','غایبین ناهار']"), 'نمای مستقل اضافه نشده');
A(html.includes("state.reportTab='absent-lunch'"), 'حالت ذخیرهشدهٔ قدیمی هدایت میشود');
A(html.includes("PANEL_VERSION='1.37.60'") && php.includes("FOOD_TICKET_PANEL_VERSION = '1.37.60'"), 'نسخههای پنل هماهنگاند');
A(bundle.includes('window.AbsentLunchReport={html:html,load:load'), 'ماژول تب تعریف شده');
A(bundle.includes('function mapped(items,orgMap)'), 'نگاشت بکاند');
A(bundle.includes('window.FoodOrgGroups.groupOf(map,r)'), 'گروهبندی معاونت');
A(bundle.includes('if(active())load(false)'), 'استارتاپ امن');

// صفحهها جدا: ۵ چیپ فقط در گزارشگیری
A(html.includes('alr-classic-chips'), 'پنج چیپ داخل فرم گزارشگیری');
A((html.match(/data-classic-chip=/g) || []).length >= 5, 'پنج چیپ آمار');
A(html.includes('alr-chips'), 'چیپهای صفحهٔ غایبین');

// سه تب و فیلدها
A(['فهرست یکجا','بر اساس معاونت','تفکیک و جزئیات'].every(t => bundle.includes(t)), 'نام سه زیرنما');
A(bundle.includes("detail:['row','pc','first','last','dep','food','jal','status']"), 'فیلدهای تفکیک و جزئیات');
A(bundle.includes("flat:['row','pc','first','last','dep','count','jal','status']"), 'تاریخ غیبت در فهرست یکجا');
A(bundle.includes("dept:['row','pc','first','last','dep','count','status']"), 'تعداد غیبت در بر اساس معاونت');
A(!bundle.includes("label:'وعده'"), 'ستون وعده حذف شده');
A(bundle.includes("label:'نوع غذا'"), 'نوع غذا = نام غذای سفارش');
A(bundle.includes('alr-group-head h4 b'), 'N نفر · M وعده بولد چسبیده');

// چارت + تیکها
A(bundle.includes('data-alr-all'), 'تیک «همه معاونتها»');
A(bundle.includes('data-alr-dep-check='), 'تیک کنار نام هر معاونت');
A(bundle.includes('data-alr-dep-toggle='), 'کلیک روی نام/legend');
A(bundle.includes('function syncIndeterminate('), 'حالت میانی indeterminate');
A(bundle.includes('function rerender('), 'رندر بعد از تغییر تیکها');
A(bundle.includes('.alr-row.off{opacity:.38}'), 'معاونتهای انتخابنشده کمرنگ');
A(bundle.includes('شروع بازه: ') && bundle.includes('پایان بازه: '), 'محور صریح شروع ← پایان بازه');
A(bundle.includes('alr-mean') && bundle.includes('alr-grid'), 'grid و میانگین خطچین');
A(!bundle.includes('<svg'), 'چارت SVG کشیده نیست');

// چاپ خالص
A(bundle.includes('body>*:not(#alrPrint){display:none!important}'), 'چاپ فقط لیست');
A(!bundle.includes('absent-lunch-printing'), 'کلاس printing حذف شده');
A(!bundle.includes("'afterprint'"), 'afterprint حذف شده');
A(bundle.includes('<h1>گزارش غایبین ناهار</h1>'), 'عنوان گزارش');
A(bundle.includes('beforeprint'), 'beforeprint فقط محتوا را تازه میکند');

// XLSX و بکاند
A(bundle.includes('export_view_xlsx'), 'XLSX واقعی');
A(bundle.includes('width:100%!important') && bundle.includes('table-layout:fixed'), 'لیست تمامعرض');
A(bundle.includes('function percents('), 'ستونها خودکار');
A(guide.includes('تفکیک و جزئیات') && guide.includes('همه معاونتها'), 'راهنما بهروز');
A(bot.includes("'food_absent_lunch_report'") && bot.includes("'رفتن به گزارش غایبین ناهار'"), 'چتبات بهروز');
A(php.includes('SELECT id FROM users WHERE employee_number = ?') && php.includes("'declared_absent'"), 'بکاند unchanged');
A(php.includes("COALESCE(meal_type, 'lunch') = 'lunch'"), 'بکاند فقط ناهار');
console.log('food-absent-lunch-tab (final 1.63.3): OK');
