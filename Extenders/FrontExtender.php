<?php

namespace Okay\Modules\OkayCMS\Multiregions\Extenders;

use Okay\Core\Design;
use Okay\Core\Request;
use Okay\Core\TemplateConfig;
use Okay\Modules\OkayCMS\Multiregions\Helpers\SubdomainDetector;
use Okay\Modules\OkayCMS\Multiregions\Helpers\SeoProcessor;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainSeoEntity;

class FrontExtender
{
    private $design;
    private $request;
    private $templateConfig;
    private $subdomainDetector;
    private $seoProcessor;
    
    public function __construct(
        Design $design,
        Request $request,
        TemplateConfig $templateConfig,
        SubdomainDetector $subdomainDetector,
        SeoProcessor $seoProcessor
    ) {
        $this->design = $design;
        $this->request = $request;
        $this->templateConfig = $templateConfig;
        $this->subdomainDetector = $subdomainDetector;
        $this->seoProcessor = $seoProcessor;
    }
    
    /**
     * Инициализация при каждом запросе
     */
    public function init()
    {
        // Определяем текущий поддомен
        $subdomain = $this->subdomainDetector->getCurrentSubdomain();
        
        if ($subdomain) {
            // Передаем в шаблоны
            $this->design->assign('current_subdomain', $subdomain);
            $this->design->assign('current_city', $subdomain->city_name);
            
            // Добавляем склонения в шаблоны
            $this->design->assign('city_cases', [
                'nominative' => $subdomain->city_nominative ?: $subdomain->city_name,
                'genitive' => $subdomain->city_genitive ?: $subdomain->city_name,
                'dative' => $subdomain->city_dative ?: $subdomain->city_name,
                'accusative' => $subdomain->city_accusative ?: $subdomain->city_name,
                'instrumental' => $subdomain->city_instrumental ?: $subdomain->city_name,
                'prepositional' => $subdomain->city_prepositional ?: $subdomain->city_name,
                'in_city' => 'в ' . ($subdomain->city_prepositional ?: $subdomain->city_name)
            ]);
        }
    }
    
    /**
     * Обработка главной страницы
     */
    public function extendMain()
    {
        $this->processSeoForPage(SubdomainSeoEntity::PAGE_TYPE_MAIN);
    }
    
    /**
     * Обработка страницы категории
     */
    public function extendCategory($category = null)
    {
        if (!$category) {
            // Получаем категорию из шаблона
            $category = $this->design->getVar('category');
        }
        
        if ($category) {
            $pageData = ['category' => $category];
            $this->processSeoForPage(SubdomainSeoEntity::PAGE_TYPE_CATEGORY, $pageData);
        }
    }
    
    /**
     * Обработка страницы товара
     */
    public function extendProduct($product = null)
    {
        if (!$product) {
            // Получаем товар из шаблона
            $product = $this->design->getVar('product');
        }
        
        if ($product) {
            $pageData = ['product' => $product];
            $this->processSeoForPage(SubdomainSeoEntity::PAGE_TYPE_PRODUCT, $pageData);
        }
    }
    
    /**
     * Обработка страницы бренда
     */
    public function extendBrands($brand = null)
    {
        if (!$brand) {
            // Получаем бренд из шаблона
            $brand = $this->design->getVar('brand');
        }
        
        if ($brand) {
            $pageData = ['brand' => $brand];
            $this->processSeoForPage(SubdomainSeoEntity::PAGE_TYPE_BRAND, $pageData);
        }
    }
    
    /**
     * Обработка страницы блога
     */
    public function extendBlog($post = null)
    {
        if (!$post) {
            // Получаем пост из шаблона
            $post = $this->design->getVar('post');
        }
        
        if ($post) {
            $pageData = ['post' => $post];
            $this->processSeoForPage(SubdomainSeoEntity::PAGE_TYPE_BLOG, $pageData);
        }
    }
    
    /**
     * Обработка информационной страницы
     */
    public function extendPage($page = null)
    {
        if (!$page) {
            // Получаем страницу из шаблона
            $page = $this->design->getVar('page');
        }
        
        if ($page) {
            $pageData = ['page' => $page];
            $this->processSeoForPage(SubdomainSeoEntity::PAGE_TYPE_PAGE, $pageData);
        }
    }
    
    /**
     * Обработка SEO для страницы
     */
    private function processSeoForPage($pageType, $pageData = [])
    {
        $subdomain = $this->subdomainDetector->getCurrentSubdomain();
        
        if (!$subdomain) {
            return;
        }
        
        // Получаем обработанные SEO данные
        $seo = $this->seoProcessor->processSeo($pageType, $pageData);
        
        if (!$seo) {
            return;
        }
        
        // Сохраняем оригинальные значения
        $originalSeo = new \stdClass();
        $originalSeo->meta_title = $this->design->getVar('meta_title');
        $originalSeo->meta_description = $this->design->getVar('meta_description');
        $originalSeo->meta_keywords = $this->design->getVar('meta_keywords');
        
        // Применяем новые значения
        if (!empty($seo->meta_title)) {
            $this->design->assign('meta_title', $seo->meta_title);
            $this->templateConfig->__set('meta_title', $seo->meta_title);
        }
        
        if (!empty($seo->meta_description)) {
            $this->design->assign('meta_description', $seo->meta_description);
            $this->templateConfig->__set('meta_description', $seo->meta_description);
        }
        
        if (!empty($seo->meta_keywords)) {
            $this->design->assign('meta_keywords', $seo->meta_keywords);
            $this->templateConfig->__set('meta_keywords', $seo->meta_keywords);
        }
        
        if (!empty($seo->h1)) {
            $this->design->assign('h1', $seo->h1);
            $this->design->assign('page_h1', $seo->h1);
        }
        
        if (!empty($seo->description)) {
            $this->design->assign('seo_description', $seo->description);
        }
        
        // Сохраняем оригинальные значения для возможного использования
        $this->design->assign('original_seo', $originalSeo);
    }
}