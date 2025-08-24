<?php

namespace Okay\Modules\OkayCMS\Multiregions\Frontend;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\ServiceLocator;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainsEntity;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainSeoEntity;
use Okay\Modules\OkayCMS\Multiregions\Helpers\CityDeclension;

class MultiregionsPlugin
{
    private $design;
    private $entityFactory;
    private $currentSubdomain = null;
    private $cityDeclension;
    
    public function __construct()
    {
        $SL = ServiceLocator::getInstance();
        $this->design = $SL->getService(Design::class);
        $this->entityFactory = $SL->getService(EntityFactory::class);
        $this->cityDeclension = new CityDeclension();
        
        // Initialize on construction
        $this->init();
    }
    
    public function init()
    {
        // Detect subdomain
        $this->detectSubdomain();
        
        // If subdomain detected, set template variables
        if ($this->currentSubdomain) {
            $this->assignTemplateVariables();
        }
    }
    
    private function detectSubdomain()
    {
        // Check GET parameter from .htaccess
        $cityCode = $_GET['subdomain_city'] ?? null;
        
        // If not in GET, check subdomain directly
        if (!$cityCode) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            
            if (!empty($host)) {
                // Remove www if present
                $host = preg_replace('/^www\./i', '', $host);
                
                // Check for subdomain pattern
                if (preg_match('/^([a-z0-9-]+)\.gidro-butik\.ru$/i', $host, $matches)) {
                    // Exclude technical subdomains
                    if (!in_array($matches[1], ['www', 'mail', 'ftp', 'admin', 'api', 'dev'])) {
                        $cityCode = $matches[1];
                    }
                }
            }
        }
        
        if (!$cityCode) {
            return;
        }
        
        // Get subdomain from database
        try {
            $subdomainsEntity = $this->entityFactory->get(SubdomainsEntity::class);
            $subdomain = $subdomainsEntity->findOne([
                'subdomain' => $cityCode,
                'enabled' => 1
            ]);
            
            if ($subdomain) {
                $this->currentSubdomain = $subdomain;
            }
        } catch (\Exception $e) {
            // Log error but don't break the site
            error_log('Multiregions: Error detecting subdomain: ' . $e->getMessage());
        }
    }
    
    private function assignTemplateVariables()
    {
        if (!$this->currentSubdomain) {
            return;
        }
        
        // Basic subdomain info
        $this->design->assign('current_subdomain', $this->currentSubdomain);
        $this->design->assign('current_city', $this->currentSubdomain->city_name);
        
        // Prepare city cases
        $cases = [
            'nominative' => $this->currentSubdomain->city_nominative ?: $this->currentSubdomain->city_name,
            'genitive' => $this->currentSubdomain->city_genitive ?: $this->currentSubdomain->city_name,
            'dative' => $this->currentSubdomain->city_dative ?: $this->currentSubdomain->city_name,
            'accusative' => $this->currentSubdomain->city_accusative ?: $this->currentSubdomain->city_name,
            'instrumental' => $this->currentSubdomain->city_instrumental ?: $this->currentSubdomain->city_name,
            'prepositional' => $this->currentSubdomain->city_prepositional ?: $this->currentSubdomain->city_name,
        ];
        
        // Add preposition combinations
        $cases['in_city'] = 'в ' . $cases['prepositional'];
        $cases['from_city'] = 'из ' . $cases['genitive'];
        $cases['to_city'] = 'в ' . $cases['accusative'];
        
        $this->design->assign('city_cases', $cases);
        
        // Process SEO if needed
        $this->processSeo();
    }
    
    private function processSeo()
    {
        // Get current page type
        $pageType = $this->determinePageType();
        
        if (!$pageType) {
            return;
        }
        
        // Get SEO patterns
        $subdomainSeoEntity = $this->entityFactory->get(SubdomainSeoEntity::class);
        $seoPattern = $subdomainSeoEntity->findOne([
            'subdomain_id' => $this->currentSubdomain->id,
            'page_type' => $pageType
        ]);
        
        if (!$seoPattern) {
            return;
        }
        
        // Process patterns and update meta tags
        $this->applySeoPatterns($seoPattern);
    }
    
    private function determinePageType()
    {
        // Get current URI
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $uri = strtok($uri, '?'); // Remove query string
        
        // Check if it's homepage
        if ($uri == '/' || $uri == '' || $uri == '/index.php') {
            return 'main';
        }
        
        // Check URL patterns
        if (strpos($uri, '/products/') !== false) {
            return 'product';
        }
        
        if (strpos($uri, '/catalog/') !== false) {
            return 'category';
        }
        
        if (strpos($uri, '/brands/') !== false || strpos($uri, '/brand/') !== false) {
            return 'brand';
        }
        
        if (strpos($uri, '/blog/') !== false) {
            return 'blog';
        }
        
        // Check for info pages
        if (strpos($uri, '/info/') !== false || strpos($uri, '/page/') !== false) {
            return 'page';
        }
        
        // Default to category for catalog pages
        if (preg_match('/^\/[a-z0-9_-]+$/i', $uri)) {
            // Single segment URLs are often categories in OkayCMS
            return 'category';
        }
        
        return null;
    }
    
    private function applySeoPatterns($seoPattern)
    {
        // Get current template variables
        $category = $this->design->getVar('category');
        $product = $this->design->getVar('product');
        $brand = $this->design->getVar('brand');
        $page = $this->design->getVar('page');
        
        // Prepare replacement variables
        $vars = [
            '{city}' => $this->currentSubdomain->city_name,
            '{city_nominative}' => $this->currentSubdomain->city_nominative ?: $this->currentSubdomain->city_name,
            '{city_genitive}' => $this->currentSubdomain->city_genitive ?: $this->currentSubdomain->city_name,
            '{city_dative}' => $this->currentSubdomain->city_dative ?: $this->currentSubdomain->city_name,
            '{city_accusative}' => $this->currentSubdomain->city_accusative ?: $this->currentSubdomain->city_name,
            '{city_instrumental}' => $this->currentSubdomain->city_instrumental ?: $this->currentSubdomain->city_name,
            '{city_prepositional}' => $this->currentSubdomain->city_prepositional ?: $this->currentSubdomain->city_name,
            '{in_city}' => 'в ' . ($this->currentSubdomain->city_prepositional ?: $this->currentSubdomain->city_name),
        ];
        
        // Add content-specific variables
        if ($category) {
            $vars['{category}'] = $category->name;
        }
        if ($product) {
            $vars['{product}'] = $product->name;
            if (isset($product->variant)) {
                $vars['{price}'] = $product->variant->price;
            }
        }
        if ($brand) {
            $vars['{brand}'] = $brand->name;
        }
        if ($page) {
            $vars['{page_name}'] = $page->name;
        }
        
        // Process and apply patterns
        if (!empty($seoPattern->meta_title_pattern)) {
            $metaTitle = strtr($seoPattern->meta_title_pattern, $vars);
            $this->design->assign('meta_title', $metaTitle);
        }
        
        if (!empty($seoPattern->meta_description_pattern)) {
            $metaDescription = strtr($seoPattern->meta_description_pattern, $vars);
            $this->design->assign('meta_description', $metaDescription);
        }
        
        if (!empty($seoPattern->meta_keywords_pattern)) {
            $metaKeywords = strtr($seoPattern->meta_keywords_pattern, $vars);
            $this->design->assign('meta_keywords', $metaKeywords);
        }
        
        if (!empty($seoPattern->h1_pattern)) {
            $h1 = strtr($seoPattern->h1_pattern, $vars);
            $this->design->assign('h1', $h1);
            $this->design->assign('page_h1', $h1);
        }
    }
}