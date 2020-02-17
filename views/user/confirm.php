
<div class="box">




    <div id="login-box-inner">
        <h1>Подтверждение почты</h1>
        <?php switch($result) {
            case -2: {
                echo 'Спасибо, почта подтверждена. <a href="/feed">На главную</a>';
                break;
            }
            case -1: {
                echo 'Пользователь не найден';
                break;
            }
            case 0: {
                echo 'Не верный код активации';
                break;
            }
            case 1: {
                echo 'Спасибо, почта подтверждена. <a href="/feed">На главную</a>';
                break;
            }
        }
        ?>

    </div>
    <br />
    <br />
    <br />
    <br />
    <br />

    <?php if(Yii::app()->user->isGuest):?>

    <div id="login-box-footer">
        <div class="row">
            <div class="col-xs-12">
                Нет учетной записи?
                <a href="/reg">Зарегистрироваться</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                Забыли пароль?
                <a href="/recovery">Восстановить</a>
            </div>
        </div>
    </div>
    <?php endif;?>

</div>