<?php
/**
 * EMongoDocumentDataProvider.php
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
 * EMongoRecordDataProvider implements a data provider based on EMongoRecord.
 *
 * EMongoRecordDataProvider provides data in terms of MongoRecord objects which are
 * of class {@link modelClass}. It uses the AR {@link CActiveRecord::findAll} method
 * to retrieve the data from database. The {@link query} property can be used to
 * specify various query options, such as conditions, sorting, pagination, etc.
 *
 * @author canni
 * @since v1.0
 */
class EMongoDocumentDataProvider extends CDataProvider
{
	/**
	 * @var string the name of key field. Defaults to '_id', as a mongo default document primary key.
	 * @since v1.0
	 */
	public $keyField;

	/**
	 * @var string the primary ActiveRecord class name. The {@link getData()} method
	 * will return a list of objects of this class.
	 * @since v1.0
	 */
	public $modelClass;

	/**
	 * @var EMongoRecord the AR finder instance (e.g. <code>Post::model()</code>).
	 * This property can be set by passing the finder instance as the first parameter
	 * to the constructor.
	 * @since v1.0
	 */
	public $model;

	private $_criteria;

	/**
	 * Constructor.
	 * @param mixed $modelClass the model class (e.g. 'Post') or the model finder instance
	 * (e.g. <code>Post::model()</code>, <code>Post::model()->published()</code>).
	 * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
	 * @since v1.0
	 */
	public function __construct($modelClass, $config = array())
	{
		if(is_string($modelClass))
		{
			$this->modelClass = $modelClass;
			$this->model = EMongoDocument::model($modelClass);
		}
		else if($modelClass instanceof EMongoDocument)
		{
			$this->modelClass = get_class($modelClass);
			$this->model = $modelClass;
		}

		$this->_criteria = $this->model->getDbCriteria();
		if(isset($config['criteria']))
		{
			$this->_criteria->mergeWith($config['criteria']);
			unset($config['criteria']);
		}

		$this->setId($this->modelClass);
		foreach($config as $key=>$value)
			$this->$key=$value;

		if($this->keyField!==null)
		{
			if(is_array($this->keyField))
				throw new CException('This DataProvider cannot handle multi-field primary key!');
		}
		else
			$this->keyField='_id';
	}

	/**
	 * Returns the criteria.
	 * @return array the query criteria
	 * @since v1.0
	 */
	public function getCriteria()
	{
		return $this->_criteria;
	}

	/**
	 * Sets the query criteria.
	 * @param array $value the query criteria. Array representing the MongoDB query criteria.
	 * @since v1.0
	 */
	public function setCriteria($criteria)
	{
		if(is_array($criteria))
			$this->_criteria = new EMongoCriteria($criteria);
		else if($criteria instanceof EMongoCriteria)
			$this->_criteria = $criteria;
	}

	/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 * @since v1.0
	 */
	protected function fetchData()
	{
		if(($pagination=$this->getPagination())!==false)
		{
			$pagination->setItemCount($this->getTotalItemCount());

			$this->_criteria->setLimit($pagination->getLimit());
			$this->_criteria->setOffset($pagination->getOffset());
		}

		if(($sort=$this->getSort())!==false && ($order=$sort->getOrderBy())!='')
		{
			$sort=array();
			foreach($this->getSortDirections($order) as $name=>$descending)
			{
				$sort[$name]=$descending ? EMongoCriteria::SORT_DESC : EMongoCriteria::SORT_ASC;
			}
			$this->_criteria->setSort($sort);
		}

		return $this->model->findAll($this->_criteria);
	}

	/**
	 * Fetches the data item keys from the persistent data storage.
	 * @return array list of data item keys.
	 * @since v1.0
	 */
	protected function fetchKeys()
	{
		$keys = array();
		foreach($this->getData() as $i=>$data)
		{
			$keys[$i] = $data->{$this->keyField};
		}
		return $keys;
	}

	/**
	 * Calculates the total number of data items.
	 * @return integer the total number of data items.
	 * @since v1.0
	 */
	public function calculateTotalItemCount()
	{
		return $this->model->count($this->_criteria);
	}

	/**
	 * Converts the "ORDER BY" clause into an array representing the sorting directions.
	 * @param string $order the "ORDER BY" clause.
	 * @return array the sorting directions (field name => whether it is descending sort)
	 * @since v1.0
	 */
	protected function getSortDirections($order)
	{
		$segs=explode(',',$order);
		$directions=array();
		foreach($segs as $seg)
		{
			if(preg_match('/(.*?)(\s+(desc|asc))?$/i',trim($seg),$matches))
				$directions[$matches[1]]=isset($matches[3]) && !strcasecmp($matches[3],'desc');
			else
				$directions[trim($seg)]=false;
		}
		return $directions;
	}
}