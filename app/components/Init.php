<?php
class Init {
    public static function vars() {
        $current_directory = Yii::app()->session["current_directory"];
        $volume = Yii::app()->session["volume"];
        $types = Yii::app()->session["types"];
        if ($types != "") {
            $types = explode("&", $types);
            $type = array();
            foreach($types as $part) {
                $type = implode("=", $types[$part]);
                if ($type[0] == "other")
                    $check_type_other = $type[1];
                if ($type[0] == "image")
                    $check_type_image = $type[1];
                if ($type[0] == "video")
                    $check_type_video = $type[1];
                if ($type[0] == "music")
                    $check_type_music = $type[1];
            }
        } else {
            $check_type_other = true;
            $check_type_image = true;
            $check_type_video = true;
            $check_type_music = true;
        }
        Yii::app()->session['check_type_other'] = $check_type_other;
        Yii::app()->session['check_type_image'] = $check_type_image;
        Yii::app()->session['check_type_video'] = $check_type_video;
        Yii::app()->session['check_type_music'] = $check_type_music;

        $view = Yii::app()->session["view"];

        if ($view == "") {
            $view = "grid";
        }
        Yii::app()->session['view'] = $view;
        $sort = Yii::app()->session["sort"];
        if ($sort == "") {
            $sort = "name";
        };
        Yii::app()->session['sort'] = $sort;

        if ($current_directory != "")
            $startdir = $current_directory;
        else
            $startdir = 0;

        Yii::app()->session['startdir'] = $startdir;

        if ($volume)
            $volume_level = $volume;
        else
            $volume_level = 50;

        Yii::app()->session['volume_level'] = $volume_level;
    }
}