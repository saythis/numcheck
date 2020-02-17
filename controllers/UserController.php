<?php

class UserController extends Controller
{
    public $_model;

    public function accessRules()
    {
        return array(

            array('allow',
                'actions'=>array('login','recovery','logout','register','captcha', 'password','confirm'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array(
                    'settings',
                    'avatar',
                    'vote',
                    'pswd',
                    'follow',
                    'unfollow',
                    'bgimg',
                    'subscribes',
                    'invites',
                'view','top','favorite','confirmemail',
                    'friends',
                    'coming'

                ),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array('manage','update'),
                'users'=>array('@'),
                'expression'=>'Yii::app()->user->isAdmin()'
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + follow, unfollow, confirmemail', // we only allow deletion via POST request
            'ajaxOnly + follow, unfollow, confirmemail', // we only allow deletion via POST request
        );
    }

    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
            ),
        );
    }

    public function actionComing() {

        $criteria = new CDbCriteria;
        $criteria->addCondition('to_user = :user');
        $criteria->params[':user'] = Yii::app()->user->id;
        $criteria->order = 'id desc';

        $transactions = Transactions::model()->findAll($criteria);

        $this->render('coming',[
            'transactions'=>Transactions::toArray($transactions),
        ]);
    }

    public function actionInvites()
    {

        $this->setTitle('Инвайты');

        $invites = Invite::model()->findAll([
            'condition'=>'user_id = :uid',
            'params'=>[':uid'=>Yii::app()->user->id],
            'order'=>'created_at desc'
        ]);

        if(isset($_POST['new_invite']) && $_POST['new_invite']==1) {


            $inv = Invite::createNew();


            if($inv) {

                Yii::app()->user->setFlash('success','Приглашение добавлено');

            } else {

                Yii::app()->user->setFlash('danger','Вам пока не доступны приглашения');

            }

            $this->refresh();
        }



        $this->render('invites',['invites'=>$invites]);
    }

    public function actionLogin($service = false)
    {
        $this->layout = '//layouts/form';

        $model = new LoginForm;

        //$service = Yii::app()->request->getQuery('service');
        if ($service) {

            try {
                $authIdentity = Yii::app()->eauth->getIdentity($service);

                $authIdentity->redirectUrl = 'https://numcheck.ru/user/login/service/'.$service;//Yii::app()->user->returnUrl;
//            $authIdentity->redirectUrl = 'http://numcheck.ru/user/login/service/'.$service;//Yii::app()->user->returnUrl;
                $authIdentity->cancelUrl = $this->createAbsoluteUrl('/auth');

                if ($authIdentity->authenticate()) {
                    $identity = new ServiceUserIdentity($authIdentity);

                    // Успешный вход
                    if ($identity->authenticate()) {

                        $duration=3600*24*30; // 30 days


                        Yii::app()->user->login($identity, $duration);
                        // Специальный редирект с закрытием popup окна
                        //die('okay');
                        $url = Yii::app()->user->returnUrl;
                        if($identity->new_user) {
                            $url = '/';
                            Yii::app()->user->setFlash('warning','Добро пожаловать!');
                        }


                        if(isset(Yii::app()->request->cookies['invite'])) {
                            $invite_code = Yii::app()->request->cookies['invite']->value;
                            $invite = Invite::model()->findByAttributes(['code'=>$invite_code]);
                            if($invite
                                && $invite->user_id != Yii::app()->user->id
                                && $invite->is_activated == 0
                                //&& !($invite->is_activated == 1 && $invite->activated_by != Yii::app()->user->id)
                            ) {
                                $invite->activate(Yii::app()->user->id);

                                if($invite->connected_to > 0) {
                                    $authIdentity->redirect('/check/'.$invite->connected_to);
                                }

                            }
                        }


                        $authIdentity->redirect('/check/feed');
                    }
                    else {
                        // Закрываем popup окно и перенаправляем на cancelUrl
                        //die('error');
                        //$this->redirect('/auth');
                        //$authIdentity->cancel('/auth');

                        Yii::app()->user->setFlash('danger','Ваш профиль соц сети не привязан к профилю на numcheck. Используйте email и пароль для входа');

                    }
                }

            } catch (Exception $e) {
                Yii::app()->user->setFlash('danger', 'Ошибка авторизации через '.$service.': '.$e->getMessage());

            }





            // Что-то пошло не так, перенаправляем на страницу входа
            $this->redirect(array('/auth'));
        } else {

            if(!Yii::app()->user->isGuest) {
                $this->redirect('/check/feed');
            }
        }

        if(isset($_POST['LoginForm'])) {
            $model->attributes=$_POST['LoginForm'];
            if($model->validate() && $model->login())
            {
                if(Yii::app()->request->isAjaxRequest) {
                    $this->renderJson(['success'=>true]);
                }
                $this->redirect(Yii::app()->user->returnUrl);

            } else {
                if(Yii::app()->request->isAjaxRequest) {
                    $this->renderJson(['success'=>false,'errors'=>CHtml::errorSummary($model)]);
                }
            }
        }

        // display the login form
        $this->render('login',array('model'=>$model));



    }

    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect('/');
        //$this->redirect(Yii::app()->homeUrl);
    }

	public function actionRecovery()
	{
        $this->layout = '//layouts/form';

        $model = new RecoveryForm;

        $this->performAjaxValidation($model, 'recovery-form');

        if(isset($_POST['RecoveryForm'])){
            $model->attributes=$_POST['RecoveryForm'];
            if($model->validate()){
                $model->sendMail();

                Yii::app()->user->setFlash('success', 'Сообщение отправлено. Следуйте инструкциям в письме');


            }
        }

		$this->render('recovery', array(
            'model'=>$model
        ));
	}

    public function actionPassword($hash, $email)
    {
        $this->layout = '//layouts/form';

        $recovery = Recovery::model()->findByAttributes(array('hash'=>$hash));
        if($recovery===null)
            throw new CHttpException(404, 'Запрос не может быть обработан');

        $user = User::model()->findByPk($recovery->user_id);
        if($user===null || $user->email!==$email)
            throw new CHttpException(404, 'Запрос не может быть обработан');

        $model = new PasswordForm;

        $this->performAjaxValidation($model, 'password-form');

        if(isset($_POST['PasswordForm'])){
            $model->attributes = $_POST['PasswordForm'];
            $model->setUser($user);
            if($model->validate()){
                $model->savePassword();

                $recovery->delete();

                $this->redirect(array('/user/login'));
            }
        }

        $this->render('password', array(
            'model'=>$model
        ));
    }

	public function actionRegister($id=null)
	{

        if(!Yii::app()->user->isGuest) {

            if($id!=null) {
                $invite = Invite::model()->findByAttributes(['code'=>$id]);
                if($invite
                    && $invite->user_id != Yii::app()->user->id
                    && $invite->is_activated == 0
                    //&& !($invite->is_activated == 1 && $invite->activated_by != Yii::app()->user->id)
                ) {
                    $invite->activate(Yii::app()->user->id);

                    if($invite->connected_to > 0) {
                        $this->redirect('/check/'.$invite->connected_to);
                    }

                }

            }

            $this->redirect('/');

        }


        $this->layout = '//layouts/form';




        $model = new RegisterForm();

        $invite = false;

        if($id!=null) {
            //check invite
            $invite = Invite::model()->findByAttributes(['code'=>$id,'is_activated'=>0]);

            if($invite) {
                $model->invite = $invite->id;

                $cookie = new CHttpCookie('invite', $id);
                $cookie->expire = time()+60*60*24*10;
                Yii::app()->request->cookies['invite'] = $cookie;
                //Yii::app()->request->cookies['invite'] = new CHttpCookie('invite', $id);

            }
        }

        if(!$invite) {
            if(isset(Yii::app()->request->cookies['invite'])) {
                $id = Yii::app()->request->cookies['invite']->value;
                $invite = Invite::model()->findByAttributes(['code'=>$id,'is_activated'=>0]);
                if($invite) {
                    $model->invite = $invite->id;
                }
            }
        }

        if($invite) {
            $model->firstname = $invite->name;
        }


        $this->performAjaxValidation($model, 'register-form');

        if(isset($_POST['RegisterForm'])){
            $model->attributes=$_POST['RegisterForm'];


            $user = $model->createUser();
            if($user){

                $identity = new UserIdentity($_POST['RegisterForm']['email'],$_POST['RegisterForm']['password']);
                $identity->authenticate();

                if($identity->errorCode===UserIdentity::ERROR_NONE){
                    Yii::app()->user->login($identity,3600*24*30);

                    Yii::app()->user->setFlash('success','Добро пожаловать!');

                    try {
                        $user->sendConfirmEmail();
                    } catch(Exception $e) {
                        Yii::app()->user->setFlash('warning', 'Мы не смогли отправить вам письмо. Мы постараемся разобраться с проблем и пришлём его вам чуть позже.');
                    }

                    $this->redirect('/user/settings');



                    /*$message = new YiiMailMessage;
                    $url = 'http://wackyhyena.com/user/confirm/id/'.$model->id.'/code/'.md5($model->email.$model->id.'wh@@x'.$model->id);
                    $message->setBody('<h3>Завершение регистрации</h3><p>Пожалуйста, подтвердите вашу почту, пройдя по ссылке по <a href="'.$url.'">этой ссылке</a>', 'text/html');
                    $message->subject = 'Восстановление пароля на WackyHyena';
                    $message->view = 'welcome';
                    $message->addTo($model->email);
                    $message->setFrom(array('noreply@wackyhyena.com'=>'WackyHyena'));

                    Yii::app()->mail->send($message);*/

                } else
                    $this->redirect(array('user/login'));
            }
        }

        $this->render('register', array(

            'model'=>$model
        ));
	}

    public function actionPswd()
    {
        $this->layout='//layouts/main';
        $this->menu=$this->getMenu();

        $change_password=new ChangePasswordForm();
        if(isset($_POST['ChangePasswordForm']))
        {
            $change_password->attributes=$_POST['ChangePasswordForm'];
            if($change_password->save(true)){
                Yii::app()->user->setFlash('success', 'Пароль изменен');
                $this->refresh();
            }
        }

        $this->render('pswd',['change_password'=>$change_password]);
    }

	public function actionSettings()
	{
        $this->layout='//layouts/main';
        /** @var Users $model */
        $user = User::model()->findByPk(Yii::app()->user->id);//Yii::app()->user->getModel();

        //$attributes = array('new_password', 'new_password2');

        $model=$user->profile_detail;
        $profile_detail=$user->profile_detail;

        $pdform=new ProfileDetailForm();
        $pdform->init_with_pd($profile_detail);

        $errors = false;

        if(isset($_POST['ProfileDetail']) || isset($_POST['User']) || isset($_POST['ProfileDetailForm'])){

            /* PD */
            /*
            if(isset($_POST['ProfileDetail'])) {
                $model->attributes = $_POST['ProfileDetail'];

                $pd_attributes=array('gender','birth','city_id','username','email');

                if($model->validate($pd_attributes)){
                    /*if($model->new_password)
                        $model->password = CPasswordHelper::hashPassword($model->new_password);*/

                  //  if($model->save(true,$pd_attributes)){
                        //Yii::app()->user->setFlash('success', 'Настройки обновлены');
                        /*if(Yii::app()->request->isAjaxRequest) {
                            $this->renderJson(['success'=>true]);
                        }
                        $this->refresh();*/
                  /*  } else {
                        $errors.=CHtml::errorSummary($model);
                    }
                } else {
                    $errors.=CHtml::errorSummary($model);
                }
            }*/

            /* USER */

            if(isset($_POST['User']))
            {
                $save_attributes = array('username','name','birth','firstname','lastname','gender');
                if($user->email == '') {
                    $save_attributes[] = 'email';
                }

                $user->attributes=$_POST['User'];

                if($user->save(true,$save_attributes)) {

                } else {
                    $errors.=CHtml::errorSummary($model);
                }
            }

            if(isset($_POST['ProfileDetailForm']))
            {
                $pdform->attributes=$_POST['ProfileDetailForm'];

                $pdform->setProfileDetail($profile_detail);

                if($pdform->validate())
                {
                    $pdform->save();
                    //Yii::app()->user->setFlash('success', 'Данные сохранены');
                    //$this->refresh();
                } else {
                    $errors.=CHtml::errorSummary($model);
                }

            }
            /* end */
            if(Yii::app()->request->isAjaxRequest) {
                if(!$errors) {
                    $this->renderJson(['success'=>true]);
                } else {
                    $this->renderJson(['success'=>false,'errors'=>$errors]);
                }
            }

            if($errors===false) {
                Yii::app()->user->setFlash('success', 'Настройки обновлены');
                //$this->refresh();
            }

        }

        $this->menu=$this->getMenu();

        //$list=Company::getList();

		$this->render('settings', array(
            'profile_detail'=>$profile_detail,
            'user'=>$user,

            'pdform'=>$pdform,
        ));
	}


    protected function performAjaxValidation($model, $form)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']===$form)
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionView($id) {

        if($id == Yii::app()->user->id) {
            $this->redirect('/my');
        }

        $user = $this->loadModel($id);

        if(!$user->isFriend(Yii::app()->user->id)) {
            throw new CHttpException(404,'Такого пользователя не существует');
        }

        $profile = $user->profile;

        $this->_model = $user;

        $friend = $user->getFriendModel(Yii::app()->user->id);

        $transactions = Transactions::getFriendlyTransactions(Yii::app()->user->id, $id);

        $this->render('view',array(
            'user'=>$user,
            'profile'=>$profile,
            'friend'=>$friend,
            'transactions'=>Transactions::toArray($transactions),

        ));
    }

    public function actionView1($id)
    {

        $this->layout = '//layouts/_profile';

        $user = $this->loadModel($id);
        $profile = $user->profile;

        $this->_model = $user;

        //$dependecy = new CDbCacheDependency('SELECT MAX(id) FROM post');

        $posts = new CActiveDataProvider('Post',array(
        //$posts = new CActiveDataProvider(Post::model()->cache(30000, $dependecy, 2),array(
            'criteria'=>array(
                'condition'=>'t.user_id=:user_id and t.status>=:status',
                'order'=>'t.id desc',
                'params' => array(':user_id' => $user->id, ':status'=>Post::MODERATED),
                'with'=>['audio','cover','user','profile','_rubric'],
            ),
            'pagination'=>array(
                'pageSize'=>20,
            )
        ));




        $this->render('view',array(
            'user'=>$user,
            'posts'=>$posts,

            'profile'=>$profile
        ));
    }
    public function actionSubscribes($id)
    {

        $this->layout = '//layouts/_profile';

        $user = $this->loadModel($id);

        $this->_model = $user;

        $criteria = new CDbCriteria();
        $criteria->join='join subscribers ss on ss.user_id=t.id';
        $criteria->addCondition('ss.subscriber_id=:id');
        $criteria->params[':id']=$id;
        $users = User::model()->findAll($criteria);

        $criteria = new CDbCriteria();
        $criteria->join='join subscribers ss on ss.user_id=t.user_id';
        $criteria->addCondition('t.status>='.Post::MODERATED.' and ss.subscriber_id=:user_id');
        $criteria->params[':user_id']=$id;
        $criteria->order='id desc';
        $posts = Post::model()->findAll($criteria);


        $this->render('subscribers',array(
            'users'=>$users,
            'posts'=>$posts

        ));
    }
    public function actionFavorite($id)
    {

        $this->layout = '//layouts/_profile';

        $user = $this->loadModel($id);

        $this->_model = $user;

        $criteria = new CDbCriteria();
        $criteria->join='join post_fav ss on ss.post_id=t.id';
        $criteria->addCondition('ss.user_id=:id');
        $criteria->addCondition('ss.is_fav=1');
        $criteria->params[':id']=$id;
        //$posts = Post::model()->findAll($criteria);

        $posts=new CActiveDataProvider('Post',array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>10,
            )
        ));

        $this->render('view',array(
            'posts'=>$posts,
            'user'=>$user

        ));
    }

    public function actionFriends() {

        $user = Yii::app()->user->getModel();

        $users = $user->getAllFriends();

        $this->render('friends',[
            'users'=>$users
        ]);

    }

    public function actionAvatar()
    {

        $this->layout = '//layouts/main';

        $this->render('avatar',array(
            'model'=>Yii::app()->user->getModel()
        ));
    }

    public function actionVote()
    {
        if(!Yii::app()->request->isAjaxRequest) $this->renderResult(Err::BAD_PARAMS);

        //голосовать может только оплаченый
        if(!Yii::app()->user->isMember()) $this->renderResult(Err::BAD_ACCESS);

        if(!isset($_POST['profile_id'])||!isset($_POST['val'])) $this->renderResult(Err::BAD_PARAMS);

        $user = $this->loadModel($_POST['profile_id']);
        $success = $user->profile->vote(intval($_POST['val']));

        $data=array(
            'success'=>$success,
            'votes'=>$user->profile->rating,
            'val'=>$_POST['val']
        );

        $this->renderJson($data);

    }

    private function getMenu()
    {
        return array(
            array('label'=>'Личная информация', 'url'=>array('/user/settings'), ),
            array('label'=>'Смена пароля', 'url'=>array('/user/pswd'), ),
            array('label'=>'Аватар', 'url'=>array('/user/avatar'), ),
            //array('label'=>'Настройки профиля', 'url'=>array('/user/privacy'), ),
            //array('label'=>'Фотографии', 'url'=>array('/user/register'), ),
        );
    }


    private function loadModel($id)
    {
        if($this->_model===null)
        {
            if($id)
            {
                if(is_numeric($id))
                {
                    //$attributes=array('id'=>$id);
                    $this->_model=User::model()->with('profile')->findByPk($id);
                }
                else
                {
                    $attributes=array('username'=>$id);
                    $this->_model=User::model()->with('profile')->findByAttributes($attributes);
                }
            }
            if($this->_model===null)
                throw new CHttpException(404,'Такого пользователя не существует');
        }
        return $this->_model;
    }

    public function actionFollow()
    {
        if(!isset($_POST['user_id'])||!is_numeric($_POST['user_id'])) $this->renderJson(['success'=>false]);
        $user_id=$_POST['user_id'];
        $user = Yii::app()->user->getModel();
        $x = $user->subscribeTo($user_id);
        if($x) {
            $this->renderJson(['success'=>true,'subscribers'=>$x->subscribers_count]);
        } else {
            $this->renderJson(['success'=>false]);
        }
    }

    public function actionUnfollow()
    {
        if(!isset($_POST['user_id'])||!is_numeric($_POST['user_id'])) $this->renderJson(['success'=>false]);
        $user_id=$_POST['user_id'];
        $user = Yii::app()->user->getModel();
        $x = $user->unsubscribeFrom($user_id);
        if($x) {
            $this->renderJson(['success'=>true,'subscribers'=>$x->subscribers_count]);
        } else {
            $this->renderJson(['success'=>false]);
        }
    }

    public function actionTop()
    {
        $criteria = new CDbCriteria();
        $criteria->order='profile.posts_count DESC';
        $criteria->join='left join profile on profile.user_id=t.id';
        $criteria->condition='rating>0 and posts_count>0 and is_mc=1';
        $criteria->limit=100;

        $users = User::model()->findAll($criteria);

        $this->render('top',['users'=>$users]);
    }

    public function actionBgimg()
    {
        $img_id='file';
        if(isset($_FILES[$img_id]) && $_FILES[$img_id]['size']!=0 && $_FILES[$img_id]['error']=='0')
        {

            $img=EUploadedImage::getInstanceByName($img_id);
            if(!$img) {
                {$this->renderJson(['success'=>false,'error_text'=>'no file']);}
            }
            if($img->getHeight() > $img->getWidth()) {$this->renderJson(['success'=>false,'error_text'=>'Ширина кортинки должна быть больше высоты. Минимальный размер картинки должен быть 1170х315']);}
            if($img->getHeight() < 315 || $img->getWidth()<1170) {$this->renderJson(['success'=>false,'error_text'=>'Минимальный размер картинки должен быть 1170х315']);}

            $img->maxWidth=1170;
            $img->maxHeight=315;

            $new_name=md5($_FILES[$img_id]['tmp_name']).'.'.$img->getExtensionName();

            if(!is_dir('files/'.Yii::app()->user->id))
                mkdir('files/'.Yii::app()->user->id);

            if($img->saveAs('files/'.Yii::app()->user->id.'/'.$new_name))
            {
                $user = Yii::app()->user->getModel();
                //$user->bgimg = $new_name;
                $user->saveAttributes(['bgimg'=>$new_name]);
                $this->renderJson(['success'=>true,'data'=>'/files/'.$user->id.'/'.$new_name]);
            } else {
                $this->renderJson(['success'=>false,'error_text'=>'Не получилось сохранить']);
            }

        } else {
            $this->renderJson(['success'=>false,'error_text'=>'Нечего сохранять']);
        }


    }

    public function actionConfirm($id, $code)
    {


        $this->layout = '//layouts/empty';

        $user = User::model()->findByPk($id);
        if($user==null) $result = -1;
        else {
            if($user->email_confirmed==1) {
                $result=-2;
            } else {
                if(md5($user->email.$user->id.'wh@@x'.$user->id)==$code) {
                    $result=1;
                    $user->email_confirmed=1;
                    $user->saveAttributes(['email_confirmed'=>1]);
                    $this->refresh();
                } else {
                    $result=0;
                }
            }

        }
        $this->render('confirm',['result'=>$result]);
    }

    public function actionConfirmemail()
    {
        if(isset(Yii::app()->session['ec']) && Yii::app()->session['ec']==1) {
            $this->renderJson(['success'=>false,'error_text'=>'Вы уже запрашивали письмо, ищите на почте']);
        } else {
            Yii::app()->user->getModel()->sendConfirmEmail();
            Yii::app()->session['ec']=1;
            $this->renderJson(['success'=>true,'error_text'=>'Письмо отправлено, ищите на почте']);
        }
    }

}