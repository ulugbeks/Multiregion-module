<?php

namespace Okay\Modules\OkayCMS\Multiregions\Init;

use Okay\Core\Modules\AbstractInit;

class Init extends AbstractInit
{
    const PERMISSION = 'multiregions';

    public function install()
    {
        // Create tables using raw SQL with proper prefix handling
        $this->db->query("CREATE TABLE IF NOT EXISTS `__subdomains` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `subdomain` VARCHAR(50) NOT NULL,
            `city_name` VARCHAR(100) NOT NULL,
            `city_nominative` VARCHAR(100) DEFAULT NULL,
            `city_genitive` VARCHAR(100) DEFAULT NULL,
            `city_dative` VARCHAR(100) DEFAULT NULL,
            `city_accusative` VARCHAR(100) DEFAULT NULL,
            `city_instrumental` VARCHAR(100) DEFAULT NULL,
            `city_prepositional` VARCHAR(100) DEFAULT NULL,
            `enabled` TINYINT(1) DEFAULT 1,
            `position` INT(11) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `subdomain` (`subdomain`),
            KEY `enabled` (`enabled`),
            KEY `position` (`position`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `__subdomain_seo` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `subdomain_id` INT(11) NOT NULL,
            `page_type` VARCHAR(50) NOT NULL,
            `meta_title_pattern` TEXT DEFAULT NULL,
            `meta_description_pattern` TEXT DEFAULT NULL,
            `meta_keywords_pattern` TEXT DEFAULT NULL,
            `h1_pattern` TEXT DEFAULT NULL,
            `description_pattern` TEXT DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `subdomain_page` (`subdomain_id`, `page_type`),
            KEY `subdomain_id` (`subdomain_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    
    public function init()
    {
        // Register permission using the correct method
        $this->addPermission(self::PERMISSION);
        
        // Set the main backend controller
        $this->setBackendMainController('MultiregionsAdmin');
        
        // Register backend controllers
        $this->registerBackendController('MultiregionsAdmin');
        $this->addBackendControllerPermission('MultiregionsAdmin', self::PERMISSION);
        
        $this->registerBackendController('MultiregionAdmin');
        $this->addBackendControllerPermission('MultiregionAdmin', self::PERMISSION);
        
        // Add menu item to admin panel - using the correct method signature
        $this->extendBackendMenu('left_settings', [
            'left_multiregions_title' => ['MultiregionsAdmin', 'MultiregionAdmin'],
        ]);
    }
}