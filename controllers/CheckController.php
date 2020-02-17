<?php

class CheckController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/main';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(

			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update','index','view','feed','my','delete'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{

        $model = Check::model()->findByAttributes([
            'id'=>$id,
            'is_deleted'=>0
        ]);

        if($model===null || !$model->canView(Yii::app()->user->id))
            throw new CHttpException(404,'The requested page does not exist.');


        if($model->owner_id != Yii::app()->user->id) {
            $transactions = $model->getUserTransaction(Yii::app()->user->id);
        } else {
            $transactions = $model->transactions;
        }

        $transactions = Transactions::toArray($transactions);
        $this->render('view',array(
            'check'=>$model->to_array(),
            'transactions'=>$transactions,
            'subs'=>$model->subs,
            'model'=>$model
        ));

	}

	public function actionMy()
	{
	    $checks = Check::model()->findAll([
	        'condition'=>'owner_id = :id and is_deleted = 0',
            'params'=>[':id'=>Yii::app()->user->id],
            'order'=>'check_at desc, created_at desc'
        ]);

		$this->render('my',array(
			'checks'=>$checks
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Check;

		$model->currency = 1;
		$model->type = Check::TYPE_SHARE;
        $model->owner_id = Yii::app()->user->id;


		// Uncomment the following line if AJAX validation is needed

        $user = Yii::app()->user->getModel();
        $popular_friends = $user->getPopularFriends();
        $popular_friends = User::toArray($popular_friends);

        $popular_friends = array_merge(
            [$user->me('Я')]
            , $popular_friends);



		$this->render('create',array(
			'model'=>$model,
            'popular_friends' => $popular_friends
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
        $model = Check::model()->findByAttributes([
            'id'=>$id,
            'owner_id'=>Yii::app()->user->id,
            'is_deleted'=>0
        ]);

        $user = Yii::app()->user->getModel();
        $popular_friends = $user->getPopularFriends();
        $popular_friends = User::toArray($popular_friends);

        $popular_friends = array_merge(
            [$user->me('Я')]
            , $popular_friends);

        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');

        if(!$model->canUpdate()) {
            $this->redirect(['check/view','id'=>$model->id]);
        }

		$this->render('update',array(
			'model'=>$model,
            'popular_friends' => $popular_friends

        ));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$model = Check::model()->findByAttributes([
		    'id'=>$id,
            'owner_id'=>Yii::app()->user->id,
            'is_deleted'=>0
        ]);

		if(!$model) {
            throw new CHttpException(404,'The requested page does not exist.');
        }

        $model->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{/*
		$dataProvider=new CActiveDataProvider('Check');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));*/
	}

	/**
	 * Lists all models.
	 */
	public function actionFeed()
	{
		$user = Yii::app()->user->getModel();

		$overall_balance = $user->getOverallBalance();

        $users = $user->getAllFriends();
        $users = User::toArray($users,['withFriend'=>Yii::app()->user->id]);

		$this->render('index', [
		    'user'=>$user,
		    'profile'=>$user->profile,
            'friends'=>$users,
            'overall_balance'=>$overall_balance
        ]);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{/*
		$model=new Check('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Check']))
			$model->attributes=$_GET['Check'];

		$this->render('admin',array(
			'model'=>$model,
		));*/
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Check the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Check::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Check $model the model to be validated
	 */
	protected function performAjaxValidation($model, $form = '')
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='check-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
