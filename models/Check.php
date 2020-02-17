<?php

/**
 * This is the model class for table "check".
 *
 * The followings are the available columns in table 'check':
 * @property string $id
 * @property integer $owner_id
 * @property string $created_at
 * @property string $check_at
 * @property string $members
 * @property string $members_by_link
 * @property string $title
 * @property integer $is_deleted
 * @property integer $is_closed
 * @property integer $type
 * @property integer $status
 * @property string $sum
 * @property integer $currency
 */
class Check extends CActiveRecord
{

    const TYPE_SHARE = 1;
    const TYPE_SELF = 2;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'check';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
		    ['sum, type, owner_id','required'],

			array('owner_id, is_deleted, is_closed, type, status, currency, members_amount', 'numerical', 'integerOnly'=>true),
			array('members, members_by_link', 'length', 'max'=>511),
			array('title', 'length', 'max'=>255),
			array('sum', 'length', 'max'=>11),

            array('title', 'match', 'pattern' => '/^[A-zА-яЁё\s\d]+$/u', 'message' => 'Заголовок может содержать только русские и английские буквы'),


            array('currency', 'numerical', 'integerOnly' => true, 'min' => 1, 'max'=>3, 'message'=>'Укажите валюту', 'tooBig'=>'Укажите валюту', 'tooSmall'=>'Укажите валюту'),
            array('type', 'numerical', 'integerOnly' => true, 'min' => 1, 'max'=>2, 'message'=>'Укажите тип', 'tooBig'=>'Укажите тип', 'tooSmall'=>'Укажите тип'),
            array('status', 'numerical', 'integerOnly' => true, 'min' => 0, 'max'=>5),

            array('members, members_by_link', 'match', 'pattern'=>'/^[0-9,\s]+$/', 'message'=>'Tags can only contain num characters.','allowEmpty'=>true),

