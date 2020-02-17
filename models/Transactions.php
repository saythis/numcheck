<?php

/**
 * This is the model class for table "transactions".
 *
 * The followings are the available columns in table 'transactions':
 * @property string $id
 * @property string $sum
 * @property string $balance
 * @property string $created_at
 * @property string $confirmed_at
 * @property string $proceed_at
 * @property integer $status
 * @property integer $from_user
 * @property integer $to_user
 * @property integer $connected_to
 * @property string $comment
 */
class Transactions extends CActiveRecord
{

    const STATUS_NEW = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = -1;
    const STATUS_CANCELED = 2;
    const STATUS_DONE = 3;

    public function getStatusLabel() {
        $status = [
            self::STATUS_NEW => 'ожидает',
            self::STATUS_ACCEPTED => 'подтвержден',
            self::STATUS_DECLINED => 'отклонен',
            self::STATUS_CANCELED => 'отменен',
            self::STATUS_DONE => 'завершен',
        ];
        return $status[$this->status];
    }

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'transactions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, from_user, to_user, connected_to', 'numerical', 'integerOnly'=>true),
			array('sum, balance', 'length', 'max'=>11),
			array('comment', 'length', 'max'=>511),
			array('created_at, confirmed_at, proceed_at', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, sum, balance, created_at, confirmed_at, proceed_at, status, from_user, to_user, connected_to, comment', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'check'=>array(self::BELONGS_TO,'Check','connected_to'),
            'from'=>array(self::BELONGS_TO,'User','from_user'),
            'to'=>array(self::BELONGS_TO,'User','to_user'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'sum' => 'Sum',
			'balance' => 'Balance',
			'created_at' => 'Created At',
			'confirmed_at' => 'Confirmed At',
			'proceed_at' => 'Proceed At',
			'status' => 'Status',
			'from_user' => 'From User',
			'to_user' => 'To User',
			'connected_to' => 'Connected To',
			'comment' => 'Comment',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('sum',$this->sum,true);
		$criteria->compare('balance',$this->balance,true);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('confirmed_at',$this->confirmed_at,true);
		$criteria->compare('proceed_at',$this->proceed_at,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('from_user',$this->from_user);
		$criteria->compare('to_user',$this->to_user);
		$criteria->compare('connected_to',$this->connected_to);
		$criteria->compare('comment',$this->comment,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Transactions the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function getFriendlyTransactions($user1, $user2) {

	    $tr = Transactions::model()->findAll([
	        'condition'=>'(from_user = :user1 and to_user = :user2) or (from_user = :user2 and to_user = :user1)',
            'order'=>'id desc',
            'params'=>[':user1'=>$user1, ':user2'=>$user2]
        ]);

	    return $tr;

    }

    public function getCreated() {
	    return date('d.m.Y H:i', strtotime($this->created_at));
    }

    public static function createBuyback($from, $to, $sum) {

	    if($sum <= 0) return false;

	    $transaction = new Transactions();
	    $transaction->from_user = $from;
	    $transaction->to_user = $to;
	    $transaction->status = 0;
	    $transaction->connected_to = -Yii::app()->user->id;
	    $transaction->sum = $sum;
	    $transaction->created_at = new CDbExpression('NOW()');
	    $transaction->save();

        return $transaction;

    }

    public function accept($sum = 0) {
        $sum = floor($sum);
        if($sum == 0) $sum = $this->sum;

        Yii::app()->db->createCommand("LOCK TABLES transactions WRITE, friends WRITE")->execute();

        $transaction = Yii::app()->db->beginTransaction();

        try {

            $tr = Transactions::model()->findByPk($this->id);
            if($tr->status == Transactions::STATUS_ACCEPTED) {
                throw new Exception('Уже начисляли');
            }

            $tr->sum = $sum;

            $friend = Friends::getFriendModel($this->from_user, $this->to_user);

            if($friend->user1 == $this->from_user) {
                $friend->balance -= $sum;
                $tr->balance = $friend->balance;
            } else {
                $friend->balance += $sum;
                $tr->balance = -$friend->balance;
            }
            $friend->last_balance_change = new CDbExpression('NOW()');
            $friend->save();

            $tr->confirmed_at = new CDbExpression('NOW()');
            $tr->status = Transactions::STATUS_ACCEPTED;
            $tr->save();

            $transaction->commit();
            Yii::app()->db->createCommand("UNLOCK TABLES")->execute();

            if($this->connected_to>0) {
                $check = Check::model()->findByPk($this->connected_to);
                if($check->is_closed != 1) {
                    $check->is_closed = 1;
                    $check->save(false,['is_closed']);
                }
            }


            return $tr;

        } catch(Exception $e) {

            $transaction->rollback();
            Yii::app()->db->createCommand("UNLOCK TABLES")->execute();
            return false;
        }
    }

    public function decline() {

	    if($this->status == self::STATUS_ACCEPTED) return false;
	    if($this->status == self::STATUS_DECLINED) return false;

	    $this->status = self::STATUS_DECLINED;
	    return $this->save(false,['status']);
    }

    public static function toArray($transactions) {

	    $transactionsArray = [];

	    if(!is_array($transactions)) {

	        $transactions = [$transactions];
        }

	    foreach ($transactions as $_transaction) {
            if($_transaction->from_user == Yii::app()->user->id) {
                $from = $_transaction->from;
                $to = $_transaction->to;
            } else {
                $from = $_transaction->to;
                $to = $_transaction->from;
            }

            $item = [
                'from_user'=>$_transaction->from_user,
                'to_user'=>$_transaction->to_user,
                'connected_to'=>$_transaction->connected_to,
                'direction'=>$_transaction->from_user == Yii::app()->user->id,
                'created'=>$_transaction->getCreated(),
                'id'=>$_transaction->id,
                'status'=>$_transaction->status,
                'status_label'=>$_transaction->getStatusLabel(),
                'sum'=>round($_transaction->sum),

            ];

            //if($_transaction->connected_to < 0) {

                $_user_data = [

                    'to'=>[
                        'firstname'=>$to->firstname,
                        'lastname'=>$to->lastname,
                        'avatar_html'=>$to->hasAvatar()?$to->getAvatar():$to->getCavatar(),
                        'id'=>$to->id
                    ],
                    'from'=>[
                        'firstname'=>$from->firstname,
                        'lastname'=>$from->lastname,
                        'avatar_html'=>$from->hasAvatar()?$from->getAvatar():$from->getCavatar(),
                        'id'=>$from->id
                    ],
                ];

                $item = array_merge($item, $_user_data);
            //} else {
            if($_transaction->connected_to > 0) {
                $item['check'] = [
                    'sum'=>round($_transaction->check->sum),
                    'name'=>$_transaction->check->getName(),
                    'comment'=>htmlspecialchars($_transaction->check->comment)
                ];
            }

            $transactionsArray[] = $item;
        }

        return $transactionsArray;

    }
}
