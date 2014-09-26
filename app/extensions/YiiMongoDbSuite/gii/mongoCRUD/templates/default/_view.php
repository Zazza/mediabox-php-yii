<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<div class="view">

<?php
echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$this->modelObject->primaryKey()}')); ?>:</b>\n";
echo "\t<?php echo CHtml::link(CHtml::encode(\$data->{$this->modelObject->primaryKey()}), array('view', 'id'=>\$data->{$this->modelObject->primaryKey()})); ?>\n\t<br />\n\n";
$count=0;
foreach($this->modelObject->attributeNames() as $name)
{
	if($name == $this->modelObject->primaryKey())
		continue;
	if(++$count==7)
		echo "\t<?php /*\n";
	echo "\t<b><?php echo CHtml::encode(\$data->getAttributeLabel('{$name}')); ?>:</b>\n";
	echo "\t<?php echo CHtml::encode(\$data->{$name}); ?>\n\t<br />\n\n";
}
if($count>=7)
	echo "\t*/ ?>\n";
?>

</div>