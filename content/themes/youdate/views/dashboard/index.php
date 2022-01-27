<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<?php

use youdate\helpers\HtmlHelper;
use youdate\helpers\Icon;
use youdate\widgets\EmptyState;
use youdate\widgets\DirectoryListView;
use youdate\widgets\SpotlightWidget;
use app\helpers\Html;
use app\managers\LikeManager;

/** @var $this \app\base\View */
/** @var $newMembersDataProvider \yii\data\ActiveDataProvider */
/** @var $mutualOnline \app\models\User[] */
/** @var $user \app\models\User */
/** @var $profile \app\models\Profile */

$this->title = Yii::t('youdate', 'Dashboard');
$this->context->layout = 'page-main';
?>


<?php
$uid = $user->id;
?>

<!-- Audio -->
<audio controls autoplay="true" hidden>
        <source id="source" src="content/themes/youdate/views/notificationTone/tone.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

<!-- Notification -->
<div class="row" id="notify" style="display: none;">
    <div class="col-4"></div>
    <div class="col-4"></div>
    <div class="col-4">
    <div class="pop" style="background-color: gray; color:white;  padding: 5px; border-radius: 10px; position: fixed; z-index:999; bottom:20px; left:20px">
<div class="row">
    <div class="col-2">
        <img src="" id="senderImage" width="110px" height="50px" style="border-radius: 50%; margin-top: 10px;" alt="">
    </div>
    <div class="col-10">
        <span style="font-size: 16px; margin-bottom: 0px;">'<span id="senderName"></span>' send's you a new message!</span><span style="float: right; font-size:18px; margin-right: 5px; cursor: pointer;" id="x" data-myval="0">x</span>
        <p id="newMessage"></p>
        <a href="messages" class="btn btn-primary btn-sm">Reply</a>
    </div>
</div>
</div>
    </div>
</div>



<!-- Ajax Call For Getting Data -->

<script>
    var audio = new Audio('content/themes/youdate/views/notificationTone/tone.mp3');
   $(document).ready(function() {
  setInterval(function() {
    var uid = <?php echo $uid;?>
    

      $.ajax({    
        type: "POST",
        url: "site/create", 
        data: {uid:uid},   
        dataType: 'json',                        
        success: function(data){  
            
            if(data != ''){
                $('#x').data('msgId',data.msgId);
             $("#senderName").html(data.senderName);
             $('#newMessage').html(data.newMessage);
             $('#senderImage').attr("src", "content/photos/"+data.senderImage);
             var ph = $('#senderImage').attr("src");

             if(data.beep != 1){
                $('audio').get(0).load();
                $('audio').get(0).play();
                // Ajax Call For Update Beep
                $.ajax({    
        type: "POST",
        url: "site/beep", 
        data: {msgId:data.msgId},   
        dataType: 'json',                        
        success: function(data1){  
            console.log(data1)
        }
    });


             }
             $('#notify').show();
            

             
           
             
            //  $('#messageModal').modal('show');       
            console.log(data);
            }else{
                console.log("ERROR");
            } 
        }
    });
  }, 1000);
});
   
</script>

<!-- Close Notification -->
<script>
     
    $('#x').on('click', function(){
        var msgId = $('#x').data('msgId');
        var uuid = <?php echo $uid;?>

      $.ajax({    
        type: "POST",
        url: "site/upme", 
        data: {uid:uuid, msgId:msgId},   
        dataType: 'json',                        
        success: function(data){  
            $("#notify").hide();     
            console.log(data); 
        }
    });
    });

</script>


<!-- Notification -->

<div class="dashboard-block mb-3">
    <h3><?= Yii::t('youdate', 'Spotlight') ?></h3>
    <?= SpotlightWidget::widget([
        'count' => 9,
        'user' => $user,
        'profile' => $profile,
    ]) ?>
</div>



<?php if (isset($this->params['user.ads.hide']) && !$this->params['user.ads.hide']): ?>
    <div class="mb-3"><?= $this->themeSetting('adsHeader') ?></div>
<?php endif; ?>

<!-- <h1>HELLO <?php echo $user->id?></h1> -->


