<?php

return [
    'name' => \mfe\core\mfe::ENGINE_NAME,
    'version' => \mfe\core\mfe::ENGINE_VERSION,
    'options' => [
        'MFE_PHAR_INIT' => false,
        'MFE_AUTOLOAD' => false,

        'stackObject' => \mfe\core\libs\components\CObjectsStack::className(),
        'FileHelper' => \mfe\core\libs\helpers\CSimpleFileHelper::className()
    ],
    'components' => [
        'application' => [
            'class' => \mfe\core\libs\managers\CApplicationManager::className(),
            'default' => \mfe\core\applications\WebApplication::className(),
            'autoload' => true
        ],
        'di' => [
            'class' => \mfe\core\libs\managers\CComponentManager::className()
        ],
        'events' => [
            'class' => \mfe\core\libs\managers\CEventManager::className()
        ],
        'loader' => [
            'class' => \mfe\core\libs\system\Loader::className()
        ]
    ],
    'core' => [
        'loader' => [
            'class' => \mfe\core\core\Loader::className()
        ],
        'page' => [
            'class' => \mfe\core\core\Page::className()
        ],
        'request' => [
            'class' => \mfe\core\core\Request::className()
        ],
        'router' => [
            'class' => \mfe\core\core\Router::className()
        ]
    ]
];
