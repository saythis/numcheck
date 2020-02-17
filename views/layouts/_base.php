<?php
/**
 * @var $this Controller
 */
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />


    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Cache-Control" content="max-age=3600, must-revalidate" />

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title><?php echo $this->getTitle(); ?></title>

    <?php if(!empty($this->meta)): ?>
        <meta name="description" content="<?php echo $this->meta[3]; ?>">
        <meta name="keywords" content="<?php echo $this->meta[4]; ?>">
        <meta property="og:type" content="<?php echo $this->meta[0]; ?>">
        <meta property="og:url" content="<?php echo $this->meta[1]; ?>">
        <meta property="og:title" content="<?php echo CHtml::encode($this->meta[2]); ?>">
        <meta property="og:description" content="<?php echo CHtml::encode($this->meta[3]); ?>">
        <meta property="og:image" content="<?php echo CHtml::encode($this->meta[5]); ?>">
    <?php else:?>
        <meta name="description" content="Устали записывать кто кому сколько не отдал? Учёт финансов среди друзей теперь в одном месте! Простой и удобный сервис для вашей тусовочки">
        <meta name="keywords" content="сервис учета финансов разделение счетов в ресторане">

        <meta property="og:url" content="https://numcheck.ru">
        <meta property="og:title" content="Сервис учета финансов и счетов NumCheck">
        <meta property="og:description" content="Устали записывать кто кому сколько не отдал? Учёт финансов среди друзей теперь в одном месте! Простой и удобный сервис для вашей тусовочки">
        <meta property="og:image" content="https://numcheck.ru/static/img/sharing.jpg?1">

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="" />
        <meta name="twitter:title" content="Сервис учета финансов и счетов NumCheck" />
        <meta name="twitter:description" content="Устали записывать кто кому сколько не отдал? Учёт финансов среди друзей теперь в одном месте! Простой и удобный сервис для вашей тусовочки" />
        <meta name="twitter:image:src" content="https://numcheck.ru/static/img/sharing.jpg?1" />
        <meta name="twitter:domain" content="https://numcheck.ru/" />

    <?php endif;?>



    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?1242">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?1242">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?1242">
    <link rel="manifest" href="/manifest.json?1241">
    <meta name="theme-color" content="#ffffff">

    <script type="text/javascript">
        var cfg = {

            csrf_token: '<?php echo Yii::app()->request->csrfToken;?>',
            is_auth: <?php echo intval(!Yii::app()->user->isGuest);?>,
            user_id: <?=Yii::app()->user->isGuest?0:Yii::app()->user->id;?>,
        };

    </script>


    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <link href="https://fonts.googleapis.com/css?family=Lora|Montserrat:400,700&amp;subset=cyrillic" rel="stylesheet">


    <script src="/static/js/vue.js"></script>
    <!--script type="text/javascript" src="https://unpkg.com/vue2-selectize@1.0.3"></script>
    <script type="text/javascript" src="https://unpkg.com/vue-the-mask@0.11.1/dist/vue-the-mask.js"></script-->

    <link rel="stylesheet" type="text/css" href="/static/css/normalize.css">
    <link rel="stylesheet" type="text/css" href="/static/css/hamburgers.css">
    <link rel="stylesheet" type="text/css" href="/static/css/main.css">




</head>
<body>

<?php if(!Yii::app()->user->isGuest):?>

<div class="hamburger-wrap">
    <div class="">
        <div class="nav-mobile"  >
            <button class="hamburger hamburger--collapse" type="button">

                <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                      </span>
            </button>
        </div>
    </div>
</div>

<?php endif;?>

<?php echo $content; ?>


<div class="overlay" style="display: none"></div>

<div class="loading-window" style="display: none">
    <div class="loader"></div>
</div>

<script src="/static/js/app.js"></script>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/solid.css" integrity="sha384-rdyFrfAIC05c5ph7BKz3l5NG5yEottvO/DQ0dCrwD8gzeQDjYBHNr1ucUpQuljos" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/brands.css" integrity="sha384-QT2Z8ljl3UupqMtQNmPyhSPO/d5qbrzWmFxJqmY7tqoTuT2YrQLEqzvVOP2cT5XW" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/fontawesome.css" integrity="sha384-u5J7JghGz0qUrmEsWzBQkfvc8nK3fUT7DCaQzNQ+q4oEXhGSx+P2OqjWsfIRB8QT" crossorigin="anonymous">


<script>
    $().ready(function(){
        $('.hamburger').on('click',function(){
            $('.hamburger').toggleClass('is-active');
            $('.menu').toggleClass('is-active');
        });
    });

</script>
</body>

</html>

