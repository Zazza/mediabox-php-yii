<?php

class FmController extends Controller
{
    private $_sort;

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('fs', 'chdir', 'upload', "thumb", 'getThumb', 'copy', 'restore', 'getTypesNum', 'create', 'fileToTrash', 'folderToTrash', 'remove', 'rmFolder', 'removeFileByName', 'buffer', 'past', 'deleteFileFromBuffer', 'clearBuffer', 'getMoveFiles', 'sort', 'view', 'types'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function beforeAction($action) {
        if ( (!isset(Yii::app()->session['sort'])) or (Yii::app()->session['sort'] == "") ) {
            $this->_sort = "name";
        } else {
            $this->_sort = Yii::app()->session['sort'];
        }

        Init::vars();

        return parent::beforeAction($action);
    }

    public function actionFs()
    {
        if (!isset($_GET["id"])) {
            $array = array(
                0 => array(
                    "text"              => "Upload",
                    "id"                => "0",
                    "expanded"          => true,
                    "hasChildren"       => true,
                    "spriteCssClass"    => "rootfolder"
                ),
                1 => array(
                    "text"              => "Trash",
                    "id"                => "trash",
                    "expanded"          => false,
                    "hasChildren"       => false,
                    "spriteCssClass"    => "rootfolder"
                ),
            );

            echo json_encode($array);
        } elseif ($_GET["id"] != "trash") {
            $array = array();

            $criteria = new EMongoCriteria();
            if ($_GET["id"] === "0") {
                $criteria->parent = "0";
            } else {
                $criteria->parent = new MongoId($_GET["id"]);
            }
            $criteria->trash != "1";
            $criteria->sort('name', EMongoCriteria::SORT_ASC);
            $nodes = Fs::model()->findAll($criteria);

            foreach($nodes as $node) {
                $array[] = array(
                    "text"              => $node->name,
                    "id"                => $node->_id->{'$id'},
                    "trash"             => "0",
                    "hasChildren"       => $this->hasChildren($node->_id),
                    "spriteCssClass"    => "folder"
                );
            }

            echo json_encode($array);
        }
    }

    public function hasChildren($id) {
        $criteria = new EMongoCriteria();
        $criteria->parent = $id;
        $criteria->trash != "1";
        $exists = Fs::model()->find($criteria);

        if ($exists) {
            return true;
        } else {
            return false;
        }
    }

    private function getFilePath($id)
    {
        $file = Files::model()->findByPk($id);

        $path = array();
        $parent_id = $file->parent;
        while($parent_id != "0") {
            $criteria = new EMongoCriteria();
            $criteria->_id = new MongoId($parent_id);

            $model = Fs::model()->find($criteria);
            if (isset($model->_id)) {
                $parent_id = $model->parent;
                $path[] = $model->name;
            } else {
                exit();
            }
        }

        return "/" . join("/", array_reverse($path)) . "/" . $file->name;
    }

    private function getPath($parent_id)
    {
        $path = array();
        while($parent_id != "0") {
            $criteria = new EMongoCriteria();
            $criteria->_id = new MongoId($parent_id);

            $model = Fs::model()->find($criteria);
            if (isset($model->_id)) {
                if ($model->parent != "0") {
                    $parent_id = $model->parent->{'$id'};
                } else {
                    $parent_id = "0";
                }
                $path[] = $model->name;
            } else {
                exit();
            }
        }

        if (count($path) > 0) {
            return "/" . join("/", array_reverse($path)) . "/";
        } else {
            return "/";
        }
    }

    public function actionChdir() {
        if ( (!isset($_GET["id"])) or ($_GET["id"] === "0") ) {
            $id = "0";

            $trash = "0";

            $files = array("trash" => "0");
        } elseif ($_GET["id"] != "trash") {
            $id = $_GET["id"];

            $parent = Fs::model()->findByPk(new MongoID($id));

            $trash = "0";

            $files = array("trash" => $parent->trash);
        } else {
            $id = "0";

            $trash = "1";

            $files = array("trash" => "1");
        }

        Yii::app()->session['current_directory'] = $id;

        if (isset($parent->_id)) {
            Yii::app()->session['current_path'] = $this->getPath($id);
        } else {
            Yii::app()->session['current_path'] = "/";
        }
        $files["current_path"] = Yii::app()->session['current_path'];
        $files["files"] = array();

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
        if ($trash == "0") {
            $criteria = new EMongoCriteria();
            if ($id === "0") {
                $criteria->parent = "0";
            } else {
                $criteria->parent = new MongoId($id);
            }
            $criteria->trash = "0";
            $criteria->sort($dir_sort, EMongoCriteria::SORT_ASC);
            $nodes = Fs::model()->findAll($criteria);
        } else {
            $criteria = new EMongoCriteria();
            $criteria->trash = "1";
            $criteria->sort($dir_sort, EMongoCriteria::SORT_ASC);
            $nodes = Fs::model()->findAll($criteria);
        }

        foreach($nodes as $node) {
            $folder = new Folder();

            $folder->name = urlencode($node->name);
            $folder->id = $node->_id->{'$id'};
            $folder->path = $this->getPath($node->parent);
            $folder->date = $node->timestamp;

            $files["files"][] = $folder;
        };

        // Get Files
        if ($trash == "0") {
            $criteria = new EMongoCriteria();
            if ($id === "0") {
                $criteria->parent = "0";
            } else {
                $criteria->parent = new MongoId($id);
            }
            $criteria->trash = "0";
            $criteria->sort($file_sort, EMongoCriteria::SORT_ASC);
            $nodes = Files::model()->findAll($criteria);
        } else {
            $criteria = new EMongoCriteria();
            $criteria->trash = "1";
            $criteria->sort($file_sort, EMongoCriteria::SORT_ASC);
            $nodes = Files::model()->findAll($criteria);
        }

        foreach($nodes as $node) {
            $file = new File();

            $file->id = $node->_id->{'$id'};
            $file->path = $this->getPath($node->parent);
            $file->name = urlencode($node->name);
            $file->size = $node->size;
            $file->date = $node->timestamp;
            $file->type = $node->type;

            $files["files"][] = $file;
        }

        if (count($files) > 0) {
            echo json_encode($files);
        } else {
            echo json_encode(array());
        }
    }

    public function actionUpload() {
        $file = new Files();
        if (Yii::app()->session['current_directory'] == "0") {
            $file->parent = "0";
        } else {
            $file->parent = new MongoId(Yii::app()->session['current_directory']);
        }
        $file->name = $_GET["file"];
        $file->trash = "0";
        $file->user_id = Yii::app()->user->id;
        $file->size = $_GET["size"];
        $file->type = $_GET["type"];
        $file->timestamp = date("Y-m-d H:i:s");

        if ($file->validate()) {
            $file->save();

            $model = new File();

            $model->id   = $file->_id->{'$id'};
            $model->name = $file->name;
            $model->size = $file->size;
            $model->type = $file->type;
            $model->date = $file->timestamp;

            echo json_encode($model);
        }
    }

    public function actionThumb() {
        $image = new Image();
        $image->file_id = new MongoId($_POST["id"]);
        $image->data = $_POST["data"];
        if ($image->validate()) {
            $image->save();
        } else {
            print_r($image->getErrors());
        }
    }

    public function actionGetThumb() {
        $criteria = new EMongoCriteria();
        $criteria->file_id = new MongoId($_GET["name"]);
        $image = Image::model()->find($criteria);

        header('Content-Type: image/png');
        echo base64_decode($image->data);
    }

    private function getFolder($id) {
        $criteria = new EMongoCriteria();
        $criteria->_id = new MongoID($id);
        $fs = Fs::model()->find($criteria);

        $model = new Folder();

        $model->id = $fs->_id->{'$id'};
        $model->name = urlencode($fs->name);
        $model->date = $fs->timestamp;
        if ($fs->parent == "0") {
            $model->parent = "0";
        } else {
            $model->parent = $fs->parent->{'$id'};
        }

        return $model;
    }

    private function getFile($id) {
        $criteria = new EMongoCriteria();
        $criteria->_id = new MongoID($id);
        $file = Files::model()->find($criteria);

        $model = new File();
        $model->id = $file->_id->{'$id'};
        $model->name = urlencode($file->name);
        $model->size = $file->size;
        $model->date = $file->timestamp;

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
                $criteria = new EMongoCriteria();
                $criteria->_id = new MongoID($part);
                $model = Files::model()->find($criteria);
                $model->trash = "0";

                $model->save();
            }
        }
        if (isset($_POST["folder"])) {
            foreach($_POST["folder"] as $part) {
                $criteria = new EMongoCriteria();
                $criteria->_id = new MongoID($part);
                $model = Fs::model()->find($criteria);
                $model->trash = "0";

                $model->save();
            }
        }
    }

    public function actionGetTypesNum() {

        $type = array();
        $type["path"] = "";
        $path = array();

        $tmp_id = $_GET["id"];

        while($tmp_id !== "0") {
            $criteria = new EMongoCriteria();
            $criteria->_id = new MongoId($tmp_id);
            $fs = Fs::model()->find($criteria);
            if(isset($fs->_id)) {
                $tmp_id = $fs->parent;

                $count = count($path);
                $path[$count]["id"] = $fs->_id->{'$id'};
                $path[$count]["name"] = $fs->name;
            } else {
                $tmp_id = "0";
            }
        }

        $path = array_reverse($path);

        if (count($path) > 2) {
            $pre = '<a href="#" class="btn btn-default one_folder" data-id=' . $path[count($path)-2]["id"] . '><div>' . $path[count($path)-2]["name"] . '</div></a>';
            $last = '<a href="#" class="btn btn-default one_folder" data-id=' . $path[count($path)-1]["id"] . '><div>' . $path[count($path)-1]["name"] . '</div></a>';

            $type["path"] = '<a href="#" class="btn btn-default one_folder" data-id="' . $path[count($path)-3]["id"] . '"><i class="icon-chevron-left"></i></a>' . $pre . $last;
        } else {
            $type["path"] = '<a href="#" class="btn btn-default one_folder" data-id="0"><i class="icon-home"></i></a>';
            if (isset($path["0"])) {
                $type["path"] .= '<a href="#" class="btn btn-default one_folder" data-id=' . $path[0]["id"] . '><div>' . $path[0]["name"] . '</div></a>';
            }
            if (isset($path["1"])) {
                $type["path"] .= '<a href="#" class="btn btn-default one_folder" data-id=' . $path[1]["id"] . '><div>' . $path[1]["name"] . '</div></a>';
            }
        }

        $criteria = new EMongoCriteria();
        if ($_GET["id"] == "0") {
            $criteria->parent = "0";
        } else {
            $criteria->parent = new MongoId($_GET["id"]);
        }
        $criteria->trash != "1";
        $files = Files::model()->findAll($criteria);

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
        if (Yii::app()->session['current_directory'] == "0") {
            $fs->parent = "0";
        } else {
            $fs->parent = new MongoId(Yii::app()->session['current_directory']);
        }
        $fs->name = $_GET["name"];
        $fs->user_id = Yii::app()->user->id;
        $fs->trash = "0";

        if ($fs->validate()) {
            $fs->save();

            echo json_encode($this->getFolder($fs->_id));
        }
    }

    public function actionFileToTrash() {
        $criteria = new EMongoCriteria();
        $criteria->_id = new MongoId($_GET["id"]);

        $file = Files::model()->find($criteria);
        $file->trash = "1";

        if ($file->validate()) {
            $file->save();
        }
    }

    public function actionFolderToTrash() {
        $criteria = new EMongoCriteria();
        $criteria->_id = new MongoId($_GET["id"]);

        $fs = Fs::model()->find($criteria);
        $fs->trash = "1";
        if ($fs->validate()) {
            $fs->save();
        }
    }

    public function actionRemove() {
        $criteria = new EMongoCriteria();
        $criteria->_id = new MongoId($_GET["id"]);

        $file = Files::model()->find($criteria);

        if ($file->type == "image") {
            $criteria = new EMongoCriteria();
            $criteria->file_id = $file->_id;

            ImagesCrops::model()->deleteAll($criteria);
            ImagesTags::model()->deleteAll($criteria);
            ImagesComments::model()->deleteAll($criteria);
        }

        $file->delete();
    }

    public function actionRmFolder() {
        Fs::model()->deleteByPk(new MongoId($_GET["id"]));
    }

    public function actionRemoveFileByName() {
        $criteria = new EMongoCriteria();
        $criteria->parent = new MongoId(Yii::app()->session['current_directory']);
        $criteria->name = $_GET["name"];
        $file = Files::model()->find($criteria);

        $file_id = $file->id;

        $file->delete();

        echo $file_id;
    }

    public function actionBuffer() {
        echo json_encode(Buffer::getBuffer());
    }

    public function actionGetMoveFiles() {
        $result = array();
        $buffer = Buffer::getBuffer();
        foreach($buffer as $part) {
            if ($part->obj == "file") {
                $result[] = urlencode($this->getFilePath($part->id));
            }
            if ($part->obj == "folder") {
                $result[] = urlencode($this->getPath($part->id));
            }
        }

        echo json_encode($result);
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

    public function actionRenameFile() {
        $result = array();

        $model = Files::model()->findByPk(new MongoId($_GET["id"]));
        $model->name = $_GET["name"];
        if ($model->validate()) {
            $model->save(false);
        }
    }

    public function actionRenameFolder() {
        $folder = Fs::model()->findByPk(new MongoId($_GET["id"]));
        $folder->name = $_GET["name"];
        if ($folder->validate()) {
            $folder->save(false);
        }
    }
}