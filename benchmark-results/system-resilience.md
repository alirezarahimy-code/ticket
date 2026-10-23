# سامانهٔ آزمون تاب‌آوری مسیرها و APIها — شکست در راه‌اندازی

- خطا: `UnboundLocalError: cannot access local variable 'urllib' where it is not associated with a value`

```text
Traceback (most recent call last):
  File "/home/runner/work/ticket/ticket/tools/benchmark_system_resilience.py", line 329, in <module>
    raise SystemExit(main())
                     ^^^^^^
  File "/home/runner/work/ticket/ticket/tools/benchmark_system_resilience.py", line 118, in main
    parsed = urllib.parse.urlparse(args.base_url)
             ^^^^^^
UnboundLocalError: cannot access local variable 'urllib' where it is not associated with a value
```
