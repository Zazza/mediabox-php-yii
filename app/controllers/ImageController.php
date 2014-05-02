<?php

class ImageController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('setCrop', 'addTag', 'getCrops', 'getTags', 'addComment', 'getComments', 'getAllTags', 'getAllCrops', 'selTag', 'selCrop', 'getFsImg'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionSetCrop() {
        $crop = new ImagesCrops();
        $crop->user_id = Yii::app()->user->id;
        $crop->file_id = $_GET["_id"];
        $crop->description = $_GET["desc"];
        $crop->ws = $_GET["ws"];
        $crop->x1 = $_GET["x1"];
        $crop->x2 = $_GET["x2"];
        $crop->y1 = $_GET["y1"];
        $crop->y2 = $_GET["y2"];
        if ($crop->validate()) {
            $crop->save(false);
        } else {
            print_r($crop->getErrors());
        }
    }

    public function actionAddTag() {
        $tag = new ImagesTags();
        $tag->user_id = Yii::app()->user->id;
        $tag->file_id = $_GET["_id"];
        $tag->tag = $_GET["tag"];
        if ($tag->validate()) {
            $tag->save(false);
        } else {
            print_r($tag->getErrors());
        }
    }

    public function actionGetCrops() {
        $result = array();

        $crops = ImagesCrops::model()->findAll("file_id = :file_id", array(":file_id"=>$_GET["id"]));
        foreach($crops as $crop) {
            $array = array();

            $array["x1"] = $crop->x1;
            $array["x2"] = $crop->x2;
            $array["y1"] = $crop->y1;
            $array["y2"] = $crop->y2;
            $array["ws"] = $crop->ws;
            $array["description"] = $crop->description;

            $result[] = $array;
        }

        echo json_encode($result);
    }

    public function actionGetTags() {
        $result = array();

        $tags = ImagesTags::model()->findAll("file_id = :file_id", array(":file_id"=>$_GET["id"]));
        foreach($tags as $tag) {
            $result[]["tag"] = $tag->tag;
        }

        echo json_encode($result);
    }

    public function actionAddComment() {
        $comment = new ImagesComments();

        $comment->user_id = Yii::app()->user->id;
        $comment->file_id = $_GET["id"];
        $comment->comment = urldecode($_GET["text"]);

        if ($comment->validate()) {
            $comment->save(false);
        } else {
            print_r($comment->getErrors());
        }
    }

    public function actionGetComments() {
        $result = array();

        $comments = ImagesComments::model()->findAll("file_id = :file_id", array(":file_id"=>$_GET["id"]));
        foreach($comments as $comment) {
            $array = array();

            $array["text"] = $comment->comment;
            $array["timestamp"] = $comment->timestamp;

            $result[] = $array;
        }

        echo json_encode($result);
    }







    private function _setCrops($selected_crops) {
        $ids = array();
        $selected_cropsArray = json_decode($selected_crops);

        if (isset($selected_cropsArray)) {
            foreach ($selected_cropsArray as $crop) {
                if (count($ids) > 0) {
                    $criteria = new CDbCriteria();
                    $criteria->condition = "description = :description";
                    $criteria->params = array(
                        ":description"=>urldecode($crop)
                    );
                    $criteria->addInCondition("file_id", $ids);

                    $db = ImagesCrops::model()->findAll($criteria);
                } else {
                    $db = ImagesCrops::model()->findAll("description = :description", array(
                        ":description"=>urldecode($crop)
                    ));
                };

                foreach($db as $part) {
                    $ids[] = $part["file_id"];
                };
            }
        }

        return $ids;
    }

    private function _setTags($selected_tags) {
        $ids = array();
        $selected_tagsArray = json_decode($selected_tags);

        if (isset($selected_tagsArray)) {
            foreach ($selected_tagsArray as $tag) {
                if (count($ids) > 0) {
                    $criteria = new CDbCriteria();
                    $criteria->condition = "tag = :tag";
                    $criteria->params = array(
                        ":tag"=>urldecode($tag)
                    );
                    $criteria->addInCondition("file_id", $ids);

                    $db = ImagesTags::model()->findAll($criteria);
                } else {
                    $db = ImagesTags::model()->findAll("tag = :tag", array(
                        ":tag"=>urldecode($tag)
                    ));
                };

                foreach($db as $part) {
                    $ids[] = $part["file_id"];
                };
            }
        }

        return $ids;
    }

    public function _setTagsAndCrops($selected_tags, $selected_crops) {
        $crops_and_tags = array();

        $selTags = $this->_setTags($selected_tags);
        $selCrops =  $this->_setCrops($selected_crops);

        if (count($selTags) > 0) {
            foreach($selTags as $tag) {
                if (count($selCrops) > 0) {
                    if (in_array($tag, $selCrops)) {
                        $crops_and_tags[] = $tag;
                    }
                } else {
                    $crops_and_tags = $selTags;
                }
            }
        } else {
            if (count($selCrops) > 0) {
                $crops_and_tags = $selCrops;
            }
        }

        return $crops_and_tags;
    }

    public function actionGetAllTags() {
        $selected_tags = Yii::app()->session['selected_tags'];
        $selected_crops = Yii::app()->session['selected_crops'];

        $crops_and_tags = $this->_setTagsAndCrops($selected_tags, $selected_crops);

        if (count($crops_and_tags) > 0) {
            $criteria = new CDbCriteria();
            $criteria->addInCondition("file_id", $crops_and_tags);
            $tags = ImagesTags::model()->findAll($criteria);
        } else {
            $tags = ImagesTags::model()->findAll();
        }

        $result = array();

        foreach($tags as $tag) {
            $result[] = $tag->tag;
        }

        echo json_encode(array_unique($result));
    }

    public function actionGetAllCrops() {
        $selected_tags = Yii::app()->session['selected_tags'];
        $selected_crops = Yii::app()->session['selected_crops'];

        $crops_and_tags = $this->_setTagsAndCrops($selected_tags, $selected_crops);

        if (count($crops_and_tags) > 0) {
            $criteria = new CDbCriteria();
            $criteria->addInCondition("file_id", $crops_and_tags);
            $crops = ImagesCrops::model()->findAll($criteria);
        } else {
            $crops = ImagesCrops::model()->findAll();
        }

        $result = array();

        foreach($crops as $crop) {
            $result[] = $crop->description;
        }

        echo json_encode(array_unique($result));
    }

    public function actionSelTag() {
        $selected_tags = Yii::app()->session['selected_tags'];

        $res = array();
        $flag = false;

        $selected_tagsArray = json_decode($selected_tags);
        if (isset($selected_tagsArray)) {
            foreach ($selected_tagsArray as $tag) {
                if ($_GET["tag"] == urldecode($tag))
                    $flag = true;
                else
                    $res[] = $tag;
            }
        }

        if (!$flag)
            $res[] = urlencode($_GET["tag"]);

        Yii::app()->session['selected_tags'] = json_encode($res);

        echo $selected_tags;
    }

    public function actionSelCrop() {
        $selected_crops = Yii::app()->session['selected_crops'];

        $res = array();
        $flag = false;

        $selected_cropsArray = json_decode($selected_crops);
        if (isset($selected_cropsArray)) {
            foreach ($selected_cropsArray as $crop) {
                if ($_GET["crop"] == urldecode($crop))
                    $flag = true;
                else
                    $res[] = $crop;
            }
        }

        if (!$flag)
            $res[] = urlencode($_GET["crop"]);

        Yii::app()->session['selected_crops'] = json_encode($res);

        echo $selected_crops;
    }

    public function actionGetFsImg() {
        $selected_tags = Yii::app()->session['selected_tags'];
        $selected_crops = Yii::app()->session['selected_crops'];

        $crops_and_tags = $this->_setTagsAndCrops($selected_tags, $selected_crops);

        $result = array();
        $files = Files::model()->findAll();

        foreach($files as $file) {
            $flag = true;
            $shortname = "";

            if (count($crops_and_tags) > 0)
                if (!in_array($file->id, $crops_and_tags))
                    $flag = false;

            if ($flag) {
                if ($file->type == "image") {
                    $ico = Yii::app()->params["mediaTypes"]["image"];

                    if (mb_strlen($file->name) > 20) {
                        $shortname = mb_substr($file->name, 0, 10) . ".." . mb_substr($file->name, mb_strrpos($file->name, ".")-1);
                    } else {
                        $shortname = $file->name;
                    }

                    $extension = mb_substr($file->name, mb_strrpos($file->name, ".")+1);

                    $model = new File();

                    $model->id = $file->id;
                    $model->name = $file->name;
                    $model->shortname = $shortname;
                    $model->obj = "file";
                    $model->type = $file->type;
                    $model->size = $file->size;
                    $model->ico = $ico;
                    $model->data = $ico;
                    $model->ext = $extension;

                    $result[] = $model;
                }
            }
        }

        if ( (count($result) > 0) and (count($crops_and_tags) > 0) ) {
            echo json_encode($result);
        }
    }
}