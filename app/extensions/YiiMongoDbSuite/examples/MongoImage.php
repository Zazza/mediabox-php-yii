<?php
/**
 * EMongoGridFS.php
 *
 * PHP version 5.2+
 *
 * @author		Jose Martinez <jmartinez@ibitux.com>
 * @author		Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright	2010 Ibitux
 * @license		http://www.yiiframework.com/license/ BSD license
 * @version		SVN: $Revision: $
 * @category	ext
 * @package		ext.YiiMongoDbSuite
 */

/**
 * EMongoGridFS
 *
 * Authorization management, dispatches actions and views on the system
 *
 * @author		Jose Martinez <jmartinez@ibitux.com>
 * @author		Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright	2010 Ibitux
 * @license		http://www.yiiframework.com/license/ BSD license
 * @version		SVN: $Revision: $
 * @category	ext
 * @package		ext.YiiMongoDbSuite
 *
 */
class MongoImage extends EMongoGridFS
{
	public $metadata;

	/**
	 * this is similar to the get tableName() method. this returns tha name of the
	 * document for this class. this should be in all lowercase.
	 */
	public function getCollectionName()
	{
		return 'images';
	}

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className class name
	 *
	 * @return CompaniesDb the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function rules()
	{
		return array(
			array('filename, metadata','safe'),
			array('filename','required'),
		);
	}
}