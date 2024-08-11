<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'timeZone' => 'Europe/Moscow',
    'language' => 'ru-RU',
    'name' => 'Greenavicash',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'rbac' => [
            'class' => 'mdm\admin\Module',
            'controllerMap' => [
                'assignment' => [
                    'class' => 'mdm\admin\controllers\AssignmentController',
                    'idField' => 'id',
                    'usernameField' => 'login',
                    'fullnameField' => '',
                ],
            ],
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        'yii2images' => [
            'class' => 'rico\yii2images\Module',
            //be sure, that permissions ok
            //if you cant avoid permission errors you have to create "images" folder in web root manually and set 777 permissions
            'imagesStorePath' => '@webroot/yii2images/store', //path to origin images
            'imagesCachePath' => '@webroot/yii2images/cache', //path to resized copies
            'graphicsLibrary' => 'GD', //but really its better to use 'Imagick'
            'placeHolderPath' => '@webroot/images/placeholder.png', // if you want to get placeholder when image not exists, string will be processed by Yii::getAlias
            //     'imageCompressionQuality' => 100, // Optional. Default value is 85.
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '_5x99maBZNQ6du_A4l6Hx2WPAW8-EAp6',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'sms' =>  [
            'class' =>  'sem\smsru\Sms',
            //'api_id'=>  '7FE29EDA-B9EC-1015-FD6A-89D2153C4963',
            'api_id'=>  'A9731626-81EB-DCE9-BAEA-3DB89C2DD374',
            'oneSmsCost'=>	0.94
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache'
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'global' => [
            'class' => 'app\components\globalComponent'
        ],
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.hosting.reg.ru',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username' => 'support@greenavi.com',
                'password' => 'M354at790!',
                'port' => '465', // Port 25 is a very common port too
                'encryption' => 'ssl', // It is often used, check your provider or mail server specs
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],
        ],
        'mailer_old' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.greenavicash.ru',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username' => 'support@greenavicash.ru',
                'password' => 'M354at790!',
                'port' => '465', // Port 25 is a very common port too
                'encryption' => 'ssl', // It is often used, check your provider or mail server specs
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],
        ],        
        'mail' => [
            'class'            => 'gulltour\phpmailer\Mailer',
            //'viewPath'         => '@common/mail',
            'useFileTransport' => false,
            'config'           => [
                'mailer'     => 'smtp',
                'host'       => 'smtp.greenavi.com',
                'port'       => '465',
                'smtpsecure' => 'ssl',
                'smtpauth'   => true,
                'username'   => 'support@greenavi.com',
                'password'   => 'M354at790!',
            ],
        ],
        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                "api/swagger" => "api/default/swagger",
                "api/swagger-json" => "api/default/swagger-json",
            ],
        ],
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/*',
            'api/*',
            'yii2images/*',
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        //'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        'allowedIPs' => ['*'],
    ];
}

return $config;
