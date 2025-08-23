<?php

namespace Okay\Modules\OkayCMS\Multiregions\Helpers;

use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Core\Config;

class SubdomainDetector
{
    private $db;
    private $queryFactory;
    private $config;
    private $currentSubdomain = null;
    private $detected = false;
    
    public function __construct()
    {
        // Получаем экземпляры через синглтоны
        $this->db = Database::getInstance();
        $this->queryFactory = QueryFactory::getInstance();
        $this->config = Config::getInstance();
    }
    
    /**
     * Получить текущий поддомен
     */
    public function getCurrentSubdomain()
    {
        if (!$this->detected) {
            $this->detect();
        }
        
        return $this->currentSubdomain;
    }
    
    /**
     * Определить поддомен из URL
     */
    public function detect()
    {
        $this->detected = true;
        
        // Сначала проверяем GET параметр от .htaccess
        $cityCode = $_GET['subdomain_city'] ?? null;
        
        // Если нет в GET, проверяем поддомен напрямую
        if (!$cityCode) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            
            if (!empty($host)) {
                // Убираем www если есть
                $host = preg_replace('/^www\./i', '', $host);
                
                // Проверяем паттерны для dev и основного домена
                if (preg_match('/^([a-z0-9-]+)\.dev\.gidro-butik\.ru$/i', $host, $matches)) {
                    $cityCode = $matches[1];
                } elseif (preg_match('/^([a-z0-9-]+)\.gidro-butik\.ru$/i', $host, $matches)) {
                    // Исключаем технические поддомены
                    if (!in_array($matches[1], ['www', 'mail', 'ftp', 'admin', 'api', 'dev'])) {
                        $cityCode = $matches[1];
                    }
                }
            }
        }
        
        // Альтернатива - проверяем /city/xxx/ в URL
        if (!$cityCode) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#^/city/([a-z0-9-]+)/?#i', $uri, $matches)) {
                $cityCode = $matches[1];
            }
        }
        
        // Еще альтернатива - GET параметр city
        if (!$cityCode) {
            $cityCode = $_GET['city'] ?? null;
        }
        
        if (!$cityCode) {
            return null;
        }
        
        // Получаем префикс таблиц
        $prefix = $this->config->db_prefix ?? 'ok_';
        
        // Проверяем, существует ли такой поддомен в базе
        try {
            $query = $this->queryFactory->newSqlQuery();
            $query->setStatement(
                "SELECT * FROM {$prefix}subdomains 
                 WHERE subdomain = :subdomain 
                 AND enabled = 1 
                 LIMIT 1"
            );
            $query->bindValue('subdomain', $cityCode);
            
            $this->db->query($query);
            $subdomain = $this->db->result();
            
            if ($subdomain) {
                $this->currentSubdomain = $subdomain;
                
                // Сохраняем в глобальную переменную для доступа из любого места
                $GLOBALS['current_subdomain'] = $subdomain;
            }
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем работу сайта
            error_log('Multiregions: Error detecting subdomain: ' . $e->getMessage());
        }
        
        return $this->currentSubdomain;
    }
    
    /**
     * Проверить, находимся ли мы на поддомене
     */
    public function hasSubdomain()
    {
        return $this->getCurrentSubdomain() !== null;
    }
    
    /**
     * Получить базовый домен без поддомена
     */
    public function getBaseDomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        if (empty($host)) {
            return '';
        }
        
        // Убираем www если есть
        $host = preg_replace('/^www\./i', '', $host);
        
        // Убираем поддомен если есть
        if (preg_match('/^[a-z0-9-]+\.(dev\.)?gidro-butik\.ru$/i', $host)) {
            // Если это dev поддомен
            if (strpos($host, '.dev.gidro-butik.ru') !== false) {
                return 'dev.gidro-butik.ru';
            }
            // Если это обычный поддомен основного домена
            elseif (preg_match('/^[a-z0-9-]+\.gidro-butik\.ru$/i', $host)) {
                return 'gidro-butik.ru';
            }
        }
        
        return $host;
    }
    
    /**
     * Сгенерировать URL для поддомена
     */
    public function generateSubdomainUrl($subdomain, $path = '/')
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $baseDomain = $this->getBaseDomain();
        
        if (is_object($subdomain)) {
            $subdomain = $subdomain->subdomain;
        }
        
        // Обеспечиваем корректный путь
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $protocol . '://' . $subdomain . '.' . $baseDomain . $path;
    }
    
    /**
     * Получить URL для основного домена (без поддомена)
     */
    public function getMainDomainUrl($path = '/')
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $baseDomain = $this->getBaseDomain();
        
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $protocol . '://' . $baseDomain . $path;
    }
    
    /**
     * Установить текущий поддомен (для тестирования)
     */
    public function setCurrentSubdomain($subdomain)
    {
        $this->currentSubdomain = $subdomain;
        $this->detected = true;
        $GLOBALS['current_subdomain'] = $subdomain;
    }
    
    /**
     * Сбросить определение (для тестирования)
     */
    public function reset()
    {
        $this->currentSubdomain = null;
        $this->detected = false;
        unset($GLOBALS['current_subdomain']);
    }
}