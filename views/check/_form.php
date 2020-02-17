<?php
/* @var $this CheckController */
/* @var $model Check */
/* @var $form CActiveForm */
?>

<?php

$members = User::toArray($model->getMembers(['withMe'=>false]));

$members_by_link = Check::toArray($model->getMembersByLink());

?>

<style>
    .check-title {
        font-size: 22px;margin-bottom: 20px;

    }
    .profiles-for-subs .profile-compact {
        opacity: 0.4;
        cursor: pointer;
    }
    .profiles-for-subs .profile-compact:hover {
        opacity: 0.8;
    }
    .profiles-for-subs .profile-compact.selected {
        opacity: 1;
    }

    .sub-title {
        max-width: 250px;
    }
    .inline-delete {
        cursor: pointer;
        padding: 3px;display: inline-block;
    }
    .remove-sub {
        padding-top: 6px;
    }
</style>

<div class="form" id="check" v-cloak>


    <?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'check-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions'=>['class'=>'form-horizontal'],
)); ?>


    <div v-if="Object.keys(errors).length>0" class="alert alert-warning">
        <ul>
            <li v-for="_error in errors">{{_error[0]}}</li>
        </ul>
    </div>


	<?php echo $form->errorSummary($model); ?>


    <div class="check-title" @click="editTitle()" v-show="edit_title == false">
        <div>счёт #</div>
        <div class="small-grey"><?=date('d.m.Y H:i');?></div>
    </div>

    <div class="form-group" v-show="edit_title == true">
        <div class="check-title">
            <?php echo $form->textField($model,'title',['maxlength'=>255,'class'=>'form-control', 'v-model'=>'check.title','ref'=>'title']); ?>
        </div>
    </div>


    <div class="form-group form-sum mb-3" :class="{'has-error':errors['sum']!=undefined}">
        <?php echo $form->labelEx($model,'sum',['class'=>'control-label col-md-4']); ?>
        <div class="col-md-8">
            <?php echo $form->textField($model,'sum',['maxlength'=>11,'pattern'=>'\d*','class'=>'form-control', 'v-model'=>'check.sum','placeholder'=>'1000 ₽']); ?>
        </div>
        <?php echo $form->error($model,'sum'); ?>
    </div>



    <div class="form-group" style="display: none">
        <?php //echo $form->labelEx($model,'currency',['class'=>'control-label col-md-4']); ?>
        <div>
            <div class="tabs">
                <div v-for="(_currency, _c_name) in currencies" @click="check.currency = _c_name" :class="{active:check.currency == _c_name}">{{_currency}}</div>
            </div>
        </div>
        <div class="col-md-8">
            <?php echo $form->hiddenField($model,'currency',['class'=>'form-control','v-model'=>'check.currency']); ?>
        </div>
        <?php echo $form->error($model,'currency'); ?>
    </div>


    <div class="form-group" id="members-form" v-show="show_search">
        <div class="close" @click="closeSearch();"></div>
        <?php //echo $form->labelEx($model,'members',['class'=>'control-label col-md-4']); ?>
        <div class="mb-2">
            <input type="text" class="form-control" v-model="search" ref="search" placeholder="начните вводить имя" />
        </div>
        <div class="autocomplete">

            <div class="users-list profile-compact">
                <div @click="createInviteLink()" class="add-link">создать приглашение</div>

                <div v-for="_user in friends" @click="selectUser(_user)" :class="{disabled:isAdded(_user.id)}">
                    <div class="profile-img" v-html="_user.avatar_html"></div>
                    <div class="profile-name">{{_user.firstname}} {{_user.lastname}}</div>
                </div>
            </div>

        </div>
        <?php echo $form->error($model,'members'); ?>
    </div>

    <div class="form-group">
        <div class="mb-2"><label for="">Участники</label></div>


        <a href="#" class="add-person" @click="showSearch();"  onclick="return false;">добавить человека</a>
        <a href="#" class="add-link" @click="createInviteLink()" onclick="return false;">создать приглашение</a>

        <div class="selected-friends">
            <div class="profiles-list">
                <div v-for="_user in members"  class="profile-compact">
                    <div class="profile-img" v-html="_user.avatar_html"></div>
                    <div class="profile-name">
                        {{_user.firstname}} {{_user.lastname}} <small>({{getPreSum(_user,'user')}} ₽)</small> <span @click="removeUser(_user)" class="red inline-delete" >x</span>
                    </div>
                </div>
            </div>

        </div>
        <div class="selected-bylink">
            <div class="profiles-list">
                <div v-for="_link in members_by_link"  class="profile-compact">
                    <div class="profile-img"><img src="/static/img/link-w@2x.png"/> </div>
                    <div class="profile-name">
                        <span v-if="_link.name != ''">({{_link.name}})</span> <span>https://numcheck.ru/reg/{{_link.code}}</span> <span v-if="_link.name != ''">{{_link.name}}</span> <small>({{getPreSum(_link,'link')}} ₽)</small>
                        <span @click="removeLink(_link)" class="red inline-delete">x</span>
                    </div>
                </div>
            </div>
        </div>
    </div>




	<!--div class="form-group">
		<?php echo $form->labelEx($model,'check_at',['class'=>'control-label col-md-4']); ?>
        <div class="col-md-8">
		    <?php echo $form->textField($model,'check_at',['class'=>'form-control']); ?>
        </div>
		<?php echo $form->error($model,'check_at'); ?>
	</div-->

    <div class="dashed"></div>

	<div class="form-group">

        <div>
            <div class="tabs">
                <div @click="check.type = 1" :class="{active:check.type == 1}">поровну на {{total_members}}</div>
                <div @click="check.type = 2" :class="{active:check.type == 2}">каждый сам за себя на {{total_members}}</div>
            </div>
        </div>

        <div class="col-md-8">
		    <?php echo $form->hiddenField($model,'type',['class'=>'form-control']); ?>
        </div>
		<?php echo $form->error($model,'type'); ?>
	</div>


    <div class="subs" v-show="check.type==1">

        <div class="dashed" ></div>


        <div v-for="(_sub, _index) in check.subs">


            <div class="autofloat">
                <div class="check-title sub-title" @click="editTitle(_sub, _index)" v-show="_sub.edit_title == false">
                    <div>вычет #{{_index}}</div>
                    <div class="small-grey"><?=date('d.m.Y H:i');?></div>
                </div>
                <div class="form-group sub-title" v-show="_sub.edit_title == true || _sub.edit_title == undefined">
                    <div class="check-title">
                        <input class="form-control" v-model="_sub.title" :ref="'subtitle' + _index">
                    </div>
                </div>
                <div @click="removeSub(_index)" class="small-grey remove-sub">удалить вычет</div>

            </div>




            <div class="form-group form-sum mb-3" >
                <label for="">Сумма</label>
                <div class="col-md-8">
                    <input type="text" class="form-control" v-model="_sub.sum">
                </div>
            </div>

            <div class="profiles-for-subs">
                <div class="selected-friends">
                    <div class="profiles-list">
                        <div v-for="_user in members"  class="profile-compact" @click="selectForSub(_sub, _user, 'user')" :class="{selected:isSelectedForSub(_sub,_user,'user')}">
                            <div class="profile-img" v-html="_user.avatar_html"></div>
                            <div class="profile-name">
                                {{_user.firstname}} {{_user.lastname}} <small>({{getPreSum(_user,'user')}} ₽)</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="selected-bylink">
                    <div class="profiles-list">
                        <div v-for="_link in members_by_link"  class="profile-compact"  @click="selectForSub(_sub, _link, 'link')" :class="{selected:isSelectedForSub(_sub,_link,'link')}">
                            <div class="profile-img"><img src="/static/img/link-w@2x.png"/> </div>
                            <div class="profile-name">
                                <span v-if="_link.name != ''">({{_link.name}}) </span>https://numcheck.ru/reg/{{_link.code}} <span v-if="_link.name != ''">{{_link.name}}</span> <small>({{getPreSum(_link,'link')}} ₽)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dashed"></div>

        </div>

        <div v-show="total_members > check.subs.length+1">
            <button class="btn btn-check-white" @click="addSub()" onclick="return false;" >добавить вычет</button>
        </div>

    </div>
    <div class="dashed" v-show="total_members > check.subs.length+1 || check.type == 2"></div>


    <div class="" style="padding-bottom: 40px">
        <label for="">Комментарий</label>
        <textarea class="form-control" v-model="check.comment" placeholder="Не забудь оплатить за секс"></textarea>
    </div>


    <div v-if="Object.keys(errors).length>0" class="alert alert-red">
        <ul>
            <li v-for="_error in errors">{{_error[0]}}</li>
        </ul>
    </div>


    <div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать счёт' : 'Сохранить',['class'=>'btn btn-check solid','@click'=>'submit()','onclick'=>'return false;']); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->


