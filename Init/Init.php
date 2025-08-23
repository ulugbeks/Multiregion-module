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

        // Создаем таблицу поддоменов
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

        // Создаем таблицу SEO шаблонов
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
        // Добавляем разрешение
        $this->addPermission(self::PERMISSION);
        
        // Регистрируем контроллеры
        $this->registerBackendController('MultiregionsAdmin');
        $this->addBackendControllerPermission('MultiregionsAdmin', self::PERMISSION);
        
        $this->registerBackendController('MultiregionAdmin');
        $this->addBackendControllerPermission('MultiregionAdmin', self::PERMISSION);
        
        // Добавляем пункт в меню
        $this->extendBackendMenu('left_settings', [
            'left_multiregions_title' => ['MultiregionsAdmin', 'MultiregionAdmin'],
        ]);
    }
}