<?php

namespace Okay\Modules\OkayCMS\Multiregions\Entities;

use Okay\Core\Entity\Entity;

class SubdomainsEntity extends Entity
{
    protected static $fields = [
        'id',
        'subdomain',
        'city_name',
        'city_nominative',
        'city_genitive',
        'city_dative',
        'city_accusative',
        'city_instrumental',
        'city_prepositional',
        'enabled',
        'position',
        'created_at',
        'updated_at'
    ];
    
    protected static $defaultOrderFields = [
        'position ASC',
        'city_name ASC'
    ];
    
    // ВАЖНО: Используем ok_ или ваш префикс из config.local.php
    protected static $table = 'ok_subdomains';
    protected static $tableAlias = 's';
    
    protected static $alternativeIdField = 'subdomain';
    
    public function getBySubdomain($subdomain)
    {
        if (empty($subdomain)) {
            return null;
        }
        
        $this->setUp();
        $filter = [
            'subdomain' => $subdomain,
            'limit' => 1
        ];
        
        $this->buildFilter($filter);
        $this->select->cols($this->getAllFields());
        
        $this->db->query($this->select);
        return $this->getResult();
    }
    
    public function getEnabled()
    {
        return $this->find(['enabled' => 1]);
    }
    
    public function updatePositions($positions)
    {
        if (!is_array($positions)) {
            return false;
        }
        
        foreach ($positions as $id => $position) {
            $this->update((int)$id, ['position' => (int)$position]);
        }
        
        return true;
    }
    
    protected function filter__enabled($enabled)
    {
        $this->select->where('s.enabled = :enabled');
        $this->select->bindValue('enabled', (int)$enabled);
    }
    
    protected function filter__subdomain($subdomain)
    {
        $this->select->where('s.subdomain = :subdomain');
        $this->select->bindValue('subdomain', $subdomain);
    }
}

// ===========================================
// SubdomainSeoEntity.php
// ===========================================

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
    
    // ВАЖНО: Используем ok_ или ваш префикс из config.local.php
    protected static $table = 'ok_subdomain_seo';
    protected static $tableAlias = 'ss';
    
    const PAGE_TYPE_MAIN = 'main';
    const PAGE_TYPE_CATEGORY = 'category';
    const PAGE_TYPE_PRODUCT = 'product';
    const PAGE_TYPE_BRAND = 'brand';
    const PAGE_TYPE_BLOG = 'blog';
    const PAGE_TYPE_PAGE = 'page';
    
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
    
    public function getBySubdomain($subdomainId)
    {
        if (empty($subdomainId)) {
            return [];
        }
        
        $patterns = $this->find(['subdomain_id' => (int)$subdomainId]);
        
        $result = [];
        foreach ($patterns as $pattern) {
            $result[$pattern->page_type] = $pattern;
        }
        
        return $result;
    }
    
    public function getPattern($subdomainId, $pageType)
    {
        if (empty($subdomainId) || empty($pageType)) {
            return null;
        }
        
        $this->setUp();
        $filter = [
            'subdomain_id' => (int)$subdomainId,
            'page_type' => $pageType,
            'limit' => 1
        ];
        
        $this->buildFilter($filter);
        $this->select->cols($this->getAllFields());
        
        $this->db->query($this->select);
        return $this->getResult();
    }
    
    public function updatePattern($subdomainId, $pageType, $data)
    {
        $existing = $this->getPattern($subdomainId, $pageType);
        
        $patternData = [
            'subdomain_id' => (int)$subdomainId,
            'page_type' => $pageType,
            'meta_title_pattern' => $data['meta_title_pattern'] ?? '',
            'meta_description_pattern' => $data['meta_description_pattern'] ?? '',
            'meta_keywords_pattern' => $data['meta_keywords_pattern'] ?? '',
            'h1_pattern' => $data['h1_pattern'] ?? '',
            'description_pattern' => $data['description_pattern'] ?? ''
        ];
        
        if ($existing) {
            return $this->update($existing->id, $patternData);
        } else {
            return $this->add($patternData);
        }
    }
    
    public function deleteBySubdomain($subdomainId)
    {
        $query = $this->queryFactory->newSqlQuery();
        $query->setStatement("DELETE FROM " . $this->getTable() . " WHERE subdomain_id = :subdomain_id")
              ->bindValue('subdomain_id', (int)$subdomainId);
        
        return $this->db->query($query);
    }
    
    protected function filter__subdomain_id($subdomainId)
    {
        $this->select->where('ss.subdomain_id = :subdomain_id');
        $this->select->bindValue('subdomain_id', (int)$subdomainId);
    }
    
    protected function filter__page_type($pageType)
    {
        $this->select->where('ss.page_type = :page_type');
        $this->select->bindValue('page_type', $pageType);
    }
}