<?php
/* @var $this UsersController */

$this->setTitle('Сервис учета счетов и взаиморасчетов');
?>

<?php if(Yii::app()->user->hasFlash('danger')): ?>
    <div class="alert alert-danger">
        <?php echo Yii::app()->user->getFlash('danger'); ?>
    </div>
<?php endif; ?>

<h2>Вход через соц. сети</h2>

<?php if(Yii::app()->user->isGuest): ?>
    <?php Yii::app()->eauth->renderWidget(); ?>
<?php else: ?>
    Вы уже авторизированы
<?php endif; ?>

<div class="dashed"></div>

<h2>Вход через почту</h2>

<?php
/** @var CActiveForm $form */
$form = $this->beginWidget('CActiveForm', array(
    'id'=>'login-form',
    'enableAjaxValidation'=>false,
    'enableClientValidation'=>false,
    'errorMessageCssClass'=>'text-danger',
    'clientOptions'=>array(
        'errorCssClass'=>'has-error'
    ),
    'focus'=>array($model,'email'),
));
?>
<div class="form-group <?php if($model->hasErrors('email')): ?> has-error<?php endif; ?>">
    <div class="input-profile input-group">
        <?php //echo $form->textField($model,'email', array('class'=>'form-control','placeholder'=>'E-mail','type'=>'email')); ?>
        <input type="email" value="<?=$model->email;?>" name="LoginForm[email]" class="form-control" placeholder="E-mail"/>

    </div>
    <span class="error-item"><?php echo $form->error($model,'email'); ?></span>
</div>

<div class="form-group">
    <div class="input-lock input-group<?php if($model->hasErrors('password')): ?> has-error<?php endif; ?>">
        <?php echo $form->passwordField($model,'password', array('class'=>'form-control','placeholder'=>'Пароль')); ?>
    </div>
    <span class="error-item"><?php echo $form->error($model,'password'); ?></span>
</div>

<div class="form-group">
    <div class="row">
        <div class="col-xs-6">
            <div class="checkbox-nice">
                <?php echo $form->checkBox($model, 'rememberMe',array('id'=>'remember-me')); ?>
                <label for="remember-me">
                    Запомнить меня
                </label>
            </div>
        </div>

    </div>
</div>

<div class="form-group">
    <button class="btn solid">Войти в сервис</button>

</div>

<?php $this->endWidget(); ?>


<div class="text-right">
    <a href="/recovery" id="login-forget-link" class="col-xs-6">
        Забыли пароль?
    </a>
</div>

<div class="dashed"></div>

<div class="text-right">
    <?php echo CHtml::link('Зарегистрироваться',array('user/register')); ?>
</div>


<!--script type="text/javascript" src="https://vk.com/js/api/openapi.js?159"></script>
<script type="text/javascript">
    VK.init({apiId: API_ID});
</script-->

<!-- VK Widget -->
<!--div id="vk_auth"></div>
<script type="text/javascript">
    VK.Widgets.Auth("vk_auth", {"authUrl":"/user/login"});
</script-->