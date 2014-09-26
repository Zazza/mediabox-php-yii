<?php

class ImagesTags extends EMongoDocument
{
	public $user_id;
	public $file_id;
	public $tag;

    public function getCollectionName()
    {
        return 'images_tags';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, file_id, tag', 'required'),
			array('user_id, file_id', 'length', 'max'=>64),
			array('tag', 'length', 'max'=>64),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, file_id, tag', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'file_id' => 'File',
			'tag' => 'Tag',
		);
	}

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
