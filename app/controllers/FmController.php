<?php

class FmController extends Controller
{
    private $_sort;

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('fs', 'chdir', 'upload', "thumb", 'getThumb', 'copy', 'restore', 'getTypesNum', 'create', 'getTrash', 'fileToTrash', 'folderToTrash', 'remove', 'rmFolder', 'removeFileByName', 'buffer', 'past', 'deleteFileFromBuffer', 'clearBuffer', 'sort', 'view', 'types'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function beforeAction() {
        if ( (!isset(Yii::app()->session['sort'])) or (Yii::app()->session['sort'] == "") ) {
            $this->_sort = "name";
        } else {
            $this->_sort = Yii::app()->session['sort'];
        }

        Init::vars();

        return true;
    }

    public function actionFs()
    {
        if (!isset($_GET["id"])) {
            $array = array(0 => array(
                "text"              => "Upload",
                "id"                => "0",
                "expanded"          => true,
                "hasChildren"       => true,
                "spriteCssClass"    => "rootfolder"
            ));

            echo json_encode($array);
        } else {
            $array = array();

            $nodes = Fs::model()->findAll(
                "parent = :parent AND trash = 0",
                array(":parent" => $_GET["id"])
            );

            foreach($nodes as $node) {
                $array[] = array(
                    "text"              => $node->name,
                    "id"                => $node->id,
                    "hasChildren"       => $this->hasChildren($node->id),
                    "spriteCssClass"    => "folder"
                );
            }

            echo json_encode($array);
        }
    }

    public function hasChildren($id) {
        if (Fs::model()->exists(
            "parent = :parent AND trash = 0",
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
        $criteria = new CDbCriteria;
        $criteria->condition = 'parent = :parent AND trash = 0';
        $criteria->order = $dir_sort;
        $criteria->params = array(":parent" => $_GET["id"]);
        $nodes = Fs::model()->findAll($criteria);

        foreach($nodes as $node) {
            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strlen($node["name"]) - 3);
            } else {
                $shortname = $node["name"];
            }

            $folder = new Folder();

            $folder->obj = "folder";
            $folder->name = urlencode($node->name);
            $folder->shortname = urlencode($shortname);
            $folder->id = $node->id;
            $folder->date = $node->timestamp;

            $files[] = $folder;
        };



        // Get Files
        $criteria = new CDbCriteria;
        $criteria->condition = 'parent = :parent AND trash = 0';
        $criteria->order = $file_sort;
        $criteria->params = array(":parent" => $_GET["id"]);
        $nodes = Files::model()->findAll($criteria);

        foreach($nodes as $node) {
            if (isset(Yii::app()->params["mediaTypes"][$node->type])) {
                $ico = Yii::app()->params["mediaTypes"][$node->type];
            } else {
                $ico = Yii::app()->params["mediaTypes"]["any"];
            };

            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strrpos($node["name"], ".") - 1);
            } else {
                $shortname = $node["name"];
            };

            $extension = strtolower(mb_substr($node["name"], mb_strrpos($node["name"], ".") + 1));

            if (isset(Yii::app()->params["mimetypes"][$extension])) {
                $mimetype = Yii::app()->params["mimetypes"][$extension];
            } else {
                $mimetype = $node["type"] . "/" . $extension;
            };


            $file = new File();

            $file->id = $node["id"];
            $file->name = urlencode($node["name"]);
            $file->shortname = urlencode($shortname);
            $file->obj = "file";
            $file->type = $node["type"];
            $file->mimetype = $mimetype;
            $file->size = $node["size"];
            $file->date = $node["timestamp"];
            $file->ico = $ico;
            $file->src = $ico;
            $file->ext = $extension;

            $files[] = $file;
        }

