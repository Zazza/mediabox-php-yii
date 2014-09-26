<?php
/**
 * EEmbeddedArraysBehavior.php
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
 * @since		v1.0
 */

/**
 * @since v1.0
 */
class EEmbeddedArraysBehavior extends EMongoDocumentBehavior
{
	/**
	 * Name of property witch holds array od documents
	 *
	 * @var string $arrayPropertyName
	 * @since v1.0
	 */
	public $arrayPropertyName;

	/**
	 * Class name of doc in array
	 *
	 * @var string $arrayDocClassName
	 * @since v1.0
	 */
	public $arrayDocClassName;

	private $_cache;
	
	/**
	 * This flag shows us if we're connected to an embedded document
	 *
	 * @var boolean $_embeddedOwner
	 */
	private $_embeddedOwner;
	
	public function events() {
		if (!$this->_embeddedOwner) {
			return parent::events();
		}
		else {
			// If attached to an embedded document these events are not defined
			// and would throw an error if attached to
			$events = parent::events();
			unset($events['onBeforeSave']);
			unset($events['onAfterSave']);
			unset($events['onBeforeDelete']);
			unset($events['onAfterDelete']);
			unset($events['onBeforeFind']);
			unset($events['onAfterFind']);
			return $events;
		}
	}

	/**
	 * @since v1.0
	 * @see CBehavior::attach()
	 */
	public function attach($owner)
	{
		// Test if we have correct embding class
		if(!is_subclass_of($this->arrayDocClassName, 'EMongoEmbeddedDocument'))
			throw new CException(Yii::t('yii', get_class($testObj).' is not a child class of EMongoEmbeddedDocument!'));
		
		$this->_embeddedOwner = !($owner instanceof EMongoDocument);
		
		parent::attach($owner);

		$this->parseExistingArray();
	}

	/**
	 * Event: initialize array of embded documents
	 * @since v1.0
	 */
	public function afterEmbeddedDocsInit($event)
	{
		$this->parseExistingArray();
	}

	/**
	 * @since v1.0
	 */
	private function parseExistingArray()
	{
		if(is_array($this->getOwner()->{$this->arrayPropertyName}))
		{
			$arrayOfDocs = array();
			foreach($this->getOwner()->{$this->arrayPropertyName} as $doc)
			{
				$obj = new $this->arrayDocClassName;
				$obj->setAttributes($doc, false);
				
				// If any EEmbeddedArraysBehavior is attached,
				// then we should trigger parsing of the newly set
				// attributes
				foreach (array_keys($obj->behaviors()) as $name) {
					$behavior = $obj->asa($name);
					if ($behavior instanceof EEmbeddedArraysBehavior) {
						$behavior->parseExistingArray();
					}
				}
				$arrayOfDocs[] = $obj;
			}
			$this->getOwner()->{$this->arrayPropertyName} = $arrayOfDocs;
		}
	}

	/**
	 * @since v1.0.2
	 */
	public function afterValidate($event)
	{
		parent::afterValidate($event);
		foreach($this->getOwner()->{$this->arrayPropertyName} as $doc)
		{
			if(!$doc->validate())
				$this->getOwner()->addErrors($doc->getErrors());
		}
	}

	public function beforeToArray($event)
	{
		if(is_array($this->getOwner()->{$this->arrayPropertyName}))
		{
			$arrayOfDocs = array();
			$this->_cache = $this->getOwner()->{$this->arrayPropertyName};

			foreach($this->_cache as $doc)
			{
				$arrayOfDocs[] = $doc->toArray();
			}

			$this->getOwner()->{$this->arrayPropertyName} = $arrayOfDocs;
			return true;
		}
		else
			return false;
	}

	/**
	 * Event: re-initialize array of embedded documents which where toArray()ized by beforeSave()
	 */
	public function afterToArray($event)
	{
		$this->getOwner()->{$this->arrayPropertyName} = $this->_cache;
		$this->_cache = null;
	}
}
