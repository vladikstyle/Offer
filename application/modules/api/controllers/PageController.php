<?php

namespace app\modules\api\controllers;

use app\modules\api\components\ApiResult;
use app\modules\api\components\Controller;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\controllers
 */
class PageController extends Controller
{
    public function actionView($alias)
    {
        $content = $this->renderFile($this->resolveViewName());

        return ApiResult::create()->withData([
            'title' => $this->view->title,
            'alias' => $alias,
            'content' => $content,
        ]);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function resolveViewName()
    {
        $viewName = $this->request->get('alias');
        if (!is_string($viewName) || !preg_match('~^\w(?:(?!\/\.{0,2}\/)[\w\/\-\.])*$~', $viewName)) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested view "{name}" was not found.', ['name' => $viewName]));
        }

        $files = [
            Yii::getAlias("@content/pages/$viewName.php"),
            Yii::getAlias("@theme/views/site/pages/$viewName.php"),
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        throw new NotFoundHttpException(Yii::t('youdate', 'Page not found'));
    }
}
