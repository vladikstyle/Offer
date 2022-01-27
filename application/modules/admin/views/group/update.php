<?php

use app\helpers\Html;
use app\models\Group;
use app\modules\admin\widgets\CitySelector;
use app\modules\admin\widgets\CountrySelector;
use trntv\filekit\widget\Upload;
use yii\bootstrap\ActiveForm;

/** @var $this \yii\web\View */
/** @var $group \app\models\Group */

$this->title = Yii::t('app', 'Group info');
?>
<?php $this->beginContent('@app/modules/admin/views/group/_layout.php', ['group' => $group]) ?>

<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title"><?= Yii::t('app', 'Group info') ?></h2>
    </div>
    <div class="box-body">
        <?php $form = ActiveForm::begin([
            'id' => 'group-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
        ]); ?>

        <?= $form->field($group, 'title')->textInput(['autocomplete' => 'off']) ?>
        <?= $form->field($group, 'alias')->textInput(['autocomplete' => 'off']) ?>
        <?= $form->field($group, 'description')->textarea(['rows' => 10]) ?>
        <?= $form->field($group, 'visibility')->dropDownList([
            Group::VISIBILITY_VISIBLE => Yii::t('app', 'Public group'),
            Group::VISIBILITY_PRIVATE => Yii::t('app', 'Private group'),
            Group::VISIBILITY_BLOCKED => Yii::t('app', 'Blocked group'),
        ], ['prompt' => '']) ?>

        <div class="row">
            <div class="col-sm-12 col-md-6">
                <?= $form->field($group, 'country')->widget(CountrySelector::class) ?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?= $form->field($group, 'city')->widget(CitySelector::class, [
                    'items' => [],
                    'preloadedValue' => [
                        'value' => $group->city,
                        'title' => $group->getCityName(),
                        'city' => $group->getCityName(),
                        'region' => null,
                        'population' => null,
                    ],
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <?= $form->field($group, 'photo')->widget(Upload::class, [
                    'id' => 'photo-upload',
                    'url' => ['default/upload-photo'],
                    'multiple' => false,
                    'maxNumberOfFiles' => 1,
                ]) ?>
            </div>
            <div class="col-sm-12 col-md-6">
                <?= $form->field($group, 'cover')->widget(Upload::class, [
                    'id' => 'cover-upload',
                    'url' => ['default/upload-photo'],
                    'multiple' => false,
                    'maxNumberOfFiles' => 1,
                ]) ?>
            </div>
        </div>
        <?= Html::submitButton($group->isNewRecord ?
            Yii::t('app', 'Create') :
            Yii::t('app', 'Save'),
            ['class' => 'btn float-right btn-primary']) ?><br>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php $this->endContent() ?>
