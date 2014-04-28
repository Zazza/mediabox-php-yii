<?php

class ImageController extends Controller
{
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('setCrop', 'addTag', 'getCrops', 'getTags', 'addComment', 'getComments', 'getAllTags', 'getAllCrops', 'selTag', 'selCrop', 'getFsImg'),
                //'roles'=>array('admin'),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionSetCrop() {

    }

    public function actionAddTag() {

    }

    public function actionGetCrops() {

    }

    public function actionGetTags() {

    }

    public function actionAddComment() {

    }

    public function actionGetComments() {

    }

    public function actionGetAllTags() {

    }

    public function actionGetAllCrops() {

    }

    public function actionSelTag() {

    }

    public function actionSelCrop() {

    }

    public function actionGetFsImg() {

    }
}