ALTER TABLE tiki_calendar_items ADD COLUMN `recurrence_override` int(14) default 0 AFTER `recurrenceId`;
ALTER TABLE tiki_calendar_roles ADD COLUMN `recurrenceId` int(14) DEFAULT 0 AFTER `calitemId`;
ALTER TABLE tiki_calendar_roles DROP PRIMARY KEY, ADD PRIMARY KEY (calitemId, recurrenceId, username, role);
