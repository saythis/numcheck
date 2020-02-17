<?php /* @var $this Controller */ ?>
<?php $profile = $this->_model->profile;?>


    <div class="profile-header">
        <div class="profile-cover">
            <div class="profile-cover-wrap">


                <img id="profile-bg-image" src="<?php echo $this->_model->getBgImage();?>" />
                <div class="profile-cover-border"></div>

                <div class="profile-header-name-wrap">
                    <h1>
                        <a>
                                <span><?php $this->_model->name?$this->_model->name:'(<small>';?>
                                    <?php echo $this->_model->name(); ?>
                                    <?php !$this->_model->name?'</small>)':'';?>
                                </span>

                            <div class="online-status">
                                <span ><?php echo $this->_model->getOnlineStatus(); ?></span></div>
                        </a>
                    </h1>
                </div>

                <?php if($this->_model->id==Yii::app()->user->id):?>
                    <div class="profile-bg-edit">

                        <span class="btn btn-success fileinput-button">
                            <i class="fa fa-pencil"></i>
                            <span>Изменить фоновую картинку</span>
                            <!-- The file input field used as target for the file upload widget -->
                            <input id="bgfileupload" type="file" name="file">
                        </span>

                        <!-- blueimp Gallery styles -->
                        <link rel="stylesheet" href="//blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
                        <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
                        <link rel="stylesheet" href="/css/fileupload/jquery.fileupload.css">
                        <link rel="stylesheet" href="/css/fileupload/jquery.fileupload-ui.css">
                        <!-- CSS adjustments for browsers with JavaScript disabled -->
                        <noscript><link rel="stylesheet" href="/css/fileupload/jquery.fileupload-noscript.css"></noscript>
                        <noscript><link rel="stylesheet" href="/css/fileupload/jquery.fileupload-ui-noscript.css"></noscript>


                        <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
                        <script src="/js/fileupload/jquery.iframe-transport.js"></script>
                        <!-- The basic File Upload plugin -->
                        <script src="/js/fileupload/jquery.fileupload.js"></script>

                        <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
                        <!--[if (gte IE 8)&(lt IE 10)]>
                        <script src="js/cors/jquery.xdr-transport.js"></script>
                        <![endif]-->

                        <script>

                            /*jslint unparam: true */
                            /*global window, $ */
                            $(function () {
                                'use strict';

                                $('#bgfileupload').fileupload({
                                    url: '/user/bgimg',
                                    dataType: 'json',
                                    formData: {YII_CSRF_TOKEN:csrf_name},
                                    done: function (e, data) {
                                        if(data.result.success==true) {
                                            $('#profile-bg-image').attr('src',data.result.data);
                                        } else {
                                            alertX(data.result.error_text);
                                        }

                                    },
                                    progressall: function (e, data) {

                                    }
                                }).prop('disabled', !$.support.fileInput)
                                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
                            });


                        </script>
                    </div>
                <?php endif;?>
            </div>
        </div>
        <div class="profile-submenu-wrap">
            <div class="profile-actions-wrap">
                <div class="profile-actions">
                    <div class="">
                        <div class="">
                            <div class="followers" title="Фанаты"><i class="fa fa-users"></i> <span id="followers-num"><?php echo $this->_model->profile->subscribers_count;?></span></div>
                        </div>
                        <div class="pull-right">
                            <div class="profile-vote-box">

                                <div class="profile-vote" id="votes-controls">
                                    <a href="#" class="pull-left" onclick="return voteUser(this, <?php echo $profile->user_id;?>, -1)"><span class=""><i class="fa fa-arrow-down fa-lg"></i></span></a>
                                    <div class="profile-vote-summary pull-left">
                                        <span class="" id="votes"><?php echo $profile->rating; ?></span>
                                        <p>рейтинг</p>
                                    </div>
                                    <a href="#" class="pull-right" onclick="return voteUser(this, <?php echo $profile->user_id;?>, 1)"><span class=""><i class="fa fa-arrow-up fa-lg"></i></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>

                    </div>

                    <?php  if(!Yii::app()->user->isGuest): ?>
                        <?php if(Yii::app()->user->id==$this->_model->id): ?>
                            <a href="/user/settings" class="btn btn-default">Редактировать</a>
                        <?php else:?>
                        <?php if($this->_model->isFollowing()):?>
                            <button class="btn btn-default" id="follow-button" data-act="-1" data-userid="<?php echo $this->_model->id;?>">Отписаться</button>
                        <?php else: ?>
                            <button class="btn btn-primary" id="follow-button" data-act="1" data-userid="<?php echo $this->_model->id;?>">Подписаться</button>
                        <?php endif;?>

                            <script>
                                $('#follow-button').click(function(){
                                    var target='';
                                    var user_id=$(this).data('userid');
                                    var btn = $(this);
                                    var after_text='';
                                    var act=$(this).data('act');
                                    if(act==1) {
                                        target='/user/follow';
                                        after_text='Отписаться';
                                    } else {
                                        target='/user/unfollow';
                                        after_text='Подписаться';
                                    }
                                    $.post(target,'user_id='+user_id+getCsrf(),function(data){
                                        if(data.success==true) {
                                            $(btn).text(after_text);
                                            $(btn).data('act',act*-1);
                                            $('#followers-num').text(data.subscribers);
                                        }
                                    });
                                });
                            </script>
                            <?php
                            $this->renderPartial('application.modules.message.views.message.write_a_message', array('model' => $this->_model));
                            ?>
                        <?php endif;?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-image">
                <div class="profile-image-container">
                    <a href="#">
                        <img src="<?php echo $this->_model->getAvatar(true, '_medium', true); ?>" />
                    </a>
                </div>
            </div>
            <div class="profile-submenu">
                <?php $this->widget('zii.widgets.CMenu', array(
                    'items'=>$this->getProfileMenu(),
                    'htmlOptions'=>['class'=>'list-inline list-unstyled']
                )); ?>

            </div>

        </div>
    </div>


    <div class="row">
        <div class="col-md-3">



            <div>
                <?php echo $this->_model->profile_detail->about;?>
            </div>
        </div>
    </div>
    <div id="profile-content">
        <?php echo $content;?>

    </div>
