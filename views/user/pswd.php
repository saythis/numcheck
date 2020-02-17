<?php
/* @var $this UsersController */

$this->setTitle('Настройки');
?>


<div class="row">




    <div class="col-lg-6 col-lg-offset-3">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Смена пароля</h3>
            </div>
            <div class="panel-body">

                <?php /** @var CActiveForm $form */
                $form = $this->beginWidget('CActiveForm', array(
                    'id'=>'settings-form',
                    'errorMessageCssClass'=>'text-danger',

                    'htmlOptions'=>array(
                        'class'=>'form-horizontal',
                        'autocomplete'=>'off'
                    )
                )); ?>

                <div class="form-group">
                    <?php echo $form->labelEx($change_password, 'password', array('class'=>'col-sm-4 control-label')); ?>
                    <div class="col-sm-8">
                        <?php echo $form->passwordField($change_password, 'password', array('class'=>'form-control')); ?>
                        <?php echo $form->error($change_password, 'password'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($change_password, 'password2', array('class'=>'col-sm-4 control-label')); ?>
                    <div class="col-sm-8">
                        <?php echo $form->passwordField($change_password, 'password2', array('class'=>'form-control')); ?>
                        <?php echo $form->error($change_password, 'password2'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-8">
                        <button type="submit" class="btn btn-success">Сохранить</button>
                    </div>
                </div>

                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>


</div>



