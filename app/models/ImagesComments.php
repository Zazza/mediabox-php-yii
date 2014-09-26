<?php

class ImagesComments extends EMongoDocument
{
	public $user_id;
    public $file_id;
    public $comment;
    public $timestamp;

    public function getCollectionName()
    {
        return 'images_comments';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, file_id, comment', 'required'),
			array('user_id, file_id', 'length', 'max'=>64),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, file_id, comment, timestamp', 'safe', 'on'=>'search'),
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
			'comment' => 'Comment',
			'timestamp' => 'Timestamp',
		);
	}

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
