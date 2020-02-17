<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/_base'); ?>

    <div class="full-dashed"></div>

    <div class="page">

        <div class="menu">
            <?php if(!Yii::app()->user->isGuest):?>

                <?php $unconfirmedChecks = Yii::app()->user->getModel()->countUnconfirmedIncomingChecks();?>

                <div class="ingoing">
                    <a href="/coming">Входящие
                        <?php if($unconfirmedChecks>0):?>
                            <span><?=$unconfirmedChecks?></span>
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
                <a href="/" class="logo">numcheck.ru</a>
            </div>


            <div class="body">




                <?php echo $content; ?>
            </div>


        </div>
    </div>


<?php $this->endContent(); ?>