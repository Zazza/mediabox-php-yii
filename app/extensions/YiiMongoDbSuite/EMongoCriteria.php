<?php
/**
 * EMongoCriteria.php
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
 *
 */

/**
 * EMongoCriteria class
 *
 * This class is a helper for building MongoDB query arrays, it support three syntaxes for adding conditions:
 *
 * 1. 'equals' syntax:
 * $criteriaObject->fieldName = $value; // this will produce fieldName == value query
 * 2. fieldName call syntax
 * $criteriaObject->fieldName($operator, $value); // this will produce fieldName <operator> value
 * 3. addCond method
 * $criteriaObject->addCond($fieldName, $operator, $vale); // this will produce fieldName <operator> value
 *
 * For operators list {@see EMongoCriteria::$operators}
 *
 * @author		Dariusz Górecki <darek.krk@gmail.com>
 * @since		v1.0
 */
class EMongoCriteria extends CComponent
{
	/**
	 * @since v1.0
	 * @var array $operators supported operators lists
	 */
	public static $operators = array(
		'greater'		=> '$gt',
		'>'				=> '$gt',
		'greatereq'		=> '$gte',
		'>='			=> '$gte',
		'less'			=> '$lt',
		'<'				=> '$lt',
		'lesseq'		=> '$lte',
		'<='			=> '$lte',
		'noteq'			=> '$ne',
		'!='			=> '$ne',
		'<>'			=> '$ne',
		'in'			=> '$in',
		'notin'			=> '$nin',
		'all'			=> '$all',
		'size'			=> '$size',
		'type'			=> '$type',
		'exists'		=> '$exists',
		'notexists'		=> '$exists',
		'elemmatch'		=> '$elemMatch',
		'mod'			=> '$mod',
		'%'				=> '$mod',
		'equals'		=> '$$eq',
		'eq'			=> '$$eq',
		'=='			=> '$$eq',
		'where'			=> '$where',
		'or'			=> '$or'
	);

	const SORT_ASC		= 1;
	const SORT_DESC		= -1;

	private $_select		= array();
	private $_limit			= null;
	private $_offset		= null;
	private $_conditions	= array();
	private $_sort			= array();
	private $_workingFields	= array();
	private $_useCursor		= null;

	/**
	 * Constructor
	 * Example criteria:
	 *
	 * <PRE>
	 * 'criteria' = array(
	 * 	'conditions'=>array(
	 *		'fieldName1'=>array('greater' => 0),
	 *		'fieldName2'=>array('>=' => 10),
	 *		'fieldName3'=>array('<' => 10),
	 *		'fieldName4'=>array('lessEq' => 10),
	 *		'fieldName5'=>array('notEq' => 10),
	 *		'fieldName6'=>array('in' => array(10, 9)),
	 *		'fieldName7'=>array('notIn' => array(10, 9)),
	 *		'fieldName8'=>array('all' => array(10, 9)),
	 *		'fieldName9'=>array('size' => 10),
	 *		'fieldName10'=>array('exists'),
	 *		'fieldName11'=>array('notExists'),
	 *		'fieldName12'=>array('mod' => array(10, 9)),
	 * 		'fieldName13'=>array('==' => 1)
	 * 	),
	 * 	'select'=>array('fieldName', 'fieldName2'),
	 * 	'limit'=>10,
	 *  'offset'=>20,
	 *  'sort'=>array('fieldName1'=>EMongoCriteria::SORT_ASC, 'fieldName2'=>EMongoCriteria::SORT_DESC),
	 * );
	 * </PRE>
	 * @param mixed $criteria
	 * @since v1.0
	 */
	public function __construct($criteria=null)
	{
		if(is_array($criteria))
		{
			if(isset($criteria['conditions']))
				foreach($criteria['conditions'] as $fieldName=>$conditions)
				{
					$fieldNameArray = explode('.', $fieldName);
					if(count($fieldNameArray) === 1)
						$fieldName = array_shift($fieldNameArray);
					else
						$fieldName = array_pop($fieldNameArray);

					foreach($conditions as $operator => $value)
					{
						$this->setWorkingFields($fieldNameArray);
						$operator = strtolower($operator);

						$this->$fieldName($operator, $value);
					}
				}

			if(isset($criteria['select']))
				$this->select($criteria['select']);
			if(isset($criteria['limit']))
				$this->limit($criteria['limit']);
			if(isset($criteria['offset']))
				$this->offset($criteria['offset']);
			if(isset($criteria['sort']))
				$this->setSort($criteria['sort']);
			if(isset($criteria['useCursor']))
				$this->setUseCursor($criteria['useCursor']);
		}
		else if($criteria instanceof EMongoCriteria)
			$this->mergeWith($criteria);
	}

