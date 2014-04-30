<?php
class Buffer {
    public static function setBuffer($array) {
        Yii::app()->session['buffer'] = serialize($array);
    }

    public static function getBuffer() {
        //return implode(",", Yii::app()->session['buffer']);
        return unserialize(Yii::app()->session['buffer']);
    }

    public static function bufferPast($parent) {
        $buffer = self::getBuffer();
        foreach($buffer as $part) {
            if ($part->obj == "file") {
                $file = Files::model()->findByPk($part->id);
                $file->parent = $parent;
                if ($file->validate()) {
                    $file->save();
                } else {;
                    print_r($file->getErrors());
                }
            }
            if ($part->obj == "folder") {
                $folder = Fs::model()->findByPk($part->id);
                $folder->parent = $parent;
                if ($folder->validate()) {
                    $folder->save();
                } else {
                    print_r($folder->getErrors());
                }
            }
        }

        Buffer::setBuffer(array());
        return true;
    }
}