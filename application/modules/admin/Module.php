<?php

namespace app\modules\admin;

use app\base\Event;
use app\models\LanguageTranslate;
use app\modules\admin\components\translations\scanners\ScannerPhpFunction;
use app\modules\admin\models\GiftCategory;
use app\modules\admin\models\GiftItem;
use app\traits\CacheTrait;
use app\traits\CurrentUserTrait;
use trntv\filekit\widget\UploadAsset;
use Yii;
use yii\i18n\DbMessageSource;
use yii\web\ErrorHandler;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin
 */
class Module extends \yii\base\Module
{
    use CacheTrait, CurrentUserTrait;

    const EVENT_BEFORE_INIT = 'beforeInit';
    const EVENT_AFTER_INIT = 'afterInit';

    /**
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        // disallow guests
        if (Yii::$app->user->isGuest) {
            throw new NotFoundHttpException();
        }

        $event = new \app\base\Event();
        $this->trigger(self::EVENT_BEFORE_INIT, $event);

        if (!isset($this->getCurrentUser()->admin)) {
            throw new ForbiddenHttpException('Access denied.');
        }

        // override default error handler
        $handler = new ErrorHandler(['errorAction' => env('ADMIN_PREFIX') . '/default/error']);
        Yii::$app->set('errorHandler', $handler);
        $handler->register();

        // translation manager
        $this->components = [
            'translations' => [
                'class' => \app\modules\admin\components\Translations::class,
                'root' => '@app',
                'scanRootParentDirectory' => true,
                'tmpDir' => '@runtime',
                'phpTranslators' => ['::t'],
                'patterns' => ['*.php'],
                'ignoredCategories' => ['yii'],
                'ignoredItems' => ['config'],
                'scanTimeLimit' => null,
                'searchEmptyCommand' => '!',
                'defaultExportStatus' => 1,
                'defaultExportFormat' => 'json',
                'scanners' => [
                    ScannerPhpFunction::class,
                ],
            ]
        ];

        Yii::$app->assetManager->bundles[UploadAsset::class] = [
            'class' => \app\modules\admin\assets\UploadAsset::class,
        ];

        Event::on(LanguageTranslate::class, LanguageTranslate::EVENT_AFTER_UPDATE, [$this, 'deleteLanguageCache']);
        Event::on(LanguageTranslate::class, LanguageTranslate::EVENT_AFTER_INSERT, [$this, 'deleteLanguageCache']);
        Event::on(LanguageTranslate::class, LanguageTranslate::EVENT_AFTER_DELETE, [$this, 'deleteLanguageCache']);

        Event::on(GiftItem::class, GiftItem::EVENT_AFTER_UPDATE, [Yii::$app->giftManager, 'deleteCache']);
        Event::on(GiftItem::class, GiftItem::EVENT_AFTER_INSERT, [Yii::$app->giftManager, 'deleteCache']);
        Event::on(GiftItem::class, GiftItem::EVENT_AFTER_DELETE, [Yii::$app->giftManager, 'deleteCache']);
        Event::on(GiftItem::class, GiftCategory::EVENT_AFTER_UPDATE, [Yii::$app->giftManager, 'deleteCache']);
        Event::on(GiftItem::class, GiftCategory::EVENT_AFTER_INSERT, [Yii::$app->giftManager, 'deleteCache']);
        Event::on(GiftItem::class, GiftCategory::EVENT_AFTER_DELETE, [Yii::$app->giftManager, 'deleteCache']);

        $this->trigger(self::EVENT_AFTER_INIT, $event);
    }

    /**
     * @param $event \yii\base\Event
     */
    public function deleteLanguageCache($event)
    {
        /** @var LanguageTranslate $model */
        $model = $event->sender;
        $this->cache->delete([
            DbMessageSource::class,
            $model->languageSource->category,
            $model->language
        ]);
    }
}
