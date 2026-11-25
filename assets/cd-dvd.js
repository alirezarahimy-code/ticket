document.addEventListener('click', function (event) {
    var target = event.target;
    if (!target || typeof target.closest !== 'function') {
        return;
    }
    if (target.closest('[data-print-records]')) {
        event.preventDefault();
        if (typeof printCdDvdHistory === 'function') {
            printCdDvdHistory();
        }
        return;
    }
    if (target.closest('[data-print-page]')) {
        event.preventDefault();
        var report = document.querySelector('.cd-dvd-table, .cd-dvd-surface-table, .cd-dvd-record-table');
        if (report) printCdDvdList(report);
    }
});

(function () {
    'use strict';

    const media = document.getElementById('cd-dvd-media');
    const direction = document.getElementById('cd-dvd-direction');
    const serial = document.getElementById('cd-dvd-serial');
    const serialFull = document.getElementById('cd-dvd-serial-full');
    const serialManual = document.getElementById('cd-dvd-serial-manual');
    const serialOutWrap = document.querySelector('.cd-dvd-serial-out');
    const serialHelp = document.getElementById('cd-dvd-serial-help');
    const exitSheetField = document.querySelector('.cd-dvd-exit-sheet-field');
    const outgoingFields = document.querySelectorAll('.cd-dvd-outgoing-field');
    const incomingField = document.querySelector('.cd-dvd-incoming-field');
    const broughtBy = document.getElementById('cd-dvd-brought-by');
    const broughtById = document.getElementById('cd-dvd-brought-by-id');
    const broughtByExternal = document.getElementById('cd-dvd-brought-by-external');
    const broughtByKind = document.querySelectorAll('input[name="brought_by_type"]');
    const broughtByInternalWrap = document.querySelector('.cd-dvd-brought-internal');
    const broughtByExternalWrap = document.querySelector('.cd-dvd-brought-external');
    const userPicker = document.querySelector('[data-user-picker]');
    const userOptions = userPicker ? userPicker.querySelector('.cd-dvd-user-options') : null;
    const nextSerial = window.CD_DVD_NEXT || {};
    let lastSelectedMedia = media ? media.value : ((document.querySelector('input[name="media"]:checked') || {}).value || 'CD');
    let serialFormInitialized = false;

    function normalize(value) {
        return String(value || '').toLocaleLowerCase('fa').trim();
    }

    /**
     * ── انتخابگر کاربر (۱.۶۳.۴) ───────────────────────────────────────────
     *
     * باگ قبلی: وقتی کاربری از قبل انتخاب شده بود (یا بعد از انتخاب یکی)،
     * فیلد `readOnly` می‌شد. در HTML، input با readOnly **هیچ کاراکتری**
     * نمی‌پذیرد — پس کاربر نمی‌توانست نام تازه‌ای تایپ کند. تنها راه، کلیک
     * اول بود که فیلد را کامل پاک می‌کرد؛ و روی بعضی مرورگرها ترتیب رویدادها
     * باعث می‌شد فقط یک کاراکتر جا بیفتد. نتیجه: «فقط یک حرف می‌شود تایپ کرد».
     *
     * رفتار تازه:
     *   • فیلد هرگز readOnly نمی‌شود — همیشه می‌شود تایپ کرد.
     *   • انتخاب فعلی با `pickedValue` نگه داشته می‌شود، نه با readOnly.
     *   • تا وقتی متن عوض نشده، همان کاربر انتخاب‌شده معتبر است.
     *   • با اولین کاراکتر تازه، انتخاب پاک می‌شود و جست‌وجو شروع می‌شود.
     *   • جست‌وجو چندکلمه‌ای است: «علی رضا» هر ردیفی را پیدا می‌کند که هر
     *     دو کلمه در نام/نام کاربری/واحدش باشد (ترتیب不重要 نیست).
     *   • جست‌وجو «همه‌جا» است: «رضایی» علی رضایی را هم می‌آورد، نه فقط
     *     ردیف‌هایی که با «ر» شروع می‌شوند. این همان چیزی است که کاربر
     *     خواست: «ر» بزنم باید رضایی‌ها هم بیایند.
     */
    let pickedValue = broughtBy ? String(broughtBy.value || '') : '';
    let pickedId = broughtById ? String(broughtById.value || '') : '0';

    /** نرمال‌سازی برای تطبیق: کوچک‌کردن، ی/ک عربی، حذف اعراب و فاصلهٔ اضافه. */
    function normalize(value) {
        return String(value || '')
            .toLocaleLowerCase('fa')
            .replace(/[\u064A\u0649]/g, '\u06CC')   // ي ي ⇒ ی
            .replace(/\u0643/g, '\u06A9')             // ك ⇒ ک
            .replace(/[\u064B-\u0652\u0670]/g, '')   // اعراب
            .replace(/\u0640/g, '')                    // کشیده
            .replace(/\s+/g, ' ')
            .trim();
    }

    /**
     * آیا این ردیف با عبارت جست‌وجو می‌خواند؟
     * همهٔ کلمه‌ها باید باشند، ولی در هر جای متن — نه فقط از ابتدا.
     */
    function matchesSearch(haystack, needle) {
        const text = normalize(haystack);
        if (needle === '') {
            return true;
        }
        return needle.split(' ').every(function (word) {
            return word !== '' && text.indexOf(word) !== -1;
        });
    }

    /** آیا عبارت جست‌وجو عیناً همان انتخاب فعلی است؟ (یعنی چیزی تایپ نشده) */
    function searchUnchanged() {
        if (!broughtBy) {
            return true;
        }
        return normalize(broughtBy.value) === normalize(pickedValue);
    }

    function filterUsers() {
        if (!userOptions || !broughtBy) {
            return;
        }
        const search = normalize(broughtBy.value);
        const unchanged = searchUnchanged();
        userOptions.hidden = false;
        let visibleCount = 0;
        userOptions.querySelectorAll('.cd-dvd-user-option').forEach(function (option) {
            let show;
            if (option.classList.contains('external')) {
                // «خارج از سامانه» فقط وقتی دیده شود که چیزی تایپ شده و با
                // عبارت می‌خواند؛ در حالت خالی بودن جست‌وجو اول فهرست است.
                show = search === '' || matchesSearch('خارج سامانه بیرون', search);
            } else if (search === '') {
                show = true;
            } else if (unchanged) {
                // متن همان انتخاب فعلی است ⇒ همه را نشان بده تا کاربر
                // بتواند عوض کند (و ردیف انتخاب‌شده هم دیده شود).
                show = true;
            } else {
                show = matchesSearch(option.dataset.search || option.textContent, search);
            }
            option.hidden = !show;
            if (show && !option.classList.contains('external')) {
                visibleCount++;
            }
        });
        // اگر هیچ کاربری نماند، پیام روشن بده (به‌جای فهرست خالیِ گیج‌کننده).
        const empty = userOptions.querySelector('.cd-dvd-user-empty');
        if (empty) {
            empty.hidden = !(visibleCount === 0 && search !== '' && !unchanged);
        }
    }

    function selectUser(option) {
        if (!broughtBy || !broughtById) {
            return;
        }
        const id = option.dataset.userId || '0';
        broughtById.value = id;
        pickedId = id;
        if (id === '-1') {
            broughtBy.value = '';
            pickedValue = '';
            broughtBy.placeholder = 'نام شخص خارج از سامانه';
        } else {
            broughtBy.value = option.dataset.label || option.textContent.trim();
            pickedValue = broughtBy.value;
            broughtBy.placeholder = 'انتخاب کاربر';
        }
        if (userOptions) {
            userOptions.hidden = true;
        }
    }

    function initializeUserPicker() {
        if (!userPicker || !userOptions || !broughtBy || !broughtById) {
            return;
        }
        // پیام «پیدا نشد» یک‌بار ساخته می‌شود و در فهرست می‌ماند.
        if (!userOptions.querySelector('.cd-dvd-user-empty')) {
            const empty = document.createElement('div');
            empty.className = 'cd-dvd-user-empty';
            empty.hidden = true;
            empty.textContent = 'کاربری با این عبارت پیدا نشد. املای دیگری را امتحان کنید یا «شخص خارج از سامانه» را انتخاب کنید.';
            userOptions.appendChild(empty);
        }

        // ⚠️ هرگز readOnly نمی‌شود: کاربر باید همیشه بتواند تایپ کند.
        // مقدار اولیه (کاربر انتخاب‌شده) در pickedValue/pickedId نگه داشته شد.
        broughtBy.readOnly = false;
        // اگر مرورگر یا نسخهٔ قدیمی <select> را رندر کرده باشد، فقط هم‌گام‌سازی
        // شناسه کافی است؛ فیلتر متنی برای آن معنا ندارد.
        if (broughtBy.tagName === 'SELECT') {
            return;
        }

        broughtBy.addEventListener('focus', function () {
            filterUsers();
        });

        broughtBy.addEventListener('input', function () {
            // کاربر تایپ کرد ⇒ اگر متن با انتخاب فعلی یکی نیست، انتخاب پاک شود.
            if (!searchUnchanged()) {
                broughtById.value = '0';
                pickedId = '0';
                pickedValue = '';
            }
            filterUsers();
        });

        // Escape فهرست را می‌بندد؛ Enter اولین ردیفِ دیده‌شده را انتخاب می‌کند.
        broughtBy.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                userOptions.hidden = true;
                return;
            }
            if (event.key === 'Enter') {
                const firstVisible = Array.from(userOptions.querySelectorAll('.cd-dvd-user-option'))
                    .find(function (option) { return !option.hidden && !option.classList.contains('external'); });
                if (firstVisible && !userOptions.hidden) {
                    event.preventDefault();
                    selectUser(firstVisible);
                }
            }
        });

        userOptions.querySelectorAll('.cd-dvd-user-option').forEach(function (option) {
            option.addEventListener('click', function () {
                selectUser(option);
            });
        });

        document.addEventListener('click', function (event) {
            if (!userPicker.contains(event.target)) {
                userOptions.hidden = true;
            }
        });
    }

    function getSelectedMedia() {
        return media ? media.value : ((document.querySelector('input[name="media"]:checked') || {}).value || 'CD');
    }

    function mediaBounds(value) {
        return value === 'DVD' ? { min: 1001, max: 2000, label: 'DVD: P86-1001 تا P86-2000' } : { min: 1, max: 1000, label: 'CD: P86-0001 تا P86-1000' };
    }

    function normalizeDigits(value) {
        const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
        const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
        return String(value || '').replace(/[۰-۹٠-٩]/g, function (digit) {
            const persianIndex = persianDigits.indexOf(digit);
            return String(persianIndex >= 0 ? persianIndex : arabicDigits.indexOf(digit));
        });
    }

    function updateForm() {
        const isIncoming = direction && direction.value === 'IN';
        const selectedMedia = getSelectedMedia();
        const mediaChanged = selectedMedia !== lastSelectedMedia;
        const bounds = mediaBounds(selectedMedia);

        if (incomingField) {
            incomingField.hidden = !isIncoming;
        }
        outgoingFields.forEach(function (field) {
            field.hidden = isIncoming;
            field.querySelectorAll('input, select, textarea').forEach(function (control) { control.disabled = isIncoming; });
        });
        const externalBroughtBy = Array.from(broughtByKind).some(function (radio) { return radio.checked && radio.value === 'external'; });
        if (broughtByInternalWrap) broughtByInternalWrap.hidden = externalBroughtBy;
        if (broughtByExternalWrap) broughtByExternalWrap.hidden = !externalBroughtBy;
        if (broughtBy) {
            broughtBy.required = isIncoming && !externalBroughtBy;
            if (broughtBy.tagName === 'SELECT') broughtBy.disabled = !isIncoming || externalBroughtBy;
        }
        if (broughtByExternal) {
            broughtByExternal.required = isIncoming && externalBroughtBy;
            broughtByExternal.disabled = !isIncoming || !externalBroughtBy;
        }
        if (broughtById && isIncoming) {
            broughtById.value = externalBroughtBy ? '-1' : (broughtBy && broughtBy.tagName === 'SELECT' ? (broughtBy.value || '0') : (broughtById.value || '0'));
        }
        if (exitSheetField) {
            exitSheetField.hidden = isIncoming;
            const exitSheetInput = exitSheetField.querySelector('input[name="exit_sheet"]');
            if (exitSheetInput) {
                exitSheetInput.disabled = isIncoming;
                exitSheetInput.required = !isIncoming;
            }
        }
        if (serialOutWrap) {
            serialOutWrap.hidden = isIncoming;
        }
        if (serial) {
            // The visible outgoing control is the submitted value (not the mirror field).
            // Disable it only when the incoming manual field is active.
            serial.disabled = Boolean(serialManual && isIncoming);
            serial.required = serialManual ? !isIncoming : true;
        }
        if (serialManual) {
            serialManual.hidden = !isIncoming;
            serialManual.disabled = !isIncoming;
            serialManual.required = isIncoming;
        }
        if (serialHelp) {
            serialHelp.textContent = isIncoming
                ? 'برای رسانه ورودی از بیرون سازمان، شماره را همان‌طور که روی رسانه درج شده دستی وارد کنید.'
                : bounds.label;
        }

        if (isIncoming) {
            // شماره ورودی از فیلد دستی ارسال می‌شود؛ فیلد خروجی غیرفعال است.
            if (serialManual && serialManual.value.trim() === '' && serial && serial.value.trim() !== '' && mediaChanged) {
                serial.value = '';
            }
            if (serialFull) {
                serialFull.value = serialManual ? serialManual.value.trim() : '';
            }
        } else if (serial) {
            const next = String(nextSerial[selectedMedia] || String(bounds.min).padStart(4, '0')).replace(/^P86-/, '');
            // پیشنهاد فقط هنگام ورود اولیه یا تعویض نوع رسانه اعمال می‌شود. هنگام
            // تایپ، حتی مقدارهای موقت مثل «0» حفظ می‌شوند تا پیشنهاد با هر کلید
            // جایگزین نشده و کاربر بتواند شمارهٔ برگشتی را دستی وارد کند.
            if ((mediaChanged || (!serialFormInitialized && String(serial.value || '').trim() === '')) && next) {
                serial.value = next;
            }
            serial.placeholder = String(bounds.min).padStart(4, '0');
            const normalizedSerial = normalizeDigits(serial.value).trim();
            const numeric = /^\d{1,4}$/.test(normalizedSerial) ? normalizedSerial : '';
            const rangeIsValid = numeric !== '' && Number(numeric) >= bounds.min && Number(numeric) <= bounds.max;
            if (serialFull) {
                serialFull.value = rangeIsValid ? 'P86-' + numeric.padStart(4, '0') : '';
            }
        }
        serialFormInitialized = true;
        lastSelectedMedia = selectedMedia;
    }

    const handleMediaChange = function () {
        lastSelectedMedia = '';
        updateForm();
    };
    const form = serial ? serial.closest('form') : null;
    if (form) {
        form.noValidate = true;
        form.addEventListener('submit', function (event) {
            // Rebuild the canonical hidden serial from the final visible value at submit time.
            updateForm();
            const emptyField = Array.from(form.querySelectorAll('[required]')).find(function (field) {
                return !field.disabled && !field.closest('[hidden]') && String(field.value || '').trim() === '';
            });
            if (emptyField) {
                event.preventDefault();
                const label = emptyField.dataset.requiredLabel || 'نامشخص';
                window.alert('فیلد «' + label + '» خالی است و باید پر شود.');
                emptyField.focus();
                return;
            }
            // An outgoing number must resolve to the CD/DVD range before submit.
            // Keep the user's field active and focus it rather than sending a blank mirror.
            if (serialManual && direction && direction.value !== 'IN' && serial
                && String(serial.value || '').trim() !== '') {
                const submittedDigits = normalizeDigits(serial.value).trim();
                const submittedNumber = /^\d{1,4}$/.test(submittedDigits) ? Number(submittedDigits) : NaN;
                const submittedBounds = mediaBounds(getSelectedMedia());
                if (!Number.isFinite(submittedNumber) || submittedNumber < submittedBounds.min || submittedNumber > submittedBounds.max) {
                    event.preventDefault();
                    window.alert('شماره CD/DVD با بازهٔ نوع رسانه سازگار نیست؛ شماره را بررسی کنید.');
                    serial.focus();
                    return;
                }
                if (serialFull) {
                    serialFull.value = 'P86-' + String(submittedNumber).padStart(4, '0');
                }
            }
        });
        form.addEventListener('change', function (event) {
            if (event.target.matches('input[name="media"], #cd-dvd-media')) {
                handleMediaChange();
            }
        });
    }
    if (serial) {
        serial.addEventListener('input', updateForm);
    }
    if (serialManual) {
        serialManual.addEventListener('input', function () {
            if (serialFull) {
                serialFull.value = serialManual.value.trim();
            }
        });
    }
    if (direction) {
        direction.addEventListener('change', updateForm);
    }
    broughtByKind.forEach(function (radio) {
        radio.addEventListener('change', updateForm);
    });
    if (broughtBy && broughtBy.tagName === 'SELECT' && broughtById) {
        broughtBy.addEventListener('change', function () {
            broughtById.value = broughtBy.value || '0';
        });
    }
    initializeUserPicker();
    updateForm();

}());

