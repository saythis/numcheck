<?php
/* @var $this CheckController */
/* @var $model Check */

$this->setTitle($model->getName());
?>

<h1>Изменение <?php echo $model->getName(); ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model, 'popular_friends'=>$popular_friends)); ?>