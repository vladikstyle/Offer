<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use app\helpers\Url;
use app\helpers\Html;
use app\models\Language;

/* @var $this \yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\admin\models\search\LanguageSearch */

$this->title = Yii::t('app', 'Manage languages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box no-border">
    <?php Pjax::begin(['id' => 'languages']) ?>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-vcenter'],
            'rowOptions' => function(Language $model, $key, $index, $grid) {
                if ($model->status == Language::STATUS_INACTIVE) {
                    return ['class' => 'language-inactive'];
                }

                return [];
            },
            'columns' => [
                ['class' => \yii\grid\SerialColumn::class],
                [
                    'attribute' => 'language_id',
                    'format' => 'raw',
                    'contentOptions' => ['width' => 120],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off'],
                    'value' => function(Language $model) {
                        return sprintf('<span class="label label-%s">%s</span>',
                            $model->status == Language::STATUS_INACTIVE ? 'default' : 'primary',
                            $model->language_id
                        );
                    }
                ],
                [
                    'attribute' => 'name_ascii',
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off'],
                ],
                [
                    'format' => 'raw',
                    'filter' => Language::getStatusNames(),
                    'attribute' => 'status',
                    'contentOptions' => ['width' => 120],
                    'filterInputOptions' => ['class' => 'form-control', 'id' => 'status'],
                    'label' => Yii::t('app', 'Status'),
                    'content' => function ($language) {
                        return Html::activeDropDownList($language, 'status', Language::getStatusNames(), [
                            'class' => 'form-control status',
                            'id' => $language->language_id,
                            'data-url' => Url::to(['change-status']),
                        ]);
                    },
                ],
                [
                    'format' => 'raw',
                    'attribute' => Yii::t('app', 'Stats'),
                    'value' => function(Language $language) {
                        return $this->render('_col_stats', [
                            'value' => $language->gridStatistic,
                        ]);
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {translate} {delete}',
                    'contentOptions' => ['class' => 'text-right'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-pencil"></span>', $url, [
                                'title' => Yii::t('app', 'Update'),
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-default',
                            ]);
                        },
                        'translate' => function ($url, Language $model, $key) {
                            return Html::a('<span class="fa fa-list-alt"></span>',
                                ['translate', 'language_id' => $model->language_id],
                                ['title' => Yii::t('app', 'Translate'),
                                    'data-pjax' => 0,
                                    'class' => 'btn btn-sm btn-primary',
                                ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="fa fa-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('app', 'Are you sure want to delete this user?'),
                                'class' => 'btn btn-sm btn-danger',
                            ]);
                        },
                    ],
                ],
            ],
        ]) ?>
    </div>
    <?php Pjax::end() ?>
</div>
