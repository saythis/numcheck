<?php /* @var $this Controller */ ?>
<?php
$unconfirmedChecks = 0;
if(!Yii::app()->user->isGuest)
    $unconfirmedChecks = Yii::app()->user->getModel()->countUnconfirmedIncomingChecks();
?>

<?php $this->beginContent('//layouts/_base'); ?>

    <div class="full-dashed"></div>


    <?php if($unconfirmedChecks>0):?>
        <div class="ingoing-circle-fixed"><?=$unconfirmedChecks?></div>
    <?php endif;?>

    <div class="page">


        <div class="menu">
            <?php if(!Yii::app()->user->isGuest):?>


            <div class="ingoing">
                <a href="/coming">Входящие
                    <?php if($unconfirmedChecks>0):?>
                        <span class="ingoing-circle"><?=$unconfirmedChecks?></span>
                    <?php endif;?>
                </a>
            </div>
            <?php endif;?>

        <?php $this->widget('zii.widgets.CMenu', array(
        'items'=>$this->getPublicMenu(),
        'htmlOptions'=>['class'=>'list-inline list-unstyled']
        )); ?>
        </div>

        <div class="wrapper">

            <div class="head">
                <a href="/check/feed" class="logo">numcheck.ru</a>
            </div>


            <div class="body">


                <?php if(Yii::app()->user->hasFlash('danger')): ?>
                    <div class="alert alert-danger">
                        <?php echo Yii::app()->user->getFlash('danger'); ?>
                    </div>
                <?php endif; ?>
                <?php if(Yii::app()->user->hasFlash('success')): ?>
                    <div class="alert alert-success">
                        <?php echo Yii::app()->user->getFlash('success'); ?>
                    </div>
                <?php endif; ?>
                <?php if(Yii::app()->user->hasFlash('warning')): ?>
                    <div class="alert alert-warning">
                        <?php echo Yii::app()->user->getFlash('warning'); ?>
                    </div>
                <?php endif; ?>

                <?php if(Yii::app()->user->getModel()->email_confirmed!=1 && Yii::app()->user->getModel()->email!='' && Yii::app()->user->getModel()->identity == ''):?>
                    <div class="alert alert-warning" id="confirmemail-alert">
                        <div class="mb-2">
                            Пожалуйста, подвердите ваш email.<br />
                            Если письмо вам не пришло, проверьте спам или нажмите кнопку
                        </div>
                        <button onclick="confirmemail(); return false;" class="btn btn-warning">запросить повторно</button>
                    </div>
                <?php endif;?>

                <?php if(Yii::app()->user->getModel()->email=='' && Yii::app()->user->getModel()->identity == ''):?>
                    <div class="alert alert-warning">
                        <div class="">
                            Пожалуйста, <a href="/user/settings">укажите ваш email</a>
                        </div>
                    </div>
                <?php endif;?>

                <?php echo $content; ?>
            </div>


        </div>
    </div>

<script>
    function confirmemail() {
        siteLoading.enable();
        $.post('/user/confirmemail',{NC_CSRF_TOKEN: cfg.csrf_token,},function(response){
            $('#confirmemail-alert').text(response.error_text)
            siteLoading.disable();
        });
    }
</script>


<?php $this->endContent(); ?>