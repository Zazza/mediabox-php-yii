<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>
<section class="container">
    <div class="row">
        <div id="left-vertical" class="span3 blocked">
            <?php
            $this->beginWidget('zii.widgets.CPortlet', array(
                'title'=>'Operations',
            ));
            $this->widget('zii.widgets.CMenu', array(
                'items'=>$this->menu,
                'htmlOptions'=>array('class'=>'operations'),
            ));
            $this->endWidget();
            ?>
        </div>

        <div id="vertical" class="span9">
            <?php echo $content; ?>
        </div>
    </div>
</section>
<?php $this->endContent(); ?>