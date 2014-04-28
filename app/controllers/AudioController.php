<?php

class AudioController extends Controller
{
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array("saveList", "createList", "playlist", "showList", "getTracksList", "setPlaylist", "deletePlaylist"),
                //'roles'=>array('admin'),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionSaveList() {

    }

    public function actionCreateList() {

    }

    public function actionPlaylist() {

    }

    public function actionShowList() {

    }

    public function actionGetTracksList() {

    }

    public function actionSetPlaylist() {

    }

    public function actionDeletePlaylist() {

    }
}