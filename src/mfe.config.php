<?php

return [
    'name' => \mfe\core\mfe::ENGINE_NAME,
    'version' => \mfe\core\mfe::ENGINE_VERSION,
    'options' => [
        'autoload' => false,
    ],
    'registry' => [
        'final' => [],
        'override' => [
            'StackObject' => \mfe\core\libs\components\CObjectsStack::className(),
            'FileHelper' => \mfe\core\libs\helpers\CSimpleFileHelper::className()
        ]
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
    'cores' => [
        'loader' => [
            'class' => \mfe\core\cores\Loader::className()
        ],
        'page' => [
            'class' => \mfe\core\cores\Page::className()
        ],
        'request' => [
            'class' => \mfe\core\cores\Request::className()
        ],
        'router' => [
            'class' => \mfe\core\cores\Router::className()
        ]
    ]
];
