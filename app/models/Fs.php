<?php

class Fs extends EMongoDocument
{
    public $user_id;
    public $name;
    public $parent;
    public $trash;
    public $timestamp;

    public function getCollectionName()
    {
        return 'fs';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('user_id, name, parent', 'required'),
			array('trash', 'numerical', 'integerOnly'=>true),
			array('user_id, parent', 'length', 'max'=>64),
			array('name', 'length', 'max'=>128),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
		);
	}


    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Files the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
