<?php
class UserAddress extends EMongoEmbeddedDocuemnt
{
	public $city;
	public $street;
	public $apartment;
	public $zip;

	public function rules()
	{
		return array(
			array('city, street, house', 'length', 'max'=>255),
			array('house, apartment, zip', 'length', 'max'=>10),
		);
	}

	public function attributeLabels()
	{
		return array(
			'zip' => 'Postal Code',
		);
	}
}
