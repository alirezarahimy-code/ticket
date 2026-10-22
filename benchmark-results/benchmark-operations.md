# گزارش آزمون نوشتنی هم‌زمان تیکت و CD/DVD

- شروع UTC: 2026-10-22T21:22:53.812519+00:00
- محیط: ephemeral CI runner; disposable MariaDB; PHP CLI worker pool; synthetic identities/data only
- مقصد: 127.0.0.1 only; production and staging were not contacted
- سناریو: 250 concurrent sessions; each creates and resolves one ticket through application POST handlers and records one CD/DVD event; 60 additional same-serial duplicate races (30 IN, 30 OUT) test serial-state integrity.
- کاربران هم‌زمان: 250; workerهای PHP: 32; زمان: 38.28s
- عملیات منطقی: 1846; throughput منطقی: 48.22 عملیات/ثانیه
- اوج مجموع CPU پردازه‌های PHP: 136.3%; اوج مجموع RSS: 1186.92 MB
- خطاهای عملیات HTTP/تأیید برنامه: 24; هشدار/Notice/Fatal در PHP log: 0

| عملیات | تلاش | موفقیت تأییدشده | p50 ms | p95 ms | p99 ms | بیشینه ms | HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| cd-dvd-in | 186 | 156 | 4824.83 | 5241.63 | 5358.72 | 5468.72 | {'200': 186} |
| cd-dvd-out | 184 | 154 | 4819.57 | 5230.37 | 5450.72 | 5491.97 | {'200': 184} |
| login-form | 250 | 250 | 1369.89 | 3357.28 | 3844.33 | 4105.79 | {'200': 250} |
| login-post | 250 | 250 | 3687.94 | 5675.34 | 6979.43 | 7170.53 | {'200': 250} |
| prepare-cd-dvd | 250 | 250 | 1398.96 | 2696.27 | 3592.39 | 3842.92 | {'200': 250} |
| prepare-ticket | 250 | 250 | 1673.39 | 3277.26 | 4277.46 | 4371.08 | {'200': 250} |
| ticket-create | 250 | 226 | 5703.26 | 7246.66 | 7852.36 | 8490.47 | {'200': 250} |
| ticket-resolve | 226 | 226 | 4350.6 | 5088.59 | 5305.48 | 5344.35 | {'200': 226} |

## بررسی مستقل دادهٔ نهایی

```json
{
  "collection_status": "ok",
  "tickets": {
    "created_for_run": 226,
    "resolved_for_run": 226,
    "status_counts": {
      "resolved": 226
    },
    "message_rows": 452,
    "event_rows": 904,
    "ticket_numbers_assigned": 226,
    "unique_ticket_numbers": 226,
    "it_sequence_counter": 226,
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
  "application_system_errors_since_start": 175,
  "expected_duplicate_state_rejection_log_rows": 120,
  "slow_query_warning_count": 6,
  "unexpected_application_system_error_count": 49,
  "application_system_error_samples": [
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 763,
      "slow_query": "SELECT ou.id, ou.name, ou.code, ou.parent_id, m.full_name AS manager_name\n         FROM org_units ou\n         LEFT JOIN users m ON m.id = ou.manager_user_id\n         WHERE ou.is_active = 1 AND ou.unit_type = \"deputy\"\n         ORDER BY ou.name",
      "message": "یک کوئری 763 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:22:58"
    },
    {
      "level": "warning",
      "context": "کندی صفحه",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "صفحهٔ «login» 2 ثانیه طول کشید",
      "request_uri": "/index.php?page=login",
      "created_at": "2026-10-22 21:23:07"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 508,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0) ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)",
      "message": "یک کوئری 508 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:17"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 501,
      "slow_query": "UPDATE ticket_number_seq SET seq = ? WHERE prefix = ?",
      "message": "یک کوئری 501 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00193' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00194' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00194' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 516,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0) ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)",
      "message": "یک کوئری 516 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00195' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00196' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 504,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0) ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)",
      "message": "یک کوئری 504 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00197' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00199' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00200' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "warning",
      "context": "کندی کوئری",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": true,
      "slow_query_ms": 502,
      "slow_query": "INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0) ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)",
      "message": "یک کوئری 502 میلی‌ثانیه طول کشید",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00201' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00203' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00206' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00206' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00206' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00208' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00209' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00209' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00211' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00211' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00213' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00213' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00216' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00216' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00216' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:18"
    },
    {
      "level": "error",
      "context": "request",
      "expected_duplicate_state_rejection": false,
      "slow_query_warning": false,
      "slow_query_ms": null,
      "slow_query": null,
      "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'IT-00217' for key 'uq_tickets_no'",
      "request_uri": "/index.php?page=new-ticket",
      "created_at": "2026-10-22 21:23:18"
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
      "created_at": "2026-10-22 21:23:25"
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
      "created_at": "2026-10-22 21:23:25"
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
      "created_at": "2026-10-22 21:23:25"
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
      "created_at": "2026-10-22 21:23:25"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
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
      "created_at": "2026-10-22 21:23:26"
    }
  ]
}
```

## یکپارچگی رقابت سریال CD/DVD

- جفت‌های هم‌زمان تکراری: 60؛ جفت‌هایی که دقیقاً یک درخواست موفق داشتند: 60
- رخدادهای متوالیِ هم‌جهت برای یک سریال در دادهٔ نهایی: 0
- شکست‌های invariant: ["ticket creates confirmed by HTTP: 226/250", "ticket resolutions confirmed by HTTP: 226/250", "database final resolved-ticket count did not equal 250", "database-created ticket count did not equal 250", "ticket numbers were missing or not unique in the final database state", "unexpected application/system errors were recorded during the test"]

## محدودیت‌ها
- This run directly exercises the ticket create/update and CD/DVD create handlers through authenticated HTTP POSTs; it is not exhaustive coverage of every module or every route.
- Food external Access/ODBC sources, LDAP/Active Directory, physical media, printers, and production/staging capacity were not exercised.
- The database and all test accounts/records are disposable synthetic CI fixtures; no production or staging traffic/data was used.
- HTTP response, application confirmation, PHP logs, and independently queried final database state are reported separately.
