
<div class="transactions-list" id="transactions" v-cloak>



    <?php if(isset($withFilters) && $withFilters == true):?>


        <div class="tabs">
            <div :class="{active:filter == 'all'}" @click="filter = 'all'">все</div>
            <div :class="{active:filter == 'checks'}" @click="filter = 'checks'">только чеки</div>
        </div>

        <div class="dashed"></div>

    <?php endif;?>

    <?php /* foreach ($transactions as $_transaction) :?>

        <?php
            if($_transaction->from_user == Yii::app()->user->id) {
                $from = $_transaction->from;
                $to = $_transaction->to;
            } else {
                $from = $_transaction->to;
                $to = $_transaction->from;
            }
        ?>
        <div class="transaction">



                <?php if($_transaction->connected_to <= 0):?>
                    <div class="transaction-title">
                        <div class="<?php if($_transaction->from_user == Yii::app()->user->id) echo 'from-me';?>">

                            <div class="profile-compact">
                                <div class="profile-img">
                                    <?php if($from->hasAvatar()):?>
                                        <?=$from->getAvatar();?>
                                    <?php else:?>
                                        <?=$from->getCavatar();?>
                                    <?php endif;?>
                                </div>
                                <div class="profile-description">
                                    <div class="profile-name">
                                        <?php if($_transaction->from_user == Yii::app()->user->id):?>
                                            Вы отдали
                                        <?php else:?>
                                            Вы получили
                                        <?php endif;?>

                                    </div>
                                    <div class="transaction-date"><?=$_transaction->getCreated();?></div>
                                </div>

                            </div>

                            <?php if($_transaction->from_user == Yii::app()->user->id):?>
                            (стрелка вниз)
                            <?php else:?>
                            (стрелка вверх)
                            <?php endif;?>

                            <div class="profile-compact">
                                <div class="profile-img">
                                    <?php if($to->hasAvatar()):?>
                                        <?=$to->getAvatar();?>
                                    <?php else:?>
                                        <?=$to->getCavatar();?>
                                    <?php endif;?>
                                </div>
                                <div class="profile-description">
                                    <div class="profile-name">
                                        <?=$to->firstname;?>
                                        <?=$to->lastname;?>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>


                    <div class="transaction-actions">
                        <?php if(abs($_transaction->connected_to) != Yii::app()->user->id && $_transaction->status == Transactions::STATUS_NEW):?>
                            <div class="buttons-row">
                                <button class="btn btn-primary btn-accep" @click="confirmCheck(<?=$_transaction->id;?>)">подтвердить</button>
                                <button class="btn btn-danger btn-decline" @click="declineCheck(<?=$_transaction->id;?>)">отклонить</button>
                            </div>
                        <?php endif;?>
                    </div>

                <?php else:?>
                    <div class="transaction-title">
                        <?=$_transaction->check->getName();?>
                    </div>
                    <div class="transaction-date"><?=$_transaction->getCreated();?></div>

                    <div class="transaction-footer">
                        <div class="check-sum">
                            чек на <?=$_transaction->check->sum;?> ₽
                        </div>
                        <div>
                            <a href="/check/<?=$_transaction->connected_to;?>">открыть чек</a>
                        </div>
                    </div>

                <?php endif;?>


            <div class="transaction-status">
                <?=$_transaction->getStatusLabel();?> <div class="t-status t-status-<?=$_transaction->status;?>"></div>
            </div>

            <div class="transaction-sum">
                <?=$_transaction->sum;?> ₽
            </div>

        </div>
        <div class="dashed"></div>
    <?php endforeach; */?>

    <div v-for="_transaction in transactions" v-show="filter=='all' || (filter=='checks'&&_transaction.connected_to>0)">
        <div class="transaction">

            <div v-if="_transaction.connected_to < 0">
                <div class="transaction-title">
                    <div :class="{'from-me':_transaction.direction == true}">

                        <div class="profile-compact">
                            <div class="profile-img" v-html="_transaction.from.avatar_html">

                            </div>
                            <div class="profile-description">
                                <div class="profile-name">
                                    <span v-if="_transaction.direction == true">
                                        Вы отдали
                                    </span>
                                    <span v-else>
                                        Вы получили
                                    </span>

                                </div>
                                <div class="transaction-date">{{_transaction.created}}</div>
                            </div>

                        </div>

                        <div class="profile-compact-arrow" v-if="_transaction.direction == true">
                            <div class="arrow-down"></div>
                        </div>
                        <div class="profile-compact-arrow" v-else>
                            <div class="arrow-up"></div>
                        </div>

                        <div class="profile-compact">
                            <div class="profile-img" v-html="_transaction.to.avatar_html">

                            </div>

                            <div class="profile-description">
                                <div class="profile-name">
                                    {{_transaction.to.firstname}}
                                    {{_transaction.to.lastname}}
                                </div>
                            </div>


                        </div>

                    </div>
                </div>


                <div class="transaction-actions" v-if="_transaction.direction == false && _transaction.status == 0">

                    <div class="buttons-row">
                        <button class="btn btn-primary btn-accep" @click="confirmCheck(_transaction)">подтвердить</button>
                        <button class="btn btn-danger btn-decline" @click="declineCheck(_transaction)">отклонить</button>
                    </div>

                </div>

            </div>
            <div v-else>
                <div class="transaction-title">
                    {{_transaction.check.name}}
                </div>
                <div class="transaction-date">{{_transaction.created}}</div>

                <div class="transaction-footer">
                    <div class="check-sum">
                        чек на {{_transaction.check.sum}} ₽
                    </div>
                    <div>
                        <a :href="'/check/'+_transaction.connected_to">открыть чек</a>
                    </div>
                </div>
            </div>


            <div class="transaction-status">
                {{_transaction.status_label}} <div class="t-status" :class="['t-status-'+_transaction.status]"></div>
            </div>

            <div class="transaction-sum">
                {{_transaction.sum}} ₽
            </div>

        </div>
        <div class="dashed"></div>
    </div>

</div>

<script>
    var transactions = new Vue({
        el: '#transactions',
        computed: {

        },
        data: {
            loading:false,

            filter:'all',

            transactions:<?=json_encode($transactions);?>
        },
        watch: {
            loading: function (new_val, old_val) {
                if (new_val == true) siteLoading.enable();
                if (new_val == false) siteLoading.disable();
            },
        },
        methods: {
            saveStatus:function(_transaction, status) {

                var _this = this;

                if(this.loading) return false;

                this.loading = true;

                var data = {
                    transaction_id:_transaction.id,
                    status:status,

                    NC_CSRF_TOKEN: cfg.csrf_token,
                };

                $.post('/api/save__transactionConfirmation',data,function(response){

                    if(response.success == true) {
                        _this.transaction_status = response.transaction.status;

                        _transaction.status = response.transaction.status;
                        _transaction.status_label = response.transaction.status_label;
                    }

                }).always(function(){
                    _this.loading = false;
                })
            },
            confirmCheck:function(_transaction) {
                this.saveStatus(_transaction, 1);
            },
            declineCheck:function(_transaction) {
                this.saveStatus(_transaction, -1);

            }
        }
    });
</script>