ALTER TABLE `users` add `trainercode` varchar(12) CHARACTER SET utf8mb4 DEFAULT NULL AFTER `trainername`;
ALTER TABLE `users` ADD `tutorial` TINYINT(1) NOT NULL DEFAULT 0 AFTER `level`;
CREATE TABLE `user_input` (`id` int(11) NOT NULL AUTO_INCREMENT,`user_id` int(11) DEFAULT NULL,`handler` varchar(45) DEFAULT NULL,`modifiers` text DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `events` (`id` int(3) unsigned NOT NULL AUTO_INCREMENT,`name` varchar(45) CHARACTER SET latin1 NOT NULL,`description` varchar(200) CHARACTER SET latin1 NOT NULL,`vote_key_mode` int(3) NOT NULL DEFAULT 0,`time_slots` int(3) DEFAULT NULL,`raid_duration` int(3) unsigned NOT NULL DEFAULT 0,`hide_raid_picture` tinyint(1) DEFAULT 0, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
ALTER TABLE `raids` ADD `event` int(3) unsigned DEFAULT NULL AFTER `gender`;
ALTER TABLE `raids` ADD `event_note` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL AFTER `event`;