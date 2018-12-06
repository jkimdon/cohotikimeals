ALTER TABLE cohomeals_financial_log MODIFY COLUMN cal_log_id INT auto_increment;
CREATE TABLE cohomeals_multiplier (
       `userId` varchar(25) NOT NULL,
       `startdate` int(8) DEFAULT 0,
       `enddate` int(8) DEFAULT 99999999,
       `multiplier` decimal(4,2),
       PRIMARY KEY (`userId`, `startdate`)
);
