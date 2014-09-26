<?php

class PlaylistsTracks extends EMongoDocument
{
	public $playlist_id;
    public $file_id;

    public function getCollectionName()
    {
        return 'playlists_tracks';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('playlist_id, file_id', 'required'),
			array('playlist_id, file_id', 'length', 'max'=>64),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, playlist_id, file_id', 'safe', 'on'=>'search'),
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
            'file' => array(self::BELONGS_TO, 'Files', 'file_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'playlist_id' => 'Playlist',
			'file_id' => 'File',
		);
	}

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
