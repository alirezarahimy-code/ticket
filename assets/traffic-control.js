(function () {
    'use strict';

    var codeField = document.getElementById('traffic-national-code');
    var hint = document.getElementById('traffic-lookup-hint');
    var quickExitForm = document.getElementById('traffic-quick-exit-form');
    var quickExitId = document.getElementById('traffic-quick-exit-id');
    if (!codeField || !hint) {
        return;
    }

    var fields = {
        full_name: document.getElementById('traffic-full-name'),
        phone: document.getElementById('traffic-phone'),
        company: document.getElementById('traffic-company'),
        meeting_with: document.getElementById('traffic-meeting-with'),
        approved_by: document.getElementById('traffic-approved-by'),
    };

    function syncNewDestinationApprover() {
        if (!fields.meeting_with || !fields.approved_by) return;
        var selected = fields.meeting_with.options[fields.meeting_with.selectedIndex];
        fields.approved_by.value = selected ? (selected.getAttribute('data-approver') || '') : '';
    }

    if (fields.meeting_with) fields.meeting_with.addEventListener('change', syncNewDestinationApprover);
    syncNewDestinationApprover();

    function setHint(html, kind) {
        hint.className = 'alert ' + (kind || 'info');
        hint.innerHTML = html;
        hint.hidden = false;
    }

    function clearHint() {
        hint.hidden = true;
        hint.innerHTML = '';
    }

    function lookup() {
        var code = codeField.value.replace(/[^0-9]/g, '');
        if (code.length !== 10) {
            clearHint();
            return;
        }
        fetch('index.php?page=traffic-control&traffic_api=lookup&code=' + encodeURIComponent(code), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.valid) {
                    setHint('کد ملی وارد شده معتبر نیست.', 'danger');
                    return;
                }
                if (!data.found) {
                    setHint('مهمان جدید — لطفاً بقیه‌ی اطلاعات را کامل وارد کنید.', 'info');
                    return;
                }
                if (data.needs_exit_only) {
                    setHint(
                        'این مراجعه‌کننده امروز قبلاً ثبت شده و ساعت خروج ندارد (' + data.last_visit_label + ').' +
                        ' <button type="button" class="mini-button" id="traffic-quick-exit-btn">فقط ثبت ساعت خروج</button>' +
                        ' یا برای ثبت یک تردد جدید همین فرم را کامل و ذخیره کنید.',
                        'info'
                    );
                    if (quickExitForm && quickExitId) {
                        quickExitId.value = data.record_id;
                        var btn = document.getElementById('traffic-quick-exit-btn');
                        if (btn) {
                            btn.addEventListener('click', function () { quickExitForm.submit(); });
                        }
                    }
                } else {
                    setHint('آخرین تردد: ' + data.last_visit_label + '. اطلاعات پیشنهادی پر شد؛ در صورت نیاز اصلاح کنید.', 'success');
                }
                if (fields.full_name && !fields.full_name.value) fields.full_name.value = data.full_name || '';
                if (fields.phone && !fields.phone.value) fields.phone.value = data.phone || '';
                if (fields.company && !fields.company.value) fields.company.value = data.company || '';
                if (fields.meeting_with && data.meeting_with) fields.meeting_with.value = data.meeting_with;
                syncNewDestinationApprover();
            })
            .catch(function () { clearHint(); });
    }

    codeField.addEventListener('blur', lookup);
    codeField.addEventListener('input', function () {
        if (codeField.value.replace(/[^0-9]/g, '').length === 10) {
            lookup();
        }
    });
}());

(function () {
    'use strict';
    var form = document.getElementById('traffic-report-form');
    var all = document.getElementById('traffic-report-all');
    if (!form || !all) return;

    var destinations = Array.prototype.slice.call(form.querySelectorAll('input[name="destinations[]"]'));
    all.addEventListener('change', function () {
        all.setCustomValidity('');
        destinations.forEach(function (input) { input.checked = all.checked; });
    });
    destinations.forEach(function (input) {
        input.addEventListener('change', function () {
            all.setCustomValidity('');
            if (!input.checked) all.checked = false;
            else all.checked = destinations.length > 0 && destinations.every(function (item) { return item.checked; });
        });
    });
    form.addEventListener('submit', function (event) {
        if (all.checked || destinations.some(function (input) { return input.checked; })) return;
        event.preventDefault();
        all.setCustomValidity('یک مقصد را انتخاب کنید یا گزینهٔ «همه» را علامت بزنید.');
        all.reportValidity();
    });
}());

