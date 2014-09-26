<?php


class ImagesCrops extends EMongoDocument
{
	public $user_id;
    public $file_id;
    public $description;
    public $ws;
    public $x1;
	public $x2;
	public $y1;
	public $y2;

    public function getCollectionName()
    {
        return 'images_crops';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, file_id, description, ws, x1, x2, y1, y2', 'required'),
            array('user_id, file_id', 'length', 'max'=>64),
			array('ws, x1, x2, y1, y2', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, file_id, description, ws, x1, x2, y1, y2', 'safe', 'on'=>'search'),
		);
	}

    public function beforeValidate()
    {
        $this->ws = number_format($this->ws, 2, '.', '');
        $this->x1 = number_format($this->x1, 2, '.', '');
        $this->x2 = number_format($this->x2, 2, '.', '');
        $this->y1 = number_format($this->y1, 2, '.', '');
        $this->y2 = number_format($this->y2, 2, '.', '');

        return true;
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

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
