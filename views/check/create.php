<?php
/* @var $this CheckController */
/* @var $model Check */

$this->setTitle('Новый счёт');
?>


<?php $this->renderPartial('_form', array('model'=>$model, 'popular_friends'=>$popular_friends)); ?>