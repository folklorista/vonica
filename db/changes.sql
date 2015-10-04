ALTER TABLE `reservation`
CHANGE `confirmed` `confirmed` timestamp NULL AFTER `created`;
UPDATE `reservation` SET `changed` = `changed`, `confirmed` = NULL;

ALTER TABLE `reservation`
ADD `type` varchar(32) COLLATE 'utf8_czech_ci' NOT NULL DEFAULT 'default' AFTER `note`;

UPDATE `reservation` SET `type` = 'distribution' WHERE `id` = '1';
UPDATE `reservation` SET `type` = 'ensemble' WHERE `id` = '12';

ALTER TABLE `reservation`
ADD `cancelled` timestamp NULL;