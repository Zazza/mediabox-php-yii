<?php

class User extends EMongoDocument
{
    public $username;
    public $password;
    public $role;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function getCollectionName()
    {
        return 'user';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username, password, role', 'required'),
			array('username', 'length', 'max'=>16),
			array('password', 'length', 'max'=>64),
			array('role', 'length', 'max'=>8),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, username, password, role', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => 'Login',
			'password' => 'Password',
			'role' => 'Role',
		);
	}

	protected function beforeSave(){
		$this->password = md5($this->password);
		return parent::beforeSave();
	}
}