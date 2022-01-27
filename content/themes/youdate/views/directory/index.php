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

