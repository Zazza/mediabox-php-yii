<?php
/**
 * EMongoDocumentBehavior.php
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
class EMongoDocumentBehavior extends CActiveRecordBehavior
{
	public function events()
	{
		return array_merge(parent::events(), array(
			'onBeforeEmbeddedDocsInit'=>'beforeEmbeddedDocsInit',
			'onAfterEmbeddedDocsInit'=>'afterEmbeddedDocsInit',
			'onBeforeToArray'=>'beforeToArray',
			'onAfterToArray'=>'afterToArray'
		));
	}

	public function beforeEmbeddedDocsInit($event){}
	public function afterEmbeddedDocsInit($event){}
	public function beforeToArray($event){}
	public function afterToArray($event){}
}