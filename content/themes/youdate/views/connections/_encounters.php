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
                        <button class="btn btn-secondary btn-lg btn-like" ng-click="onEncounterAction('like')">
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