(function () {
    'use strict';

    const tabLinks = document.querySelectorAll('[data-cd-dvd-tab]');
    const panels = document.querySelectorAll('[data-cd-dvd-panel]');
    if (!tabLinks.length || !panels.length) {
        return;
    }

    function activateTab(name, updateUrl) {
        const shell = document.querySelector('.content-wrap');
        if (shell) shell.classList.toggle('cd-history-wide', name === 'history');
        tabLinks.forEach(function (link) {
            const active = link.dataset.cdDvdTab === name;
            link.classList.toggle('active', active);
            link.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.dataset.cdDvdPanel !== name;
        });
        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', name);
            url.hash = '';
            window.history.replaceState({}, '', url);
        }
    }

    tabLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            activateTab(link.dataset.cdDvdTab, true);
        });
    });

    const selected = Array.from(tabLinks).find(function (link) { return link.classList.contains('active'); });
    activateTab(selected ? selected.dataset.cdDvdTab : 'dashboard', false);
}());

function printCdDvdList(table) {
    if (!table || !document.body || typeof window.print !== 'function') return;
    const report = table.cloneNode(true);
    report.querySelectorAll('.actions').forEach(function (node) { node.remove(); });
    report.querySelectorAll('.table-head, .cd-dvd-record-head').forEach(function (head) {
        const last = head.lastElementChild;
        if (last && /عملیات/.test(last.textContent || '')) last.remove();
    });
    const printHost = document.createElement('div');
    printHost.className = 'cd-dvd-print-host';
    printHost.appendChild(report);
    document.body.appendChild(printHost);
    document.body.classList.add('cd-dvd-print-selected');
    let cleaned = false;
    let cleanupTimer = null;
    function cleanup() {
        if (cleaned) return;
        cleaned = true;
        document.body.classList.remove('cd-dvd-print-selected');
        if (printHost.parentNode) printHost.parentNode.removeChild(printHost);
        if (cleanupTimer !== null) window.clearTimeout(cleanupTimer);
        window.removeEventListener('afterprint', cleanup);
    }
    window.addEventListener('afterprint', cleanup, { once: true });
    try {
        window.print();
    } catch (error) {
        cleanup();
        return;
    }
    if (!cleaned) cleanupTimer = window.setTimeout(cleanup, 30000);
}

