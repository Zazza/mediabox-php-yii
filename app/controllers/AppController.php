<?php

class AppController extends Controller
{
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('volume', 'types', 'view'),
                //'roles'=>array('admin'),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionVolume() {

    }

    public function actionTypes() {

    }

    public function actionView() {

    }
}