	/**
	 * Merge with other criteria
	 * - Field list operators will be merged
	 * - Limit and offet will be overriden
	 * - Select fields list will be merged
	 * - Sort fields list will be merged
	 * @param array|EMongoCriteria $criteria
	 * @since v1.0
	 */
	public function mergeWith($criteria)
	{
		if(is_array($criteria))
			$criteria = new EMongoCriteria($criteria);
		else if(empty($criteria))
			return $this;

		$opTable = array_values(self::$operators);

		foreach($criteria->_conditions as $fieldName=>$conds)
		{
			if(
				is_array($conds) &&
				count(array_diff(array_keys($conds), $opTable)) == 0
			)
			{
				if(isset($this->_conditions[$fieldName]) && is_array($this->_conditions[$fieldName]))
				{
					foreach($this->_conditions[$fieldName] as $operator => $value)
						if(!in_array($operator, $opTable))
							unset($this->_conditions[$fieldName][$operator]);
				}
				else
					$this->_conditions[$fieldName] = array();

				foreach($conds as $operator => $value)
					$this->_conditions[$fieldName][$operator] = $value;
			}
			else
				$this->_conditions[$fieldName] = $conds;
		}

		if(!empty($criteria->_limit))
			$this->_limit	= $criteria->_limit;
		if(!empty($criteria->_offset))
			$this->_offset	= $criteria->_offset;
		if(!empty($criteria->_sort))
			$this->_sort	= array_merge($this->_sort, $criteria->_sort);
		if(!empty($criteria->_select))
			$this->_select	= array_merge($this->_select, $criteria->_select);

		return $this;
	}

	/**
	 * If we have operator add it otherwise call parent implementation
	 * @see CComponent::__call()
	 * @since v1.0
	 */
	public function __call($fieldName, $parameters)
	{
		if(isset($parameters[0]))
			$operatorName = strtolower($parameters[0]);
		if(isset($parameters[1]) || ($parameters[1] === null))
			$value = $parameters[1];

		if(is_numeric($operatorName))
		{
			$operatorName = strtolower(trim($value));
			$value = (strtolower(trim($value)) === 'exists') ? true : false;
		}

		if(in_array($operatorName, array_keys(self::$operators)))
		{
			array_push($this->_workingFields, $fieldName);
			$fieldName = implode('.', $this->_workingFields);
			$this->_workingFields = array();
			switch($operatorName)
			{
				case 'exists':
						$this->addCond($fieldName, $operatorName, true);
					break;
				case 'notexists':
						$this->addCond($fieldName, $operatorName, false);
					break;
				default:
					$this->addCond($fieldName, $operatorName, $value);
			}
			return $this;
		}
		else
			return parent::__call($fieldName, $parameters);
	}

	/**
	 * @since v1.0.2
	 */
	public function __get($name)
	{
		array_push($this->_workingFields, $name);
		return $this;
	}

	/**
	 * @since v1.0.2
	 */
	public function __set($name, $value)
	{
		array_push($this->_workingFields, $name);
		$fieldList = implode('.', $this->_workingFields);
		$this->_workingFields = array();
		$this->addCond($fieldList, '==', $value);
	}

	/**
	 * Return query array
	 * @return array query array
	 * @since v1.0
	 */
	public function getConditions()
	{
		return $this->_conditions;
	}

	/**
	 * @since v1.0
	 */
	public function setConditions(array $conditions)
	{
		$this->_conditions = $conditions;
	}

	/**
	 * @since v1.0
	 */
	public function getLimit()
	{
		return $this->_limit;
	}