function printCdDvdHistory() {
    const table = document.querySelector('#cd-dvd-history .cd-dvd-record-table') || document.querySelector('.cd-dvd-surface-table, .cd-dvd-record-table');
    if (!table) return;
    const printWindow = window.open('', '_blank', 'width=1200,height=800');
    if (!printWindow) {
        printCdDvdList(table);
        return;
    }
    const surfaceTable = table.classList.contains('cd-dvd-surface-table');
    const header = table.querySelector(surfaceTable ? '.cd-dvd-surface-head' : '.cd-dvd-record-head');
    const rowSelector = surfaceTable ? '.cd-dvd-surface-row' : '.cd-dvd-record-row';
    const headerCells = header ? Array.from(header.children) : [];
    if (!surfaceTable && headerCells.length) headerCells.pop(); // عملیات در نسخهٔ چاپی حذف می‌شود.
    const headers = header ? headerCells.map(function (cell) { return '<th>' + cell.innerHTML + '</th>'; }).join('') : '';
    const rows = Array.from(table.querySelectorAll(rowSelector)).map(function (row) {
        const cells = surfaceTable ? Array.from(row.children) : Array.from(row.children).slice(0, -1);
        return '<tr>' + cells.map(function (cell) { return '<td>' + cell.innerHTML + '</td>'; }).join('') + '</tr>';
    }).join('');
    printWindow.document.write('<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><title>سوابق CD/DVD</title><style>body{font-family:Tahoma,Arial,sans-serif;padding:24px;color:#172b4d;text-align:center}h1{font-size:22px;margin:0 0 18px}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #cbd5e1;padding:8px;text-align:center;vertical-align:middle}th{background:#eaf2ff;font-weight:700}td:nth-child(7){max-width:260px;white-space:normal;word-break:break-word}</style></head><body><h1>سوابق کنترل CD/DVD</h1><table><thead><tr>' + headers + '</tr></thead><tbody>' + rows + '</tbody></table></body></html>');
    printWindow.document.close();
    printWindow.onafterprint = function () {
        try { printWindow.close(); } catch (error) { /* ignored */ }
    };
    window.setTimeout(function () {
        try {
            printWindow.focus();
            printWindow.print();
        } catch (error) {
            printCdDvdList(table);
        }
    }, 400);
}
