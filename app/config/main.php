<?php
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Mediabox',
    'charset'=>'utf-8',

	'preload'=>array('log'),

	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'123123',
            'ipFilters'=>array('*'),
		),
	),

	'components'=>array(
        'authManager' => array(
            'class' => 'PhpAuthManager',
            'defaultRoles' => array('guest'),
        ),
        'user'=>array(
            'allowAutoLogin'=>true,
            'class'=>'WebUser'
        ),
		'urlManager'=>array(
			'urlFormat'=>'path',
            'showScriptName'=>false,
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=mediabox',
			'emulatePrepare' => true,
			'username' => 'mediabox',
			'password' => 'mediabox',
			'charset' => 'utf8',
		),
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
        'viewRenderer' => array(
            'class' => 'ext.ETwigViewRenderer',
            'fileExtension' => '.html',
            'options' => array(
                'autoescape' => true,
            )
        ),
	),

	'params'=>array(
	),
);