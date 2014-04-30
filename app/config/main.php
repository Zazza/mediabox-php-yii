<?php
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Mediabox',
    'language'=>'ru',
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
            'fileExtension' => '.twig',
            'options' => array(
                'autoescape' => true,
            )
        ),
	),

	'params'=>array(
        'config' => array(
            'session_limit' => 3600,
            'session_long_limit' => 2592000,
            'storage' => 'http://tushkan.com/fm'
        ),

        'mediaTypes' => array(
            'image' => '/assets/img/mediabox/ftypes/image.png',
            'doc' => '/assets/img/mediabox/ftypes/msword.png',
            'pdf' => '/assets/img/mediabox/ftypes/pdf.png',
            'txt' => '/assets/img/mediabox/ftypes/text.png',
            'exe' => '/assets/img/mediabox/ftypes/executable.png',
            'xls' => '/assets/img/mediabox/ftypes/excel.png',
            'audio' => '/assets/img/mediabox/ftypes/audio.png',
            'html' => '/assets/img/mediabox/ftypes/html.png',
            'zip' => '/assets/img/mediabox/ftypes/compress.png',
            'video' => '/assets/img/mediabox/ftypes/flash.png',
            'any' => '/assets/img/mediabox/ftypes/unknown.png',
            'folder' => '/assets/img/mediabox/ftypes/folder.png'
        ),

        'extension' => array(
            array('image', 'bmp', 'jpg', 'jpeg', 'gif', 'png'),
            array('audio', 'ogg', 'mp3'),
            array('video', 'mp4', 'mov', 'wmv', 'flv', 'avi', 'mpg', '3gp'),
            array('text', 'txt'),
            array('doc', 'doc', 'rtf', 'docx'),
            array('pdf', 'pdf', 'djvu'),
            array('txt', 'txt', 'lst', 'ini'),
            array('exe', 'exe', 'com',' bat', 'sh'),
            array('xls', 'xls', 'xlsx'),
            array('html', 'htm', 'html', 'shtml'),
            array('zip', 'zip', 'rar', 'tar', 'gz', '7z')
        )
	),
);