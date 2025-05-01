<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'timeZone' => 'Europe/Riga',
    'name' => 'Paletizācijas uzskaite',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ]
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'MGhqacVMyPbQFGyyVcFL',
            'csrfParam' => '_backendCSRF'
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        
        'urlManager' => [
            'class'                 => 'yii\web\UrlManager',
            'enablePrettyUrl'       => true,
            'showScriptName'        => false,
            'enableStrictParsing'   => false,
            'rules' => [
                'paletes' => 'paletes/index',
                'produkti' => 'produkti/index',
                'statistika' => 'statistics/index',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',        
            ],
        ],       
        
    ],
    'params' => $params,
];
