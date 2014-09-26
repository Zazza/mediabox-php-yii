<?php

class ImageController extends Controller
{
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
        $crop->file_id = new MongoId($_GET["_id"]);
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
        $tag->file_id = new MongoId($_GET["_id"]);
        $tag->tag = $_GET["tag"];
        if ($tag->validate()) {
            $tag->save(false);
        } else {
            print_r($tag->getErrors());
        }
    }

    public function actionGetCrops() {
        $result = array();

        $criteria = new EMongoCriteria();
        $criteria->file_id = new MongoId($_GET["id"]);
        $crops = ImagesCrops::model()->findAll($criteria);
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

        $criteria = new EMongoCriteria();
        $criteria->file_id = new MongoId($_GET["id"]);
        $tags = ImagesTags::model()->findAll($criteria);
        foreach($tags as $tag) {
            $result[]["tag"] = $tag->tag;
        }

        echo json_encode($result);
    }

    public function actionAddComment() {
        $comment = new ImagesComments();

        $comment->user_id = Yii::app()->user->id;
        $comment->file_id = new MongoId($_GET["id"]);
        $comment->comment = urldecode($_GET["text"]);
        $comment->timestamp = date("Y-m-d H:i:s");

        if ($comment->validate()) {
            $comment->save(false);

            // for timestamp
            $comment = ImagesComments::model()->findByPk($comment->_id);

            $array = array();

            $array["text"] = nl2br($comment->comment);
            $user = User::model()->findByPk($comment->user_id);
            $array["user"] = $user->username;
            $array["timestamp"] = $comment->timestamp;

            echo json_encode($array);
        }
    }

    public function actionGetComments() {
        $result = array();

        $criteria = new EMongoCriteria();
        $criteria->file_id = new MongoId($_GET["id"]);
        $comments = ImagesComments::model()->findAll($criteria);
        foreach($comments as $comment) {
            $array = array();

            $array["text"] = nl2br($comment->comment);
            $user = User::model()->findByPk($comment->user_id);
            $array["user"] = $user->username;
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
                    $criteria = new EMongoCriteria();
                    $criteria->description = urldecode($crop);
                    $criteria->file_id = $ids; // $ids = array() ?

                    $db = ImagesCrops::model()->findAll($criteria);
                } else {
                    $criteria = new EMongoCriteria();
                    $criteria->description = urldecode($crop);
                    $db = ImagesCrops::model()->findAll($criteria);
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
                    $criteria = new EMongoCriteria();
                    $criteria->tag = urldecode($tag);
                    $criteria->file_id = $ids; // $ids = array() ?

                    $db = ImagesTags::model()->findAll($criteria);
                } else {
                    $criteria = new EMongoCriteria();
                    $criteria->tag = urldecode($tag);
                    $db = ImagesTags::model()->findAll($criteria);
                };

                foreach($db as $part) {
                    $ids[] = $part["file_id"];
                };
            }
        }

        return $ids;
    }

    private function _setTagsAndCrops($selected_tags, $selected_crops) {
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
            $criteria = new EMongoCriteria();
            $criteria->file_id = $crops_and_tags; // $crops_and_tags = array() ?
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
            $criteria = new EMongoCriteria();
            $criteria->file_id = $crops_and_tags; // $crops_and_tags = array() ?
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

                    if (isset(Yii::app()->params["mimetypes"][$extension])) {
                        $mimetype = Yii::app()->params["mimetypes"][$extension];
                    } else {
                        $mimetype = $file->type . "/" . $extension;
                    };


                    $model = new File();

                    $model->id = $file->id;
                    $model->name = $file->name;
                    $model->shortname = $shortname;
                    $model->obj = "file";
                    $model->type = $file->type;
                    $model->mimetype = $mimetype;
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