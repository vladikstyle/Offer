<?php

namespace app\modules\admin\controllers;

use app\models\Admin;
use app\modules\admin\components\Permission;
use app\modules\admin\forms\GiftItemsUploadForm;
use app\modules\admin\models\GiftCategory;
use app\modules\admin\models\GiftItem;
use dosamigos\grid\actions\ToggleAction;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii2mod\editable\EditableAction;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\controllers
 */
class GiftController extends \app\modules\admin\components\Controller
{
    /**
     * @var string
     */
    public $layout = '@app/modules/admin/views/gift/_layout.php';

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'toggle-category' => [
                'class' => ToggleAction::class,
                'modelClass' => GiftCategory::class,
                'scenario' => GiftCategory::SCENARIO_TOGGLE,
            ],
            'toggle-item' => [
                'class' => ToggleAction::class,
                'modelClass' => GiftItem::class,
                'scenario' => GiftItem::SCENARIO_TOGGLE,
            ],
            'editable-category' => [
                'class' => EditableAction::class,
                'modelClass' => GiftCategory::class,
            ],
            'editable-item' => [
                'class' => EditableAction::class,
                'modelClass' => GiftItem::class,
            ],
        ];
    }

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'permission' => [
                'class' => Permission::class,
                'roles' => [Admin::ROLE_ADMIN, Admin::ROLE_MODERATOR],
                'permission' => Permission::GIFTS,
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionCategories()
    {
        return $this->render('categories', [
            'dataProvider' => $this->giftManager->getCategoriesProvider(),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionCreateCategory()
    {
        $giftCategory = new GiftCategory();

        if ($giftCategory->load($this->request->post()) && $giftCategory->save()) {
            $this->session->setFlash('success',
                Yii::t('app', 'Gift category has been created')
            );
            if ($this->request->post('scan')) {
                $this->giftManager->scanDirectory($giftCategory->directory);
            }
            return $this->redirect(['update-category', 'id' => $giftCategory->id]);
        }

        return $this->render('create-category', [
            'giftCategory' => $giftCategory,
            'directories' => $this->giftManager->getDirectories(),
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateCategory($id)
    {
        $giftCategory = $this->findModel(['model' => GiftCategory::class, 'id' => $id]);

        if ($giftCategory->load($this->request->post()) && $giftCategory->save()) {
            $this->session->setFlash('success',
                Yii::t('app', 'Gift category has been updated')
            );
            return $this->refresh();
        }

        $itemsProvider = $this->giftManager->getItemsProvider($giftCategory);

        return $this->render('update-category', [
            'giftCategory' => $giftCategory,
            'itemsProvider' => $itemsProvider,
            'giftItemsUploadForm' => new GiftItemsUploadForm(),
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionScan()
    {
        $this->giftManager->scanDirectories();

        $this->session->setFlash('success', Yii::t('app', 'Directories have been scanned'));

        return $this->redirect(['categories']);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionScanCategory($id)
    {
        /** @var GiftCategory $category */
        $category = $this->findModel(['model' => GiftCategory::class, 'id' => $id]);
        $this->giftManager->scanDirectory($category->directory, $category);

        $this->session->setFlash('success', Yii::t('app', 'Category has been scanned'));

        return $this->redirect(['update-category', 'id' => $id]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionDeleteCategory($id)
    {
        $category = $this->findModel(['model' => GiftCategory::class, 'id' => $id]);
        if (!$this->giftManager->deleteGiftCategory($category)) {
            throw new \Exception('Could not delete gift category');
        }

        $this->session->setFlash('success', Yii::t('app', 'Gift category has been removed'));
        return $this->redirect(['categories']);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionDeleteItem($id)
    {
        /** @var GiftItem $item */
        $item = $this->findModel(['model' => GiftItem::class, 'id' => $id]);
        if (!$this->giftManager->deleteGiftItem($item)) {
            throw new \Exception('Could not delete gift item');
        }

        $this->session->setFlash('success', Yii::t('app', 'Gift item has been removed'));
        return $this->redirect(['update-category', 'id' => $item->category_id]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUploadItems($id)
    {
        $form = new GiftItemsUploadForm();
        $category = $this->findModel(['model' => GiftCategory::class, 'id' => $id]);

        if ($this->request->isPost) {
            $form->files = UploadedFile::getInstances($form, 'files');
            if ($form->upload($category)) {
                $this->session->setFlash('success', Yii::t('app', 'Gift items has been uploaded'));
            }
        }

        return $this->redirect(['update-category', 'id' => $category->id]);
    }
}
