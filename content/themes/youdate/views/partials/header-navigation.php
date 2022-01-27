<?php

use yii\helpers\ArrayHelper;
use youdate\widgets\HeaderNavigation;

$countersMessagesNew = ArrayHelper::getValue($this->params, 'counters.messages.new');
$groupsEnabled = ArrayHelper::getValue($this->params, 'site.groups.enabled', true);
?>
<div class="header collapse d-md-block p-0" id="header-navigation">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg order-lg-first">
                <?= HeaderNavigation::widget([
                    'options' => ['class' => 'nav nav-tabs border-0 d-flex flex-row justify-content-center justify-content-sm-start'],
                    'itemOptions' => [
                        'class' => 'nav-item',
                    ],
                    'items' => [
                        [
                            'label' => Yii::t('youdate', 'Dashboard'),
                            'url' => ['dashboard/index'],
                            'icon' => 'home'
                        ],
                        [
                            'label' => Yii::t('youdate', 'Browse'),
                            'url' => ['directory/index'],
                            'icon' => 'user'
                        ],
                        [
                            'label' => Yii::t('youdate', 'Groups'),
                            'active' => $this->context instanceof \app\controllers\GroupController,
                            'url' => ['group/index'],
                            'icon' => 'grid',
                            'visible' => $groupsEnabled,
                        ],
                        [
                            'label' => Yii::t('youdate', 'Connections'),
                            'active' => $this->context instanceof \app\controllers\ConnectionsController,
                            'url' => ['connections/encounters'],
                            'icon' => 'heart'
                        ],
                        [
                            'label' => Yii::t('youdate', 'Messages'),
                            'url' => ['messages/index'],
                            'icon' => 'mail',
                            'count' => $countersMessagesNew,
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