	/**
	 * @since v1.0
	 */
	public function setLimit($limit)
	{
		$this->limit($limit);
	}

	/**
	 * @since v1.0
	 */
	public function getOffset()
	{
		return $this->_offset;
	}

	/**
	 * @since v1.0
	 */
	public function setOffset($offset)
	{
		$this->offset($offset);
	}

	/**
	 * @since v1.0
	 */
	public function getSort()
	{
		return $this->_sort;
	}

	/**
	 * @since v1.0
	 */
	public function setSort(array $sort)
	{
		$this->_sort = $sort;
	}

	/**
	 * @since v1.3.7
	 */
	public function getUseCursor()
	{
		return $this->_useCursor;
	}

	/**
	 * @since v1.3.7
	 */
	public function setUseCursor($useCursor)
	{
		$this->_useCursor = $useCursor;
	}

	/**
	 * Return selected fields
	 *
	 * @param boolean $forCursor MongoCursor::fields() method requires
	 *                the fields to be specified as a hashmap. When this
	 *                parameter is set to true, then we'll return
	 *                the fields in this format
	 * @since v1.3.1
	 */
	public function getSelect($forCursor = false)
	{
		if (!$forCursor) return $this->_select;
		return array_fill_keys($this->_select, true); // PHP 5.2.0+ required!
	}

	/**
	 * @since v1.3.1
	 */
	public function setSelect(array $select)
	{
		$this->_select = $select;
	}

	/**
	 * @since v1.3.1
	 */
	public function getWorkingFields()
	{
		return $this->_workingFields;
	}

	/**
	 * @since v1.3.1
	 */
	public function setWorkingFields(array $select)
	{
		$this->_workingFields = $select;
	}

	/**
	 * List of fields to get from DB
	 * Multiple calls to this method will merge all given fields
	 *
	 * @param array $fieldList list of fields to select
	 * @since v1.0
	 */
	public function select(array $fieldList=null)
	{
		if($fieldList!==null)
			$this->_select = array_merge($this->_select, $fieldList);
		return $this;
	}

	/**
	 * Set linit
	 * Multiple calls will overrride previous value of limit
	 *
	 * @param integer $limit limit
	 * @since v1.0
	 */
	public function limit($limit)
	{
		$this->_limit = intval($limit);
		return $this;
	}

	/**
	 * Set offset
	 * Multiple calls will override previous value
	 *
	 * @param integer $offset offset
	 * @since v1.0
	 */
	public function offset($offset)
	{
		$this->_offset = intval($offset);
		return $this;
	}

	/**
	 * Add sorting, avaliabe orders are: EMongoCriteria::SORT_ASC and EMongoCriteria::SORT_DESC
	 * Each call will be groupped with previous calls
	 * @param string $fieldName
	 * @param integer $order
	 * @since v1.0
	 */
	public function sort($fieldName, $order)
	{
		$this->_sort[$fieldName] = intval($order);
		return $this;
	}

	/**
	 * Add condition
	 * If specified field already has a condition, values will be merged
	 * duplicates will be overriden by new values!
	 * @param string $fieldName
	 * @param string $op operator
	 * @param mixed $value
	 * @since v1.0
	 */
	public function addCond($fieldName, $op, $value)
	{
		$op = self::$operators[$op];
		
		if($op == self::$operators['or']) 
		{
			if(!isset($this->_conditions[$op])) 
			{
				$this->_conditions[$op] = array();
			}
			$this->_conditions[$op][] = array($fieldName=>$value);
		} else {
		
			if(!isset($this->_conditions[$fieldName]) && $op != self::$operators['equals'])
				$this->_conditions[$fieldName] = array();
	
			if($op != self::$operators['equals'])
			{
				if(
					!is_array($this->_conditions[$fieldName]) ||
					count(array_diff(array_keys($this->_conditions[$fieldName]), array_values(self::$operators))) > 0
				)
				{
					$this->_conditions[$fieldName] = array();
				}
				$this->_conditions[$fieldName][$op] = $value;
			}
			else
				$this->_conditions[$fieldName] = $value;
		}
		return $this;
	}
}
