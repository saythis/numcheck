<?php
/*
 * @var $user User
 * @var $profile Profile
 *
 */
$this->setPageTitle($user->name());
?>


<div class="back-wrap">
    <a href="/feed" class="btn btn-back">назад</a>
</div>

<div id="user" v-cloak>

    <div class="profile">
        <div class="profile-img">
            <?php if($user->hasAvatar()):?>
            <?=$user->getAvatar(true, '_small');?>
            <?php else:?>
            <div class="profile-img-circle">
                <span><?=mb_substr($user->firstname,0,1,'UTF-8');?><?=mb_substr($user->lastname,0,1,'UTF-8');?></span>
            </div>
            <?php endif;?>
        </div>
        <div class="profile-description">
            <div class="profile-name">
                <?=$user->firstname;?>
                <?=$user->lastname;?>
            </div>
            <div class="profile-balance">
                <div class="">
                    <span v-if="balance<0">забрать</span>
                    <span v-if="balance>0">отдать</span>
                    <span :class="[sum>0 ? 'red' : 'green']"><?=abs(round($friend->getBalance()));?> ₽</span>
                </div>
            </div>
        </div>
    </div>

    <?php if($friend->balance != 0):?>

    <div class="pay-form-alert" style="display: none; padding-bottom: 30px">
        <div class="alert">
            <span v-if="balance<0">Принято!</span>
            <span v-else>Запрос создан. Ожидайте подтверждения</span>
        </div>
    </div>
    <div class="pay-form" >
        <div class="form-group form-sum">
            <span v-if="balance<0">
                <label for="">впишите сумму которую отдал человек</label>
            </span>
            <span v-else>
                <label for="">впишите сумму которую вы отдали человеку</label>
            </span>
            <input type="text" class="form-control" v-model="sum" pattern="\d*">
        </div>
        <button class="btn btn-grey" @click="createBuyback()">подтвердить</button>
    </div>

    <?php endif;?>

</div>



<?php $this->renderPartial('//check/_transactions',['transactions'=>$transactions,'withFilters'=>true]);?>



<script>





    var user = new Vue({
        el: '#user',
        computed: {
        },
        data: {
            balance:<?=($friend->getBalance());?>,
            sum:<?=abs($friend->getBalance());?>,
            user_id:<?=$user->id;?>
        },
        watch: {
            loading: function (new_val, old_val) {
                if (new_val == true) siteLoading.enable();
                if (new_val == false) siteLoading.disable();
            },
            sum: function(n) {
                if(n == '') return;

                var t = parseInt(n);
                if(isNaN(t)) {
                    t = '';
                }

                this.sum = t;

            }


        },
        methods: {
            createBuyback:function() {

                var _this = this;

                if(this.loading) return false;

                this.loading = true;

                var data = {
                    user_id: this.user_id,
                    sum:     this.sum,
                    NC_CSRF_TOKEN: cfg.csrf_token,

                }

                $.post('/api/create__buyback',data,function(response){

                    if(response.success == true) {
                        $('.pay-form').slideUp();
                        $('.pay-form-alert').slideDown();
                    }


                }).always(function () {
                    _this.loading = false;
                })
            }
        }
    });

</script>

</script>
