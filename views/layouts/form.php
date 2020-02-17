<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/_base'); ?>
    <div class="full-dashed login-page"></div>

    <div class="wrapper login-page">

        <div class="head">
            <a href="/" class="logo">numcheck.ru</a>
        </div>


        <?php if(Yii::app()->user->hasFlash('danger')): ?>
            <div class="alert alert-warning">
                <?php echo Yii::app()->user->getFlash('danger'); ?>
            </div>
        <?php endif; ?>

        <div class="body">
            <?php echo $content; ?>
        </div>


    </div>

    <noindex>
        <div style="float:left; padding: 15px;font-size: 12px">
            <a href="/info/policy">Политика обработки персональных данных</a> |
            <a href="/info/agreement">Пользовательское соглашение</a>

        </div>
    </noindex>

    <div style="float:right;padding: 15px;text-align: center; font-size: 12px">
        powered by <a href="http://randywendy.ru">randywendy.ru</a>
    </div>

<?php $this->endContent(); ?>