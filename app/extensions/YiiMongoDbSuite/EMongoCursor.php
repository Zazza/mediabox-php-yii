<?php
/**
 * EMongoCursor.php
 *
 * PHP version 5.2+
 *
 * @author		Nagy Attila Gábor <nagy.attila.gabor@gmail.com>
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
 * EMongoCursor
 *
 * Cursor object, that behaves much like the MongoCursor,
 * but this one returns instantiated objects
 * @since v1.3.4
 */
class EMongoCursor
implements Iterator, Countable
{
	/**
	 * @var MongoCursor $_cursor the MongoCursor returned by the query
	 * @since v1.3.4
	 */
	protected $_cursor;

	/**
	 * @var EMongoDocument $_model the model used for instantiating objects
	 * @since v1.3.4
	 */
	protected $_model;

	/**
	 * Construct a new EMongoCursor
	 *
	 * @param MongoCursor $cursor the cursor returned by the query
	 * @param EMongoDocument $model the model for instantiating objects
	 * @since v1.3.4
	 */
	public function __construct(MongoCursor $cursor, EMongoDocument $model)
	{
		$this->_cursor	= $cursor;
		$this->_model	= $model;
	}

	/**
	 * Return MongoCursor for additional tuning
	 *
	 * @return MongoCursor the cursor used for this query
	 * @since v1.3.4
	 */
	public function getCursor()
	{
		return $this->_cursor;
	}

	/**
	 * Return the current element
	 * @return EMongoDocument
	 * @since v1.3.4
	 */
	public function current()
	{
		$document = $this->_cursor->current();
		if(empty($document))
			return $document;

		return $this->_model->populateRecord($document);
	}

	/**
	 * Return the key of the current element
	 * @return scalar
	 * @since v1.3.4
	 */
	public function key()
	{
		return $this->_cursor->key();
	}

	/**
	 * Move forward to next element
	 * @return void
	 * @since v1.3.4
	 */
	public function next()
	{
		$this->_cursor->next();
	}

	/**
	 * Rewind the Iterator to the first element
	 * @return void
	 * @since v1.3.4
	 */
	public function rewind()
	{
		$this->_cursor->rewind();
	}

	/**
	 * Checks if current position is valid
	 * @return boolean
	 * @since v1.3.4
	 */
	public function valid()
	{
		return $this->_cursor->valid();
	}

	/**
	 * Returns the number of documents found
	 * {@see http://www.php.net/manual/en/mongocursor.count.php}
	 * @param boolean $foundOnly default FALSE
	 * @return integer count of documents found
	 * @since v1.3.4
	 */
	public function count($foundOnly = false)
	{
		return $this->_cursor->count($foundOnly);
	}

	/**
	 * Apply a limit to this cursor
	 * {@see http://www.php.net/manual/en/mongocursor.limit.php}
	 * @param integer $limit new limit
	 * @since v1.3.4
	 */
	public function limit($limit)
	{
		$this->_cursor->limit($limit);
	}

	/**
	 * Skip a $offset records
	 * {@see http://www.php.net/manual/en/mongocursor.skip.php}
	 * @param integer $skip new skip
	 * @since v1.3.4
	 */
	public function offset($offset)
	{
		$this->_cursor->offset($offset);
	}

	/**
	 * Apply sorting directives
	 * {@see http://www.php.net/manual/en/mongocursor.sort.php}
	 * @param array $sort sorting directives
	 * @since v1.3.4
	 */
	public function sort(array $fields)
	{
		$this->_cursor->sort($fields);
	}
}
