<?php

class PlaylistsDefaultTracks extends EMongoDocument
{
	public $user_id;
    public $file_id;

    public function getCollectionName()
    {
        return 'playlists_default_tracks';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, file_id', 'required'),
			array('user_id, file_id', 'length', 'max'=>64),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, file_id', 'safe', 'on'=>'search'),
		);
	}

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
