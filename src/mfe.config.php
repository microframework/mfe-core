<?php

return [
    'name' => \mfe\core\MfE::ENGINE_NAME,
    'version' => \mfe\core\MfE::ENGINE_VERSION,
    'defaultApplication' => \mfe\core\applications\DefaultApplication::class,
    'params' => [],
    'options' => [
        'autoload' => false,
        'debug' => true
    ],
    'utility' => [
        'StackObject' => \mfe\core\libs\components\CObjectsStack::class,
        'FileHelper' => \mfe\core\libs\helpers\CSimpleFileHelper::class
    ],
    'components' => [
        'application' => [
            'class' => \mfe\core\libs\managers\CApplicationManager::class,
            'default' => \mfe\core\applications\WebApplication::class,
            'autoload' => true
        ],
        'di' => [
            'class' => \mfe\core\libs\managers\CComponentManager::class
        ],
        'events' => [
            'class' => \mfe\core\libs\managers\CEventManager::class
        ],
        'loader' => [
            'class' => \mfe\core\libs\system\Loader::class
        ]
    ]
];
