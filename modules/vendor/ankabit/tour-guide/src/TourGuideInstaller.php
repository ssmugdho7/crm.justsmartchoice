<?php

namespace TourGuide;

class TourGuideInstaller
{
    static function install()
    {
        $TG = tourGuideHelper()->dbInstance();
        $dbPrefix = $TG->getPrefix();

        // Add tour guide table
        $table = $dbPrefix . 'tour_guide';
        $query = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(150) NOT NULL,
            `description` text,
            `priority` INT NOT NULL DEFAULT 1,
            `status` varchar(50) DEFAULT 'active',
            `steps` MEDIUMTEXT,
            `settings` text,
            `steps_translations` MEDIUMTEXT,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET='" . $TG->getCharset() . "' COLLATE='" . $TG->getCollation() . "';";
        $TG->runRawQuery($query);

        // Add meta table for storing any other data
        $table = $dbPrefix . 'tour_guide_metadata';
        $query = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(150) NOT NULL UNIQUE,
            `value` MEDIUMTEXT,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET='" . $TG->getCharset() . "'  COLLATE='" . $TG->getCollation() . "';";
        $TG->runRawQuery($query);
    }

    static function uninstall()
    {
        $TG = tourGuideHelper()->dbInstance();
        $dbPrefix = $TG->getPrefix();

        $table = $dbPrefix . 'tour_guide';
        $query = "DROP TABLE IF EXISTS `$table`;";
        $TG->runRawQuery($query);

        $table = $dbPrefix . 'tour_guide_metadata';
        $query = "DROP TABLE IF EXISTS `$table`;";
        $TG->runRawQuery($query);
    }
}