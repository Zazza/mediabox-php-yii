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
        'config' => array(
            'session_limit' => 3600,
            'session_long_limit' => 2592000,
            //'storage' => 'http://tushkan.com/fm'
            'storage' => 'http://storage'
        ),

        'mediaTypes' => array(
            'image' => '/img/mediabox/ftypes/image.png',
            'doc' => '/img/mediabox/ftypes/msword.png',
            'pdf' => '/img/mediabox/ftypes/pdf.png',
            'txt' => '/img/mediabox/ftypes/text.png',
            'exe' => '/img/mediabox/ftypes/executable.png',
            'xls' => '/img/mediabox/ftypes/excel.png',
            'audio' => '/img/mediabox/ftypes/audio.png',
            'html' => '/img/mediabox/ftypes/html.png',
            'zip' => '/img/mediabox/ftypes/compress.png',
            'video' => '/img/mediabox/ftypes/flash.png',
            'any' => '/img/mediabox/ftypes/unknown.png',
            'folder' => '/img/mediabox/ftypes/folder.png'
        ),

        'extension' => array(
            array('image', 'bmp', 'jpg', 'jpeg', 'gif', 'png'),
            array('audio', 'ogg', 'mp3', ),
            array('video', 'mp4', 'mov', 'wmv', 'flv', 'avi', 'mpg', '3gp', 'ogv', 'webm'),
            array('text', 'txt'),
            array('doc', 'doc', 'rtf', 'docx'),
            array('pdf', 'pdf', 'djvu'),
            array('txt', 'txt', 'lst', 'ini'),
            array('exe', 'exe', 'com',' bat', 'sh'),
            array('xls', 'xls', 'xlsx'),
            array('html', 'htm', 'html', 'shtml'),
            array('zip', 'zip', 'rar', 'tar', 'gz', '7z')
        ),

        'mimetypes' => array(
            'ogv' => 'video/ogg',
            '3gp' => 'video/3gpp'
        )
	),
);