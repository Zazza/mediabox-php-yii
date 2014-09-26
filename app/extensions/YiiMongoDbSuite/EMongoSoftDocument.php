<?php
/**
 * EMongoSoftDocument.php
 *
 * PHP version 5.2+
 *
 * @author		Dariusz Górecki <darek.krk@gmail.com>
 * @author		Invenzzia Group, open-source division of CleverIT company http://www.invenzzia.org
 * @copyright	2011 CleverIT http://www.cleverit.com.pl
 * @license		http://www.yiiframework.com/license/ BSD license
 * @version		1.3
 * @category	ext
 * @package		ext.YiiMongoDbSuite
 * @since		v1.3.4
 */

/**
 * EmongoSoftDocument cass
 * @since v1.3.4
 */
abstract class EMongoSoftDocument extends EMongoDocument
{
	/**
	 * Array that holds initialized soft attributes
	 * @var array $softAttributes
	 * @since v1.3.4
	 */
	protected $softAttributes = array();

	/**
	 * Adds soft attributes support to magic __get method
	 * @see EMongoEmbeddedDocument::__get()
	 * @since v1.3.4
	 */
	public function __get($name)
	{
		if(array_key_exists($name, $this->softAttributes)) // Use of array_key_exists is mandatory !!!
		{
			return $this->softAttributes[$name];
		}
		else
			return parent::__get($name);
	}

	/**
	 * Adds soft attributes support to magic __set method
	 * @see EMongoEmbeddedDocument::__set()
	 * @since v1.3.4
	 */
	public function __set($name, $value)
	{
		if(array_key_exists($name, $this->softAttributes)) // Use of array_key_exists is mandatory !!!
		{
			$this->softAttributes[$name] = $value;
		}
		else
			parent::__set($name, $value);
	}

	/**
	 * Adds soft attributes support to magic __isset method
	 * @see EMongoEmbeddedDocument::__isset()
	 * @since v1.3.4
	 */
	public function __isset($name)
	{
		if(array_key_exists($name, $this->softAttributes)) // Use of array_key_exists is mandatory !!!
			return true;
		else
			return parent::__isset($name);
	}

	/**
	 * Adds soft attributes support to magic __unset method
	 * @see CComponent::__unset()
	 * @since v1.3.4
	 */
	public function __unset($name)
	{
		if(array_key_exists($name, $this->softAttributes)) // Use of array_key_exists is mandatory !!!
			unset($this->softAttributes[$name]);
		else
			parent::__unset($name);
	}

	/**
	 * Initializes a soft attribute, before it can be used
	 * @param string $name attribute name
	 * @since v1.3.4
	 */
	public function initSoftAttribute($name)
	{
		if(!array_key_exists($name, $this->softAttributes))
			$this->softAttributes[$name] = null;
	}

	/**
	 * Initializes a soft attributes, from given list, before they can be used
	 * @param mixed $attributes attribute names list
	 * @since v1.3.4
	 */
	public function initSoftAttributes($attributes)
	{
		foreach($attributes as $name)
			$this->initSoftAttribute($name);
	}

	/**
	 * Return the list of attribute names of this model, with respect of initialized soft attributes
	 * @see EMongoEmbeddedDocument::attributeNames()
	 * @since v1.3.4
	 */
	public function attributeNames()
	{
		return array_merge(array_keys($this->softAttributes), parent::attributeNames());
	}

	/**
	 * Instantiate the model object from given document, with respect of soft attributes
	 * @see EMongoDocument::instantiate()
	 * @since v1.3.4
	 */
	protected function instantiate($attributes)
	{
		$class=get_class($this);
		$model=new $class(null);
		$model->initEmbeddedDocuments();

		$model->initSoftAttributes(
			array_diff(
				array_keys($attributes),
				parent::attributeNames()
			)
		);

		$model->setAttributes($attributes, false);
		return $model;
	}

	/**
	 * This method does the actual convertion to an array
	 * Does not fire any events
	 * @return array an associative array of the contents of this object
	 * @since v1.3.4
	 */
	protected function _toArray()
	{
		$arr = parent::_toArray();
		foreach($this->softAttributes as $key => $value)
			$arr[$key]=$value;
		return $arr;
	}

	/**
	 * Return the actual list of soft attributes being used by this model
	 * @return array list of initialized soft attributes
	 * @since v1.3.4
	 */
	public function getSoftAttributeNames()
	{
		return array_diff(
			array_keys($this->softAttributes),
			parent::attributeNames()
		);
	}
}