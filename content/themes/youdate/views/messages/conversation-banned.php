<?php

use youdate\helpers\Icon;

?>
<div class="conversation-banned-contact d-flex justify-content-center align-items-center h-100"
     style="min-height: 200px"
     ng-show="currentContactBanned">
    <div class="m-auto text-center">
        <div class="mb-5 mt-5">
            <h4><?= Yii::t('youdate', 'User not found') ?></h4>
            <div class="text-muted"><?= Yii::t('youdate', 'Profile deleted or banned') ?></div>
        </div>
        <div class="mb-5">
            <button type="button" ng-click="deleteConversation()" class="btn btn-danger btn-block">
                <?= Icon::fe('trash', ['class' => 'mr-2']) ?>
                <strong><?= Yii::t('youdate', 'Delete conversation') ?></strong>
            </button>
            <button type="button" ng-click="toggleConversations()" class="btn btn-primary d-md-none mt-3 mb-3">
                <?= Icon::fe('users', ['class' => 'mr-2']) ?>
                <?= Yii::t('youdate', 'Show conversations') ?>
            </button>
        </div>
    </div>
</div>
