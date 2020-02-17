<?php
/* @var $this UsersController */

$this->setTitle('Регистрация - сервис учета счетов и взаиморасчетов');
?>


<h2>Регистрация через соц. сети</h2>

<?php if(Yii::app()->user->isGuest): ?>
    <?php Yii::app()->eauth->renderWidget(); ?>
<?php else: ?>
    Вы уже авторизированы
<?php endif; ?>

<div class="dashed"></div>

<h2>
        Регистрация через почту
</h2>
<div id="login-box-inner">

    <?php
    /** @var CActiveForm $form */
    $form = $this->beginWidget('CActiveForm', array(
        'id'=>'register-form',
        'enableAjaxValidation'=>false,
        'enableClientValidation'=>false,
        'errorMessageCssClass'=>'text-danger',
        'clientOptions'=>array(
            'errorCssClass'=>'has-error'
        ),
        'focus'=>array($model,'name'),
    ));
    ?>


    <div class="form-group">
        <div class="input-group<?php if($model->hasErrors('firstname')): ?> has-error<?php endif; ?>">
            <span class="input-group-addon">И</span>
            <?php echo $form->textField($model,'firstname', array('class'=>'form-control','placeholder'=>'Имя')); ?>
        </div>
        <span class="error-item"><?php echo $form->error($model,'firstname'); ?></span>
    </div>

    <div class="form-group">
        <div class="input-group<?php if($model->hasErrors('lastname')): ?> has-error<?php endif; ?>">
            <span class="input-group-addon">Ф</span>
            <?php echo $form->textField($model,'lastname', array('class'=>'form-control','placeholder'=>'Фамилия')); ?>
        </div>
        <span class="error-item"><?php echo $form->error($model,'lastname'); ?></span>
    </div>


    <div class="form-group">
        <div class="input-group<?php if($model->hasErrors('email')): ?> has-error<?php endif; ?>">
            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
            <?php echo $form->textField($model,'email', array('class'=>'form-control','placeholder'=>'E-mail')); ?>
        </div>
        <span class="error-item"><?php echo $form->error($model,'email'); ?></span>
    </div>

    <div class="form-group">
        <div class="input-group<?php if($model->hasErrors('password')): ?> has-error<?php endif; ?>">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <?php echo $form->passwordField($model,'password', array('class'=>'form-control', 'placeholder'=>'Пароль')); ?>
        </div>
        <span class="error-item"><?php echo $form->error($model,'password'); ?></span>
    </div>

    <!--div class="form-group">
        <div class="input-group<?php if($model->hasErrors('password2')): ?> has-error<?php endif; ?>">
            <span class="input-group-addon"><i class="fa fa-unlock-alt"></i></span>
            <?php echo $form->passwordField($model,'password2', array('class'=>'form-control','placeholder'=>'Повторите пароль')); ?>
        </div>
        <span class="error-item"><?php echo $form->error($model,'password2'); ?></span>
    </div-->

    <?php if(false && CCaptcha::checkRequirements() && Yii::app()->user->isGuest):?>
        <?php echo CHtml::activeLabelEx($model, 'verifyCode')?><br />
        <?php $this->widget('CCaptcha')?><br />
        <?php echo CHtml::activeTextField($model, 'verifyCode')?><br />
    <?php endif?>
    <span class="error-item"><?php echo $form->error($model,'verifyCode'); ?></span>




    <?php /*if($model->referal): ?>
        <div class="finput-group">
            <?php //echo $form->labelEx($model,'referral_id'); ?>
            <p class="form-control-static"><?php //echo $model->referral->name; ?></p>
        </div>
    <?php endif;*/ ?>

    <div id="remember-me-wrapper" class="form-group">
        <div class="row">
            <div class="col-xs-12">
                <div class="checkbox-nice">
                    <?php $model->accept=1;?>
                    <?php echo $form->checkBox($model,'accept'); ?>
                    <?php echo $form->labelEx($model, 'accept'); ?>

                    <span class="error-item"><?php echo $form->error($model,'accept'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <span class="error-item"><?php echo $form->error($model,'referal'); ?></span>

    <div class="form-group">
        <div class="row">
            <div class="col-xs-12">
                <button type="submit" class="btn solid">Зарегистрироваться</button>
            </div>
        </div>
    </div>


    <?php $this->endWidget(); ?>


</div>

<div id="login-box-footer">
    <div class="row">
        <div class="col-xs-12">
            Уже есть аккаунт?
            <?php echo CHtml::link('Войти',array('user/login')); ?>
        </div>
    </div>
</div>
