<?php

use youdate\helpers\Icon;

/** @var $this \app\base\View */
/** @var $showQueue bool */

$this->registerAssetBundle(\youdate\assets\EncountersAsset::class);
?>
<div class="encounters d-flex flex-fill" ng-app="youdateEncounters" ng-controller="EncountersController as $ctrl">
    <div class="encounters-wrapper d-flex w-100 flex-column flex-fill flex-md-row">
        <div class="card">
            <?= $this->render('_encounters_loader') ?>
            <?= $this->render('_encounters_empty') ?>
            <div class="d-flex flex-fill flex-column flex-md-row align-items-stretch ng-hide mh-100" ng-show="initialStateLoaded === true && hasEncounters()">
                <div class="encounters-photo flex-fill">
                    <?= $this->render('_encounters_carousel') ?>
                    <div class="encounters-controls">
                        <button class="btn btn-secondary btn-lg btn-like" id="skipThis" data-Uid="{{ currentEncounter.user.id }}" ng-click="onEncounterAction('like')">
                            <?= Icon::fa('heart') ?>
                        </button>
                        <button class="btn btn-secondary btn-lg btn-skip" ng-click="onEncounterAction('skip')">
                            <?= Icon::fa('close') ?>
                        </button>
                    </div>
                </div>
                <div class="encounters-info ml-0 ml-md-auto">
                    <?= $this->render('_encounters_profile') ?>
                </div>
            </div>
        </div>
        <?php if ($showQueue): ?>
            <?= $this->render('_encounters_queue') ?>
        <?php endif; ?>
    </div>
    <?= $this->render('_encounters_mutual') ?>
</div>
<?php
$userId = Yii::$app->user->id;
?>

<script>
    var userId = <?php echo $userId?>;
    var limit = 0;
    $('#skipThis').click(function(){
    var encounterUser = $(this).attr("data-Uid");
    limit += 1;
    if(limit == 4){
        $.ajax({    
        type: "POST",
        url: "site/matchpop",   
        data: {uid: userId, encId: encounterUser},          
        dataType: "json",                  
        success: function(data){                    
            // Swal.fire(data)
            // Active User
            $('#activeImage').attr("src", "content/photos/"+data.ActiveImage);
            $('#ActiveName').html(data.ActiveName);
            // Match User
            $('#matchImage').attr("src", "content/photos/"+data.MatchImage);
            $('#matchName').html(data.MatchName);
            // Match Chat Link
            $('#matchChat').attr("href", "/profile/"+data.MatchUserName);
            // $("#matchModal").modal('show');
            console.log(data);
           
        }
    });
        limit = 0;
    }
}); 
</script>



<!-- Modal -->
<div class="modal fade" id="matchModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Hi, We have found a match for you</h5>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-5">
                <img src="" alt="" id="activeImage" width="100px" height="100px"  style="border-radius: 50%; margin-left: 23%;">
                <h5 style="text-align: center;" id="ActiveName"></h5>
            </div>
            <div class="col-2">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f1/Heart_coraz%C3%B3n.svg/1200px-Heart_coraz%C3%B3n.svg.png" alt="" width="100%" style="margin-top: 30%;">
            </div>
            <div class="col-5">
            <img src="" alt="" id="matchImage" width="100px" height="100px"  style="border-radius: 50%; margin-left: 23%;">
                <h5 style="text-align: center;" id="matchName"></h5>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="" id="matchChat" class="btn btn-primary">See Profile</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>