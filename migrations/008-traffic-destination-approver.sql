ALTER TABLE traffic_destinations
    ADD COLUMN IF NOT EXISTS approver_name VARCHAR(190) NULL;
