<?php

class AudioController extends Controller
{
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
            $criteria = new EMongoCriteria();
            $criteria->user_id = Yii::app()->user->id;
            $playlist = PlaylistsDefaultTracks::model()->deleteAll($criteria);

            if (!isset($_POST["track"])) {
                return;
            }

            foreach($_POST["track"] as $track) {
                $playlist = new PlaylistsDefaultTracks();
                $playlist->user_id = Yii::app()->user->id;
                $playlist->file_id = new MongoId($track);
                $playlist->save();
            }
        } else {
            $criteria = new EMongoCriteria();
            $criteria->playlist_id = new MongoId(Yii::app()->session['current_playlist']);
            $criteria->user_id = Yii::app()->user->id;
            $playlist = PlaylistsTracks::model()->deleteAll($criteria);

            if (!isset($_POST["track"])) {
                return;
            }

            foreach($_POST["track"] as $track) {
                $playlist = new PlaylistsTracks();
                $playlist->playlist_id = new MongoId(Yii::app()->session['current_playlist']);
                $playlist->file_id = new MongoId($track);
                $playlist->save();
            }
        }
    }

    public function actionGetTracksList() {
        $tracks = array();

        if ( (!isset(Yii::app()->session['current_playlist'])) or (Yii::app()->session['current_playlist'] == "") ) {
            $criteria = new EMongoCriteria();
            $criteria->user_id = Yii::app()->user->id;
            $db = PlaylistsDefaultTracks::model()->findAll($criteria);

            foreach($db as $part) {
                $array = array();

                $array["id"] = $part->file_id->{'$id'};
                $file = Files::model()->findByPk($part->file_id);
                $array["name"] = urlencode($file->name);
                $array["path"] = urlencode($file->getPath());

                $tracks[] = $array;
            }
        } else {
            $criteria = new EMongoCriteria();
            $criteria->playlist_id = new MongoId(Yii::app()->session['current_playlist']);
            $db = PlaylistsTracks::model()->findAll($criteria);

            foreach($db as $part) {
                $array = array();

                $array["id"] = $part->file_id->{'$id'};
                $file = Files::model()->findByPk($part->file_id);
                $array["name"] = urlencode($file->name);
                $array["path"] = urlencode($file->getPath());

                $tracks[] = $array;
            }
        }

        echo json_encode($tracks);
    }

    public function actionCreateList() {
        $playlist = new Playlists();
        $playlist->name = $_GET["name"];
        $playlist->user_id = Yii::app()->user->id;
        if ($playlist->validate()) {
            $playlist->save();

            Yii::app()->session['current_playlist'] = $playlist->_id->{'$id'};
        } else {
            // NEED: Сохранять ошибки
            print_r($playlist->getErrors());

            return;
        }

        Yii::app()->session['current_playlist'] = $playlist->_id->{'$id'};
    }

    public function actionShowList() {
        $playlists = array();

        $criteria = new EMongoCriteria();
        $criteria->user_id = Yii::app()->user->id;
        $db = Playlists::model()->findAll($criteria);

        foreach($db as $part) {
            $array = array();

            $array["id"] = $part->_id->{'$id'};
            $array["name"] = $part->name;

            $playlists[] = $array;
        }

            echo json_encode($playlists);
    }

    public function actionSetPlaylist() {
        if ( (isset($_GET["playlist-id"])) and ($_GET["playlist-id"] != "") ){
            Yii::app()->session['current_playlist'] = $_GET["playlist-id"];
        } else {
            Yii::app()->session['current_playlist'] = "";
        }
    }

    public function actionDeletePlaylist() {
        $criteria = new EMongoCriteria();
        $criteria->user_id = Yii::app()->user->id;
        $criteria->_id = new MongoId($_GET["playlist-id"]);
        $db = Playlists::model()->find($criteria);

        if (Yii::app()->session['current_playlist'] == $db->_id->{'$id'}) {
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