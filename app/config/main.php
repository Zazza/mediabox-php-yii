<?php
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Mediabox',
    'charset'=>'utf-8',

	'preload'=>array('log'),

	'import'=>array(
		'application.models.*',
		'application.components.*',
        'ext.YiiMongoDbSuite.*',
	),

	'modules'=>array(
		'gii'=>array(
		    'class'=>'system.gii.GiiModule',
		    'password'=>'',
	            'ipFilters'=>array('*'),
		),
	),

	'components'=>array(
        'user'=>array(
            'allowAutoLogin'=>true,
            'class'=>'WebUser'
        ),
		'urlManager'=>array(
			'urlFormat'=>'path',
            'showScriptName'=>false,
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view'
			),
		),
        'mongodb' => array(
            'class'            => 'EMongoDB',
            'connectionString' => 'mongodb://localhost',
            'dbName'           => 'mediabox',
            'fsyncFlag'        => true,
            'safeFlag'         => true,
            'useCursor'        => false
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