<?php

use Okay\Core\Design;
use Okay\Core\FrontTranslations;
use Okay\Core\TemplateConfig;
use Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender;

return [
    // Hook into the design initialization to set up subdomain data
    'Okay\Core\Design@__construct' => [
        'class' => FrontExtender::class,
        'method' => 'init'
    ],
    
    // Hook into main page rendering
    'Okay\Controllers\MainController@render' => [
        'class' => FrontExtender::class,
        'method' => 'extendMain',
        'position' => 100
    ],
    
    // Hook into category page rendering
    'Okay\Controllers\CategoryController@render' => [
        'class' => FrontExtender::class,
        'method' => 'extendCategory',
        'position' => 100
    ],
    
    // Hook into product page rendering
    'Okay\Controllers\ProductController@render' => [
        'class' => FrontExtender::class,
        'method' => 'extendProduct',
        'position' => 100
    ],
    
    // Hook into brands page rendering
    'Okay\Controllers\BrandsController@render' => [
        'class' => FrontExtender::class,
        'method' => 'extendBrands',
        'position' => 100
    ],
    
    // Hook into blog page rendering
    'Okay\Controllers\BlogController@render' => [
        'class' => FrontExtender::class,
        'method' => 'extendBlog',
        'position' => 100
    ],
    
    // Hook into static page rendering
    'Okay\Controllers\PageController@render' => [
        'class' => FrontExtender::class,
        'method' => 'extendPage',
        'position' => 100
    ],
    
    // Hook before template compilation to ensure variables are available
    'Okay\Core\TemplateConfig@__construct' => [
        'class' => FrontExtender::class,
        'method' => 'init',
        'position' => 1
    ]
];