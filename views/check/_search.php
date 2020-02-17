<?php
/* @var $this CheckController */
/* @var $model Check */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id',['size'=>11,'maxlength'=>11,'class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'owner_id'); ?>
		<?php echo $form->textField($model,'owner_id',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'created_at'); ?>
		<?php echo $form->textField($model,'created_at',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'check_at'); ?>
		<?php echo $form->textField($model,'check_at',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'members'); ?>
		<?php echo $form->textField($model,'members',['size'=>60,'maxlength'=>511,'class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'title'); ?>
		<?php echo $form->textField($model,'title',['size'=>60,'maxlength'=>255,'class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_deleted'); ?>
		<?php echo $form->textField($model,'is_deleted',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'is_closed'); ?>
		<?php echo $form->textField($model,'is_closed',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'type'); ?>
		<?php echo $form->textField($model,'type',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'status'); ?>
		<?php echo $form->textField($model,'status',['class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'sum'); ?>
		<?php echo $form->textField($model,'sum',['size'=>11,'maxlength'=>11,'class'=>'form-control']); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'currency'); ?>
		<?php echo $form->textField($model,'currency',['class'=>'form-control']); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->