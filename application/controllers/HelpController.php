<?php

namespace app\controllers;

use app\models\Help;
use app\models\HelpCategory;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class HelpController extends \app\base\Controller
{
    /**
     * @param null $category
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex($category = null)
    {
        $helpCategories = HelpCategory::find()->active()->sorted()->all();
        if (count($helpCategories) == 0) {
            throw new NotFoundHttpException();
        }

        if ($category == null) {
            $helpCategory = reset($helpCategories);
        } else {
            $helpCategory = HelpCategory::find()->active()->where(['help_category.alias' => $category])->one();
            if ($helpCategory == null) {
                throw new NotFoundHttpException();
            }
        }

        $helpItems = Help::find()->active()->sorted()->andWhere(['help.help_category_id' => $helpCategory->id])->all();

        return $this->render('index', [
            'category' => $category,
            'helpCategories' => $helpCategories,
            'helpCategory' => $helpCategory,
            'helpItems' => $helpItems,
        ]);
    }
}