        if (count($files) > 0) {
            echo json_encode($files);
        } else {
            echo json_encode(array());
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
        $file->parent = Yii::app()->session['current_directory'];
        $file->name = $_GET["file"];
        $file->user_id = Yii::app()->user->id;
        $file->size = $_GET["size"];
        $type = $this->get_type(strtolower($_GET["extension"]));
        $file->type = $type;

        if ($file->validate()) {
            $file->save();

            if (isset(Yii::app()->params["mediaTypes"][$file->type])) {
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

            $extension = strtolower(mb_substr($filename, mb_strrpos($filename, ".") + 1));

            if (isset(Yii::app()->params["mimetypes"][$extension])) {
                $mimetype = Yii::app()->params["mimetypes"][$extension];
            } else {
                $mimetype = $file->type . "/" . $extension;
            };

            if ($file->type == "image") {
                $model = new File();

                $model->id = $file->id;
                $model->name = urlencode($filename);
                $model->shortname = urlencode($shortname);
                $model->obj = "file";
                $model->type = $file->type;
                $model->mimetype = $mimetype;
                $model->size = $file->size;
                $model->date = $file->timestamp;
                $model->ico = $ico;
                $model->src = "fm/getThumb/?name=" . $file->id;
                $model->ext = $extension;

                echo json_encode($model);
            } else {
                $model = new File();

                $model->id = $file->id;
                $model->name = urlencode($filename);
                $model->shortname = urlencode($shortname);
                $model->obj = "file";
                $model->type = $file->type;
                $model->mimetype = $mimetype;
                $model->size = $file->size;
                $model->date = $file->timestamp;
                $model->ico = $ico;
                $model->src = "fm/getThumb/?name=" . $file->id;
                $model->ext = $ico;

                echo json_encode($model);
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

    private function getFolder($id) {
        $fs = Fs::model()->findByPk($id);

        if (mb_strlen($fs->name) > 20) {
            $shortname = mb_substr($fs->name, 0, 10) . ".." . mb_substr($fs->name, mb_strlen($fs->name)-2);
        } else {
            $shortname = $fs->name;
        }

        $model = new Folder();

        $model->id = $fs->id;
        $model->name = urlencode($fs->name);
        $model->shortname = urlencode($shortname);
        $model->obj = "folder";
        $model->date = $fs->timestamp;
        $model->size;
        $model->ico = Yii::app()->params["mediaTypes"]["folder"];
        $model->parent = $fs->parent;

        return $model;
    }

    private function getFile($id) {
        $file = Files::model()->findByPk($id);

        if (mb_strlen($file->name) > 20) {
            $shortname = mb_substr($file->name, 0, 10) . ".." . mb_substr($file->name, mb_strrpos($file->name, ".")-1);
        } else {
            $shortname = $file->name;
        }

        if (isset(Yii::app()->params["mediaTypes"][$file->type])) {
            $ico = Yii::app()->params["mediaTypes"][$file->type];
        } else {
            $ico = Yii::app()->params["mediaTypes"]["any"];
        };

        $extension = strtolower(mb_substr($file->name, mb_strrpos($file->name, ".")+1));

        if (isset(Yii::app()->params["mimetypes"][$extension])) {
            $mimetype = Yii::app()->params["mimetypes"][$extension];
        } else {
            $mimetype = $file->type . "/" . $extension;
        };

        if ( ($file->type != "image") && ($file->type != "audio") && ($file->type != "video") ) {
            $type = "all";
        } else {
            $type = $file->type;
        }

        $model = new File();
        $model->id = $file->id;
        $model->name = urlencode($file->name);
        $model->shortname = urlencode($shortname);
        $model->obj = "file";
        $model->type = $type;
        $model->mimetype = $mimetype;
        $model->size = $file->size;
        $model->date = $file->timestamp;
        $model->ico = $ico;
        $model->src = "fm/getThumb/?name=" . $file->id;
        $model->ext = $extension;

        return $model;
    }

    public function actionCopy() {
        if (Buffer::getBuffer() != "") {
            $result = Buffer::getBuffer();
        } else {
            $result = array();
        }

        if (isset($_POST["file"])) {
            foreach($_POST["file"] as $part) {
                $flag = true;
                foreach($result as $buffer_file) {
                    if ( ($buffer_file->id == $part) and ($buffer_file->obj == "file") ) {
                        $flag = false;
                    }
                }
                if ($flag)
                    $result[] = $this->getFile($part);
            }
        }

        if (isset($_POST["folder"])) {
            foreach($_POST["folder"] as $part) {
                $flag = true;
                foreach($result as $buffer_file) {
                    if ( ($buffer_file->id == $part) and ($buffer_file->obj == "folder") ) {
                        $flag = false;
                    }
                }
                if ($flag)
                    $result[] = $this->getFolder($part);
            }
        }

        Buffer::setBuffer($result);

        echo json_encode(Buffer::getBuffer());
    }

    public function actionRestore() {
        if (isset($_POST["file"])) {
            foreach($_POST["file"] as $part) {
                $model = Files::model()->findByPk($part);
                $model->trash = 0;

                $model->save();
            }
        }
        if (isset($_POST["folder"])) {
            foreach($_POST["folder"] as $part) {
                $model = Fs::model()->findByPk($part);
                $model->trash = 0;

                $model->save();
            }
        }
    }

    public function actionGetTypesNum() {

        $type = array();
        $type["path"] = "";

        $tmp_id = $_GET["id"];

        while($tmp_id != 0) {
            if(Fs::model()->exists($_GET["id"])) {
                $fs = Fs::model()->findByPk($_GET["id"]);

                $tmp_id = $fs->parent;

                $type["path"] = " > " . "<a href='#' class='one_folder' data-id=" . $fs->id . ">" . $fs->name . "</a>" . $type["path"];
            } else {
                $tmp_id = 0;
            }
        }

        $type["path"] = "<nobr><a href='#' class='one_folder' data-id='0'>Upload</a>" . $type["path"] . "</nobr>";

        $files = Files::model()->findAll("parent = :parent AND trash = 0", array(":parent" => $_GET["id"]));

        $type["all"] = 0;
        $type["image"] = 0;
        $type["video"] = 0;
        $type["audio"] = 0;
        $type["other"] = 0;

        for ($i = 0; $i < count($files); $i++) {
            if ($files[$i]["type"] == "image") {
                $type["image"]++;
            } else if ($files[$i]["type"] == "video") {
                $type["video"]++;
            } else if ($files[$i]["type"] == "audio") {
                $type["audio"]++;
            } else {
                $type["other"]++;
            }

            $type["all"]++;
        }

        echo json_encode(
            array(
                "all" => $type["all"],
                "image" => $type["image"],
                "video" => $type["video"],
                "audio" => $type["audio"],
                "other" => $type["other"],
                "path" => $type["path"]
            )
        );
    }

    public function actionCreate() {
        $fs = new Fs();
        $fs->parent = Yii::app()->session['current_directory'];
        $fs->name = $_GET["name"];
        $fs->user_id = Yii::app()->user->id;

        if ($fs->validate()) {
            $fs->save();

            echo json_encode($this->getFolder($fs->id));
        } else {
            print_r($fs->getErrors());
        }
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
        $criteria = new CDbCriteria;
        $criteria->condition = 'parent = :parent AND trash = 1';
        $criteria->order = $dir_sort;
        $criteria->params = array(":parent" => $_GET["id"]);
        $nodes = Fs::model()->findAll($criteria);

        foreach($nodes as $node) {
            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strlen($node["name"]) - 3);
            } else {
                $shortname = $node["name"];
            }

            $folder = new Folder();

            $folder->obj = "folder";
            $folder->name = urlencode($node->name);
            $folder->shortname = urlencode($shortname);
            $folder->id = $node->id;
            $folder->date = $node->timestamp;

            $files[] = $folder;
        };



        // Get Files
        $criteria = new CDbCriteria;
        $criteria->condition = 'parent = :parent AND trash = 1';
        $criteria->order = $file_sort;
        $criteria->params = array(":parent" => $_GET["id"]);
        $nodes = Files::model()->findAll($criteria);

        foreach($nodes as $node) {
            if (isset(Yii::app()->params["mediaTypes"][$node->type])) {
                $ico = Yii::app()->params["mediaTypes"][$node->type];
            } else {
                $ico = Yii::app()->params["mediaTypes"]["any"];
            };

            if (mb_strlen($node["name"]) > 20) {
                $shortname = mb_substr($node["name"], 0, 10) . ".." . mb_substr($node["name"], mb_strrpos($node["name"], ".") - 1);
            } else {
                $shortname = $node["name"];
            };

            $extension = strtolower(mb_substr($node["name"], mb_strrpos($node["name"], ".") + 1));

            $file = new File();

            $file->id = $node["id"];
            $file->name = urlencode($node["name"]);
            $file->shortname = urlencode($shortname);
            $file->obj = "file";
            $file->type = $node["type"];
            $file->size = $node["size"];
            $file->date = $node["timestamp"];
            $file->ico = $ico;
            $file->src = $ico;
            $file->ext = $extension;

            $files[] = $file;
        }

        if (count($files) > 0) {
            echo json_encode($files);
        } else {
            echo json_encode(array());
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
        $fs = Fs::model()->findByPk($_GET["id"]);
        $fs->trash = 1;
        if ($fs->validate()) {
            $fs->save();
        } else {
            print_r($fs->getErrors());
        }
    }

    public function actionRemove() {
        $file = Files::model()->findByPk($_GET["id"]);

        if ($file->type = "image") {
            ImagesCrops::model()->deleteAll("file_id = :file_id", array(":file_id"=>$file->id));
            ImagesTags::model()->deleteAll("file_id = :file_id", array(":file_id"=>$file->id));
            ImagesComments::model()->deleteAll("file_id = :file_id", array(":file_id"=>$file->id));
        }

        $file->delete();
    }

    public function actionRmFolder() {
        Fs::model()->deleteByPk($_GET["id"]);
    }

    public function actionRemoveFileByName() {
        $file = Files::model()->find("parent = :parent AND name = :name", array(
            ":parent" => Yii::app()->session['current_directory'],
            ":name" => $_GET["name"]
        ));

        $file_id = $file->id;

        $file->delete();

        echo $file_id;
    }

    public function actionBuffer() {
        echo json_encode(Buffer::getBuffer());
    }

    public function actionPast() {
        if ($buffer = Buffer::getBuffer()) {
            Buffer::bufferPast(Yii::app()->session['current_directory']);
        }
    }

    public function actionDeleteFileFromBuffer() {
        $res = array();
        $buffer = Buffer::getBuffer();
        foreach($buffer as $part) {
            if ($_GET["id"] != $part->id) {
                $res[] = $part;
            }
        }

        Buffer::setBuffer($res);

        echo json_encode(Buffer::getBuffer());
    }

    public function actionClearBuffer() {
        Buffer::setBuffer(array());
    }

    public function actionSort() {
        Yii::app()->session['sort'] = $_GET["type"];
    }

    public function actionView() {
        Yii::app()->session['view'] = $_GET["view"];
    }

    public function actionTypes() {
        $types = array();

        $types["other"] = $_GET["other"];
        $types["image"] = $_GET["image"];
        $types["video"] = $_GET["video"];
        $types["music"] = $_GET["music"];

        Yii::app()->session['types'] = $types;
    }
}