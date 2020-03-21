CREATE TABLE cohomeals_costshare_log (
       `logId` int(8) NOT NULL AUTO_INCREMENT,
       `dateEntered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
       `lender_billing_group_number` INT(11) NOT NULL,
       `borrower_billing_group_number` INT(11) NOT NULL,
       `amount` INT(11),
       `memo` text,
       PRIMARY KEY (`logId`)
);
CREATE TABLE cohomeals_costshare_quickref (
       `lender_billing_group_number` INT(11) NOT NULL,
       `borrower_billing_group_number` INT(11) NOT NULL,
       `amount` INT(11),
       `memo` text,
       PRIMARY KEY (`lender_billing_group_number`, `borrower_billing_group_number`)
);
