-- Таблица поддоменов/регионов
-- Префикс __ будет автоматически заменен на префикс из конфига
CREATE TABLE IF NOT EXISTS `__subdomains` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subdomain` VARCHAR(50) NOT NULL,
    `city_name` VARCHAR(100) NOT NULL,
    `city_nominative` VARCHAR(100) DEFAULT NULL COMMENT 'Именительный: Москва',
    `city_genitive` VARCHAR(100) DEFAULT NULL COMMENT 'Родительный: Москвы', 
    `city_dative` VARCHAR(100) DEFAULT NULL COMMENT 'Дательный: Москве',
    `city_accusative` VARCHAR(100) DEFAULT NULL COMMENT 'Винительный: Москву',
    `city_instrumental` VARCHAR(100) DEFAULT NULL COMMENT 'Творительный: Москвой',
    `city_prepositional` VARCHAR(100) DEFAULT NULL COMMENT 'Предложный: Москве',
    `enabled` TINYINT(1) DEFAULT 1,
    `position` INT(11) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subdomain` (`subdomain`),
    KEY `enabled` (`enabled`),
    KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица SEO шаблонов для поддоменов
CREATE TABLE IF NOT EXISTS `__subdomain_seo` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subdomain_id` INT(11) NOT NULL,
    `page_type` VARCHAR(50) NOT NULL COMMENT 'main, category, product, brand, blog, page',
    `meta_title_pattern` TEXT DEFAULT NULL,
    `meta_description_pattern` TEXT DEFAULT NULL,
    `meta_keywords_pattern` TEXT DEFAULT NULL,
    `h1_pattern` TEXT DEFAULT NULL,
    `description_pattern` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subdomain_page` (`subdomain_id`, `page_type`),
    KEY `subdomain_id` (`subdomain_id`),
    CONSTRAINT `fk_subdomain_seo` FOREIGN KEY (`subdomain_id`) 
        REFERENCES `__subdomains` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Примеры поддоменов для тестирования (опционально)
-- Раскомментируйте если хотите добавить тестовые данные
/*
INSERT INTO `__subdomains` (`subdomain`, `city_name`, `city_nominative`, `city_genitive`, `city_dative`, `city_accusative`, `city_instrumental`, `city_prepositional`, `enabled`, `position`) VALUES
('spb', 'Санкт-Петербург', 'Санкт-Петербург', 'Санкт-Петербурга', 'Санкт-Петербургу', 'Санкт-Петербург', 'Санкт-Петербургом', 'Санкт-Петербурге', 1, 1),
('msk', 'Москва', 'Москва', 'Москвы', 'Москве', 'Москву', 'Москвой', 'Москве', 1, 2),
('ekb', 'Екатеринбург', 'Екатеринбург', 'Екатеринбурга', 'Екатеринбургу', 'Екатеринбург', 'Екатеринбургом', 'Екатеринбурге', 1, 3);

-- Примеры SEO шаблонов
INSERT INTO `__subdomain_seo` (`subdomain_id`, `page_type`, `meta_title_pattern`, `meta_description_pattern`, `h1_pattern`) VALUES
(1, 'main', '{site_name} в {city_prepositional} - интернет-магазин сантехники', 'Купить сантехнику в {city_prepositional} с доставкой. ✓ Большой выбор ✓ Низкие цены ✓ Гарантия качества', 'Интернет-магазин сантехники в {city_prepositional}'),
(1, 'category', '{category} в {city_prepositional} - купить по низким ценам | {site_name}', '{category} в {city_prepositional} с доставкой. Большой выбор, низкие цены. Доставка по {city_dative}.', '{category} в {city_prepositional}');
*/