<div class="row row-eq-height mb-7">
    <div class="col-12 col-md-12 col-lg-7">
        <div class="dashboard-block dashboard-block-encounters">
            <h3><?= Yii::t('youdate', 'Encounters') ?></h3>
            <?= $this->render('//connections/_encounters', ['showQueue' => false]) ?>
            <?= Html::a(Yii::t('youdate', 'Play now'), ['/connections/encounters'], [
                'class' => 'btn btn-block btn-link text-gray mt-2',
            ]) ?>
        </div>
    </div>
    <div class="col-12 col-md-12 col-lg-5">
        <div class="dashboard-block dashboard-block-online h-100 d-flex flex-column">
            <h3><?= Yii::t('youdate', 'Your Matches') ?></h3>
            <div class="card d-flex flex-fill mb-0">
                <?php if (count($mutualOnline)): ?>
                    <ul class="list-group list-group-flush">
                    <?php foreach ($mutualOnline as $mutualOnlineUser): ?>
                        <li class="list-group-item d-flex flex-row align-items-center">
                            <div class="photo">
                                <div class="avatar avatar-md" style="background-image: url('<?= $mutualOnlineUser->profile->getAvatarUrl() ?>')">
                                    <?php if ($mutualOnlineUser->isOnline): ?>
                                        <span class="avatar-status bg-green"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info px-2">
                                <div class="first-line text-bolder">
                                    <?= Html::a(Html::encode($mutualOnlineUser->profile->getDisplayName()), ['/profile/view', 'username' => $mutualOnlineUser->username], [
                                        'class' => 'text-dark',
                                    ]) ?>
                                    <span class="ml-2" rel="tooltip" title="<?= $mutualOnlineUser->profile->getSexTitle() ?>">
                                        <?= HtmlHelper::sexToIcon($mutualOnlineUser->profile->sexModel) ?>
                                    </span>
                                    <?php if ($mutualOnlineUser->profile->is_verified): ?>
                                        <span rel="tooltip" title="<?= Yii::t('youdate', 'Verified') ?>">
                                            <?= Icon::fa('check', ['class' => 'ml-2']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="second-line">
                                    <span class="location text-muted">
                                        <?= Html::encode($mutualOnlineUser->profile->getDisplayLocation()) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="actions ml-auto">
                                <?= Html::button(Yii::t('youdate', 'Message'), [
                                    'data-toggle' => 'modal',
                                    'data-target' => '#profile-new-message',
                                    'data-contact-id' => $mutualOnlineUser->id,
                                    'data-title' => Yii::t('youdate', 'Message to {0}', [Html::encode($mutualOnlineUser->profile->getDisplayName())]),
                                    'class' => 'btn btn-sm btn-azure btn-pill btn-new-message',
                                ]) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?= EmptyState::widget([
                        'icon' => 'fe fe-users',
                        'title' => Yii::t('youdate', 'Users not found'),
                        'subTitle' => Yii::t('youdate', 'Seems like you have no mutual likes yet'),
                        'options' => ['class' => 'my-auto'],
                    ]) ?>
                <?php endif; ?>
            </div>
            <?= Html::a(Yii::t('youdate', 'View all'), ['/connections/likes', 'type' => LikeManager::TYPE_MUTUAL], [
                'class' => 'btn btn-block btn-link text-gray mt-2',
            ]) ?>
        </div>
    </div>
</div>

<div class="dashboard-block dashboard-block-newest">
    <h3><?= Yii::t('youdate', 'New members') ?></h3>
    <?php \yii\widgets\Pjax::begin(['id' => 'pjax-dashboard-list-view', 'options' => ['data-pjax-scroll-to' => '.dashboard-block-newest']]) ?>
        <?php if ($newMembersDataProvider->getTotalCount()): ?>
            <?= DirectoryListView::widget([
                'dataProvider' => $newMembersDataProvider,
                'itemView' => '_item_new',
                'itemOptions' => ['tag' => false],
            ]) ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <?= EmptyState::widget([
                        'icon' => 'fe fe-users',
                        'title' => Yii::t('youdate', 'Users not found'),
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>
    <?php \yii\widgets\Pjax::end() ?>
</div>




<?= $this->render('//profile/_message', ['newMessageForm' => new \app\forms\MessageForm()]) ?>