<script>
    var check = new Vue({
        el: '#check',
        computed: {
            total_members: function() {
                return this.members.length + this.members_by_link.length;
            },
            friends:function() {
                if(this.search.length >= 1) return this.friends_found;
                else return this.popular_friends;
            },
        },
        data: {
            edit_title:false,

            friends_found:[],
            popular_friends:<?=json_encode($popular_friends);?>,

            show_search:false,

            members:<?=json_encode($members);?>,
            members_by_link:<?=json_encode($members_by_link);?>,

            check: {
                id:<?=intval($model->id);?>,
                title:'<?=$model->title;?>',
                sum:'<?=$model->sum>0?$model->sum:'';?>',
                type:'<?=$model->type;?>',
                currency:'<?=$model->currency;?>',
                owner_id:<?=$model->owner_id;?>,
                members:'',
                members_by_link:'',
                subs:<?=$model->getSubsJson();?>,
                comment:'',
            },

            search:'',

            errors:[],

            currencies: {
                1:'₽',
                2:'Доллар',
                3:'Евро',
            },
           /* check_types: {
                1:'Поровну',
                2:'Каждый сам за себя',
            },*/

            loading:false,
        },
        watch: {
            'check.subs':function(n) {
                console.log(n);
            },
            search:function(n){
                if(n != '' && n.length>=1) {
                    this.searchFriends();
                }
            },
            loading: function(new_val, old_val) {
                if(new_val == true) siteLoading.enable();
                if(new_val == false) siteLoading.disable();
            },
            'check.sum':function(n) {

                if(n == '') return;

                var t = parseInt(n);
                if(isNaN(t)) {
                    t = '';
                }

                this.check.sum = t;


            }
        },
        methods: {
            addSub:function() {
                var new_sub = {
                        title:'',
                        link:[],
                        user:[],
                        sum:'',
                        edit_title:false,
                    };

                this.check.subs.push(new_sub);
            },
            removeSub:function(index) {
                this.check.subs.splice(index, 1);
            },
            selectForSub:function(sub, model, type) {

                var items = sub[type];

                var _index = -1;

                $.each(items, function(index, item){
                    if(item == model.id) _index = index;
                })

                if(_index>-1) {
                    sub[type].splice(_index, 1);
                } else {
                    sub[type].push(model.id);
                }
            },
            isSelectedForSub:function(sub, model, type) {
                var items = sub[type];

                var _index = -1;

                $.each(items, function(index, item){
                    if(item == model.id) _index = index;
                })

                if(_index>-1) {
                    return true;
                } else {
                    return false;
                }
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
            editTitle:function(obj, index) {

                if(obj != undefined && index != undefined) {
                    obj.edit_title = true;

                    var ref_id = 'subtitle'+index;

                    this.$nextTick(function(){

                        this.$refs[ref_id][0].focus()
                    });
                    return false;
                }

                this.edit_title = true;

                this.$nextTick(function(){
                    this.$refs.title.focus()
                });
            },
            isAdded:function(user_id) {

                var result = false;

                $.each(this.members, function(index, item){
                    if(item.id == user_id) result = true;
                })

                return result;
            },
            searchFriends:function() {

                var _this = this;

                var data = {
                    name:this.search,
                    NC_CSRF_TOKEN: cfg.csrf_token,


                }

                $.post('/api/get__friends',data,function(response){
                    if(response.success == true)
                        _this.friends_found = response.friends;
                })
            },
            createInviteLink:function() {

                if(this.loading) return false;
                this.loading = true;

                var _this = this;

                var data = {
                    NC_CSRF_TOKEN: cfg.csrf_token,
                    name:this.search
                }

                $.post('/api/get__invite',data,function(response){
                    if(response.success == true) {
                        _this.members_by_link.push(response.invite)
                    }

                    _this.loading = false;
                    _this.closeSearch();
                });
            },
            removeUser:function(user) {
                //if(user.id == this.check.owner_id) return false;


                var _index = -1;

                $.each(this.members, function(index, item){
                    if(item.id == user.id) _index = index;
                })

                if(_index>-1) {
                    this.members.splice(_index, 1);
                }
            },
            removeLink:function(link) {
                var _index = -1;

                $.each(this.members_by_link, function(index, item){
                    if(item.id == link.id) _index = index;
                })

                if(_index>-1) {
                    this.members_by_link.splice(_index, 1);
                }
            },
            selectUser:function(user) {

                if(this.isAdded(user.id)) return false;

                this.members.push(user);
                this.search = '';
                this.closeSearch();
            },
            closeSearch:function() {
                this.show_search = false;
                $('.overlay').hide();
            },
            showSearch:function() {
                this.show_search = true;
                $('.overlay').show();

                this.$nextTick(function(){
                    this.$refs.search.focus()
                });

            },
            validate: function() {

                this.submit(true);

            },
            submit: function(validate) {

                if(this.loading) return false;
                this.loading = true;

                var _this = this;

                var users_ids = [];
                $.each(this.members, function(index, item){
                    users_ids.push(item.id)
                });
                var link_ids = [];
                $.each(this.members_by_link, function(index, item){
                    link_ids.push(item.id)
                });

                _this.check.members = users_ids;
                _this.check.members_by_link = link_ids;

                console.log(cfg);
                var data = {
                    Check: this.check,
                    'NC_CSRF_TOKEN': cfg.csrf_token,
                };

                if(validate == true) {
                    data.ajax = 'check-form';
                }

                $.post('/api/create__check', data, function(response){
                    if(response.success == true) {
                        document.location.href = '/check/' + response.check.id;
                    } else {
                        _this.errors = response.errors;
                        _this.loading = false;
                    }

                })
            }
        }
    });
</script>
