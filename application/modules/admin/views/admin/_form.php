<?php

use app\models\Admin;
use app\modules\admin\helpers\Html;
use app\modules\admin\components\Permission;
use app\modules\admin\widgets\UserSearch;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $admin app\models\Admin */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-primary">
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false]); ?>
    <div class="box-header with-border">
        <h1 class="box-title"><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="box-body">
        <?= $form->errorSummary($admin) ?>

        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <?php if ($admin->isNewRecord): ?>
                    <?= $form->field($admin, 'user_id')->widget(UserSearch::class) ?>
                <?php else: ?>
                    <?= Html::activeHiddenInput($admin, 'user_id') ?>
                    <?= $form->field($admin, 'user_id')->staticControl(['value' => $admin->user->profile->getDisplayName()]) ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-12 col-sm-6">
                <?= $form->field($admin, 'role')->dropDownList(Admin::getRoleOptions()) ?>
            </div>
        </div>

        <?= $form->field($admin, 'permissionsArray')
            ->listBox(
                Permission::getPermissionsList(),
                array_merge(Html::getSelectedPermissions($admin->getPermissionsArray()), ['multiple' => true, 'style' => 'height: 250px'])
            )
            ->hint(Yii::t('app', 'Do not select anything if you want to grant all permissions'))
        ?>

        <div>
            <strong class="text-danger"><?= Yii::t('app', 'Warning!') ?></strong>
            <?= Yii::t('app', '"Pages" permission allows moderators to create and edit pages with raw PHP code') ?>
        </div>
    </div>
    <div class="box-footer">
        <?= Html::submitButton($admin->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'),
            ['class' => 'btn btn-primary']) ?>
        <?php if (!$admin->isNewRecord): ?>
            <?= Html::a( Yii::t('app', 'Delete'),
                ['admin/delete', 'id' => $admin->id],
                [
                    'class' => 'btn btn-danger',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('app', 'Are you sure want to delete this user from admins/moderators?'),
                ]
            ) ?>
        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
