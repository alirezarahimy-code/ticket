# گزارش آزمون نوشتنی هم‌زمان تیکت و CD/DVD

- شروع UTC: 2026-10-22T21:16:41.827117+00:00
- محیط: ephemeral CI runner; disposable MariaDB; PHP CLI worker pool; synthetic identities/data only
- مقصد: 127.0.0.1 only; production and staging were not contacted
- سناریو: 250 concurrent sessions; each creates and resolves one ticket through application POST handlers and records one CD/DVD event; 60 additional same-serial duplicate races (30 IN, 30 OUT) test serial-state integrity.
- کاربران هم‌زمان: 250; workerهای PHP: 32; زمان: 37.05s
- عملیات منطقی: 1870; throughput منطقی: 50.47 عملیات/ثانیه
- اوج مجموع CPU پردازه‌های PHP: 132.9%; اوج مجموع RSS: 1191.3 MB
- خطاهای عملیات HTTP/تأیید برنامه: 0; هشدار/Notice/Fatal در PHP log: 0

| عملیات | تلاش | موفقیت تأییدشده | p50 ms | p95 ms | p99 ms | بیشینه ms | HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| cd-dvd-in | 186 | 157 | 4458.96 | 4963.16 | 5068.66 | 5382.13 | {'200': 186} |
| cd-dvd-out | 184 | 159 | 4476.72 | 4877.15 | 4989.53 | 5151.97 | {'200': 184} |
| login-form | 250 | 250 | 823.46 | 2533.51 | 3306.62 | 6350.41 | {'200': 250} |
| login-post | 250 | 250 | 3299.66 | 5445.68 | 6502.72 | 7402.69 | {'200': 250} |
| prepare-cd-dvd | 250 | 250 | 928.57 | 2277.96 | 2800.91 | 2952.35 | {'200': 250} |
| prepare-ticket | 250 | 250 | 1151.38 | 3017.42 | 4477.37 | 5343.14 | {'200': 250} |
| ticket-create | 250 | 250 | 5261.32 | 6705.45 | 7421.61 | 8037.08 | {'200': 250} |
| ticket-resolve | 250 | 250 | 4221.06 | 4874.41 | 4997.33 | 5065.56 | {'200': 250} |

## بررسی مستقل دادهٔ نهایی

```json
{
  "collection_status": "ok",
  "tickets": {
    "created_for_run": 250,
    "resolved_for_run": 250,
    "status_counts": {
      "resolved": 250
    },
    "message_rows": 500,
    "event_rows": 1000,
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
    "records_for_run": 316,
    "all_records_in_disposable_database": 316,
    "media_direction_counts": {
      "CD": {
        "IN": 94,
        "OUT": 62
      },
      "DVD": {
        "IN": 63,
        "OUT": 97
      }
    },
    "consecutive_same_direction_pairs": 6,
    "same_direction_violation_samples": [
      {
        "media": "CD",
        "serial": "T15EC19C4P008",
        "direction": "IN",
        "first_record_id": 303,
        "repeat_record_id": 304
      },
      {
        "media": "DVD",
        "serial": "P86-1706",
        "direction": "OUT",
        "first_record_id": 292,
        "repeat_record_id": 293
      },
      {
        "media": "DVD",
        "serial": "P86-1707",
        "direction": "OUT",
        "first_record_id": 311,
        "repeat_record_id": 312
      },
      {
        "media": "DVD",
        "serial": "P86-1708",
        "direction": "OUT",
        "first_record_id": 314,
        "repeat_record_id": 315
      },
      {
        "media": "DVD",
        "serial": "P86-1723",
        "direction": "OUT",
        "first_record_id": 271,
        "repeat_record_id": 272
      },
      {
        "media": "DVD",
        "serial": "P86-1727",
        "direction": "OUT",
        "first_record_id": 298,
        "repeat_record_id": 299
      }
    ]
  },
  "application_system_errors_since_start": 108,
  "expected_duplicate_state_rejection_log_rows": 108,
  "slow_query_warning_count": 0,
  "unexpected_application_system_error_count": 0,
  "application_system_error_samples": [
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": true,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: این رسانه در حال حاضر داخل سازمان ثبت شده است. | /index.php?page=cd-dvd",
      "request_uri": "/index.php?page=cd-dvd",
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:12"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:13"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
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
      "created_at": "2026-10-22 21:17:14"
    }
  ]
}
```

## یکپارچگی رقابت سریال CD/DVD

- جفت‌های هم‌زمان تکراری: 60؛ جفت‌هایی که دقیقاً یک درخواست موفق داشتند: 54
- رخدادهای متوالیِ هم‌جهت برای یک سریال در دادهٔ نهایی: 6
- شکست‌های invariant: ["database CD/DVD record count did not match 250 regular writes plus one winner per 60 pairs", "CD/DVD same-serial repeated-direction integrity violations: 6", "6 concurrent duplicate pairs did not yield exactly one successful response"]

## محدودیت‌ها
- This run directly exercises the ticket create/update and CD/DVD create handlers through authenticated HTTP POSTs; it is not exhaustive coverage of every module or every route.
- Food external Access/ODBC sources, LDAP/Active Directory, physical media, printers, and production/staging capacity were not exercised.
- The database and all test accounts/records are disposable synthetic CI fixtures; no production or staging traffic/data was used.
- HTTP response, application confirmation, PHP logs, and independently queried final database state are reported separately.
