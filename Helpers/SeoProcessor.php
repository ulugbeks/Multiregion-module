<?php

namespace Okay\Modules\OkayCMS\Multiregions\Helpers;

use Okay\Core\ServiceLocator;
use Okay\Core\EntityFactory;
use Okay\Core\Settings;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainSeoEntity;

class SeoProcessor
{
    private $subdomainDetector;
    private $cityDeclension;
    private $variables = [];
    private $entityFactory;
    private $settings;
    
    public function __construct()
    {
        $this->subdomainDetector = new SubdomainDetector();
        $this->cityDeclension = new CityDeclension();
    }
    
    private function getEntityFactory()
    {
        if (!$this->entityFactory) {
            $serviceLocator = ServiceLocator::getInstance();
            $this->entityFactory = $serviceLocator->getService(EntityFactory::class);
        }
        return $this->entityFactory;
    }
    
    private function getSettings()
    {
        if (!$this->settings) {
            $serviceLocator = ServiceLocator::getInstance();
            $this->settings = $serviceLocator->getService(Settings::class);
        }
        return $this->settings;
    }
    
    /**
     * Process SEO for current page
     */
    public function processSeo($pageType, $pageData = [])
    {
        $subdomain = $this->subdomainDetector->getCurrentSubdomain();
        
        if (!$subdomain) {
            return null;
        }
        
        // Get SEO templates for subdomain
        $subdomainSeoEntity = $this->getEntityFactory()->get(SubdomainSeoEntity::class);
        $seoPattern = $subdomainSeoEntity->getPattern($subdomain->id, $pageType);
        
        if (!$seoPattern) {
            return null;
        }
        
        // Prepare variables
        $this->prepareVariables($subdomain, $pageData);
        
        // Process templates
        $result = new \stdClass();
        $result->meta_title = $this->processTemplate($seoPattern->meta_title_pattern);
        $result->meta_description = $this->processTemplate($seoPattern->meta_description_pattern);
        $result->meta_keywords = $this->processTemplate($seoPattern->meta_keywords_pattern);
        $result->h1 = $this->processTemplate($seoPattern->h1_pattern);
        $result->description = $this->processTemplate($seoPattern->description_pattern);
        
        return $result;
    }
    
    /**
     * Process template with variable replacement
     */
    public function processTemplate($template)
    {
        if (empty($template)) {
            return '';
        }
        
        // Replace variables
        foreach ($this->variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        // Remove unreplaced variables
        $template = preg_replace('/\{[^}]+\}/', '', $template);
        
        // Remove extra spaces
        $template = preg_replace('/\s+/', ' ', $template);
        
        return trim($template);
    }
    
    /**
     * Prepare variables for replacement
     */
    private function prepareVariables($subdomain, $pageData)
    {
        $this->variables = [];
        
        // City declensions
        $cases = [
            'city_nominative' => $subdomain->city_nominative,
            'city_genitive' => $subdomain->city_genitive,
            'city_dative' => $subdomain->city_dative,
            'city_accusative' => $subdomain->city_accusative,
            'city_instrumental' => $subdomain->city_instrumental,
            'city_prepositional' => $subdomain->city_prepositional
        ];
        
        // If some declensions are not filled, use automatic ones
        foreach ($cases as $key => $value) {
            if (empty($value)) {
                $caseName = str_replace('city_', '', $key);
                $cases[$key] = $this->cityDeclension->getCase($subdomain->city_name, $caseName);
            }
        }
        
        // Main city variables
        $this->variables['city'] = $cases['city_nominative'];
        $this->variables['city_nominative'] = $cases['city_nominative'];
        $this->variables['city_genitive'] = $cases['city_genitive'];
        $this->variables['city_dative'] = $cases['city_dative'];
        $this->variables['city_accusative'] = $cases['city_accusative'];
        $this->variables['city_instrumental'] = $cases['city_instrumental'];
        $this->variables['city_prepositional'] = $cases['city_prepositional'];
        
        // Prepositions with city
        $this->variables['in_city'] = 'в ' . $cases['city_prepositional'];
        $this->variables['from_city'] = 'из ' . $cases['city_genitive'];
        $this->variables['to_city'] = 'в ' . $cases['city_accusative'];
        
        // Page data
        if (isset($pageData['category'])) {
            $category = $pageData['category'];
            $this->variables['category'] = $category->name ?? '';
            $this->variables['category_h1'] = $category->name_h1 ?? $category->name ?? '';
        }
        
        if (isset($pageData['product'])) {
            $product = $pageData['product'];
            $this->variables['product'] = $product->name ?? '';
            $this->variables['product_h1'] = $product->name ?? '';
            
            if (isset($product->variant)) {
                $this->variables['price'] = $product->variant->price ?? '';
                $this->variables['old_price'] = $product->variant->compare_price ?? '';
            }
        }
        
        if (isset($pageData['brand'])) {
            $brand = $pageData['brand'];
            $this->variables['brand'] = $brand->name ?? '';
        }
        
        if (isset($pageData['post'])) {
            $post = $pageData['post'];
            $this->variables['post_name'] = $post->name ?? '';
            $this->variables['post_description'] = strip_tags($post->annotation ?? '');
        }
        
        if (isset($pageData['page'])) {
            $page = $pageData['page'];
            $this->variables['page_name'] = $page->name ?? '';
        }
        
        // General site variables
        $settings = $this->getSettings();
        $this->variables['site_name'] = $settings->get('site_name');
        $this->variables['company_name'] = $settings->get('company_name');
        $this->variables['phone'] = $settings->get('phone');
        $this->variables['email'] = $settings->get('email');
    }
    
    /**
     * Get list of all available variables
     */
    public static function getAvailableVariables()
    {
        return [
            'Город' => [
                '{city}' => 'Название города (Москва)',
                '{city_nominative}' => 'Именительный падеж (Москва)',
                '{city_genitive}' => 'Родительный падеж (Москвы)',
                '{city_dative}' => 'Дательный падеж (Москве)',
                '{city_accusative}' => 'Винительный падеж (Москву)',
                '{city_instrumental}' => 'Творительный падеж (Москвой)',
                '{city_prepositional}' => 'Предложный падеж (Москве)',
                '{in_city}' => 'В городе (в Москве)',
                '{from_city}' => 'Из города (из Москвы)',
                '{to_city}' => 'В город (в Москву)'
            ],
            'Контент' => [
                '{category}' => 'Название категории',
                '{product}' => 'Название товара',
                '{price}' => 'Цена товара',
                '{brand}' => 'Название бренда',
                '{post_name}' => 'Название статьи',
                '{page_name}' => 'Название страницы'
            ],
            'Сайт' => [
                '{site_name}' => 'Название сайта',
                '{company_name}' => 'Название компании',
                '{phone}' => 'Телефон',
                '{email}' => 'Email'
            ]
        ];
    }
}