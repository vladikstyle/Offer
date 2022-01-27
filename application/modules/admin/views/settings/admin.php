<?php

use app\helpers\Html;
use app\helpers\Url;
use app\models\Admin;
use app\settings\SettingsForm;
use yii\grid\GridView;

/** @var $settingsManager \app\settings\SettingsManager */
/** @var $settingsModel \app\settings\SettingsModel */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $this \yii\web\View */
?>

<?php $this->beginContent('@app/modules/admin/views/settings/_layout.php') ?>

<?= SettingsForm::widget([
    'manager' => $settingsManager,
    'model' => $settingsModel,
    'formView' => '@app/modules/admin/views/partials/settings_form',
    'title' => Yii::t('app', 'Admin area settings'),
]) ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Admins and moderators') ?></h3>
        <div class="box-tools">
            <?= Html::a(Yii::t('app', 'Add'), ['admin/create'], ['class' => ['btn btn-primary btn-sm']]) ?>
        </div>
    </div>
    <div class="box-body no-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-vcenter'],
            'columns' => [
                [
                    'attribute' => 'username',
                    'label' => Yii::t('app', 'User'),
                    'format' => 'raw',
                    'value' => function(Admin $admin) {
                        return $this->render('/partials/_user_column', [
                            'user' => $admin->user,
                        ]);
                    }
                ],
                [
                    'attribute' => 'role',
                    'format' => 'raw',
                    'value' => function (Admin $admin) {
                        if ($admin->role == Admin::ROLE_ADMIN) {
                            $cssClass = 'bg-red';
                        } elseif ($admin->role == Admin::ROLE_MODERATOR) {
                            $cssClass = 'bg-orange';
                        } else {
                            $cssClass = 'bg-gray';
                        }
                        return Html::tag('span', $admin->role, ['class' => 'badge ' . $cssClass]);
                    },
                ],
                [
                    'attribute' => 'permissions',
                    'format' => 'raw',
                    'value' => function (Admin $admin) {
                        $permissions = $admin->getPermissionsArray();
                        if (!count($permissions) || $admin->role == Admin::ROLE_ADMIN) {
                            return Html::tag('span', Yii::t('app', 'All'), ['class' => 'badge bg-blue']);
                        }
                        $html = '';
                        foreach ($permissions as $permission) {
                            $html .= Html::tag('span', $permission, ['class' => 'badge bg-green mr-1']);
                        }
                        return $html;
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'contentOptions' => ['width' => 100, 'class' => 'text-right'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            $url = Url::to(['admin/update', 'id' => $model->id]);
                            return Html::a('<span class="fa fa-pencil"></span>', $url, [
                                'title' => Yii::t('app', 'Update'),
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-primary',
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            $url = Url::to(['admin/delete', 'id' => $model->id]);
                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to delete this user from admins/moderators?'),
                                'class' => 'btn btn-sm btn-danger',
                            ]);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>

<?php $this->endContent() ?>
