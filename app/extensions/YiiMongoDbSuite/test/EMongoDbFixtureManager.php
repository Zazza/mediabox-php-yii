<?php
/**
 * EMongoDbFixtureManager
 *
 * @author		Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright	2010-2011 Ibitux
 * @license		http://www.yiiframework.com/license/ BSD license
 * @category	tests
 * @package		ext.YiiMongoDbSuite.tests
 * @since		v1.3.6
 */

/**
 * EMongoDbFixtureManager manages simple mongodb fixtures during tests
 *
 * A fixture represents a list of documents for a specific collection.
 * For a test method, using a fixture means that a the beginning of the method,
 * the collection has and only has the documents that are given in the fixture.
 * Therefore, the collection's state is predictable.
 *
 * A fixture is represented as a PHP script whose name (without suffix) is the
 * same as the collection name. The PHP script returns an array representing a list
 * of documents. Each row is an associative array of properties values indexed
 * by property names.
 *
 * A fixture can be associated with an init script which sits under the same fixture
 * directory and is named as "CollectionName.init.php". The init script is used to
 * initialize the collection before populating the fixture data into the collection.
 * If the init script does not exist, the collection will be emptied.
 *
 * Fixtures must be stored under the {@link basePath} directory. The directory
 * may contain a file named "init.php" which will be executed once to initialize
 * the database. If this file is not found, all available fixtures will be loaded
 * into the database.
 *
 * @author		Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright	2010-2011 Ibitux
 * @license		http://www.yiiframework.com/license/ BSD license
 * @category	tests
 * @package		ext.YiiMongoDbSuite.tests
 * @since		v1.3.6
 */
class EMongoDbFixtureManager extends CApplicationComponent
{
	/**
	 * @var string the name of the initialization script that would be executed before the whole test set runs.
	 * Defaults to 'init.php'. If the script does not exist, every collection with a fixture file will be reset.
	 */
	public $initScript='init.php';
	/**
	 * @var string the suffix for fixture initialization scripts.
	 * If a collection is associated with such a script whose name is CollectionName suffixed this property value,
	 * then the script will be executed each time before the table is reset.
	 */
	public $initScriptSuffix='.init.php';
	/**
	 * @var string the base path containing all fixtures. Defaults to null, meaning
	 * the path 'protected/tests/fixtures'.
	 */
	public $basePath;
	/**
	 * @var string the ID of the mongodb connection. Defaults to 'mongodb'.
	 * Note, data in this database may be deleted or modified during testing.
	 * Make sure you have a backup database.
	 */
	public $connectionID='mongodb';

	private $_mongoDb;
	private $_fixtures;
	private $_rows;				// fixture name, row alias => row
	private $_records;			// fixture name, row alias => record (or class name)
	private $_collectionList;	// list of collections available in database

