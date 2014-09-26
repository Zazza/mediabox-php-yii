<?php
/**
 * EMongoPartialDocument.php
 *
 * PHP version 5.2+
 *
 * @author		Nagy Attila Gabor
 * @author		Dariusz Górecki <darek.krk@gmail.com>
 * @copyright	2011 CleverIT http://www.cleverit.com.pl
 * @license		http://www.yiiframework.com/license/ BSD license
 * @version		1.3
 * @category	ext
 * @package		ext.YiiMongoDbSuite
 * @since		v1.3.6
 */

/**
 * EMongoPartialDocument
 *
 * @property-read array $loadedFields
 * @property-read array $unloadedFields
 * @since	v1.3.6
 */
abstract class EMongoPartialDocument extends EMongoDocument
{
	protected $_loadedFields	= array();	// Fields that have not been loaded from DB
	protected $_partial			= false;	// Whatever the document has been partially loaded

	/**
	 * Returns if this document is only partially loaded
	 * @return boolean true if the document is partially loaded
	 */
	public function isPartial()
	{
		return $this->_partial;
	}

	/**
	 * Returns list of fields that have been loaded from DB by
	 * {@link EMongoDocument::instantiate} method.
	 * @return array
	 */
	public function getLoadedFields()
	{
		return $this->_partial ? $this->_loadedFields : array();
	}

	/**
	 * Returns list of fields that have not been loaded from DB by
	 * {@link EMongoDocument::instantiate} method.
	 * @return array
	 */
	public function getUnloadedFields()
	{
		return $this->_partial ? array_diff(
			$this->_loadedFields,
			$this->attributeNames()
		) : array();
	}

	/**
	 * Check if this attribute is loaded, and if not, then return null
	 */
	public function __get($name)
	{
		if(
			$this->_partial &&
			$this->hasEmbeddedDocuments() &&
			isset(self::$_embeddedConfig[get_class($this)][$name]) &&
			!in_array($name, $this->_loadedFields)
		){
			return null;
		}
		else
			return parent::__get($name);
	}

	/**
	 * If user explicitly sets the unloaded embedded field, consider it as an loaded one, if model is partially loaded
	 * @see EMongoEmbeddedDocument::__set()
	 */
	public function __set($name, $value)
	{
		$return = parent::__set($name, $value);

		if($this->_partial && !in_array($name, $this->_loadedFields))
		{
			$this->_loadedFields[] = $name;

			if(count($this->_loadedFields) === count($this->attributeNames()))
			{
				$this->_partial		= false;
				$this->loadedFields	= null;
			}
		}

		return $return;
	}

	/**
	 * Loads additional, previously unloaded attributes
	 * to this document.
	 * @param array $attributes attributes to be loaded
	 * @return boolean wether the load was successfull
	 */
	public function loadAttributes($attributes = array())
	{
		$document = $this->getCollection()->findOne(
			array('_id' => $this->_id),
			$attributes
		);

		unset($document['_id']);

		$attributesSum = array_merge($this->_loadedFields, array_keys($document));

		if(count($attributesSum) === count($this->attributeNames()))
		{
			$this->_partial			= false;
			$this->_loadedFields	= null;
		}
		else
		{
			$this->_loadedFields = $attributesSum;
		}

		$this->setAttributes($document, false);

		return true;
	}

	/**
	 * Updates the row represented by this active record.
	 * All loaded attributes will be saved to the database.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @param boolean modify if set true only selected attributes will be replaced, and not
	 * the whole document
	 * @return boolean whether the update is successful
	 * @throws CException if the record is new
	 * @throws EMongoException on fail of update
	 * @throws MongoCursorException on fail of update, when safe flag is set to true
	 * @throws MongoCursorTimeoutException on timeout of db operation , when safe flag is set to true
	 * @since v1.0
	 */
	public function update(array $attributes=null, $modify = false)
	{
		if($this->_partial)
		{
			$attributes = count($attributes) > 0 ? array_intersect($attributes, $this->_loadedFields) : array_diff($this->_loadedFields, array('_id'));
			return parent::update($attributes, true);
		}

		return parent::update($attributes, $modify);
	}

	protected function instantiate($attributes)
	{
		$model = parent::instantiate($attributes);

		$loadedFields = array_keys($attributes);

		if(count($loadedFields) < count($model->attributeNames()))
		{
			$model->_partial		= true;
			$model->_loadedFields	= $loadedFields;
		}

		return $model;
	}
}
