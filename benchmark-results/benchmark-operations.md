# گزارش آزمون نوشتنی هم‌زمان تیکت و CD/DVD

- شروع UTC: 2026-10-22T21:14:07.124776+00:00
- محیط: ephemeral CI runner; disposable MariaDB; PHP CLI worker pool; synthetic identities/data only
- مقصد: 127.0.0.1 only; production and staging were not contacted
- سناریو: 250 concurrent sessions; each creates and resolves one ticket through application POST handlers and records one CD/DVD event; 60 additional same-serial duplicate races (30 IN, 30 OUT) test serial-state integrity.
- کاربران هم‌زمان: 250; workerهای PHP: 32; زمان: 21.75s
- عملیات منطقی: 1870; throughput منطقی: 85.96 عملیات/ثانیه
- اوج مجموع CPU پردازه‌های PHP: 143.1%; اوج مجموع RSS: 1184.86 MB
- خطاهای عملیات HTTP/تأیید برنامه: 250; هشدار/Notice/Fatal در PHP log: 0

| عملیات | تلاش | موفقیت تأییدشده | p50 ms | p95 ms | p99 ms | بیشینه ms | HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| cd-dvd-in | 186 | 159 | 2612.22 | 2867.25 | 2943.65 | 2998.93 | {'200': 186} |
| cd-dvd-out | 184 | 155 | 2596.69 | 2844.75 | 2917.02 | 2982.73 | {'200': 184} |
| login-form | 250 | 250 | 416.91 | 1345.42 | 1772.83 | 2001.75 | {'200': 250} |
| login-post | 250 | 250 | 1847.27 | 3077.67 | 3535.05 | 4002.16 | {'200': 250} |
| prepare-cd-dvd | 250 | 250 | 435.21 | 1369.59 | 1978.92 | 2106.84 | {'200': 250} |
| prepare-ticket | 250 | 250 | 506.25 | 1453.19 | 1921.44 | 2048.8 | {'200': 250} |
| ticket-create | 250 | 250 | 2832.8 | 4127.19 | 6264.53 | 6392.65 | {'200': 250} |
| ticket-resolve | 250 | 0 | 2342.64 | 2678.7 | 2765.15 | 2847.98 | {'404': 250} |

## بررسی مستقل دادهٔ نهایی

