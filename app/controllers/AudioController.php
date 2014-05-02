<?php

class AudioController extends Controller
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
                'actions'=>array("saveList", "createList", "showList", "getTracksList", "setPlaylist", "deletePlaylist", "volume"),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionSaveList() {
        if ( (!isset(Yii::app()->session['current_playlist'])) or (Yii::app()->session['current_playlist'] == "") ) {
            foreach($_POST["track"] as $track) {
                $playlist = new PlaylistsDefaultTracks();
                $playlist->user_id = Yii::app()->user->id;
                $playlist->file_id = $track;
                $playlist->save();
            }
        } else {
            foreach($_POST["track"] as $track) {
                $playlist = new PlaylistsTracks();
                $playlist->playlist_id = Yii::app()->session['current_playlist'];
                $playlist->file_id = $track;
                $playlist->save();
            }
        }
    }

    public function actionGetTracksList() {
        $tracks = array();

        if ( (!isset(Yii::app()->session['current_playlist'])) or (Yii::app()->session['current_playlist'] == "") ) {
            $db = PlaylistsDefaultTracks::model()->findAll("user_id = :user_id", array(":user_id" => Yii::app()->user->id));

            foreach($db as $part) {
                $array = array();

                $array["id"] = $part->file_id;
                $array["name"] = $part->file->name;

                $tracks[] = $array;
            }
        } else {
            $db = PlaylistsTracks::model()->findAll("playlist_id = :playlist_id", array(":playlist_id" => Yii::app()->session['current_playlist']));

            foreach($db as $part) {
                $array = array();

                $array["id"] = $part->file_id;
                $array["name"] = $part->file->name;

                $tracks[] = $array;
            }
        }

        echo json_encode($tracks);
    }

    public function actionCreateList() {
        $playlist = new Playlists();
        $playlist->name = $_GET["name"];
        $playlist->user_id = Yii::app()->user->id;
        $playlist->save();

        Yii::app()->session['current_playlist'] = $playlist->id;
    }

    public function actionShowList() {
        $playlists = array();

        $db = Playlists::model()->findAll("user_id = :user_id", array(":user_id" => Yii::app()->user->id));

        foreach($db as $part) {
            $array = array();

            $array["id"] = $part->id;
            $array["name"] = $part->name;

            $playlists[] = $array;
        }

            echo json_encode($playlists);
    }

    public function actionSetPlaylist() {
        if ( (isset($_GET["playlist-id"])) and ($_GET["playlist-id"] != "") ){
            Yii::app()->session['current_playlist'] = $_GET["playlist-id"];
        }
    }

    public function actionDeletePlaylist() {
        $db = Playlists::model()->find("id = :playlist_id AND user_id = :user_id", array(
            ":playlist_id" => $_GET["playlist-id"],
            ":user_id" => Yii::app()->user->id
        ));

        if (Yii::app()->session['current_playlist'] == $db->id) {
            Yii::app()->session['current_playlist'] = "";
        }

        $db->delete();
    }

    public function actionVolume() {
        if (isset($_GET["level"])) {
            Yii::app()->session['volume'] = $_GET["level"];
        }
    }
}