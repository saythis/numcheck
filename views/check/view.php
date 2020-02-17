<?php
/* @var $this CheckController */
/* @var $model Check */

$this->setTitle($check['name']);
?>

<?php

$members = User::toArray($model->getMembers(['withMe'=>false]));

$members_by_link = Check::toArray($model->getMembersByLink());

?>
<style>
    .transaction-subs {
        padding-left: 90px;
        clear: both;
        position: relative;
    }
    .transaction-subs:before {
        content: '-';
        line-height: 20px;
        text-align: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border:1px #0063d7 solid;
        position: absolute;
        color:#0063d7;

        left: 60px;
        top:0;
    }
</style>
<div id="check" v-cloak>

    <?php if(Yii::app()->user->id == $check['owner_id']):?>

        <div class="back-wrap">
            <a href="/check/update/id/<?=$check['id'];?>" class="btn btn-back">редактировать</a>
        </div>

    <?php else:?>

        <div class="back-wrap">
            <a href="/user/<?=$check['owner_id'];?>" class="btn btn-back">назад</a>
        </div>

    <?php endif;?>


    <div class="mb-2">
        <div class="transaction-title">
            {{check.name}}
        </div>
        <div class="transaction-date">
            {{check.created}}
        </div>
    </div>

    <div v-if="cfg.user_id != check.owner_id">
        создатель: <a :href="'/user/'+check.owner_id">{{check.owner.firstname}} {{check.owner.lastname}}</a>
    </div>

    <div class="mb-2">
        счёт: {{check.type_label}} на {{check.members_amount}}
    </div>
    <div class="mb-2">
        сумма чека:
        <div class="big-number">{{check.sum}} ₽</div>
    </div>


    <?php if($check['owner_id'] == Yii::app()->user->id):?>
    <div class="profiles-list">
        <div v-for="_transaction in transactions"  class="profile-compact">
            <div class="profile-img" v-html="_transaction.to.avatar_html"></div>
            <div class="profile-name">
                {{_transaction.to.firstname}}
                {{_transaction.to.lastname}}
                <span class="red">{{_transaction.sum}} ₽</span>
            </div>

            <div class="transaction-subs" v-for="_sub in getSubs(_transaction.to.id,'user')">
                <span class="green">{{_sub.sum}} ₽</span> {{_sub.title}}
            </div>
        </div>
    </div>
    <?php endif;?>


    <div class="selected-bylink">
        <div class="profiles-list">
            <div v-for="_link in members_by_link"  class="profile-compact">
                <div class="profile-img"><img src="/static/img/link-w@2x.png"/> </div>
                <div class="profile-name">
                    <span v-if="_link.name != ''">({{_link.name}})</span> <span>http://numcheck.ru/reg/{{_link.code}}</span> <span v-if="_link.name != ''">{{_link.name}}</span> ({{getPreSum(_link,'link')}} ₽)
                </div>

                <div class="transaction-subs" v-for="_sub in getSubs(_link.id,'link')">
                    <span class="green">{{_sub.sum}} ₽</span> {{_sub.title}}
                </div>
            </div>
        </div>
    </div>

    <div class="dashed"></div>


    <div v-if="transactions.length>0 && transactions[0].status == 0 && cfg.user_id != check.owner_id ">
        <div class="form-group form-sum" v-if="check.type == 2">
            <label for="">Ваша сумма</label>
            <input type="text" class="form-control" v-model="sum">
        </div>
        <div v-else>
            <label for="">Ваша сумма</label>
            <div class="big-number">{{my_sum}} ₽</div>

            <div class="transaction-subs" v-for="_sub in getSubs(transactions[0].to_user,'user')">
                <span class="green">{{_sub.sum}} ₽</span> {{_sub.title}}
            </div>

        </div>

        <div class="buttons-row">
            <button class="btn btn-primary btn-accept" @click="confirmCheck()">подтвердить</button>
            <button class="btn btn-danger btn-decline" @click="declineCheck()">отклонить</button>
        </div>
    </div>
    <div v-else>
        <label for="">Ваша сумма</label>
        <div class="big-number">{{my_sum}} ₽</div>

        <div class="transaction-subs" v-for="_sub in getSubs(check.owner_id,'user')">
            <span class="green">{{_sub.sum}} ₽</span> {{_sub.title}}
        </div>
    <div>