```json
{
  "collection_status": "ok",
  "tickets": {
    "created_for_run": 250,
    "resolved_for_run": 0,
    "status_counts": {
      "assigned": 250
    },
    "message_rows": 250,
    "event_rows": 500,
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
    "records_for_run": 314,
    "all_records_in_disposable_database": 314,
    "media_direction_counts": {
      "CD": {
        "IN": 96,
        "OUT": 62
      },
      "DVD": {
        "IN": 63,
        "OUT": 93
      }
    },
    "consecutive_same_direction_pairs": 4,
    "same_direction_violation_samples": [
      {
        "media": "CD",
        "serial": "TAD6DDB68P006",
        "direction": "IN",
        "first_record_id": 247,
        "repeat_record_id": 248
      },
      {
        "media": "CD",
        "serial": "TAD6DDB68P019",
        "direction": "IN",
        "first_record_id": 303,
        "repeat_record_id": 304
      },
      {
        "media": "CD",
        "serial": "TAD6DDB68P029",
        "direction": "IN",
        "first_record_id": 261,
        "repeat_record_id": 262
      },
      {
        "media": "DVD",
        "serial": "P86-1710",
        "direction": "OUT",
        "first_record_id": 298,
        "repeat_record_id": 299
      }
    ]
  },
  "application_system_errors_since_start": 612,
  "expected_duplicate_state_rejections": 112,
  "slow_query_warning_count": 0,
  "unexpected_application_system_error_count": 500,
  "application_system_error_samples": [
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2532",
      "request_uri": "/index.php?page=ticket&id=2532",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2532",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2551",
      "request_uri": "/index.php?page=ticket&id=2551",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2551",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2514",
      "request_uri": "/index.php?page=ticket&id=2514",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2529",
      "request_uri": "/index.php?page=ticket&id=2529",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2529",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2514",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2534",
      "request_uri": "/index.php?page=ticket&id=2534",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2534",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2517",
      "request_uri": "/index.php?page=ticket&id=2517",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2517",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2537",
      "request_uri": "/index.php?page=ticket&id=2537",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2537",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2509",
      "request_uri": "/index.php?page=ticket&id=2509",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2509",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2566",
      "request_uri": "/index.php?page=ticket&id=2566",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2566",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2569",
      "request_uri": "/index.php?page=ticket&id=2569",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2569",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2559",
      "request_uri": "/index.php?page=ticket&id=2559",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2559",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2518",
      "request_uri": "/index.php?page=ticket&id=2518",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2518",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2533",
      "request_uri": "/index.php?page=ticket&id=2533",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2533",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2521",
      "request_uri": "/index.php?page=ticket&id=2521",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2521",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2523",
      "request_uri": "/index.php?page=ticket&id=2523",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2523",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2561",
      "request_uri": "/index.php?page=ticket&id=2561",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2561",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2554",
      "request_uri": "/index.php?page=ticket&id=2554",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2554",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2563",
      "request_uri": "/index.php?page=ticket&id=2563",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2563",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2506",
      "request_uri": "/index.php?page=ticket&id=2506",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2506",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2526",
      "request_uri": "/index.php?page=ticket&id=2526",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2526",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2581",
      "request_uri": "/index.php?page=ticket&id=2581",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2581",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2585",
      "request_uri": "/index.php?page=ticket&id=2585",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2585",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2582",
      "request_uri": "/index.php?page=ticket&id=2582",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2582",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2528",
      "request_uri": "/index.php?page=ticket&id=2528",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2528",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2567",
      "request_uri": "/index.php?page=ticket&id=2567",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2567",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2573",
      "request_uri": "/index.php?page=ticket&id=2573",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2519",
      "request_uri": "/index.php?page=ticket&id=2519",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2519",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2573",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2583",
      "request_uri": "/index.php?page=ticket&id=2583",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2583",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2618",
      "request_uri": "/index.php?page=ticket&id=2618",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2615",
      "request_uri": "/index.php?page=ticket&id=2615",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2618",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2587",
      "request_uri": "/index.php?page=ticket&id=2587",
      "created_at": "2026-10-22 21:14:25"
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
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2587",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2615",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2603",
      "request_uri": "/index.php?page=ticket&id=2603",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2603",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2602",
      "request_uri": "/index.php?page=ticket&id=2602",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2602",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2606",
      "request_uri": "/index.php?page=ticket&id=2606",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2606",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2580",
      "request_uri": "/index.php?page=ticket&id=2580",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2608",
      "request_uri": "/index.php?page=ticket&id=2608",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2580",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2608",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2564",
      "request_uri": "/index.php?page=ticket&id=2564",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2564",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2605",
      "request_uri": "/index.php?page=ticket&id=2605",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2605",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "خطای برنامه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "خطای ناشناخته: ITSM request failed: برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد. | /index.php?page=ticket&id=2595",
      "request_uri": "/index.php?page=ticket&id=2595",
      "created_at": "2026-10-22 21:14:25"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.",
      "request_uri": "/index.php?page=ticket&id=2595",
      "created_at": "2026-10-22 21:14:25"
    }
  ]
}
```

## یکپارچگی رقابت سریال CD/DVD

- جفت‌های هم‌زمان تکراری: 60؛ جفت‌هایی که دقیقاً یک درخواست موفق داشتند: 56
- رخدادهای متوالیِ هم‌جهت برای یک سریال در دادهٔ نهایی: 4
- شکست‌های invariant: ["ticket resolutions confirmed by HTTP: 0/250", "database final resolved-ticket count did not equal 250", "database CD/DVD record count did not match 250 regular writes plus one winner per 60 pairs", "CD/DVD same-serial repeated-direction integrity violations: 4", "database logs did not contain exactly 60 controlled duplicate-state rejections", "unexpected application/system errors were recorded during the test", "4 concurrent duplicate pairs did not yield exactly one successful response"]

## محدودیت‌ها
- This run directly exercises the ticket create/update and CD/DVD create handlers through authenticated HTTP POSTs; it is not exhaustive coverage of every module or every route.
- Food external Access/ODBC sources, LDAP/Active Directory, physical media, printers, and production/staging capacity were not exercised.
- The database and all test accounts/records are disposable synthetic CI fixtures; no production or staging traffic/data was used.
- HTTP response, application confirmation, PHP logs, and independently queried final database state are reported separately.
