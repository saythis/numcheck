<?php
/* @var $this CheckController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Checks',
);

$this->menu = [];
?>


<div class="">

    <div class="user-short mb-2">
        <div class="user-short__create-check">

            <a href="/new" class="btn solid btn-check">Создать счёт</a>



        </div>
        <div class="user-short__summary">
            <div class="autofloat right">
                <div class="profile-compact">
                    <div class="profile-img">
                        <?php if($user->hasAvatar()):?>
                            <?=$user->getAvatar();?>
                        <?php else:?>
                            <?=$user->getCavatar();?>
                        <?php endif;?>
                    </div>
                </div>
                <div class="text-right">
                    <div><?=$overall_balance;?> ₽</div>
                    <div><?php if($overall_balance !=0):?><?=$overall_balance>0?'нужно отдать':'нужно забрать';?><?php endif;?></div>
                </div>
            </div>
        </div>
    </div>

    <!--div class="tabs">
        <a href="#">Люди</a>
        <a href="#">Группы</a>
    </div-->

    <div id="friends" v-cloak>
        <div v-if="friends.length>0">
            <div class="mt-4 mb-4">
                <div><input type="text" class="form-control" v-model="search" placeholder="Начните вводить имя"></div>
                <div></div>
            </div>


            <div class="users-list">
                <a :href="'/user/' + _friend.id" class="users-item" v-for="_friend in friends">
                    <div class="user-img" v-html="_friend.avatar_html">

                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            {{_friend.firstname}}
                            {{_friend.lastname}}
                        </div>
                        <div class="user-date">{{_friend.last_balance_change}}</div>
                    </div>
                    <div class="user-balance text-right" :class="[_friend.balance>0?'red':'green']">
                        <span>{{_friend.balance}}</span> ₽
                        <div class="small-grey">
                            <span v-if="_friend.balance>0">отдать</span>
                            <span v-if="_friend.balance<0">забрать</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div v-else>
            <div class="alert alert-warning">
                Пригласите друзей при <a href="/new">создании счета</a>, <a href="/user/invites">через инвайты</a> или попросите ссылку для создания связи
            </div>
        </div>

    </div>

</div>




<script>
    var friends = new Vue({
        el: '#friends',
        computed: {

        },
        data: {
            friends_list:<?=json_encode($friends);?>,
            friends_found:[],

            search:'',

            errors:[],

            loading:false,
        },
        computed: {
            friends:function() {
                if(this.search.length>=1) return this.friends_found;
                else return this.friends_list;
            }
        },
        watch: {
            search:function(n){
                if(n != '' && n.length>=1) {
                    this.searchFriends();
                }
            },
            loading: function(new_val, old_val) {
                if(new_val == true) siteLoading.enable();
                if(new_val == false) siteLoading.disable();
            },
        },
        methods: {

            searchFriends:function() {

                var _this = this;

                var data = {
                    name:this.search,
                    NC_CSRF_TOKEN: cfg.csrf_token,
                    fullinfo:true
                }

                $.post('/api/get__friends',data,function(response){
                    _this.friends_found = response.friends;
                })
            },

        }
    });
</script>
