<?php

class ApiController extends Controller
{

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('login','view','dummy'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array(
                    'settings',
                    'vote',
                    'get__friends',
                    'get__invite',
                    'create__check',
                    'create__buyback',
                    'save__checkConfirmation',
                    'save__transactionConfirmation',
                    'test',

                ),
                'users'=>array('@'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function filters() {
        return array(
            'accessControl',
            'ajaxOnly + save__transactionConfirmation, get__friends, get__invite, create__check, create__buyback, save__checkConfirmation', // we only allow deletion via POST request
            'postOnly + save__transactionConfirmation, get__friends, get__invite, create__check, create__buyback, save__checkConfirmation', // we only allow deletion via POST request
            //array('StartFilter - login, register, recovery, logout, settings, captcha')
        );
    }

    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
            ),
        );
    }

    public function actionDummy() {
        echo Yii::app()->request->csrfToken;
    }

    public function actionCreate__buyback() {

        if(!isset($_POST['user_id']) || !isset($_POST['sum']) || !is_numeric($_POST['user_id']) || !is_numeric($_POST['sum'])) {
            $this->renderResult(Err::BAD_PARAMS);
        }

        if($_POST['user_id'] == Yii::app()->user->id) {
            $this->renderResult(Err::BAD_PARAMS);
        }

        $friend = Friends::getFriendModel(intval($_POST['user_id']), Yii::app()->user->id);

        if(!$friend) {
            $this->renderResult(Err::BAD_ACCESS);
        }

        if(round($friend->balance) == 0) $this->renderResult(Err::NO_MONEY);

        $sum = round($_POST['sum']);

        $user = intval($_POST['user_id']);
        $from = 0;
        $to = 0;

        if($friend->balance < 0) {
            if($friend->user1 == Yii::app()->user->id) {
                $from = $friend->user2;
                $to = $friend->user1;
            } else {
                $from = $friend->user2;
                $to = $friend->user1;
            }
        } else {
            if($friend->user1 == Yii::app()->user->id) {
                $from = $friend->user1;
                $to = $friend->user2;
            } else {
                $from = $friend->user1;
                $to = $friend->user2;
            }
        }

        $tr = Transactions::createBuyback($from, $to, $sum);
        if(!$tr->hasErrors()) {
            if(Yii::app()->user->id == $to) {
                $tr->accept();
            }
        }

        $this->renderJson([
            'success'=>!$tr->hasErrors(),
            'transaction'=>[
                'id'=>$tr->id,
                'sum'=>$tr->sum,
                'status'=>$tr->status,
            ],
            'errors'=>$tr->getErrors(),
        ]);

    }

    public function actionSave__checkConfirmation() {

        if(!isset($_POST['check_id']) || !isset($_POST['status']) || ($_POST['status'] == Transactions::STATUS_ACCEPTED&&!isset($_POST['sum']) || !is_numeric($_POST['sum']))) {
            $this->renderResult(Err::BAD_PARAMS);
        }

        $model = Check::model()->findByAttributes([
            'id'=>intval($_POST['check_id']),
            'is_deleted'=>0
        ]);

        if($model===null || !$model->canView(Yii::app()->user->id)) {
            $this->renderResult(Err::BAD_ACCESS);
        }

        $transaction = $model->getUserTransaction(Yii::app()->user->id);

        if(!$transaction) {
            $this->renderResult(Err::UNDEFINED_ERROR);
        }

        $transaction_details = [];

        if($_POST['status'] == Transactions::STATUS_ACCEPTED) {
            $sum = intval($_POST['sum']);



            if($sum <= 0) {
                $this->renderJson(['success'=>false,'errors'=>['sum'=>'Сумма должна быть положительной']]);
            }

            if($sum > $model->sum) {
                $this->renderJson(['success'=>false,'errors'=>['sum'=>'Сумма вашей части в рамках этого чека не может превышать его сумму']]);
            }
            if($sum > 10000000) {
                $this->renderJson(['success'=>false,'errors'=>['sum'=>'Под такие суммы наш сервис не предусмотрен']]);
            }

            $result = $transaction->accept($sum);

            if($result) {
                $transaction_details = [
                    'id'=>$result->id,
                    'status'=>$result->status,
                    'sum'=>$result->sum,
                ];
            }
        }
        if($_POST['status'] == Transactions::STATUS_DECLINED) {
            $result = $transaction->decline();
        }

        $this->renderJson(['success'=>$result != false,'errors'=>$transaction->getErrors(),'transaction'=>$transaction_details]);

    }

    public function actionSave__transactionConfirmation() {

        if(!isset($_POST['transaction_id']) || !isset($_POST['status'])) {
            $this->renderResult(Err::BAD_PARAMS);
        }

        $transaction = Transactions::model()->findByPk(intval($_POST['transaction_id']));

        if(!$transaction) {
            $this->renderResult(Err::UNDEFINED_ERROR);
        }

        if($transaction->connected_to > 0 || ($transaction->from_user != Yii::app()->user->id && $transaction->to_user != Yii::app()->user->id)) {
            $this->renderResult(Err::BAD_ACCESS);
        }


        $transaction_details = [];

        if($_POST['status'] == Transactions::STATUS_ACCEPTED) {

            $result = $transaction->accept();

            if($result) {
                $transaction_details = [
                    'id'=>$result->id,
                    'status'=>$result->status,
                    'status_label'=>$result->getStatusLabel(),
                    'sum'=>$result->sum,
                ];
            }
        }
        if($_POST['status'] == Transactions::STATUS_DECLINED) {
            $result = $transaction->decline();
        }

        $this->renderJson(['success'=>$result != false,'errors'=>$transaction->getErrors(),'transaction'=>$transaction_details]);

    }

    public function actionCreate__check() {

        $model = new Check;

        $isNewRecord = true;

        if(isset($_POST['Check'])) {

            if(is_numeric($_POST['Check']['id']) && $_POST['Check']['id']) {
                $model = Check::model()->findByAttributes([
                    'id'=>$_POST['Check']['id'],
                    'owner_id'=>Yii::app()->user->id,
                    'is_deleted'=>0
                ]);

                if(!$model) {
                    $this->renderResult(Err::BAD_ACCESS);
                }

                if(!$model->canUpdate()) {
                    $this->renderResult(Err::BAD_ACCESS);
                }

                $isNewRecord = false;
            }

            $model->attributes=$_POST['Check'];

            if($isNewRecord) {
                $attributes = ['comment','members','owner_id','created_at','check_at','title','sum','status','currency','members_by_link','type','members_amount','subs'];
            } else {
                $attributes = ['title','comment'];
            }

            if($model->save(true,$attributes)) {

                if($isNewRecord) {
                    if($model->createTransactions()) {
                        $this->renderJson(['success'=>true,'check'=>['id'=>$model->id]]);
                    } else {
                        $this->renderResult(Err::UNDEFINED_ERROR);
                    }
                } else {
                    $this->renderJson(['success'=>true,'check'=>['id'=>$model->id]]);

                }



            } else {
                $this->renderJson(['success'=>false, 'errors'=>$model->getErrors()]);
            }

        } else {
            $this->renderResult(Err::BAD_PARAMS);
        }
    }

    public function actionGet__friends() {


        if(isset($_POST['name'])) {
            $friends = Friends::searchMy($_POST['name']);

            if(isset($_POST['fullinfo'])) {
                $friends = User::toArray($friends, ['withFriend'=>Yii::app()->user->id]);
            } else {
                $friends = User::toArray($friends);
            }

        } else {
            $friends = [];
        }

        //$popular = Transactions::model()->findAll(['condition'=>'user1 = :id or user2']);

        $this->renderJson(['success'=>true,'friends'=>$friends]);
    }

    public function actionGet__invite() {

        if(isset($_POST['name'])) $name = htmlspecialchars(strip_tags($_POST['name']));
        else $name = '';

        $invite = Invite::getEmptyInvite($name);

        if($invite == false) {
            $this->renderJson(['success'=>false, 'invite'=>[],'errors'=>['limit'=>'limit reached']]);

        }

        if($invite->hasErrors()) {
            $this->renderJson(['success'=>false, 'invite'=>[],'errors'=>$invite->getErrors()]);
        } else {
            $this->renderJson(['success'=>true,'invite'=>['id'=>$invite->id,'code'=>$invite->code,'name'=>$invite->name]]);
        }


    }

	public function actionLogin()
	{
        if(!Yii::app()->user->isGuest)
        {
            $this->renderJson(array('success'=>false,'error_code'=>'-1'));
        }

        $model = new LoginForm;
        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login())
            {
                $this->renderJson(array('success'=>true,'error_code'=>'0'));
            }
        }

        $this->renderJson(array('success'=>false,'error_code'=>'-2'));
	}

    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect('/');
        //$this->redirect(Yii::app()->homeUrl);
    }

	public function actionSettings()
	{
        $this->layout='//layouts/main';
        /** @var Users $model */
        $user = Yii::app()->user->getModel();

        //$attributes = array('new_password', 'new_password2');

        $model=$user->profile_detail;

        if(isset($_POST['ProfileDetail'])){
            $model->attributes = $_POST['ProfileDetail'];

            $pd_attributes=array('gender','birth','city_id','about');

            if($model->validate($pd_attributes)){
                /*if($model->new_password)
                    $model->password = CPasswordHelper::hashPassword($model->new_password);*/

                if($model->save(true,$pd_attributes)){
                    Yii::app()->user->setFlash('success', 'Настройки обновлены');

                    $this->refresh();
                }
            }
        }

        $change_password=new ChangePasswordForm();
        if(isset($_POST['ChangePasswordForm']))
        {
            $change_password->attributes=$_POST['ChangePasswordForm'];
            if($change_password->save(true)){
                Yii::app()->user->setFlash('success', 'Пароль изменен');
                $this->refresh();
            }
        }

        $profile_detail=$user->profile_detail;
        $profile=$user->profile;


        $this->menu=$this->getMenu();

        $pdform=new ProfileDetailForm();
        $pdform->init_with_pd($profile_detail);

        if(isset($_POST['ProfileDetailForm']))
        {
            $pdform->attributes=$_POST['ProfileDetailForm'];

            $pdform->setProfileDetail($profile_detail);

            if($pdform->validate())
            {
                $pdform->save();
                Yii::app()->user->setFlash('success', 'Данные сохранены');
                $this->refresh();
            }

        }

        //$list=Company::getList();

		$this->render('settings', array(
            'profile_detail'=>$profile_detail,
            'profile'=>$profile,
            'user'=>$user,
            'change_password'=>$change_password,
            'pdform'=>$pdform,
        ));
	}

    public function actionView($id)
    {

        $this->layout = '//layouts/main';

        $user = $this->loadModel($id);
        $profile=$user->profile;

        $posts = new CActiveDataProvider('Post',array(
            'criteria'=>array(
                'condition'=>'user_id=:user_id',
                'order'=>'id desc',
                'params' => array(':user_id' => $user->id),
            ),
            'pagination'=>array(
                'pageSize'=>5,
            )
        ));

        //user contacts
        $contacts = null;

        if(!Yii::app()->user->isGuest&&(
                $user->id==Yii::app()->user->id
            ||  ($user->profile->referal==Yii::app()->user->id)
            ||  (Yii::app()->user->getModel()->profile->referal==$user->id && Yii::app()->user->isPayed()))
        )
        {
            $contacts = $user->profile_contact;
        }


        $busyness=$user->profile_detail->busyness;

        $this->render('view',array(
            'profile'=>$profile,
            'user'=>$user,
            'posts'=>$posts,
            'contacts'=>$contacts,
            'busyness'=>$busyness
        ));
    }

    public function actionVote()
    {
        if(!Yii::app()->request->isAjaxRequest) $this->renderResult(Err::BAD_PARAMS);

        //голосовать может только оплаченый
        if(!Yii::app()->user->isMember()) $this->renderResult(Err::BAD_ACCESS);

        if(!isset($_POST['profile_id'])||!isset($_POST['val'])) $this->renderResult(Err::BAD_PARAMS);

        $user = $this->loadModel($_POST['profile_id']);
        $success = $user->profile->vote($_POST['val']);

        $data=array(
            'success'=>$success,
            'votes'=>$user->profile->rating,
        );

        $this->renderJson($data);

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
                    $this->_model=User::model()->findByPk($id);
                }
                else
                {
                    $attributes=array('username'=>$id);
                    $this->_model=User::model()->findByAttributes($attributes);
                }
            }
            if($this->_model===null)
                throw new CHttpException(404,'Такого пользователя не существует');
        }
        return $this->_model;
    }
}