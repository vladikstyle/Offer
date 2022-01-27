<?php

namespace app\bootstrap;

use app\commands\CronController;
use app\components\url\UrlManager;
use app\models\DataRequest;
use app\models\Encounter;
use app\models\Notification;
use app\models\Order;
use app\models\Upload;
use app\models\User;
use app\models\UserBoost;
use app\models\UserPremium;
use app\modules\admin\components\AppStatus;
use Yii;
use yii\base\Event;
use yii\console\Application;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\bootstrap
 */
class ConsoleBootstrap extends CoreBootstrap
{
    /**
     * @var bool
     */
    public $minimalBootstrap = false;

    /**
     * @param $app Application
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        // Setup aliases
        Yii::setAlias('@web', '/');
        Yii::setAlias('@webroot', Yii::getAlias('@app') . '/../');
        Yii::setAlias('@content', Yii::getAlias('@webroot/content'));

        if ($this->minimalBootstrap == true) {
            return;
        }

        // Setup website URL (for console commands, cron and queue processing)
        $siteUrl = env('APP_URL');
        if ($siteUrl === false) {
            $siteUrl = $app->settings->get('common', 'siteUrl');
        }
        $siteUrl = rtrim($siteUrl, '/') . '/';
        Yii::$container->set(UrlManager::class, [
            'baseUrl' => $siteUrl,
            'hostInfo' => $siteUrl,
        ]);

        // Delete unlinked photos
        Event::on(CronController::class, CronController::EVENT_ON_HOURLY_RUN, function(Event $event) {
            $controller = $event->sender;
            $photos = Upload::deleteAll('(unix_timestamp() - created_at) > 3600');
            $controller->stdout(sprintf("- Removed %d unlinked photos\n", $photos));

            // check app status
            AppStatus::resetStatus();
        });

        // Delete expired search boosts
        Event::on(CronController::class, CronController::EVENT_ON_HOURLY_RUN, function(Event $event) {
            $controller = $event->sender;
            $expiredBoosts = UserBoost::deleteAll('boosted_until < unix_timestamp()');
            $controller->stdout(sprintf("- Removed %d expired search boosts\n", $expiredBoosts));
        });

        // Auto boost premium users daily
        Event::on(CronController::class, CronController::EVENT_ON_DAILY_RUN, function(Event $event) {
            $controller = $event->sender;
            $premiumUsers = User::find()->premiumOnly()->all();
            foreach ($premiumUsers as $user) {
                $boostDuration = Yii::$app->balanceManager->getBoostDuration();
                UserBoost::boostUser($user->id, $boostDuration);
            }
            $controller->stdout(sprintf("- Boosted %d premium users\n", count($premiumUsers)));
        });

        // Delete expired premiums
        Event::on(CronController::class, CronController::EVENT_ON_HOURLY_RUN, function(Event $event) {
            $controller = $event->sender;
            $expiredPremiums = UserPremium::deleteAll('premium_until < unix_timestamp()');
            $controller->stdout(sprintf("- Removed %d expired premiums\n", $expiredPremiums));
        });

        // Delete expired encounters
        Event::on(CronController::class, CronController::EVENT_ON_DAILY_RUN, function(Event $event) {
            $controller = $event->sender;
            $expiredEncountersThreshold = Yii::$app->params['expiredEncountersThreshold'];
            $expiredPremiums = Encounter::deleteAll(
                "created_at < unix_timestamp(date_sub(now(), interval $expiredEncountersThreshold day))"
            );
            $controller->stdout(sprintf("- Removed %d expired encounters\n", $expiredPremiums));
        });

        // Delete expired notifications
        Event::on(CronController::class, CronController::EVENT_ON_DAILY_RUN, function(Event $event) {
            $controller = $event->sender;
            $expiredNotificationsThreshold = Yii::$app->params['expiredNotificationsThreshold'];
            $expiredPremiums = Notification::deleteAll(
                "created_at < unix_timestamp(date_sub(now(), interval $expiredNotificationsThreshold day))"
            );
            $controller->stdout(sprintf("- Removed %d old notifications\n", $expiredPremiums));
        });

        // Delete expired data requests
        Event::on(CronController::class, CronController::EVENT_ON_DAILY_RUN, function(Event $event) {
            $controller = $event->sender;
            $expiredDataRequestsThreshold = Yii::$app->params['expiredDataRequestsThreshold'];
            $expiredDataRequests = DataRequest::deleteAll(
                "created_at < unix_timestamp(date_sub(now(), interval $expiredDataRequestsThreshold day)) and status = :status", [
                    'status' => DataRequest::STATUS_DONE,
                ]
            );
            $controller->stdout(sprintf("- Removed %d expired data requests\n", $expiredDataRequests));
        });

        // Mark expired new orders (>20min) as cancelled
        Event::on(CronController::class, CronController::EVENT_ON_DAILY_RUN, function(Event $event) {
            $controller = $event->sender;
            $orders = Order::find()
                ->andWhere(['in', 'status', [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS]])
                ->andWhere('(unix_timestamp() - created_at) > 86400 * 7') // 7 days
                ->all();

            foreach ($orders as $order) {
                $order->status = Order::STATUS_CANCELLED;
                $order->save();
            }

            $controller->stdout(sprintf("- Cancelled %d expired orders\n", count($orders)));
        });
    }
}
