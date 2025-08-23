<?php

namespace Okay\Modules\OkayCMS\Multiregions\Init;

use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainsEntity;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainSeoEntity;

class Init extends AbstractInit
{
    const PERMISSION = 'multiregions';

    public function install()
    {
        $this->setBackendMainController('MultiregionsAdmin');

        // Create subdomains table with proper prefix
        $prefix = $this->config->db_prefix ?? 'ok_';
        
        $this->migrateEntityTable(SubdomainsEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('subdomain'))->setTypeVarchar(50)->setIndexUnique(),
            (new EntityField('city_name'))->setTypeVarchar(100),
            (new EntityField('city_nominative'))->setTypeVarchar(100)->setNullable(),
            (new EntityField('city_genitive'))->setTypeVarchar(100)->setNullable(),
            (new EntityField('city_dative'))->setTypeVarchar(100)->setNullable(),
            (new EntityField('city_accusative'))->setTypeVarchar(100)->setNullable(),
            (new EntityField('city_instrumental'))->setTypeVarchar(100)->setNullable(),
            (new EntityField('city_prepositional'))->setTypeVarchar(100)->setNullable(),
            (new EntityField('enabled'))->setTypeTinyInt(1)->setDefault(1),
            (new EntityField('position'))->setTypeInt(11)->setDefault(0),
            (new EntityField('created_at'))->setTypeDatetime()->setNullable(),
            (new EntityField('updated_at'))->setTypeDatetime()->setNullable(),
        ]);

        // Create SEO templates table
        $this->migrateEntityTable(SubdomainSeoEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('subdomain_id'))->setTypeInt(11),
            (new EntityField('page_type'))->setTypeVarchar(50),
            (new EntityField('meta_title_pattern'))->setTypeText()->setNullable(),
            (new EntityField('meta_description_pattern'))->setTypeText()->setNullable(),
            (new EntityField('meta_keywords_pattern'))->setTypeText()->setNullable(),
            (new EntityField('h1_pattern'))->setTypeText()->setNullable(),
            (new EntityField('description_pattern'))->setTypeText()->setNullable(),
        ]);
    }

    public function init()
    {
        // Add permission
        $this->addPermission(self::PERMISSION);
        
        // Register backend controllers
        $this->registerBackendController('MultiregionsAdmin');
        $this->addBackendControllerPermission('MultiregionsAdmin', self::PERMISSION);
        
        $this->registerBackendController('MultiregionAdmin');
        $this->addBackendControllerPermission('MultiregionAdmin', self::PERMISSION);
        
        // Add to backend menu
        $this->extendBackendMenu('left_settings', [
            'left_multiregions_title' => ['MultiregionsAdmin', 'MultiregionAdmin'],
        ]);
        
        // Register frontend extenders
        $this->registerFrontExtender();
    }
    
    /**
     * Register frontend extenders for all page types
     */
    private function registerFrontExtender()
    {
        // Hook into MainController
        $this->extendController('Okay\Controllers\MainController', 'Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender@extendMain');
        
        // Hook into CategoryController
        $this->extendController('Okay\Controllers\CategoryController', 'Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender@extendCategory');
        
        // Hook into ProductController
        $this->extendController('Okay\Controllers\ProductController', 'Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender@extendProduct');
        
        // Hook into BrandsController
        $this->extendController('Okay\Controllers\BrandsController', 'Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender@extendBrands');
        
        // Hook into BlogController
        $this->extendController('Okay\Controllers\BlogController', 'Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender@extendBlog');
        
        // Hook into PageController
        $this->extendController('Okay\Controllers\PageController', 'Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender@extendPage');
    }
}