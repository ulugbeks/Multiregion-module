<?php

namespace Okay\Modules\OkayCMS\Multiregions\Helpers;

use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Core\Config;
use Okay\Core\Settings;

class SeoProcessor
{
    private $db;
    private $queryFactory;
    private $config;
    private $settings;
    private $subdomainDetector;
    private $cityDeclension;
    private $variables = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->queryFactory = QueryFactory::getInstance();
        $this->config = Config::getInstance();
        $this->settings = Settings::getInstance();
        $this->subdomainDetector = new SubdomainDetector();
        $this->cityDeclension = new CityDeclension();
    }
    
    /**
     * Обработать SEO для текущей страницы
     */
    public function processSeo($pageType, $pageData = [])
    {
        $subdomain = $this->subdomainDetector->getCurrentSubdomain();
        
        if (!$subdomain) {
            return null;
        }
        
        // Получаем префикс таблиц
        $prefix = $this->config->db_prefix ?? 'ok_';
        
        // Получаем SEO шаблоны для поддомена
        $query = $this->queryFactory->newSqlQuery();
        $query->setStatement(
            "SELECT * FROM {$prefix}subdomain_seo 
             WHERE subdomain_id = :subdomain_id 
             AND page_type = :page_type 
             LIMIT 1"
        );
        $query->bindValue('subdomain_id', $subdomain->id);
        $query->bindValue('page_type', $pageType);
        
        $this->db->query($query);
        $seoPattern = $this->db->result();
        
        if (!$seoPattern) {
            return null;
        }
        
        // Подготавливаем переменные
        $this->prepareVariables($subdomain, $pageData);
        
        // Обрабатываем шаблоны
        $result = new \stdClass();
        $result->meta_title = $this->processTemplate($seoPattern->meta_title_pattern);
        $result->meta_description = $this->processTemplate($seoPattern->meta_description_pattern);
        $result->meta_keywords = $this->processTemplate($seoPattern->meta_keywords_pattern);
        $result->h1 = $this->processTemplate($seoPattern->h1_pattern);
        $result->description = $this->processTemplate($seoPattern->description_pattern);
        
        return $result;
    }
    
    /**
     * Обработать шаблон с заменой переменных
     */
    public function processTemplate($template)
    {
        if (empty($template)) {
            return '';
        }
        
        // Заменяем переменные
        foreach ($this->variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        // Удаляем незамененные переменные
        $template = preg_replace('/\{[^}]+\}/', '', $template);
        
        // Удаляем лишние пробелы
        $template = preg_replace('/\s+/', ' ', $template);
        
        return trim($template);
    }
    
    /**
     * Подготовить переменные для замены
     */
    private function prepareVariables($subdomain, $pageData)
    {
        $this->variables = [];
        
        // Склонения города
        $cases = [
            'city_nominative' => $subdomain->city_nominative,
            'city_genitive' => $subdomain->city_genitive,
            'city_dative' => $subdomain->city_dative,
            'city_accusative' => $subdomain->city_accusative,
            'city_instrumental' => $subdomain->city_instrumental,
            'city_prepositional' => $subdomain->city_prepositional
        ];
        
        // Если какие-то склонения не заполнены, используем автоматические
        foreach ($cases as $key => $value) {
            if (empty($value)) {
                $caseName = str_replace('city_', '', $key);
                $cases[$key] = $this->cityDeclension->getCase($subdomain->city_name, $caseName);
            }
        }
        
        // Основные переменные города
        $this->variables['city'] = $cases['city_nominative'];
        $this->variables['city_nominative'] = $cases['city_nominative'];
        $this->variables['city_genitive'] = $cases['city_genitive'];
        $this->variables['city_dative'] = $cases['city_dative'];
        $this->variables['city_accusative'] = $cases['city_accusative'];
        $this->variables['city_instrumental'] = $cases['city_instrumental'];
        $this->variables['city_prepositional'] = $cases['city_prepositional'];
        
        // Предлоги с городом
        $this->variables['in_city'] = 'в ' . $cases['city_prepositional'];
        $this->variables['from_city'] = 'из ' . $cases['city_genitive'];
        $this->variables['to_city'] = 'в ' . $cases['city_accusative'];
        
        // Данные страницы
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
        
        // Общие переменные сайта
        $this->variables['site_name'] = $this->settings->get('site_name');
        $this->variables['company_name'] = $this->settings->get('company_name');
        $this->variables['phone'] = $this->settings->get('phone');
        $this->variables['email'] = $this->settings->get('email');
    }
    
    /**
     * Получить список всех доступных переменных
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