	/**
	 * Initializes this application component.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.tests.fixtures');
		$this->prepare();
	}

	/**
	 * Returns the database connection used to load fixtures.
	 * @return MongoDb the database connection
	 */
	public function getDbConnection()
	{
		if($this->_mongoDb===null)
		{
			$this->_mongoDb=Yii::app()->getComponent($this->connectionID)->getDbInstance();
			if(!$this->_mongoDb instanceof MongoDB)
				throw new CException(Yii::t('yii','EMongoDbFixtureManager.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$this->connectionID)));
		}
		return $this->_mongoDb;
	}

	/**
	 * Prepares the fixtures for the whole test.
	 * This method is invoked in {@link init}. It executes the database init script
	 * if it exists. Otherwise, it will load all available fixtures.
	 */
	public function prepare()
	{
		$initFile=$this->basePath . DIRECTORY_SEPARATOR . $this->initScript;
		if(is_file($initFile))
			require($initFile);
		else
		{
			foreach($this->getFixtures() as $collectionName=>$fixturePath)
			{
				$this->resetCollection($collectionName);
				$this->loadFixture($collectionName);
			}
		}
	}

	/**
	 * Resets the collection to the state that it contains no fixture data.
	 * If there is an init script named "tests/fixtures/CollectionName.init.php",
	 * the script will be executed.
	 * Otherwise, {@link truncateCollection} will be invoked to delete all documents
	 * in the collection.
	 * @param string $collectionName the collection name
	 */
	public function resetCollection($collectionName)
	{
		$initFile=$this->basePath . DIRECTORY_SEPARATOR . $collectionName . $this->initScriptSuffix;
		if(is_file($initFile))
			require($initFile);
		else
			$this->truncateCollection($collectionName);
	}

	/**
	 * Loads the fixture for the specified collection.
	 * This method will insert documents given in the fixture into the corresponding collection.
	 * The loaded documents will be returned by this method.
	 * If the fixture does not exist, this method will return false.
	 * Note, you may want to call {@link resetCollection} before calling this method
	 * so that the collection is emptied first.
	 * @param string $collectionName collection name
	 * @return array the loaded fixture rows indexed by row aliases (if any).
	 * False is returned if the collection does not have a fixture.
	 */
	public function loadFixture($collectionName)
	{
		$fileName=$this->basePath.DIRECTORY_SEPARATOR.$collectionName.'.php';
		if(!is_file($fileName))
			return false;

		$rows=array();
		foreach(require($fileName) as $alias=>$row)
		{
			$this->getDbConnection()->{$collectionName}->save($row);
			$rows[$alias]=$row;
		}
		return $rows;
	}

	/**
	 * Check if requested collection exists
	 * @param string $collectionName collection name
	 * @return boolean
	 */
	protected function isCollection($collectionName) {
		if ($this->_collectionList === null) {
			$this->_collectionList = array();
			foreach($this->getDbConnection()->listCollections() as $collection) {
				$this->_collectionList[] = $collection->getName();
			}
		}
		return in_array($collectionName, $this->_collectionList);
	}
	/**
	 * Returns the information of the available fixtures.
	 * This method will search for all PHP files under {@link basePath}.
	 * If a file's name is the same as a collection name, it is considered to be the fixture data for that table.
	 * @return array the information of the available fixtures (collection name => fixture file)
	 */
	public function getFixtures()
	{
		if($this->_fixtures===null)
		{
			$this->_fixtures=array();
			$folder=opendir($this->basePath);
			$suffixLen=strlen($this->initScriptSuffix);
			while($file=readdir($folder))
			{
				if($file==='.' || $file==='..' || $file===$this->initScript)
					continue;
				$path=$this->basePath.DIRECTORY_SEPARATOR.$file;
				if(substr($file,-4)==='.php' && is_file($path) && substr($file,-$suffixLen)!==$this->initScriptSuffix)
				{
					$collectionName=substr($file,0,-4);
					if($this->isCollection($collectionName) === true)
					{
						$this->_fixtures[$collectionName]=$path;
					}
				}
			}
			closedir($folder);
		}
		return $this->_fixtures;
	}

	/**
	 * Removes all documents from the specified collection.
	 * @param string $collectionName the collection name
	 */
	public function truncateCollection($collectionName)
	{
		$this->getDbConnection()->{$collectionName}->remove(array());
	}

	/**
	 * Truncates all collections.
	 * @see truncateCollection
	 */
	public function truncateCollections()
	{
		foreach($this->getDbConnection()->listCollections() as $collection)
				$this->truncateCollection($collection->getName());
	}

	/**
	 * Loads the specified fixtures.
	 * For each fixture, the corresponding collection will be reset first by calling
	 * {@link resetCollection} and then be populated with the fixture data.
	 * The loaded fixture data may be later retrieved using {@link getRows}
	 * and {@link getRecord}.
	 * Note, if a collection does not have fixture data, {@link resetCollection} will still
	 * be called to reset the table.
	 * @param array $fixtures fixtures to be loaded. The array keys are fixture names,
	 * and the array values are either EMongoDocument class names or collection names.
	 * If collection names, they must begin with a colon character (e.g. 'Post'
	 * means an EMongoDocument class, while ':Post' means a collection name).
	 */
	public function load($fixtures)
	{
		$this->_rows=array();
		$this->_records=array();
		foreach($fixtures as $fixtureName=>$collectionName)
		{
			if($collectionName[0]===':')
			{
				$collectionName=substr($collectionName,1);
				unset($modelClass);
			}
			else
			{
				$modelClass=Yii::import($collectionName,true);
				$collectionName=EMongoDocument::model($modelClass)->getCollectionName();
			}
			$this->resetCollection($collectionName);
			$rows=$this->loadFixture($collectionName);
			if(is_array($rows) && is_string($fixtureName))
			{
				$this->_rows[$fixtureName]=$rows;
				if(isset($modelClass))
				{
					foreach(array_keys($rows) as $alias)
						$this->_records[$fixtureName][$alias]=$modelClass;
				}
			}
		}
	}

	/**
	 * Returns the fixture data documents.
	 * The documents will have updated primary key.
	 * @param string $name the fixture name
	 * @return array the fixture data documents. False is returned if there is no such fixture data.
	 */
	public function getRows($name)
	{
		if(isset($this->_rows[$name]))
			return $this->_rows[$name];
		else
			return false;
	}

	/**
	 * Returns the specified EMongoDocument instance in the fixture data.
	 * @param string $name the fixture name
	 * @param string $alias the alias for the fixture data document
	 * @return EMongoDocument the MongoDocument instance. False is returned
	 * if there is no such fixture document.
	 */
	public function getRecord($name,$alias)
	{
		if(isset($this->_records[$name][$alias]))
		{
			if(is_string($this->_records[$name][$alias]))
			{
				$row=$this->_rows[$name][$alias];
				$model=EMongoDocument::model($this->_records[$name][$alias]);
				$pk = $row['_id'];
				$this->_records[$name][$alias]=$model->findByPk($pk);
			}
			return $this->_records[$name][$alias];
		}
		else
			return false;
	}
}
