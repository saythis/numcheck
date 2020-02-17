<div>
    <div class="mb-4">

        <a href="/new" class="btn solid btn-check">Создать счёт</a>



    </div>

</div>

<h2>Мои счета</h2>

<div class="dashed"></div>

<?php foreach ($checks as $_check):?>



<div class="transaction">




    <div class="transaction-title">
        <?=$_check->getName();?>
    </div>
    <div class="transaction-date"><?php echo $_check->getCreated();?></div>

    <div class="transaction-footer">
        <div class="check-sum">
            <?php echo round($_check->sum);?> ₽
        </div>
        <div>
            <a href="/check/<?=$_check->id;?>">открыть чек</a> /
            <a href="/check/update/id/<?=$_check->id;?>">редачить</a>

        </div>
    </div>



    <div class="transaction-status">
        <?php echo $_check->getTotalMembers();?> человека
    </div>

    <div class="transaction-sum">
        <div class="text-right">
            <div><?=round($_check->sum);?> ₽</div>
            <div class="small">
                <div>Подтвердили на: <span class="green"><?=$_check->countPayed();?></span> ₽</div>
                <div>Ваш расход по чеку:   <span class="red"><?=$_check->sum - $_check->countPayed();?></span> ₽</div>
            </div>
        </div>

    </div>






    <!--div class="profiles-list">
        <?php foreach ($_check->getMembers() as $user):?>
            <div class="profile-compact">
                <div class="profile-img">
                    <?=$user->getAvatarAuto();?>
                </div>
                <div class="profile-name">
                    <?=$user->firstname;?>
                    <?=$user->lastname;?>
                </div>
            </div>

        <?php endforeach;;?>
    </div -->
</div>



<div class="dashed"></div>

<?php endforeach;?>
