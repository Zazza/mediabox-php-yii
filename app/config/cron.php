<?php
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Mediabox',
    'charset'=>'utf-8',

    'import'=>array(
        'application.models.*',
        'application.components.*',
        'ext.YiiMongoDbSuite.*',
    ),

    'components'=>array(
        'mongodb' => array(
            'class'            => 'EMongoDB',
            'connectionString' => 'mongodb://localhost',
            'dbName'           => 'mediabox',
            'fsyncFlag'        => true,
            'safeFlag'         => true,
            'useCursor'        => false
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
    ),

    'params'=>array(
    ),
);