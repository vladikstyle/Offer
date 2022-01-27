<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\models\Language;
use app\modules\admin\components\Permission;
use app\modules\admin\forms\NewPageForm;
use Yii;
use yii\helpers\FileHelper;
use yii\filters\VerbFilter;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class PageController extends \app\modules\admin\components\Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::PAGES,
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['post'],
                    'reset' => ['post'],
                    'create' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @param null $currentPage
     * @param null $language
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionIndex($currentPage = null, $language = null)
    {
        $pages = $this->getPages();
        if (count($pages) == 0) {
            $pages = $this->restore();
        }

        $currentFile = null;
        if ($currentPage !== null) {
            foreach ($pages as $page) {
                if (basename($page) == $currentPage) {
                    $currentFile = $page;
                }
            }
        }

        $languages = Language::getLanguageNames(true);
        $content = null;
        if ($currentFile !== null) {
            if ($language !== null && isset($languages[$language])) {
                $translatedFile = dirname($currentFile) . '/'. $language . '/' . basename($currentFile);
                if (!is_dir(dirname($translatedFile))) {
                    FileHelper::createDirectory(dirname($translatedFile));
                }
                if (!is_file($translatedFile)) {
                    copy($currentFile, $translatedFile);
                }
                $content = file_get_contents($translatedFile);
            } else {
                $content = file_get_contents($currentFile);
            }
        }

        return $this->render('index', [
            'pages' => $pages,
            'currentPage' => $currentPage,
            'content' => $content,
            'pagesEditable' => env('ADMIN_PAGES_EDITABLE'),
            'language' => $language,
            'languages' => $languages,
            'newPageForm' => Yii::createObject(NewPageForm::class),
        ]);
    }

    /**
     * @param $currentPage
     * @param null $language
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionSave($currentPage, $language = null)
    {
        if (!preg_match('~^\w(?:(?!\/\.{0,2}\/)[\w\/\-\.])*$~', $currentPage)) {
            $this->session->setFlash('error', Yii::t('app', 'Invalid page name'));
            return $this->redirect(['index', 'currentPage' => $currentPage]);
        }

        $file = Yii::getAlias('@content/pages' . '/' . $currentPage);

        if (!$this->isEditable()) {
            $this->session->setFlash('error', Yii::t('app', 'Page editing is turned off'));
            return $this->redirect(['index', 'currentPage' => $currentPage]);
        }

        if (!file_exists($file)) {
            $this->session->setFlash('error', Yii::t('app', 'Page not found'));
            return $this->redirect(['index', 'currentPage' => $currentPage]);
        }

        $languages = Language::getLanguageNames(true);
        if ($language !== null && isset($languages[$language])) {
            $directory = Yii::getAlias('@content/pages/' . $language);
            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }
            $file = $directory . '/' . $currentPage;
        }

        file_put_contents($file, $this->request->post('content'));
        $this->session->setFlash('success', Yii::t('app', 'Pages has been saved'));
        return $this->redirect(['index', 'currentPage' => $currentPage, 'language' => $language]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\base\ErrorException
     */
    public function actionReset()
    {
        if (!$this->isEditable()) {
            $this->session->setFlash('error', Yii::t('app', 'Page editing is turned off'));
            return $this->redirect(['index']);
        }

        foreach ($this->getPages() as $page) {
            FileHelper::unlink($page);
        }

        $languages = Language::getLanguageNames(true);
        foreach ($languages as $languageId => $language) {
            FileHelper::removeDirectory(Yii::getAlias('@content/pages/' . $languageId));
        }

        $this->restore();
        $this->session->setFlash('success', Yii::t('app', 'Pages have been restored from theme files'));
        return $this->redirect('index');
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        if (!$this->isEditable()) {
            $this->session->setFlash('error', Yii::t('app', 'Page editing is turned off'));
            return $this->redirect(['index']);
        }

        /** @var NewPageForm $newPageForm */
        $newPageForm = Yii::createObject(NewPageForm::class);
        if ($newPageForm->load($this->request->post()) && $newPageForm->validate()) {
            $file = $newPageForm->create();
            return $this->redirect(['index', 'currentPage' => basename($file)]);
        }

        return $this->redirect(['index']);
    }

    /**
     * @return array
     */
    protected function getPages()
    {
        return FileHelper::findFiles(Yii::getAlias('@content/pages'), ['only' => ['*.php'], 'recursive' => false]);
    }

    /**
     * @return array
     */
    protected function restore()
    {
        $sourcePages = FileHelper::findFiles(Yii::getAlias('@theme/views/site/pages'), ['only' => ['*.php']]);
        foreach ($sourcePages as $page) {
            copy($page, Yii::getAlias('@content/pages') . '/' . basename($page));
        }
        return $this->getPages();
    }

    /**
     * @return mixed
     */
    protected function isEditable()
    {
        return env('ADMIN_PAGES_EDITABLE');
    }
}
