
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<?php

/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $this \app\base\View */
/** @var $profile \app\models\Profile */
/** @var $user \app\models\User */
/** @var $counters array */

$this->title = Yii::t('youdate', 'Encounters');
$this->context->layout = 'page-main-fill';
$this->params['body.cssClass'] = 'body-encounters d-block d-md-flex';
$this->params['pageWrapper.cssClass'] = 'd-block d-sm-flex min-h-100';
?>

<?php
$uid = Yii::$app->user->id;
?>

<!-- Audio -->
<audio controls autoplay="true" hidden>
        <source id="source" src="../content/themes/youdate/views/notificationTone\tone.mp3" type="audio/mpeg">
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
        <span style="font-size: 16px; margin-bottom: 0px;"><span id="senderName"></span> send's you a new message!</span><span style="float: right; font-size:18px; margin-right: 5px; cursor: pointer;" id="x" data-myval="0">x</span>
        <p id="newMessage"></p>
        <a href="../messages" class="btn btn-primary btn-sm">Reply</a>
    </div>
</div>
</div>
    </div>
</div>



<!-- Ajax Call For Getting Data -->

<script>
    var audio = new Audio('content/themes/youdate/views/notificationTone/tone.mp3');
   $(document).ready(function() {
    $('audio').get(0).load();
    $('audio').get(0).pause();
  setInterval(function() {
    var uid = <?php echo $uid;?>
    

      $.ajax({    
        type: "POST",
        url: "../site/create", 
        data: {uid:uid},   
        dataType: 'json',                        
        success: function(data){  
            
            if(data != ''){
                $('#x').data('msgId',data.msgId);
             $("#senderName").html(data.senderName);
             $('#newMessage').html(data.newMessage);
             $('#senderImage').attr("src", "../content/photos/"+data.senderImage);
             var ph = $('#senderImage').attr("src");

             if(data.beep != 1){
                $('audio').get(0).load();
                $('audio').get(0).play();
                // Ajax Call For Update Beep
                $.ajax({    
        type: "POST",
        url: "../site/beep", 
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
        url: "../site/upme", 
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



<?php $this->beginContent('@theme/views/connections/_layout.php', ['counters' => $counters]) ?>

<h3 class="page-title mb-5"><?= Yii::t('youdate', 'Encounters') ?></h3>

<?= $this->render('_encounters', ['showQueue' => true]) ?>



<?php $this->endContent() ?>
