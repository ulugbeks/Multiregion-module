<?php

namespace Okay\Modules\OkayCMS\Multiregions\Entities;

use Okay\Core\Entity\Entity;
use Okay\Core\QueryFactory;

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
        
        return $this->findOne([
            'subdomain_id' => (int)$subdomainId,
            'page_type' => $pageType
        ]);
    }
    
    public function updatePattern($subdomainId, $pageType, $data)
    {
        if (empty($subdomainId) || empty($pageType)) {
            return false;
        }
        
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
        if (empty($subdomainId)) {
            return false;
        }
        
        $patterns = $this->find(['subdomain_id' => (int)$subdomainId]);
        
        if ($patterns) {
            $ids = array_column($patterns, 'id');
            return $this->delete($ids);
        }
        
        return true;
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