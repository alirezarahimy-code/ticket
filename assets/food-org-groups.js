/**
 * ۱.۶۳.۴ — «نمایش بر اساس معاونت» برای گزارش‌های غذا.
 *
 * چهار گزارش (گزارش تجمیعی، غذای تحویلی، سفارش‌های امروز، غایبین نهار) هر کدام
 * فهرستی از افراد را نشان می‌دهند. با این ماژول همان فهرست یک‌بار دیگر — این‌بار
 * زیر سرشاخهٔ سازمانی هر نفر — چیده می‌شود:
 *
 *   • سرگروه فقط «معاونت» است (یا مدیریت مستقل / واحد مستقل (نمایندگی) / شرکت دوم).
 *   • مدیر اداره و کارشناس‌های زیر آن معاونت همه در همان گروه فهرست می‌شوند؛
 *     نام ادارهٔ میانی فقط در ستون «واحد» هر ردیف می‌آید، نه به‌عنوان سرگروه.
 *   • MANAGEMENT و LIAISON و COMPANY هر سه صریحاً نام‌گذاری می‌شوند تا هیچ فردی
 *     در گزارش گم نشود.
 *
 * وابستگی بیرونی ندارد؛ دادهٔ نگاشت از endpoint «food-menu/org-map» می‌آید.
 */
