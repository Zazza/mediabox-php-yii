<?php
/**
 * EMongoModifier.php
 *
 * PHP version 5.2+
 *
 * @author		Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright	2011 Ibitux http://www.ibitux.com
 * @license		http://www.yiiframework.com/license/ BSD license
 * @version		xxx
 * @category	ext
 * @package		ext.YiiMongoDbSuite
 *
 */

/**
 * EMongoModifier class
 *
 * This class is a helper for building MongoDB atomic updates
 *
 * 1. addCond method
 * $criteriaObject->addCond($fieldName, $operator, $vale); // this will produce fieldName <operator> value
 *
 * For modifiers list {@see EMongoModifier::$modifiers}
 *
 * @author		Philippe Gaultier <pgaultier@ibitux.com>
 * @since		v1.3.6
 */
class EMongoModifier extends CComponent
{
	/**
	 * @since v1.3.6
	 * @var array $modifiers supported modifiers
	 */
	public static $modifiers = array(
		'inc'		=> '$inc',
		'set'		=> '$set',
		'unset'		=> '$unset',
		'push'		=> '$push',
		'pushAll'	=> '$pushAll',
		'addToSet'	=> '$addToSet',
		'pop'		=> '$pop',
		'pull'		=> '$pull',
		'pullAll'	=> '$pullAll',
		'rename'	=> '$rename',
	);

	private $_fields = array();
	/**
	 * Constructor
	 * Modifier sample:
	 *
	 * <PRE>
	 * 'modifier' = array(
	 *	'fieldName1'=>array('inc' => $incValue),
	 *	'fieldName2'=>array('set' => $targetValue),
	 *	'fieldName3'=>array('unset' => 1),
	 *	'fieldName4'=>array('push' => $pushedValue),
	 *	'fieldName5'=>array('pushAll' => array($pushedValue1, $pushedValue2)),
	 *	'fieldName6'=>array('addToSet' => $addedValue),
	 *	'fieldName7'=>array('pop' => 1),
	 *	'fieldName8'=>array('pop' => -1),
	 *	'fieldName9'=>array('pull' => $removedValue),
	 *	'fieldName10'=>array('pullAll' => array($removedValue1, $removedValue2)),
	 *	'fieldName11'=>array('rename' => $newFieldName),
	 * );
	 * </PRE>
	 * @param array $modifier basic definition of modifiers
	 * @since v1.3.6
	 */
	public function __construct($modifier=null)
	{
		if(is_array($modifier))
		{
			foreach($modifier as $fieldName=>$rules)
			{
				foreach($rules as $mod=>$value) {
					$this->_fields[$fieldName] = array(self::$modifiers[$mod] => $value);
				}
			}
		}
		else if($modifier instanceof EMongoModifier)
			$this->mergeWith($modifier);
	}
	/**
	 * Compute modifier to be able to initiate request
	 * @return array
	 */
	public function getModifiers()
	{
		$modifier = array();
		foreach($this->_fields as $fieldName=>$rule)
		{
			foreach($rule as $operator=>$value)
			{
				if(isset($modifier[$operator]) && is_array($modifier[$operator]))
				{
					$modifier[$operator] = array_merge($modifier[$operator], array($fieldName=>$value));
				} else {
					$modifier[$operator] = array($fieldName=>$value);
				}
			}
		}
		return $modifier;
	}
	public function getFields()
	{
		return $this->_fields;
	}
	/**
	 * Add a new set of modifiers to current modifiers. If modifiers has already been
	 * added for specific field, they will be overwritten.
	 *
	 * @param EMongoModifier $modifier modifier to merge into current object
	 * @return EMongoModifier
	 */
	public function mergeWith($modifier)
	{
		if(is_array($modifier))
			$modifier = new EMongoModifier($modifier);
		else if(empty($modifier))
			return $this;

		foreach($modifier->getFields() as $fieldName=>$rule)
		{
			$this->_fields[$fieldName] = $rule;
		}
		return $this;
	}
	/**
	 * Add a new modifier rule to specific field
	 * @param string $fieldName name of the field we want to update
	 * @param string $modifier  type of the modifier @see EMongoModifier::$modifiers
	 * @param mixed  $value     value used by the modifier
	 * @return EMongoModifier
	 */
	public function addModifier($fieldName, $modifier, $value)
	{
		$this->_fields[$fieldName] = array(self::$modifiers[$modifier]=>$value);
		return $this;
	}
	/**
	 * Check if we have modifiers to apply
	 * @return boolean
	 */
	public function getCanApply() {
		if(count($this->_fields) > 0) {
			return true;
		} else {
			return false;
		}
	}
}

