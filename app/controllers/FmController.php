<?php

class FmController extends Controller
{
    private $_sort;

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('fs', 'chdir', 'upload', "thumb", 'getThumb', 'copy', 'restore', 'getTypesNum', 'create', 'getTrash', 'fileToTrash', 'folderToTrash', 'remove', 'rmFolder', 'removeFileByName', 'buffer', 'past', 'deleteFileFromBuffer', 'clearBuffer', 'sort'),
                //'roles'=>array('admin'),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function beforeAction() {
        // Set files sort type
        $this->_sort = Yii::app()->session['sort'];
        if ($this->_sort == "") {
            Yii::app()->session['sort'] = "name";
            $this->_sort = "name";
        }




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







        return true;
    }

    public function actionFs()
    {
        if (!isset($_GET["id"])) {
            echo '[{"text": "Upload", "id": "0", "expanded": true, "hasChildren": true, "spriteCssClass": "rootfolder"}]';
        } else {
            $array = array();

            $nodes = Fs::model()->findAll(
                "parent = :parent",
                array(":parent" => $_GET["id"])
            );

            foreach($nodes as $node) {
                $array = '{"text": "' . $nodes[i]["name"] . '", "id": "' . $nodes[i]["_id"] . '", "hasChildren": ' . $this->hasChildren($nodes[i]["_id"]) . ', "spriteCssClass": "folder"}';
            }

            echo "[" . implode(",", $array) . "]";
        }
    }

    public function hasChildren($id) {
        if (Fs::model()->exists(
            "parent = :parent",
            array(":parent" => $_GET["id"])
        )) {
            return true;
        } else {
            return false;
        }
    }

    public function actionChdir() {
        if (!isset($_GET["id"])) {
            $id = 0;
        } else {
            $id = $_GET["id"];
        };

        Yii::app()->session['current_directory'] = $id;

        $files = array();

        if ($this->_sort == "date") {
            $dir_sort = "timestamp";
            $file_sort = "timestamp";
        } else if ($this->_sort == "name") {
            $dir_sort = "name";
            $file_sort = "name";
        } else if ($this->_sort == "size") {
            $dir_sort = "name";
            $file_sort = "size";
        };

        // Get Dirs
        $nodes = Fs::model()->findAll(
            "parent = :parent AND trash = 0 ORDER BY '.$dir_sort.'",
            array(":parent" => $_GET["id"])
        );

        foreach($nodes as $node) {
            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strlen($node["name"]) - 3);
            } else {
                $shortname = $node["name"];
            }

            $files[] = '{' .
                '"obj": "folder",' .
                '"name": "' . $node["name"] + '",' .
                '"shortname": "' . $shortname . '",' .
                '"id": "' . $node["_id"] . '",' .
                '"date": "' . $node["timestamp"] .
                '"}';
        };



        // Get Files
        $nodes = Files::model()->findAll(
            "parent = :parent AND trash = 0 ORDER BY ' . $file_sort . '",
            array(":parent" => $_GET["id"])
        );

        foreach($nodes as $node) {
            if (Yii::app()->params["mediaTypes"][$node["type"]]) {
                $ico = Yii::app()->params["mediaTypes"][$node["type"]];
            } else {
                $ico = Yii::app()->params["mediaTypes"]["any"];
            };

            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strrpos($node["name"], ".") - 1);
            } else {
                $shortname = $node["name"];
            };

            $extension = strtolower(mb_substr($node["name"], mb_strrpos($node["name"], ".") + 1));

            $files[] = '{' .
                '"id": "' . $node["id"] . '",' .
                '"name": "' . $node["name"] . '",' .
                '"shortname": "' . $shortname . '",' .
                '"obj": "file",' .
                '"type": "' . $node["type"] . '",' .
                '"size": "' . $node["size"] . '",' .
                '"date": "' . $node["timestamp"] . '",' .
                '"ico": "' . $ico . '",' .
                '"src": "' . $ico . '",' .
                '"ext": "' . $extension . '"}';
        }

        if (count($files) > 0) {
            echo "[" . implode(",", $files) . "]";
        } else {
            echo "[]";
        }
    }

    private function get_type($extension) {
        $config = Yii::app()->params["extension"];
        foreach($config as $key) {
            if (in_array($extension, $key)) {
                return $key[0];
        }
    }

    return 'unknown';
}

    public function actionUpload() {
        $file = new Files();
        $file->parent = $this->_sort;
        $file->name = $_GET["file"];
        $file->user_id = Yii::app()->user->id;
        $file->size = $_GET["size"];
        $type = $this->get_type(strtolower($_GET["extension"]));
        $file->type = $type;

        if ($file->validate()) {
            $file->save();

            if (Yii::app()->params["mediaTypes"][$file->type]) {
                $ico = Yii::app()->params["mediaTypes"][$file->type];
            } else {
                $ico = Yii::app()->params["mediaTypes"]["any"];
            };

            $filename = urldecode($file->name);

            if (mb_strlen($filename) > 20) {
                $shortname = mb_substr($filename, 0, 10) . ".." . mb_substr($filename, mb_strlen($filename) - 1);
            } else {
                $shortname = $filename;
            }

            if ($file->type == "image") {
                echo '{"id": "' . $file->id . '", "name": "' . $filename . '", "shortname": "' . $shortname . '", "obj": "file", "type": "' . $file->type . '", "size": "' . $file->size . '", "date": "' . $file->timestamp . '", "ico": "' . $ico . '", "src": "fm/getThumb/?name=' . $file->id . '"}';
            } else {
                echo '{"id": "' . $file->id . '", "name": "' . $filename . '", "shortname": "' . $shortname . '", "obj": "file", "type": "' . $file->type . '", "size": "' . $file->size . '", "date": "' . $file->timestamp . '", "ico": "' . $ico . '", "src": "' . $ico . '"}';
            }
        } else {
            print_r($file->getErrors());
        }
    }

    public function actionThumb($id) {
        $image = new Image();
        $image->file_id = $id;
        $image->data = $_POST["data"];
        if ($image->validate()) {
            $image->save();
        } else {
            print_r($image->getErrors());
        }
    }

    public function actionGetThumb() {
        $image = Image::model()->find(
            "file_id = :file_id",
            array(":file_id" => $_GET["name"])
        );

        header('Content-Type: image/png');
        echo base64_decode($image->data);
    }

    public function actionCopy() {

    }

    public function actionRestore() {
        $file = Files::model()->findByPk($_POST["file"]);
        $file->trash = 0;

        if ($file->validate()) {
            $file->save();
        } else {
            print_r($file->getErrors());
        }
    }

    public function actionGetTypesNum() {
        /*
        $type = array();
        type["path"] = ""

        var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.fs)

        var tmp_id = id.toString()

        while(tmp_id != 0) {
            var query = {uid: uid, _id: {$oid: tmp_id}}
            var tmp = collection.findOne(query)

            if(tmp) {

                tmp_id = tmp.parent.toString()
                type["path"] = " > " + "<a href='#' class='one_folder' data-id="+tmp._id+">"+tmp.name+"</a>" + type["path"]
            } else {
                tmp_id = 0
            }
        }

        type["path"] = "<nobr><a href='#' class='one_folder' data-id='0'>Upload</a>" + type["path"] + "</nobr>"


        var collection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.files)

        var query = {uid: uid, parent: id.toString(), trash: {$ne: true}}
        var files = collection.find(query).toArray();

        type["all"] = 0
        type["image"] = 0
        type["video"] = 0
        type["audio"] = 0
        type["other"] = 0

        for (var i = 0; i < files.length; i++) {
                if (typeof(files[i]["type"]) == "string") {
                    if (files[i]["type"] == "image") {
                        type["image"]++
                } else if (files[i]["type"] == "video") {
                        type["video"]++
                } else if (files[i]["type"] == "audio") {
                        type["audio"]++
                } else {
                        type["other"]++
                }

                type["all"]++;
            }
        }

        return '{"all": "'+type["all"]+'", "image": "'+type["image"]+'", "video": "'+type["video"]+'", "audio": "'+type["audio"]+'", "other": "'+type["other"]+'", "path": "'+type["path"]+'"}';
        */
    }

    public function actionCreate() {

    }

    public function actionGetTrash() {
        if (!isset($_GET["id"])) {
            $id = 0;
        } else {
            $id = $_GET["id"];
        };

        Yii::app()->session['current_directory'] = $id;

        $files = array();

        if ($this->_sort == "date") {
            $dir_sort = "timestamp";
            $file_sort = "timestamp";
        } else if ($this->_sort == "name") {
            $dir_sort = "name";
            $file_sort = "name";
        } else if ($this->_sort == "size") {
            $dir_sort = "name";
            $file_sort = "size";
        };

        // Get Dirs
        $nodes = Fs::model()->findAll(
            "parent = :parent AND trash = 1 ORDER BY '.$dir_sort.'",
            array(":parent" => $_GET["id"])
        );

        foreach($nodes as $node) {
            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strlen($node["name"]) - 3);
            } else {
                $shortname = $node["name"];
            }

            $files[] = '{' .
                '"obj": "folder",' .
                '"name": "' . $node["name"] + '",' .
                '"shortname": "' . $shortname . '",' .
                '"id": "' . $node["_id"] . '",' .
                '"date": "' . $node["timestamp"] .
                '"}';
        };



        // Get Files
        $nodes = Files::model()->findAll(
            "parent = :parent AND trash = 1 ORDER BY ' . $file_sort . '",
            array(":parent" => $_GET["id"])
        );

        foreach($nodes as $node) {
            if (Yii::app()->params["mediaTypes"][$node["type"]]) {
                $ico = Yii::app()->params["mediaTypes"][$node["type"]];
            } else {
                $ico = Yii::app()->params["mediaTypes"]["any"];
            };

            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strrpos($node["name"], ".") - 1);
            } else {
                $shortname = $node["name"];
            };

            $extension = strtolower(mb_substr($node["name"], mb_strrpos($node["name"], ".") + 1));

            $files[] = '{' .
                '"id": "' . $node["id"] . '",' .
                '"name": "' . $node["name"] . '",' .
                '"shortname": "' . $shortname . '",' .
                '"obj": "file",' .
                '"type": "' . $node["type"] . '",' .
                '"size": "' . $node["size"] . '",' .
                '"date": "' . $node["timestamp"] . '",' .
                '"ico": "' . $ico . '",' .
                '"src": "' . $ico . '",' .
                '"ext": "' . $extension . '"}';
        }

        if (count($files) > 0) {
            echo "[" . implode(",", $files) . "]";
        } else {
            echo "[]";
        }
    }

    public function actionFileToTrash() {
        $file = Files::model()->findByPk($_GET["id"]);
        $file->trash = 1;

        if ($file->validate()) {
            $file->save();
        } else {
            print_r($file->getErrors());
        }
    }

    public function actionFolderToTrash() {

    }

    public function actionRemove() {
        $file = Files::model()->deleteByPk($_GET["id"]);
    }

    public function actionRmFolder() {

    }

    public function actionRemoveFileByName() {

    }

    public function actionBuffer() {

    }

    public function actionPast() {

    }

    public function actionDeleteFileFromBuffer() {

    }

    public function actionClearBuffer() {

    }

    public function actionSort() {

    }
}