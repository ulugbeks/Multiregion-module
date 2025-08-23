<?php

namespace Okay\Modules\OkayCMS\Multiregions\Entities;

use Okay\Core\Entity\Entity;

class SubdomainSeoEntity extends Entity
{
    protected static $fields = [
        'id',
        'subdomain_id',
        'page_type',
        'meta_title_pattern',
        'meta_description_pattern',
        'meta_keywords_pattern',
        'h1_pattern',
        'description_pattern'
    ];
    
    protected static $table = '__subdomain_seo';
    protected static $tableAlias = 'ss';
    
    // Типы страниц
    const PAGE_TYPE_MAIN = 'main';
    const PAGE_TYPE_CATEGORY = 'category';
    const PAGE_TYPE_PRODUCT = 'product';
    const PAGE_TYPE_BRAND = 'brand';
    const PAGE_TYPE_BLOG = 'blog';
    const PAGE_TYPE_PAGE = 'page';
    
    /**
     * Получить типы страниц
     */
    public static function getPageTypes()
    {
        return [
            self::PAGE_TYPE_MAIN => 'Главная страница',
            self::PAGE_TYPE_CATEGORY => 'Категории товаров',
            self::PAGE_TYPE_PRODUCT => 'Страницы товаров',
            self::PAGE_TYPE_BRAND => 'Страницы брендов',
            self::PAGE_TYPE_BLOG => 'Страницы блога',
            self::PAGE_TYPE_PAGE => 'Информационные страницы'
        ];
    }
    
    /**
     * Обновить или создать SEO шаблон
     */
    public function updatePattern($subdomainId, $pageType, $data)
    {
        if (empty($subdomainId) || empty($pageType)) {
            return false;
        }
        
        // Проверяем существует ли запись
        $existing = $this->findOne([
            'subdomain_id' => (int)$subdomainId,
            'page_type' => $pageType
        ]);
        
        // Подготавливаем данные
        $patternData = new \stdClass();
        $patternData->subdomain_id = (int)$subdomainId;
        $patternData->page_type = $pageType;
        $patternData->meta_title_pattern = $data['meta_title_pattern'] ?? '';
        $patternData->meta_description_pattern = $data['meta_description_pattern'] ?? '';
        $patternData->meta_keywords_pattern = $data['meta_keywords_pattern'] ?? '';
        $patternData->h1_pattern = $data['h1_pattern'] ?? '';
        $patternData->description_pattern = $data['description_pattern'] ?? '';
        
        if ($existing) {
            // Обновляем существующую запись
            return $this->update($existing->id, $patternData);
        } else {
            // Создаем новую запись
            return $this->add($patternData);
        }
    }
    
    /**
     * Удалить все шаблоны для поддомена
     */
    public function deleteBySubdomain($subdomainId)
    {
        if (empty($subdomainId)) {
            return false;
        }
        
        // Получаем все записи для поддомена
        $patterns = $this->find(['subdomain_id' => (int)$subdomainId]);
        
        if ($patterns) {
            $ids = array_column($patterns, 'id');
            return $this->delete($ids);
        }
        
        return true;
    }
    
    /**
     * Фильтр по subdomain_id
     */
    protected function filter__subdomain_id($subdomainId)
    {
        $this->select->where('ss.subdomain_id = :subdomain_id');
        $this->select->bindValue('subdomain_id', (int)$subdomainId);
    }
    
    /**
     * Фильтр по page_type
     */
    protected function filter__page_type($pageType)
    {
        $this->select->where('ss.page_type = :page_type');
        $this->select->bindValue('page_type', $pageType);
    }
}