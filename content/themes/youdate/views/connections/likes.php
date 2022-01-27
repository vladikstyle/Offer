<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<?php

use youdate\helpers\Icon;
use youdate\widgets\DirectoryListView;
use app\helpers\Html;

/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $type string */
/** @var $this \app\base\View */
/** @var $counters array */
/** @var $likesLocked boolean */

$this->title = Yii::t('youdate', 'Likes');
$this->context->layout = 'page-main';

$this->beginContent('@theme/views/connections/_layout.php', [
    'counters' => $counters,
]);
?>

<?php
$uid = $user->id;
?>

<!-- Notification -->
<div class="row" id="notify" style="display: none;">
    <div class="col-md-4"></div>
    <div class="col-md-4"></div>
    <div class="col-md-4">
    <div style="background-color: gray; color:white;  padding: 5px; border-radius: 10px; position: fixed; z-index:999; bottom:20px; right:20px">
<div class="row">
    <div class="col-md-2">
        <img src="" id="senderImage" width="110px" height="50px" style="border-radius: 50%; margin-top: 10px;" alt="">
    </div>
    <div class="col-md-10">
        <span style="font-size: 16px; margin-bottom: 0px;">'<span id="senderName"></span>' send's you a new message!</span><span style="float: right; font-size:18px; margin-right: 5px; cursor: pointer;" id="x" data-myval="0">x</span>
        <p id="newMessage"></p>
        <a href="" class="btn btn-primary btn-sm">Reply</a>
    </div>
</div>
</div>
    </div>
</div>



<!-- Ajax Call For Getting Data -->

<script>
   $(document).ready(function() {
  setInterval(function() {
    var uid = <?php echo $uid;?>

      $.ajax({    
        type: "POST",
        url: "site/create", 
        data: {uid:uid},   
        dataType: 'json',                        
        success: function(data){  
            $('#x').data('msgId',data.msgId);
             $("#senderName").html(data.senderName);
             $('#newMessage').html(data.newMessage);
             $('#senderImage').attr("src", "content/photos/"+data.senderImage);
             var ph = $('#senderImage').attr("src");

             $('#notify').show();
            //  $('#messageModal').modal('show');       
            console.log(data); 
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


<?php if ($likesLocked): ?>
<div class="card">
    <div class="card-bg card-bg-purple"></div>
    <div class="card-body d-flex align-items-center">
        <?= Icon::fa('lock', ['class' => 'text-yellow mr-2']) ?>
        <h4 class="text-gray font-weight-normal mb-0"><?= Yii::t('youdate', 'You need premium account to unlock this page') ?></h4>
        <?= Html::a(Yii::t('youdate', 'Premium settings'),
            ['balance/services'],
            ['class' => 'btn btn-primary ml-auto']
        ) ?>
    </div>
</div>
<?php endif; ?>

<?= DirectoryListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => $likesLocked ? '_item_locked' : '_item',
    'itemOptions' => ['tag' => false],
    'emptyView' => '_empty_likes',
    'emptyViewParams' => [
        'type' => $type,
    ],
]) ?>

<?php $this->endContent() ?>
