<?php
/* @var $this UsersController */

$this->setTitle('Настройки');
?>

<!--link type="text/css" href="/css/datepicker3.css" rel="stylesheet" /-->


<?php if($user->service==''):?>
    <div class="alert alert-warning">
        <h3>Привяжите социальную сеть для быстрого входа</h3>
        <?php Yii::app()->eauth->renderWidget(); ?>
    </div>
<?php endif;?>

<div class="row">
    <div class="col-md-3">
        <?php //$this->renderPartial('//avatar/index', array('user'=>$user)); ?>
        <div class="row">
            <div class="left">
                <div class="profile-avatar">
                    <?php echo $user->getAvatar(true,'_big'); ?>
                </div>
            </div>
            <div class="right">
                <a href="/user/pswd" class="btn">сменить пароль</a>
            </div>
        </div>

    </div>
    <div class="col-md-6 col-sm-9">
        <?php /** @var CActiveForm $form */
        $form = $this->beginWidget('CActiveForm', array(
            'id'=>'settings-form',
            'errorMessageCssClass'=>'text-danger',

            'htmlOptions'=>array(
                'class'=>'form-horizontal',
                'autocomplete'=>'off'
            )
        )); ?>
        <?php echo CHtml::errorSummary($user);?>
        <?php echo CHtml::errorSummary($pdform);?>
        <?php echo CHtml::errorSummary($profile_detail);?>
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Личное</h3>
            </div>
            <div class="panel-body">

                <div >




                    <div class="form-group <?=$user->email==''?'has-error':'';?>">
                        <?php echo $form->labelEx($user, 'email', array('class'=>'col-sm-3 control-label')); ?>
                        <div class="col-sm-9">
                            <?php if($user->email == '' || $user->email_confirmed==0):?>
                            <?php echo $form->textField($user, 'email', array('class'=>'form-control')); ?>
                            <?php else:?>
                            <?php echo $form->textField($user, 'email', array('class'=>'form-control','disabled'=>'true')); ?>
                            <?php endif;?>

                            <?php echo $form->error($user, 'email'); ?>
                        </div>
                    </div>




                    <div class="form-group">
                        <?php echo $form->labelEx($user, 'firstname', array('class'=>'control-label col-sm-3')); ?>
                        <div class="col-sm-9">
                            <?php echo $form->textField($user, 'firstname', array('class'=>'form-control')); ?>
                            <?php echo $form->error($user, 'firstname'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $form->labelEx($user, 'lastname', array('class'=>'control-label col-sm-3')); ?>
                        <div class="col-sm-9">
                            <?php echo $form->textField($user, 'lastname', array('class'=>'form-control')); ?>
                            <?php echo $form->error($user, 'lastname'); ?>
                        </div>
                    </div>

                    <div style="display: none">

                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'gender', array('class'=>'control-label col-sm-3')); ?>
                            <div class="col-sm-9">
                                <?php echo $form->radioButtonList($user, 'gender', $profile_detail->getGenderOptions(), array('class'=>'')); ?>
                                <?php echo $form->error($user, 'gender'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'birth', array('class'=>'control-label col-sm-3')); ?>
                            <div class="col-sm-9">
                                <div class="input-group ">
                                    <?php echo $form->textField($user, 'birth', array('class'=>'form-control ','id'=>'datepicker')); ?>
                                    <div class="input-group-addon "><i class="fa fa-calendar "></i></div>
                                </div>
                                <?php echo $form->error($user, 'birth'); ?>
                            </div>
                        </div>


                    </div>
                    <!--div class="form-group">
                        <?php echo $form->labelEx($pdform, 'about', array('class'=>'control-label col-sm-3')); ?>
                        <div class="col-sm-9">
                            <?php echo $form->textArea($pdform, 'about', array('class'=>'form-control','style'=>'height:100px;')); ?>
                            <?php echo $form->error($pdform, 'about'); ?>
                        </div>
                    </div-->

                </div>





                <div class="form-group buttons-row">
                    <div class="col-md-12">
                        <div class="pull-right">
                            <button type="submit" class="btn solid">Сохранить</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="dashed"></div>

        <div class="panel" style="display: none">

            <div class="panel-heading">
                <h3 class="panel-title">Дополнительная информация</h3>
            </div>
            <div class="panel-body">



                <!--div class="form-group">
                    <?php echo $form->labelEx($user, 'name', array('class'=>'col-sm-3 control-label')); ?>
                    <div class="col-sm-9">
                        <?php echo $form->textField($user, 'name', array('class'=>'form-control','placeholder'=>'имя в профиле')); ?>
                        <?php echo $form->error($user, 'name'); ?>
                    </div>
                </div-->

                <!--div class="form-group">
                    <?php echo $form->labelEx($user, 'username', array('class'=>'col-sm-3 control-label')); ?>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <div class="input-group-addon">numcheck.ru/</div>
                            <?php echo $form->textField($user, 'username', array('class'=>'form-control','placeholder'=>'короткий адрес')); ?>
                        </div>
                        <?php echo $form->error($user, 'username'); ?>
                    </div>
                </div-->

                <br />

                <div class="form-group">
                    <?php echo $form->labelEx($pdform, 'vk', array('class'=>'col-sm-3 control-label')); ?>
                    <div class="col-sm-9">
                        <?php echo $form->textField($pdform, 'vk', array('class'=>'form-control','placeholder'=>'ссылка на вк')); ?>
                        <?php echo $form->error($pdform, 'vk'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($pdform, 'twitter', array('class'=>'col-sm-3 control-label')); ?>
                    <div class="col-sm-9">
                        <?php echo $form->textField($pdform, 'twitter', array('class'=>'form-control','placeholder'=>'профиль в твиттер')); ?>
                        <?php echo $form->error($pdform, 'twitter'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($pdform, 'fb', array('class'=>'col-sm-3 control-label')); ?>
                    <div class="col-sm-9">
                        <?php echo $form->textField($pdform, 'fb', array('class'=>'form-control','placeholder'=>'страница в фэйсбуке')); ?>
                        <?php echo $form->error($pdform, 'fb'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($pdform, 'youtube', array('class'=>'col-sm-3 control-label')); ?>
                    <div class="col-sm-9">
                        <?php echo $form->textField($pdform, 'youtube', array('class'=>'form-control','placeholder'=>'канал на ютуб')); ?>
                        <?php echo $form->error($pdform, 'youtube'); ?>
                    </div>
                </div>

            </div>




            <div class="form-group">
                <div class="col-md-12">
                    <div class="pull-right">
                        <button type="submit" class="btn solid">Сохранить</button>
                    </div>
                </div>
            </div>

        <?php $this->endWidget(); ?>
    </div>

</div>





<?php /*


<script type="text/javascript" src="/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="/js/bootstrap-datepicker.ru.js"></script>
<!--script type="text/javascript" src="/js/bootstrap-select.js"></script>
<script type="text/javascript" src="/js/bootstrap-select-ru_RU.js"></script>
<script type="text/javascript" src="/js/switchery.min.js"></script-->

<script type="text/javascript">
   /* var elem = document.querySelector('.js-switch');
    var init = new Switchery(elem);
    elem.onchange = function() {
        if(elem.checked)
        {
            $('#has-w-state').text('Да');
        }
        else
        {
            $('#has-w-state').text('Нет');
        }
    };


    $(document).ready(function() {

        $('#datepicker').datepicker({
            format: 'dd.mm.yyyy',
            language: 'ru'
        });


        msa.init();

        //$('.selectpicker').selectpicker();
    });


</script>


*/ ?>