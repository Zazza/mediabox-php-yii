<?php

class ScanCommand extends CConsoleCommand
{
    private function getParent($string)
    {
        $array = explode("/", $string);

        $parent_id = "0";
        foreach($array as $part) {

            $criteria = new EMongoCriteria();
            $criteria->parent = $parent_id;
            $criteria->name = $part;
            $model = Fs::model()->find($criteria);

            if (isset($model->_id)) {
                $parent_id = $model->_id;
            }
        }

        return $parent_id;
    }

    public function run($args)
    {
        $login = $args[0];
        $criteria = new EMongoCriteria();
        $criteria->username = $login;
        $user = User::model()->find($criteria);

        $output = file_get_contents($args[1]);

        $json = json_decode($output, true);

        foreach($json as $part) {

            $exists = "";

            //if (!$part["deleted"]) { ???
            if (true) {

                if (!isset($part["size"])) {
                    $parent_id = $this->getParent($part["parent"]);

                    $criteria = new EMongoCriteria();
                    if ($parent_id === "0") {
                        $criteria->parent = "0";
                    } else {
                        $criteria->parent = new MongoId($parent_id);
                    }
                    $criteria->name = $part["name"];
                    $exists = Fs::model()->find($criteria);

                    $model = new Fs();
                    $model->name = $part["name"];
                    $model->user_id = $user->_id;
                    $model->trash = "0";
                    $model->timestamp = date("Y-m-d H:i:s");
                    $model->parent = $parent_id;
                }

                if (isset($part["size"])) {
                    $parent_id = $this->getParent($part["parent"]);

                    $criteria = new EMongoCriteria();
                    if ($parent_id === "0") {
                        $criteria->parent = "0";
                    } else {
                        $criteria->parent = new MongoId($parent_id);
                    }
                    $criteria->name = $part["name"];
                    $exists = Files::model()->find($criteria);

                    $model = new Files();
                    $model->name = $part["name"];
                    $model->user_id = $user->_id;
                    $model->type = $part["extension"];
                    $model->size = $part["size"];
                    $model->trash = "0";
                    $model->timestamp = date("Y-m-d H:i:s");
                    $model->parent = $this->getParent($part["parent"]);
                }

                if (!is_object($exists)) {
                    if ($model->validate()) {
                        $model->save(false);

                        if ( (isset($part["size"])) and (isset($part["data"])) ) {
                            $preview = new Image();
                            $preview->file_id = $model->_id;
                            $preview->data = $part["data"];
                            if ($preview->validate()) {
                                $preview->save(false);
                            } else {
                                print_r($preview->getErrors());
                            }
                        }
                    } else {
                        print_r($model->getErrors());
                    }
                }
            } else {
                $parent_id = $this->getParent($part["parent"]);

                if (!isset($part["size"])) {
                    $criteria = new EMongoCriteria();
                    $criteria->parent = new MongoId($parent_id);
                    $criteria->name = $part["name"];
                    $exists = Fs::model()->find($criteria);

                    $exists->delete();
                }
                if (isset($part["size"])) {
                    $criteria = new EMongoCriteria();
                    $criteria->parent = new MongoId($parent_id);
                    $criteria->name = $part["name"];
                    $exists = Files::model()->find($criteria);

                    $exists->delete();
                }
            }
        }
    }
}