/* جست‌وجوی آنی در سوابق تردد: به‌محض تایپ، ردیف‌های غیرمرتبط مخفی می‌شوند. */
(function () {
    var input = document.getElementById('traffic-history-search');
    var table = document.getElementById('traffic-history-table');
    if (!input || !table) return;
    var rows = table.querySelectorAll('[data-traffic-row]');
    var empty = document.getElementById('traffic-history-empty');
    function normalize(value) {
        return (value || '')
            .toString()
            .replace(/ي/g, 'ی')
            .replace(/ك/g, 'ک')
            .replace(/[۰-۹]/g, function (d) { return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)); })
            .replace(/[٠-٩]/g, function (d) { return String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)); })
            .toLowerCase()
            .trim();
    }
    function apply() {
        var query = normalize(input.value);
        var visible = 0;
        Array.prototype.forEach.call(rows, function (row) {
            var match = query === '' || normalize(row.getAttribute('data-search') || '').indexOf(query) !== -1;
            row.hidden = !match;
            if (match) visible++;
        });
        if (empty) empty.hidden = visible !== 0 || rows.length === 0;
    }
    input.addEventListener('input', apply);
    apply();
}());

(function () {
    'use strict';
    var alertBox = document.querySelector('[data-focus-form], #traffic-form-error');
    if (!alertBox) {
        return;
    }
    var formId = alertBox.getAttribute('data-focus-form');
    var name = alertBox.getAttribute('data-focus-field');
    var form = formId ? document.getElementById(formId) : null;
    var field = form && name ? form.querySelector('[name="' + name + '"]') : null;
    if (alertBox.scrollIntoView) {
        alertBox.scrollIntoView({ block: 'center' });
    }
    if (field) {
        field.focus();
        if (field.select) {
            field.select();
        }
    } else if (alertBox.focus) {
        alertBox.focus();
    }
})();

// تردد خاص: با تیک «فقط اطلاع ورود»، فیلدهای اضافی پنهان و غیرفعال می‌شوند (ثبت ارسال نمی‌شوند).
(function () {
    'use strict';
    var box = document.getElementById('traffic-notice-only');
    var hint = document.getElementById('traffic-notice-hint');
    var form = document.getElementById('traffic-new-form');
    if (!box || !form) {
        return;
    }
    function apply() {
        var on = box.checked;
        var parts = form.querySelectorAll('.traffic-full-only');
        for (var i = 0; i < parts.length; i++) {
            parts[i].style.display = on ? 'none' : 'contents';
            var fields = parts[i].querySelectorAll('input, select, textarea');
            for (var j = 0; j < fields.length; j++) {
                fields[j].disabled = on;
            }
        }
        if (hint) {
            hint.hidden = !on;
        }
    }
    box.addEventListener('change', apply);
    form.addEventListener('reset', function () {
        setTimeout(apply, 0);
    });
    apply();
})();

// ورودی ساعت ثابت و صریح ۲۴ساعته؛ ورود چهار رقم مثل 1430 به 14:30 تبدیل می‌شود.
(function () {
    'use strict';
    var fields = document.querySelectorAll('.traffic-time-24h');
    if (!fields.length) return;
    var persian = '۰۱۲۳۴۵۶۷۸۹';
    var arabic = '٠١٢٣٤٥٦٧٨٩';
    function asciiDigits(value) {
        return String(value || '')
            .replace(/[۰-۹]/g, function (digit) { return String(persian.indexOf(digit)); })
            .replace(/[٠-٩]/g, function (digit) { return String(arabic.indexOf(digit)); })
            .replace(/：/g, ':');
    }
    fields.forEach(function (field) {
        field.addEventListener('input', function () {
            var current = asciiDigits(field.value);
            if (current.indexOf(':') !== -1) {
                if (current !== field.value) field.value = current;
                return;
            }
            var digits = current.replace(/\D/g, '').slice(0, 4);
            if (digits.length > 2) {
                var caret = field.selectionStart || current.length;
                field.value = digits.slice(0, 2) + ':' + digits.slice(2);
                var nextCaret = caret > 2 ? caret + 1 : caret;
                try { field.setSelectionRange(nextCaret, nextCaret); } catch (ignore) {}
            } else if (current !== field.value) {
                field.value = digits;
            }
        });
        field.addEventListener('blur', function () {
            var value = asciiDigits(field.value).trim();
            var match = value.match(/^(\d{1,2}):(\d{1,2})$/);
            if (match) field.value = match[1].padStart(2, '0') + ':' + match[2].padStart(2, '0');
        });
    });
}());


// تأییدکننده در فرم ویرایش نیز فقط از مقصد انتخاب‌شده خوانده می‌شود.
(function () {
    'use strict';
    var destination = document.getElementById('traffic-edit-meeting-with');
    var approver = document.getElementById('traffic-edit-approved-by');
    if (!destination || !approver) return;
    function sync() {
        var option = destination.options[destination.selectedIndex];
        approver.value = option ? (option.getAttribute('data-approver') || '') : '';
    }
    destination.addEventListener('change', sync);
    sync();
}());