			array('created_at, check_at, subs, comment', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, owner_id, created_at, check_at, members, title, is_deleted, is_closed, type, status, sum, currency', 'safe', 'on'=>'search'),
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
            'transactions'=>array(self::HAS_MANY,'Transactions','connected_to'),
            'owner' => [self::BELONGS_TO,'User','owner_id'],
            'subs' => [self::HAS_MANY, 'CheckSub', 'check_id'],
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'owner_id' => 'Owner',
			'created_at' => 'Created At',
			'check_at' => 'Check At',
			'members' => 'Участники',
			'members_by_link' => 'Участники',
			'title' => 'Title',
			'is_deleted' => 'Is Deleted',
			'is_closed' => 'Is Closed',
			'type' => 'Type',
			'status' => 'Status',
			'sum' => 'Сумма',
			'currency' => 'Валюта',
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
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('check_at',$this->check_at,true);
		$criteria->compare('members',$this->members,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('is_deleted',$this->is_deleted);
		$criteria->compare('is_closed',$this->is_closed);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
		$criteria->compare('sum',$this->sum,true);
		$criteria->compare('currency',$this->currency);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Check the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function beforeSave() {

	    $this->members_amount = $this->getTotalMembers();

	    return parent::beforeSave();
    }

	public function beforeValidate() {

	    if($this->isNewRecord) {
	        $this->owner_id = Yii::app()->user->id;
	        $this->created_at = date('Y-m-d H:i:s');
	        $this->status = 0;
	        $this->is_deleted = 0;
	        $this->is_closed = 0;
        }

        $this->sum = intval($this->sum);
	    if($this->sum <= 0) {
            $this->addError('sum','Сумма должна быть положительной');
        }

        if($this->sum > 100000000) {
            $this->addError('sum','Под такие суммы наш сервис не предусмотрен =(');
        }


        if(!is_array($this->members) && $this->members != '') { $this->members = explode(',', $this->members); }

        if(is_array($this->members)) {
            $members = $this->members;
            /*if (($key = array_search($this->owner_id, $members)) !== false) {
                unset($members[$key]);
            }*/

	        $this->members = implode(',', $members);
        }

        if(is_array($this->members_by_link)) {
            $this->members_by_link = implode(',', $this->members_by_link);
        }


        $members_links_array = [];
	    $members_links_activated = [];

        if($this->members_by_link != '') {
            if(!preg_match('/^[0-9,]+$/', $this->members_by_link))
                $this->addError('members_by_link','wrong symbols');
            else {
                $members = explode(',', $this->members_by_link);
                $members = array_unique($members);

                if(count($members) <= 10 && !empty($members)) {

                    $criteria = new CDbCriteria();
                    $criteria->select = 'id';
                    $criteria->addCondition('user_id = :user_id');
                    //$criteria->addCondition('is_activated = 0');
                    $criteria->addInCondition('id', $members);
                        //'condition'=>'user_id = :user_id and id in (:id) and is_activated = 0',
                        //'params'=>[':id'=> implode(',',$members), ':user_id' => Yii::app()->user->id]
                    $criteria->params[':user_id'] = Yii::app()->user->id;

                    $links = Invite::model()->findAll($criteria);
                    $members = [];
                    foreach ($links as $_user) {
                        if($_user->is_activated == 0)
                            $members[] = $_user->id;
                        else
                            $members_links_activated[] = $_user->activated_by;
                    }
                    $this->members_by_link = implode(',',$members);

                    $members_links_array = $members;
                } else {
                    $this->members_by_link = '';//слишком много людей не надо нам
                }
            }
        }




        $members_users_array = [];

        if($this->members != '') {
            if(!preg_match('/^[0-9,]+$/', $this->members))
                $this->addError('members','wrong symbols');
            else {
                $members = explode(',', $this->members);

                $members = array_merge($members, $members_links_activated);

                $members = array_unique($members);

                if(count($members) <= 10 && !empty($members)) {

                    /*$criteria = new CDbCriteria();
                    $criteria->select = 'id';
                    $criteria->addInCondition('id', $members);

                    $users = User::model()->findAll($criteria);

                    $members = [];
                    foreach ($users as $_user) {
                        $members[] = $_user->id;
                    }*/

                    $criteria = new CDbCriteria();
                    $criteria->addInCondition('user1', $members);
                    $criteria->addCondition('user2 = :user2');
                    $criteria->params[':user2'] = $this->owner_id;

                    $criteria2 = new CDbCriteria();
                    $criteria2->addInCondition('user2', $members);
                    $criteria2->addCondition('user1 = :user1');
                    $criteria2->params[':user1'] = $this->owner_id;

                    $criteria->mergeWith($criteria2, 'OR');

                    $friends = Friends::model()->findAll($criteria);

                    $members = Friends::parseFriendsIds($friends, $this->owner_id, ['withMe'=>in_array($this->owner_id, $members)]);
                    $members = array_unique($members);


                    $this->members = implode(',',$members);
                    $members_users_array = $members;
                } else {
                    $this->members = '';//слишком много людей не надо нам
                    //$this->addError('members','Добавьте в счёт людей');
                }

            }
        }



        if($this->members_by_link == '' && $this->members == '') $this->addError('members','Добавьте в счёт людей');


	    if($this->type == self::TYPE_SHARE && !empty($this->subs) && is_array($this->subs) && count($this->subs) < 10) {

	        $new_subs = [];

	        //all_sum
            $all_sum = 0;

	        foreach ($this->subs as $_sub) {
                if(!isset($_sub['user']) && !isset($_sub['link']) ) continue;
                if(empty($_sub['user']) && empty($_sub['link'])) continue;


                if(!isset($_sub['sum']) || !is_numeric($_sub['sum']) || $_sub['sum'] <= 0) continue;

                $title = '';

                if(isset($_sub['title']) && $_sub['title'] != ''){
                    $_sub['title'] = mb_substr($_sub['title'],0,40);
                    $title = mb_ereg_replace('/[^a-zA-Zа-яА-ЯёЁ\s\d_-]/', '', $_sub['title']);
                }

                $item = [
                    'sum'=>$_sub['sum'],
                    'user'=>[],
                    'link'=>[],
                    'title'=>$title,
                ];

                if(!empty($_sub['user'])) {
                    if($this->checkArrayInArray($_sub['user'],$members_users_array)) {
                        $item['user'] = $_sub['user'];
                    } else {
                        $this->addError('subs','Некорректные пользователи в вычетах');
                        continue;
                    }
                }

                if(!empty($_sub['link'])) {
                    if($this->checkArrayInArray($_sub['link'],$members_links_array)) {
                        $item['link'] = $_sub['link'];
                    } else {
                        $this->addError('subs','Некорректные пригласительные ссылки в вычетах');
                        continue;
                    }
                }


                $all_sum += $_sub['sum'];
                $new_subs[] = $item;

            }

            $this->subs = '';

            if(empty($new_subs)) {
                //$this->addError('subs','Проблема с вычетами.');
            } elseif($all_sum >= $this->sum) {
                $this->addError('subs','Сумма вычетов не может превышать сумму чека');
            } else {

	            $this->subs = json_encode($new_subs);
            }

        } else {
            $this->subs = '';
        }

        //$this->addError('subs','стоп'.$this->subs);

	    return parent::beforeValidate();
    }

    public function getSubs() {

	    if(empty($this->subs)) return [];
	    return json_decode($this->subs, true);
    }

    public function getSubsJson() {

	    if($this->subs == '' || empty($this->subs)) return json_encode([]);
	    return $this->subs;
    }

    public function checkArrayInArray($users1, $users2) {

	    foreach ($users1 as $_u1) {
	        //if($_u1 == $this->owner_id) continue;
	        if(!in_array($_u1, $users2)) return false;
        }

	    return true;
    }

    public function afterSave() {

	    if($this->members_by_link != '') {
            $links = Invite::model()->findAll([
                'select'=>'id',
                'condition'=>'user_id = :user_id and id in (:id) and is_activated = 0',
                'params'=>[':id'=> $this->members_by_link,':user_id'=>$this->owner_id]
            ]);

            foreach ($links as $_link) {
                $_link->connected_to = $this->id;
                $_link->save(false, ['connected_to']);
            }
        }



	    return parent::afterSave();
    }

    public function moveMember($invite) {

	    $invite_id = $invite->id;
	    $user_id = $invite->activated_by;

	    if($this->members_by_link == '') return false;

        $members_by_link = explode(',', $this->members_by_link);
        $new_list = [];



        foreach ($members_by_link as $_member) {
            if($_member == $invite_id) {

                $sum = $this->getSeparatedSum($invite_id);

                if($this->isUserInCheck($user_id)) {
                    $tr = $this->getUserTransaction($user_id);
                    $tr->sum += $sum;
                    $tr->comment = 'Объединение по пригласительной ссылке';
                    $tr->save();
                } else {
                    $tr = new Transactions();
                    $tr->sum = $sum;
                    $tr->created_at = new CDbExpression('NOW()');
                    $tr->status = Transactions::STATUS_NEW;
                    $tr->from_user = $this->owner_id;
                    $tr->to_user = $user_id;
                    $tr->connected_to = $this->id;
                    $tr->save();
                }



            } else {
                $new_list[] = $_member;
            }
        }


        //сначала убрать из вычетов
        if($this->type == self::TYPE_SHARE && $this->subs != '') {
            $subs = $this->getSubs();
            foreach ($subs as $k => $_sub) {
                $index = array_search($invite_id, $_sub['link']);
                if($index !== false) {
                    unset($subs[$k]['link'][$index]);
                    if(!in_array($user_id, $_sub['user'])) {
                        $subs[$k]['user'][] = $user_id;
                    }
                }
            }
            $this->subs = json_encode($subs);
        }

        //подвинем из ликнов в юзеры
        $new_list = implode(',', $new_list);
        if($new_list != $this->members_by_link) {
            $this->members_by_link = $new_list;

            if($this->members != '')
                $members = explode(',', $this->members);
            else $members = [];

            $members[] = $user_id;
            $this->members = implode(',', $members);

            $this->saveAttributes(['members_by_link','members','subs']);
        }
    }

    public function getTotalMembers() {

	    if($this->members != '')
            $members = explode(',', $this->members);
	    else $members = [];

	    if($this->members_by_link != '')
            $members_by_link = explode(',', $this->members_by_link);
	    else $members_by_link = [];

        $total_members = (count($members) + count($members_by_link));

        return $total_members;
    }

    public function getSeparatedSum($user_id) {

	    $total_members = $this->getTotalMembers();

        if($this->type == self::TYPE_SHARE)
            return $this->getPreSum($user_id);

        return $this->sum / $total_members;
    }

    public function getPreSum($user_id) {

        $total_members = $this->getTotalMembers();

        //сумма вычетов
        $sub_sum = 0;

        $subs = $this->getSubs();

        foreach ($subs as $index => $item) {

            if($item['sum'] != '' && $item['sum']>0)
                $sub_sum += $item['sum'];

        }

        $base_sum = ($this->sum - $sub_sum) / $total_members;

        //группируем по людям
        $result_sum = $base_sum + $this->getAsubsum($user_id);

        return $result_sum;

    }
    public function getAsubsum($user_id) {

	    if($this->subs == '') return 0;

	    $sum = 0;
	    $total_members = $this->getTotalMembers();

        $subs = $this->getSubs();

	    foreach ($subs as $index => $item) {

	        $subs_members = count($item['link']) + count($item['user']);

            $user_found = false;

            foreach ($item['user'] as $_index => $_id) {

                if($_id == $user_id) {
                    $user_found = true;
                }
            }

            if($user_found == false) {
                if($item['sum'] != '' && $item['sum']>0)
                    $sum += $item['sum'] / ($total_members - $subs_members);
            }
        }

        return $sum;
    }

    public function getUserTransaction($user_id) {

	    return Transactions::model()->findByAttributes(['to_user'=>$user_id,'connected_to'=>$this->id]);

    }

    public function updateTransactions() {

        if($this->members == '') return false;

        $members = explode(',', $this->members);

        if(!empty($members)) {

        }
    }

    //runs once
    public function createTransactions() {
        //посчитаем сколько с кого
        if($this->members == '') return true;

        $members = explode(',', $this->members);



        //$members_by_link = explode(',', $this->members_by_link);
        //$total_members = (count($members) + count($members_by_link)) + 1;
        $total_members = $this->getTotalMembers();

        if(!empty($members)) {


            //check existing
            $existing = Transactions::model()->findAll([
                'condition'=>'connected_to = :id and status >= 0',
                'params'=>[':id'=>$this->id]
            ]);
            if(count($existing) > 0) {
                //Yii::app()->db->createCommand("UNLOCK TABLES")->execute();
                return false;
            }

            Yii::app()->db->createCommand("LOCK TABLES transactions WRITE")->execute();


            $transaction = Yii::app()->db->beginTransaction();

            $rows = [];

            try {

                foreach ($members as $_member) {

                    if($_member == $this->owner_id) continue;

                    $sum = $this->getSeparatedSum($_member);

                    $_data = [
                        'sum'=>$sum,
                        'created_at' => new CDbExpression('NOW()'),
                        'status' => Transactions::STATUS_NEW,
                        'from_user' => $this->owner_id,
                        'to_user' => $_member,
                        'connected_to' => $this->id
                    ];

                    $rows[] = $_data;

                    /*$tr = new Transactions();
                    $tr->sum = $sum;
                    $tr->created_at = new CDbExpression('NOW()');
                    $tr->status = Transactions::STATUS_NEW;
                    $tr->from_user = $this->owner_id;
                    $tr->to_user = $_member;
                    $tr->connected_to = $this->id;
                    $tr->save();*/
                }

                $builder = Yii::app()->db->schema->commandBuilder;
                $command = $builder->createMultipleInsertCommand('transactions', $rows);
                $command->execute();

                $transaction->commit();
                Yii::app()->db->createCommand("UNLOCK TABLES")->execute();

                return true;

            } catch(Exception $e) {
                $transaction->rollback();
                Yii::app()->db->createCommand("UNLOCK TABLES")->execute();

                $this->is_deleted = 1;
                $this->is_closed = -1;
                $this->save(false,['is_deleted','is_closed']);


                return false;
            }

            /*$model=Profile::model();
            $transaction=$model->dbConnection->beginTransaction();
            try {
                $users = $model->findAll(['condition'=>'user_id in (:id)','params'=>[':id'=>$this->members]]);

                foreach ($users as $_user) {



                    if($_user->save())
                        $transaction->commit();
                    else
                        $transaction->rollback();
                }


            } catch(Exception $e) {
                $transaction->rollback();
                throw $e;
            }*/

        }

    }

    public function getMembers($scopes = []) {

	    if($this->members == '' && !isset($scopes['withMe'])) return [];

        $members = explode(',',$this->members);

        if(isset($scopes['withMe']) && $scopes['withMe']) {
            $members[] = Yii::app()->user->id;
        }


	    $criteria = new CDbCriteria;
	    $criteria->select = 'id, firstname, lastname, avatar_id';
	    $criteria->addInCondition('id',$members);

	    $members = User::model()->findAll($criteria);

	    return $members;
    }

    public function getMembersByLink() {

        if($this->members_by_link == '') return [];

        $members = Invite::model()->findAll([
            'condition'=>'id in (:id)',
            'select'=>'id, code, name',
            'params'=>[':id' => $this->members_by_link]
        ]);

        return $members;
    }

    public static function toArray($model) {
	    $data = [];

	    if(!is_array($model)) $model = [$model];

	    foreach ($model as $k => $_model) {
	        $data[$k] = [];
	        foreach ($_model as $_key => $_val) {
	            if($_val !== null)
	                $data[$k][$_key] = $_val;
            }
	        //$data[] = $_model->attributes;
        }
        return $data;
    }

    public function delete() {

        $tr = $this->transactions;
        foreach ($tr as $_tr) {
            if($_tr->status != Transactions::STATUS_NEW) return false;
        }


        $this->is_deleted = 1;
        $this->save(false, ['is_deleted']);

        foreach ($tr as $_tr) {
            $_tr->status = Transactions::STATUS_CANCELED;
            $_tr->save(false,['status']);
        }

        return true;
    }

    public function canUpdate() {
        $tr = $this->transactions;
        foreach ($tr as $_tr) {
            if($_tr->status != Transactions::STATUS_NEW) return false;
        }

        return true;
    }

    public function canDelete() {
	    return $this->canUpdate();
    }

    public function canView($user_id) {

	    if($user_id == $this->owner_id) return true;

	    return $this->isUserInCheck($user_id);
    }

    public function isUserInCheck($user_id) {
        if($this->members == '') return false;

        $members = explode(',', $this->members);
        return in_array($user_id, $members);
    }

    public function getName() {
	    if($this->title == '') return 'Чек #'.$this->id;
	    else return $this->title;
    }


    public function getCreated() {
        return date('d.m.Y H:i', strtotime($this->created_at));
    }

    public function getTypeLabel() {
	    $labels = [
	        self::TYPE_SHARE => 'поровну',
	        self::TYPE_SELF => 'каждый сам за себя',
        ];

	    return $labels[$this->type];
    }

    public function to_array() {
	    $item = [
	        'id'=>$this->id,
            'members'=>self::toArray($this->getMembers()),
            'members_amount'=>$this->members_amount,
            'created'=>$this->getCreated(),
            'name'=>$this->getName(),
            'owner'=> [
                'firstname'=>$this->owner->firstname,
                'lastname'=>$this->owner->lastname,
                'avatar_html'=>$this->owner->getAvatarAuto(),
            ],
            'owner_id'=>$this->owner_id,
            'type_label'=>$this->getTypeLabel(),
            'sum'=>round($this->sum),
            'subs'=>$this->getSubs(),
            'type'=>$this->type
        ];

	    return $item;
    }


    public function countPayed() {
	    $criteria = new CDbCriteria();
	    $criteria->addCondition('connected_to = :check_id');
	    $criteria->addCondition('status = 1');
	    $criteria->params[':check_id'] = $this->id;


	    $sum = 0;

	    $t = Transactions::model()->findAll($criteria);
	    foreach ($t as $_t) {
	        $sum += $_t->sum;
        }

        return round($sum);
    }
}
