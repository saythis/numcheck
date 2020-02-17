<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle='Ошибка '.$code;
/*$this->breadcrumbs=array(
	'Error',
);*/
?>

<h2>Perperam <?php echo $code; ?></h2>

<div class="error">
<?php //echo CHtml::encode($message); ?>
    <a href="/" class="btn solid">usque ad summitatem</a>
</div>