(function (global) {
  'use strict';

  var GROUPS_URL = 'food-menu/org-map';
  var cache = null;      // پاسخ آخرین دریافت موفق
  var pending = null;    // Promise در جریان
  var cacheAt = 0;
  var CACHE_MS = 60000;  // یک دقیقه کافی است؛ چارت سازمانی لحظه‌ای عوض نمی‌شود

  /** برچسب فارسی نوع سرشاخه — عیناً همان واژه‌هایی که کاربر خواسته. */
  function typeLabel(type) {
    switch (type) {
      case 'deputy': return 'معاونت';
      case 'management': return 'مدیریت مستقل';
      case 'liaison': return 'واحد مستقل (نمایندگی)';
      case 'company': return 'شرکت دوم';
      case 'ceo': return 'مدیرعامل';
      default: return 'بدون معاونت';
    }
  }

  /** ترتیب گروه‌ها: معاونت‌ها، بعد مدیریت مستقل، بعد نمایندگی‌ها، بعد شرکت دوم. */
  function typeOrder(type) {
    switch (type) {
      case 'deputy': return 0;
      case 'management': return 1;
      case 'liaison': return 2;
      case 'company': return 3;
      case 'ceo': return 4;
      default: return 5;
    }
  }

  /**
   * نگاشت سازمانی را می‌گیرد (با کش). هرگز reject نمی‌شود؛ در خطا null می‌دهد
   * تا گزارش اصلی بدون گروه‌بندی همچنان دیده شود.
   */
  function load(api) {
    var now = Date.now();
    if (cache && now - cacheAt < CACHE_MS) {
      return Promise.resolve(cache);
    }
    if (pending) {
      return pending;
    }
    if (typeof api !== 'function') {
      return Promise.resolve(null);
    }
    pending = api('/api/' + GROUPS_URL)
      .then(function (payload) {
        if (!payload || !payload.people) {
          return null;
        }
        cache = payload;
        cacheAt = Date.now();
        return cache;
      })
      .catch(function () {
        return null;
      })
      .then(function (result) {
        pending = null;
        return result;
      });
    return pending;
  }

  /** کش را دور بریز (مثلاً وقتی کاربر چارت را عوض کرده و روی «تازه‌سازی» می‌زند). */
  function invalidate() {
    cache = null;
    cacheAt = 0;
    pending = null;
  }

  /**
   * کلیدهای شناسایی یک ردیف گزارش، به ترتیب اعتبار.
   * گزارش‌های مختلف فیلدهای متفاوتی دارند؛ هر کدام که بود پذیرفته می‌شود.
   */
  function keysForRow(row) {
    if (!row) {
      return [];
    }
    var keys = [];
    var uid = row.user_id != null ? Number(row.user_id) : (row.employee_id != null ? Number(row.employee_id) : 0);
    if (uid > 0) {
      keys.push('uid:' + uid);
    }
    var nat = digits(row.national_code != null ? row.national_code : row.nat);
    if (nat !== '') {
      keys.push('nat:' + nat);
    }
    var pc = digits(row.employee_number != null ? row.employee_number : row.pc);
    if (pc !== '') {
      keys.push('pc:' + pc);
    }
    return keys;
  }

  /** ارقام فارسی/عربی را به لاتین برمی‌گرداند تا کد ملی و پرسنلی تطبیق بخورند. */
  function digits(value) {
    return String(value == null ? '' : value)
      .replace(/[۰-۹]/g, function (ch) { return String(ch.charCodeAt(0) - 0x06F0); })
      .replace(/[٠-٩]/g, function (ch) { return String(ch.charCodeAt(0) - 0x0660); })
      .replace(/\D/g, '')
      .replace(/^0+(?=\d)/, '');
  }

  /** نگاشت یک ردیف به گروهش؛ اگر پیدا نشد گروه «بدون معاونت سازمانی». */
  function groupOf(map, row) {
    var none = { group: 'none', label: 'بدون معاونت سازمانی', type: 'none', path: '' };
    if (!map || !map.people) {
      return none;
    }
    var keys = keysForRow(row);
    for (var i = 0; i < keys.length; i++) {
      if (map.people[keys[i]]) {
        return map.people[keys[i]];
      }
    }
    return none;
  }

  /**
   * ردیف‌ها را به گروه‌ها می‌شکند.
   * @return {Array<{key,label,type,path,rows:Array}>}
   */
  function split(map, rows) {
    var buckets = {};
    (rows || []).forEach(function (row) {
      var group = groupOf(map, row);
      var key = group.group || 'none';
      if (!buckets[key]) {
        buckets[key] = {
          key: key,
          label: group.label || 'بدون معاونت سازمانی',
          type: group.type || 'none',
          rows: [],
        };
      }
      // مسیر (اداره ← معاونت) برای هر فرد جداگانه است، پس روی ردیف نگه می‌داریم.
      row.__orgPath = group.path || '';
      row.__orgLabel = group.label || 'بدون معاونت سازمانی';
      row.__orgType = group.type || 'none';
      buckets[key].rows.push(row);
    });
    return Object.keys(buckets)
      .map(function (key) { return buckets[key]; })
      .sort(function (a, b) {
        var byType = typeOrder(a.type) - typeOrder(b.type);
        if (byType !== 0) {
          return byType;
        }
        return String(a.label).localeCompare(String(b.label), 'fa');
      });
  }

  /**
   * سربرگ گروه — دقیقاً به سبک «نمایش بر اساس منو».
   *
   * خواستهٔ کاربر: «کافی تیترش اسم معاونت باشه و بینشون دیگه هیچی نباشه …
   * مثل نمایش بر اساس منو.» پس نه برچسب نوع، نه مسیر اداره، نه چیپ خلاصه:
   * فقط نام ریشه + شمار، بعد مستقیم فهرست اعضا.
   *
   * واحد مستقل هم فقط نام خودش را می‌نویسد («واحد بازرسی»)، نه «واحد مستقل
   * (نمایندگی): واحد بازرسی».
   */
  function groupHeaderHtml(group, options) {
    options = options || {};
    var count = options.uniquePeople ? uniquePeopleCount(group.rows) : group.rows.length;
    var countLabel = options.unitLabel || 'نفر';
    var breakdown = options.foodKey ? foodBreakdown(group.rows, options.foodKey) : '';
    return '<div class="fm-org-group-head">'
      + '<span class="fm-org-group-title">' + escapeHtml(group.label) + '</span>'
      + '<span class="fm-org-group-count">' + faNum(count) + ' ' + escapeHtml(countLabel) + '</span>'
      + '</div>'
      + (breakdown !== '' ? '<div class="fm-org-group-foods">' + breakdown + '</div>' : '');
  }

  /**
   * تفکیک غذای هم‌گروه: «چلوکباب ۴ · قیمه ۲».
   * همان چیزی که کاربر خواست: «به تفکیک ببینم اعضای معاونت‌ها چه غذاهایی
   * سفارش دادن».
   */
  function foodBreakdown(rows, key) {
    var counts = {};
    var order = [];
    (rows || []).forEach(function (row) {
      var name = String((row && row[key]) || '').trim();
      if (name === '') {
        return;
      }
      if (!counts[name]) {
        counts[name] = 0;
        order.push(name);
      }
      counts[name]++;
    });
    if (!order.length) {
      return '';
    }
    order.sort(function (a, b) {
      return counts[b] - counts[a] || a.localeCompare(b, 'fa');
    });
    return order.map(function (name) {
      return '<span class="fm-org-food">' + escapeHtml(name) + ' <b>' + faNum(counts[name]) + '</b></span>';
    }).join('');
  }

  /** شمار افراد یکتا در گروه (برای گزارش‌هایی که یک نفر چند ردیف دارد). */
  function uniquePeopleCount(rows) {
    var seen = {};
    var count = 0;
    (rows || []).forEach(function (row) {
      var keys = keysForRow(row);
      var key = keys[0] || ('name:' + (row.first_name || '') + '|' + (row.last_name || '') + '|' + (row.food_date || ''));
      if (!seen[key]) {
        seen[key] = true;
        count++;
      }
    });
    return count;
  }

  /** رقم فارسی برای شمارش‌ها — هم‌خوان با بقیهٔ پنل. */
  function faNum(value) {
    return String(value == null ? 0 : value).replace(/\d/g, function (d) {
      return '۰۱۲۳۴۵۶۷۸۹'[Number(d)];
    });
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /**
   * بدنهٔ کامل گروه‌بندی‌شده.
   * @param {object} map      پاسخ org-map
   * @param {Array}  rows     ردیف‌های گزارش
   * @param {function} table  تابعی که فهرست ردیف‌های یک گروه را به جدول تبدیل می‌کند
   * @param {object} options  { unitLabel, uniquePeople, emptyText }
   */
  function render(map, rows, table, options) {
    options = options || {};
    if (!rows || !rows.length) {
      return '<div class="fm-empty">' + escapeHtml(options.emptyText || 'در این بازه رکوردی ثبت نشده است.') + '</div>';
    }
    if (!map || !map.people) {
      return '<div class="fm-alert warn">نگاشت سازمانی در دسترس نیست؛ فهرست بدون گروه‌بندی نمایش داده می‌شود. '
        + 'برای گروه‌بندی بر اساس معاونت، دسترسی چارت سازمانی را بررسی کنید.</div>'
        + table(rows);
    }
    var groups = split(map, rows);
    // بدون نوار خلاصه و بدون چیپ: «بینشون دیگه هیچی نباشه».
    // فقط عنوان معاونت → فهرست اعضای همان معاونت، به‌ترتیب.
    var sections = groups.map(function (group) {
      return '<section class="fm-org-group">'
        + groupHeaderHtml(group, options)
        + table(group.rows)
        + '</section>';
    }).join('');
    return sections;
  }

  global.FoodOrgGroups = {
    load: load,
    invalidate: invalidate,
    render: render,
    split: split,
    keysForRow: keysForRow,
    typeLabel: typeLabel,
    groupOf: groupOf,
    escapeHtml: escapeHtml,
    faNum: faNum,
  };
}(typeof window !== 'undefined' ? window : this));
