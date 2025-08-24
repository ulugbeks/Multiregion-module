<?php

namespace Okay\Modules\OkayCMS\Multiregions\Helpers;

use Okay\Core\ServiceLocator;
use Okay\Core\EntityFactory;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainsEntity;

class SubdomainDetector
{
    private $currentSubdomain = null;
    private $detected = false;
    private $entityFactory;
    
    public function __construct()
    {
        // We'll lazy-load the entity factory when needed
    }
    
    private function getEntityFactory()
    {
        if (!$this->entityFactory) {
            $serviceLocator = ServiceLocator::getInstance();
            $this->entityFactory = $serviceLocator->getService(EntityFactory::class);
        }
        return $this->entityFactory;
    }
    
    /**
     * Get current subdomain
     */
    public function getCurrentSubdomain()
    {
        if (!$this->detected) {
            $this->detect();
        }
        
        return $this->currentSubdomain;
    }
    
    /**
     * Detect subdomain from URL
     */
    public function detect()
    {
        $this->detected = true;
        
        // First check GET parameter from .htaccess
        $cityCode = $_GET['subdomain_city'] ?? null;
        
        // If not in GET, check subdomain directly
        if (!$cityCode) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            
            if (!empty($host)) {
                // Remove www if present
                $host = preg_replace('/^www\./i', '', $host);
                
                // Check patterns for dev and main domain
                if (preg_match('/^([a-z0-9-]+)\.dev\.gidro-butik\.ru$/i', $host, $matches)) {
                    $cityCode = $matches[1];
                } elseif (preg_match('/^([a-z0-9-]+)\.gidro-butik\.ru$/i', $host, $matches)) {
                    // Exclude technical subdomains
                    if (!in_array($matches[1], ['www', 'mail', 'ftp', 'admin', 'api', 'dev'])) {
                        $cityCode = $matches[1];
                    }
                }
            }
        }
        
        if (!$cityCode) {
            return null;
        }
        
        // Check if subdomain exists in database
        try {
            $subdomainsEntity = $this->getEntityFactory()->get(SubdomainsEntity::class);
            
            $subdomain = $subdomainsEntity->findOne([
                'subdomain' => $cityCode,
                'enabled' => 1
            ]);
            
            if ($subdomain) {
                $this->currentSubdomain = $subdomain;
                
                // Save to global variable for access from anywhere
                $GLOBALS['current_subdomain'] = $subdomain;
            }
        } catch (\Exception $e) {
            // Log error but don't break the site
            error_log('Multiregions: Error detecting subdomain: ' . $e->getMessage());
        }
        
        return $this->currentSubdomain;
    }
    
    /**
     * Check if we're on a subdomain
     */
    public function hasSubdomain()
    {
        return $this->getCurrentSubdomain() !== null;
    }
    
    /**
     * Get base domain without subdomain
     */
    public function getBaseDomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        if (empty($host)) {
            return '';
        }
        
        // Remove www if present
        $host = preg_replace('/^www\./i', '', $host);
        
        // Remove subdomain if present
        if (preg_match('/^[a-z0-9-]+\.(dev\.)?gidro-butik\.ru$/i', $host)) {
            // If it's a dev subdomain
            if (strpos($host, '.dev.gidro-butik.ru') !== false) {
                return 'dev.gidro-butik.ru';
            }
            // If it's a regular subdomain
            elseif (preg_match('/^[a-z0-9-]+\.gidro-butik\.ru$/i', $host)) {
                return 'gidro-butik.ru';
            }
        }
        
        return $host;
    }
    
    /**
     * Generate URL for subdomain
     */
    public function generateSubdomainUrl($subdomain, $path = '/')
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $baseDomain = $this->getBaseDomain();
        
        if (is_object($subdomain)) {
            $subdomain = $subdomain->subdomain;
        }
        
        // Ensure correct path
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $protocol . '://' . $subdomain . '.' . $baseDomain . $path;
    }
}