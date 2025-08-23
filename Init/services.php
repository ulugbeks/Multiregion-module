<?php

use Okay\Core\EntityFactory;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;
use Okay\Core\Database;
use Okay\Core\QueryFactory;
use Okay\Core\Config;
use Okay\Core\Settings;
use Okay\Core\Design;
use Okay\Modules\OkayCMS\Multiregions\Backend\Requests\BackendMultiregionsRequest;
use Okay\Modules\OkayCMS\Multiregions\Backend\Helpers\BackendMultiregionsHelper;
use Okay\Modules\OkayCMS\Multiregions\Helpers\SubdomainDetector;
use Okay\Modules\OkayCMS\Multiregions\Helpers\SeoProcessor;
use Okay\Modules\OkayCMS\Multiregions\Helpers\CityDeclension;

return [
    BackendMultiregionsRequest::class => [
        'class' => BackendMultiregionsRequest::class,
        'arguments' => [
            new SR(Request::class),
        ]
    ],
    
    BackendMultiregionsHelper::class => [
        'class' => BackendMultiregionsHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
        ]
    ],
    
    SubdomainDetector::class => [
        'class' => SubdomainDetector::class,
        'arguments' => [
            new SR(Database::class),
            new SR(QueryFactory::class),
            new SR(Config::class),
        ]
    ],
    
    SeoProcessor::class => [
        'class' => SeoProcessor::class,
        'arguments' => [
            new SR(SubdomainDetector::class),
            new SR(CityDeclension::class),
            new SR(Settings::class),
            new SR(Database::class),
            new SR(QueryFactory::class),
            new SR(Config::class),
        ]
    ],
    
    CityDeclension::class => [
        'class' => CityDeclension::class,
        'arguments' => []
    ],
];