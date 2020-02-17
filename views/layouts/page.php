<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/_base'); ?>

    <style>
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
        }
        header {
            margin-bottom: 100px;
        }
        header:after {
            clear: both;
            content: '';
            display: block;
        }
        header .head {
            float: left;
        }
        header .head-menu {
            float: right;
            color:#fff;
        }
        header .head-menu a {
            color:#fff;
            text-decoration: none;
            border-bottom: 1px #fff solid;
        }
        .first-slide {
            background-color: #000;
            color:#fff;
            padding: 50px;
            background-image: url("/static/img/group-2.jpg");
            background-size: cover;

        }
        .body {
            padding-top: 0;
        }

        .mb-5 {
            margin-bottom: 50px;
        }
        h2, h2 a  {
            letter-spacing: 3px;
            color: #71b3ff;
            font-weight: bold;
            text-transform: uppercase;
        }

        p {
            line-height: 30px;
        }

        .oc {
            background-image: url("/static/img/bc.png");
            width: 250px;
            height: 250px;
            background-size: 100%;
            padding: 0px 0;
            line-height: 250px;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
            color:#fff;
            text-decoration: none;
            display: inline-block;
        }
        .oc:hover {
            background-image: url("/static/img/oc@2x.png");

        }

        .row:after {
            clear: both;
            content: '';
            display: block;
        }
        .row>.col {
            float: left;
            width: 50%;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
    <div class="full-dashed "></div>
<div class="body">
<div class="first-slide">

    <div class="container">

        <header>
            <div class="head">
                <a href="/" class=""><img src="/static/img/logo-white@2x.png" width="250px" /></a>
            </div>

            <div class="head-menu">
<?php if(!Yii::app()->user->isGuest):?>
                <a href="/check/feed">вход</a>
<?php else:?>
    <a href="/auth">вход</a> /
    <a href="/reg">регистрация</a>
<?php endif;?>
            </div>
        </header>





        <div class="row">
            <div class="col">

                <h1>Бесплатный сервис взаиморасчетов</h1>
                <p>Дели расходы с друзьями
                    в путешествиях</p>

                <p><br /></p>
                <p><br /></p>
                <p><br /></p>
                <p>↓ подробнее о сервисе</p>
                <?php //echo $content; ?>
            </div>
            <div class="col">

                <div class="text-right">

                    <?php if(!Yii::app()->user->isGuest):?>
                        <a href="/check/feed" class="oc">
                            Вход
                        </a>
                    <?php else:?>
                        <a href="/auth" class="oc">
                            Вход
                        </a>
                    <?php endif;?>

                </div>
            </div>
        </div>


    </div>

</div>


    <div style="padding: 100px 0">
        <div class="container">


            <div style="max-width: 700px">
                <div class="mb-5">
                    <h2>Для чего нужен numcheck?</h2>
                    <p>Ведите бухгалетрию со своими друзьями, считайте кто кому должен и чья очередь платить.</p>
                </div>

                <div class="mb-5">
                    <h2>Как пользоваться?</h2>
                    <p>1. Регистрируетесь в сервисе

                    <p>2. Идете с друзьями в бар/ресторан/ куда угодно /  или вместе едите в отпуск

                    <p>3. Когда вы плтатите — создаете в сервисе счет на сумму и отмечаете друзей. Если ваш друг не зарегистрирован в сервисе — создайте ссылку и отправьте ему, когда он зарегистриуется — он автоматически прикрепится к счету.

                    <p>4. Сервис разделил счет на ваших друзей! Вы также можете просто указать сумму счета, а друзья сами запишут свою часть, которую должны вам!

                    <p>5. Готово! Теперь у вас есть баланс относительно каждого вашего друга!
                </div>

                <div class="mb-5 text-center">
                    <h2>
                        <a href="/auth">вход</a> /
                        <a href="/reg">регистрация</a>
                    </h2>
                </div>
            </div>

        </div>
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

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(54443410, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/54443410" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->

<?php $this->endContent(); ?>