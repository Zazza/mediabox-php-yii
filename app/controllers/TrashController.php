<?php

class TrashController extends Controller
{
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex() {
        $this->render("/site/trash", array());
    }
}