</div>


<script>
    var check = new Vue({
        el: '#check',
        computed: {
            my_sum:function() {
                if(cfg.user_id == this.check.owner_id) {

                    var found = false;
                    for(index in this.members) {
                        if(this.members[index].id == this.check.owner_id) {
                            found = true;
                            break;
                        }
                    }

                    if(!found) return 0;


                    var sum = this.getPreSum({id:cfg.user},'user');

                    return sum;

                    var all = 0;
                    $.each(this.transactions,function(index,item){
                        all += parseInt(item.sum);
                    })

                    return sum - all;
                    //return this.check.sum - all;
                } else {
                    return this.transactions[0].sum;
                }
            },
            total_members: function() {
                return this.members.length + this.members_by_link.length;
            },
        },
        data: {
            loading: false,
            check:<?=json_encode($check);?>,
            transactions:<?=json_encode($transactions);?>,
            sum:'',


            members:<?=json_encode($members);?>,
            members_by_link:<?=json_encode($members_by_link);?>,
        },
        watch: {
            loading: function (new_val, old_val) {
                if (new_val == true) siteLoading.enable();
                if (new_val == false) siteLoading.disable();
            },
            sum:function(n) {

                if(n == '') return;

                var t = parseInt(n);
                if(isNaN(t)) {
                    t = '';
                }

                this.sum = t;


            }
        },
        mounted:function() {
            this.sum = this.transactions[0].sum;
        },
        methods: {
            getSubs:function(user_id, type){
                var subs = [];

                $.each(this.check.subs, function(index, item){
                    if(item[type].indexOf(user_id.toString()) > -1) {
                        subs.push(item);
                    }
                })

                return subs;
            },
            saveStatus:function(status) {

                var _this = this;

                if(this.loading) return false;

                this.loading = true;

                var data = {
                    check_id:this.check.id,
                    status:status,
                    sum:this.sum,

                    NC_CSRF_TOKEN: cfg.csrf_token,
                };

                $.post('/api/save__checkConfirmation',data,function(response){

                    if(response.success == true) {
                        _this.transactions[0].status = response.transaction.status;
                        _this.transactions[0].sum = response.transaction.sum;
                    }

                }).always(function(){
                    _this.loading = false;
                })
            },
            confirmCheck:function() {
                this.saveStatus(1);
            },
            declineCheck:function() {
                this.saveStatus(-1);

            },



            getAsubsum:function(id, type) {
                var sum = 0;
                var total_members = this.total_members;


                $.each(this.check.subs, function(index, item){

                    var subs_members = item.link.length + item.user.length;

                    var user_found = false;

                    $.each(item[type], function(_index, _id){
                        if(_id == id) {
                            user_found = true;
                        }
                    })

                    if(user_found == false) {
                        if(item.sum != '' && !isNaN(item.sum))
                            sum += parseInt(item.sum) / (total_members - subs_members)
                    }
                });

                return sum;

            },
            getPreSum:function(model, type) {

                if(this.check.sum == '') return 0;

                var total_members = this.total_members;

                var av = Math.round(this.check.sum/total_members);

                //сумма вычетов
                var sub_sum = 0;

                var groups = {};

                $.each(this.check.subs, function(index, item){
                    if(item.sum != '' && !isNaN(item.sum))
                        sub_sum += parseInt(item.sum);

                });

                var base_sum = (parseInt(this.check.sum) - sub_sum) / this.total_members;

                //группируем по людям
                var result_sum = base_sum + this.getAsubsum(model.id, type);

                return Math.round(result_sum);
            },
        }
    });

</script>
