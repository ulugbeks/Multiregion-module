<?php

use Okay\Core\Router;
use Okay\Modules\OkayCMS\Multiregions\Extenders\FrontExtender;

return [
    // Hook into all frontend pages
    Router::ON_BEFORE_ROUTE_EXECUTE => [
        [
            'class' => FrontExtender::class,
            'method' => 'init'
        ]
    ],
    
    // Hook into specific page types
    'main_page' => [
        [
            'class' => FrontExtender::class,
            'method' => 'extendMain',
            'position' => 1000
        ]
    ],
    
    'category_page' => [
        [
            'class' => FrontExtender::class,
            'method' => 'extendCategory',
            'position' => 1000
        ]
    ],
    
    'product_page' => [
        [
            'class' => FrontExtender::class,
            'method' => 'extendProduct',
            'position' => 1000
        ]
    ],
    
    'brand_page' => [
        [
            'class' => FrontExtender::class,
            'method' => 'extendBrands',
            'position' => 1000
        ]
    ],
    
    'blog_page' => [
        [
            'class' => FrontExtender::class,
            'method' => 'extendBlog',
            'position' => 1000
        ]
    ],
    
    'page' => [
        [
            'class' => FrontExtender::class,
            'method' => 'extendPage',
            'position' => 1000
        ]
    ]
];