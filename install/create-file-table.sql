CREATE TABLE IF NOT EXISTS `uploadcloud`.`file` ( 
 	`id` int(11) NOT NULL AUTO_INCREMENT, 
    `user_id` int(11) NOT NULL, 
    `fake_name_of_file` varchar(255) NOT NULL, 
    `real_name_of_file` varchar(255) NOT NULL,
    `description` VARCHAR(255) NULL;
    `active` int(1) NOT NULL 
    PRIMARY KEY (`id`)
)