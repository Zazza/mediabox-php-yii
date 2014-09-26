<?php
/**
 * MongocrudGenerator.php
 *
 * PHP version 5.2+
 *
 * @author		Dariusz Górecki <darek.krk@gmail.com>
 * @author		Invenzzia Group, open-source division of CleverIT company http://www.invenzzia.org
 * @copyright	2010 CleverIT http://www.cleverit.com.pl
 * @license		http://www.yiiframework.com/license/ BSD license
 * @version		1.3
 * @category	ext
 * @package		ext.YiiMongoDbSuite
 *
 */

Yii::setPathOfAlias('mongoExtRoot', realpath(implode(DIRECTORY_SEPARATOR, array(
	dirname(__FILE__), '..', '..',
))));

/**
 * @since v1.3.4
 */
class MongoModelGenerator extends CCodeGenerator
{
	public $codeModel='mongoExtRoot.gii.mongoModel.MongoModelCode';
}