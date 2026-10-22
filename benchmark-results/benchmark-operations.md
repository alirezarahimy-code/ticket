# گزارش آزمون نوشتنی هم‌زمان تیکت و CD/DVD

- شروع UTC: 2026-10-22T21:19:18.914822+00:00
- محیط: ephemeral CI runner; disposable MariaDB; PHP CLI worker pool; synthetic identities/data only
- مقصد: 127.0.0.1 only; production and staging were not contacted
- سناریو: 250 concurrent sessions; each creates and resolves one ticket through application POST handlers and records one CD/DVD event; 60 additional same-serial duplicate races (30 IN, 30 OUT) test serial-state integrity.
- کاربران هم‌زمان: 250; workerهای PHP: 32; زمان: 38.09s
- عملیات منطقی: 1866; throughput منطقی: 49.0 عملیات/ثانیه
- اوج مجموع CPU پردازه‌های PHP: 134.4%; اوج مجموع RSS: 1183.85 MB
- خطاهای عملیات HTTP/تأیید برنامه: 4; هشدار/Notice/Fatal در PHP log: 0

| عملیات | تلاش | موفقیت تأییدشده | p50 ms | p95 ms | p99 ms | بیشینه ms | HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| cd-dvd-in | 186 | 156 | 4769.44 | 5199.78 | 5378.38 | 5589.88 | {'200': 186} |
| cd-dvd-out | 184 | 154 | 4766.41 | 5183.35 | 5294.5 | 5378.07 | {'200': 184} |
| login-form | 250 | 250 | 1279.95 | 2652.55 | 3416.15 | 4506.19 | {'200': 250} |
| login-post | 250 | 250 | 3549.03 | 5418.92 | 6197.74 | 7079.15 | {'200': 250} |
| prepare-cd-dvd | 250 | 250 | 1368.47 | 2723.32 | 3437.64 | 3638.52 | {'200': 250} |
| prepare-ticket | 250 | 250 | 1528.42 | 2633.73 | 3283.49 | 4087.73 | {'200': 250} |
| ticket-create | 250 | 246 | 5185.35 | 6833.5 | 7554.91 | 8008.86 | {'200': 250} |
| ticket-resolve | 246 | 246 | 4352.28 | 5081.6 | 5166.22 | 5189.04 | {'200': 246} |

## بررسی مستقل دادهٔ نهایی

```json
{
  "collection_status": "ok",
  "tickets": {
    "created_for_run": 246,
    "resolved_for_run": 246,
    "status_counts": {
      "resolved": 246
    },
    "message_rows": 492,
    "event_rows": 984,
    "synthetic_ticket_ids_sample": [
      2501,
      2502,
      2503,
      2504,
      2505,
      2506,
      2507,
      2508,
      2509,
      2510,
      2511,
      2512,
      2513,
      2514,
      2515,
      2516,
      2517,
      2518,
      2519,
      2520
    ]
  },
  "cd_dvd": {
    "records_for_run": 310,
    "all_records_in_disposable_database": 310,
    "media_direction_counts": {
      "CD": {
        "IN": 93,
        "OUT": 62
      },
      "DVD": {
        "IN": 63,
        "OUT": 92
      }
    },
    "consecutive_same_direction_pairs": 0,
    "same_direction_violation_samples": []
  },
  "application_system_errors_since_start": 134,
  "expected_duplicate_state_rejection_log_rows": 120,
  "slow_query_warning_count": 5,
  "unexpected_application_system_error_count": 9,
  "application_system_error_samples": [
    {
      "level": "warning",
      "context": "کندی صفحه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "صفحهٔ «login» 2.4 ثانیه طول کشید",
      "request_uri": "/index.php?page=login",
      "created_at": "2026-10-22 21:19:29"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 506,
      "slow_query": "DELETE FROM login_attempts WHERE attempt_key = ?",
      "message": "یک کوئری 506 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=login",
      "created_at": "2026-10-22 21:19:31"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 502,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE seq = LAST_INSERT_ID(seq + 1)",
      "message": "یک کوئری 502 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 518,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE seq = LAST_INSERT_ID(seq + 1)",
      "message": "یک کوئری 518 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "داده تکراری یا وابستگی ناسازگار",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "داده تکراری یا وابستگی ناسازگار",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00003' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00004' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 516,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE seq = LAST_INSERT_ID(seq + 1)",
      "message": "یک کوئری 516 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "داده تکراری یا وابستگی ناسازگار",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00009' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 506,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE seq = LAST_INSERT_ID(seq + 1)",
      "message": "یک کوئری 506 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "داده تکراری یا وابستگی ناسازگار",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00012' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:19:43"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:50"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این شماره از سازمان خارج شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این شماره از سازمان خارج شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:51"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "این رسانه در حال حاضر داخل سازمان ثبت شده است.",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:19:52"
    }
  ]
}
```

## یکپارچگی رقابت سریال CD/DVD

- جفت‌های هم‌زمان تکراری: 60؛ جفت‌هایی که دقیقاً یک درخواست موفق داشتند: 60
- رخدادهای متوالیِ هم‌جهت برای یک سریال در دادهٔ نهایی: 0
- شکست‌های invariant: ["ticket creates confirmed by HTTP: 246/250", "ticket resolutions confirmed by HTTP: 246/250", "database final resolved-ticket count did not equal 250", "database-created ticket count did not equal 250", "unexpected application/system errors were recorded during the test"]

## محدودیت‌ها
- This run directly exercises the ticket create/update and CD/DVD create handlers through authenticated HTTP POSTs; it is not exhaustive coverage of every module or every route.
- Food external Access/ODBC sources, LDAP/Active Directory, physical media, printers, and production/staging capacity were not exercised.
- The database and all test accounts/records are disposable synthetic CI fixtures; no production or staging traffic/data was used.
- HTTP response, application confirmation, PHP logs, and independently queried final database state are reported separately.
