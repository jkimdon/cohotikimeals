ALTER TABLE tiki_calendar_recurrence ADD COLUMN `monthlyByWeekday` tinyint(1) default 0 AFTER `dayOfMonth`;
ALTER TABLE tiki_calendar_recurrence ADD COLUMN `monthlyWeekday` tinyint(1) default NULL AFTER `monthlyByWeekday`;
ALTER TABLE tiki_calendar_recurrence ADD COLUMN `monthlyWeekNumber` tinyint(1) default NULL AFTER `monthlyWeekday`;
