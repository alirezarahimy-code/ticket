-- هر مقصد ملاقات می‌تواند ۱ تا ۳ تأییدکننده داشته باشد؛ approver_name همان تأییدکنندهٔ اصلی می‌ماند.
ALTER TABLE traffic_destinations
    ADD COLUMN IF NOT EXISTS approver_name2 VARCHAR(190) NULL AFTER approver_name;
ALTER TABLE traffic_destinations
    ADD COLUMN IF NOT EXISTS approver_name3 VARCHAR(190) NULL AFTER approver_name2;
