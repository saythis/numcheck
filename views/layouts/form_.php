<?php
/**
 * @var $this Controller
 */
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <link rel="shortcut icon" href="/favicon.ico?0" />

    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />

    <title><?php echo $this->getTitle(); ?></title>


    <?php Yii::app()->clientScript->registerCoreScript('jquery');  ?>

    <?php Yii::app()->clientScript->registerCssFile('/css/bootstrap.min.css');  ?>
    <?php Yii::app()->clientScript->registerCssFile('/css/bootstrap-theme.css');  ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <?php Yii::app()->clientScript->registerCssFile('/css/up.css');  ?>

    <?php
    Yii::app()->clientScript->registerScriptFile('/js/bootstrap.min.js');
    Yii::app()->clientScript->registerScriptFile('/js/app.js');
    ?>


    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" rel="stylesheet" type="text/css">


</head>
<body class="login-page">


    <!-- Begin page content -->
    <div class="box">
        <div class="container-fluid">
            <?php echo $content; ?>
        </div>
    </div>


</body>

</html>
