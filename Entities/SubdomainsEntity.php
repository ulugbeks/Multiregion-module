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
    
    protected static $table = '__subdomains';
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
    
    protected function filter__keyword($keyword)
    {
        $this->select->where('(s.subdomain LIKE :keyword OR s.city_name LIKE :keyword)');
        $this->select->bindValue('keyword', '%' . $keyword . '%');
    }
}