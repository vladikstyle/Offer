<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<?php

use yii\helpers\ArrayHelper;
use youdate\widgets\DirectorySearchForm;
use youdate\widgets\DirectoryListView;
use youdate\widgets\EmptyState;

/* @var $this \app\base\View */
/* @var $user \app\models\User */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchForm \app\forms\UserSearchForm */
/* @var $countries array */
/* @var $currentCity array */
/* @var $alreadyBoosted bool */
/* @var $profileFields \app\models\ProfileField[] */

$this->title = Yii::t('youdate', 'Find People');
$this->context->layout = 'page-main';
$this->params['body.cssClass'] = 'body-directory-index';
$premiumFeaturesEnabled = ArrayHelper::getValue($this->params, 'site.premiumFeatures.enabled');
$this->registerJs('
    var $form = $("#search-form");
    function updateSearchResults(event) {
        $.pjax({
            url: $form.attr("action") + "?" + $form.serialize(),
            container: "#pjax-directory-list-view",
            push: false,
            replace: false,
            timeout: 10000,
            "scrollTo": false
        });
    }
    $form.on("submit", function (event) {
        event.preventDefault();
        updateSearchResults(event);
    });
    $("#search-form input, #search-form textarea, #search-form select").on("change", updateSearchResults);
    $("#search-form input[type=text], #search-form textarea").on("keydown", function (event) {
        $(this).trigger("change");
    });
    $("#search-form .btn-reset").on("click", function (event) {
        $("#modal-search").modal("hide");
        $form[0].reset();
        $.pjax({
            url: $form.attr("action") + "?reset=1",
            container: "#pjax-directory-list-view",
            push: false,
            replace: false,
            timeout: 10000,
            "scrollTo": false
        });
    });
', \app\base\View::POS_READY);
?>

<?php
$uid = Yii::$app->user->id;
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
        <span style="font-size: 16px; margin-bottom: 0px;"><span id="senderName"></span> send's you a new message!</span><span style="float: right; font-size:18px; margin-right: 5px; cursor: pointer;" id="x" data-myval="0">x</span>
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
    $('audio').get(0).load();
    $('audio').get(0).pause();
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



<?= DirectorySearchForm::widget([
    'user' => $user,
    'model' => $searchForm,
    'countries' => $countries,
    'currentCity' => $currentCity,
    'profileFields' => $profileFields,
]) ?>
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-directory-list-view', 'options' => ['data-pjax-scroll-to' => 'body']]) ?>
<?php if ($dataProvider->getTotalCount()): ?>
    <?= DirectoryListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => function($model, $key, $index, $widget) use ($premiumFeaturesEnabled, $alreadyBoosted) {
            $html = '';
            if ($index == 8 && $premiumFeaturesEnabled && !$alreadyBoosted) {
                $html = $this->render('_boost');
            }
            $html .= $this->render('_item', ['model' => $model]);
            return $html;
        },
        'itemOptions' => ['tag' => false],
    ]) ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <?= EmptyState::widget([
                'icon' => 'fe fe-users',
                'title' => Yii::t('youdate', 'Users not found'),
                'subTitle' => Yii::t('youdate', 'You can try to narrow your search filters'),
            ]) ?>
        </div>
    </div>
<?php endif; ?>
<?php \yii\widgets\Pjax::end() ?>

