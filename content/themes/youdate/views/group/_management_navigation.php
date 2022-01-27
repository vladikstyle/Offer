<?php

/** @var string $content */
/** @var \app\models\Group $group */

echo youdate\widgets\Sidebar::widget([
    'header' => false,
    'options' => [
        'class' => 'sidebar-menu list-group list-group-transparent mb-0',
    ],
    'items' => [
        [
            'label' => Yii::t('youdate', 'Group info'),
            'url' => ['group/management-update', 'alias' => $group->alias],
            'icon' => 'file',
        ],
        [
            'label' => Yii::t('youdate', 'Manage users'),
            'url' => ['group/management-users', 'alias' => $group->alias],
            'icon' => 'users',
        ],
    ],
]);
