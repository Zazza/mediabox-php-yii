<?php
class Init {
    public static function vars() {
        $current_directory = Yii::app()->session["current_directory"];
        $volume = Yii::app()->session["volume"];

        if (!isset(Yii::app()->session["types"])) {
            $types["other"] = 1;
            $types["image"] = 1;
            $types["video"] = 1;
            $types["music"] = 1;

            Yii::app()->session["types"] = $types;
        }

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