<h2>Пригласительные коды</h2>
<div class="table-wrap">

    <table class="table table-striped">
        <tr>
            <td style="width: 150px">Дата создания</td>
            <td>Код</td>
            <td></td>
        </tr>
        <?php foreach($invites as $invite):?>
        <?php $user = $invite->getUser();?>
            <tr>
                <td>
                    <?php if($invite->is_activated==0):?>
                    <?php echo date('d.m.Y H:i',strtotime($invite->created_at));?>

                    <?php else:?>
                        <?php echo date('d.m.Y H:i',strtotime($invite->activated_at));?>

                    <?php endif;?>

                </td>

                <td>
                    <?php if($invite->is_activated==0):?>
                        <input class="form-control" type="text" value="https://numcheck.ru/reg/<?php echo $invite->code;?>" />
                    <?php else:?>
                        <a href="/user/<?=$user->id;?>"><?=$user->getName();?></a>
                    <?php endif;?>

                </td>
                <td>
                    <?php if($invite->connected_to > 0):?>
                        <a href="/check/<?=$invite->connected_to;?>">связан с чеком</a>
                    <?php endif;?>
                </td>
                <!--td><?php echo $invite->is_activated==1?'<div class="accepted"></div>':'<div class="em"></div>';?></td-->
            </tr>
        <?php endforeach;?>
    </table>
</div>

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'post-form',
    // Please note: When you enable ajax validation, make sure the corresponding
    // controller action is handling ajax validation correctly.
    // There is a call to performAjaxValidation() commented in generated controller code.
    // See class documentation of CActiveForm for details on this.
    'enableAjaxValidation'=>true,
)); ?>

<input type="hidden" name="new_invite" value="1">
<input type="submit" value="Создать новое приглашение" class="btn btn-success" />

<?php $this->endWidget();?>