<?php foreach ($users as $_user):?>
    <a href="/user/<?=$_user->id;?>">
        <?=$_user->firstname;?>
        <?=$_user->lastname;?>
    </a>
    <div class="dashed"></div>
<?php endforeach;?>
