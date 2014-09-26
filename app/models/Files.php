<?php

class Files extends EMongoDocument
{
    public $user_id;
    public $name;
    public $parent;
    public $trash;
    public $size;
    public $type;
    public $timestamp;

    public function getCollectionName()
    {
        return 'files';
    }

	public function rules()
	{
		return array(
			array('user_id, name, parent, size', 'required'),
			array('trash', 'numerical', 'integerOnly'=>true),
			array('user_id, parent', 'length', 'max'=>64),
			array('name', 'length', 'max'=>128),
			array('size', 'length', 'max'=>20),
		);
	}

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public  function getPath()
    {
        $path = array();

        $parent_id = $this->parent;
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
}
