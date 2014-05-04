<?php

/**
 * This is the model class for table "images_crops".
 *
 * The followings are the available columns in table 'images_crops':
 * @property string $id
 * @property string $user_id
 * @property string $file_id
 * @property string $description
 * @property string $ws
 * @property string $x1
 * @property string $x2
 * @property string $y1
 * @property string $y2
 */
class ImagesCrops extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
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
			array('user_id, file_id, ws, x1, x2, y1, y2', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, file_id, description, ws, x1, x2, y1, y2', 'safe', 'on'=>'search'),
		);
	}

    public function beforeValidate()
    {
        $this->ws = number_format($this->ws, 4);
        $this->x1 = number_format($this->x1, 4);
        $this->x2 = number_format($this->x2, 4);
        $this->y1 = number_format($this->y1, 4);
        $this->y2 = number_format($this->y2, 4);

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

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'file_id' => 'File',
			'description' => 'Description',
			'ws' => 'Ws',
			'x1' => 'X1',
			'x2' => 'X2',
			'y1' => 'Y1',
			'y2' => 'Y2',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('file_id',$this->file_id,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('ws',$this->ws,true);
		$criteria->compare('x1',$this->x1,true);
		$criteria->compare('x2',$this->x2,true);
		$criteria->compare('y1',$this->y1,true);
		$criteria->compare('y2',$this->y2,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ImagesCrops the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
