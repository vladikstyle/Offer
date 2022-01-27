<?php

use app\models\Account;
use app\models\User;
use app\models\ProfileExtra;
use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210121_090000_v20 extends Migration
{
    public function safeUp()
    {
        $settings = Yii::$app->settings;

        $settings->set('common', 'photoMaxPerProfile', 50);
        $settings->set('frontend', 'siteGroupsEnabled', true);
        $settings->set('theme.youdate', 'darkModeUserOverride', true);
        $settings->set('theme.youdate', 'darkMode', app\helpers\DarkMode::AUTO);
        $settings->set('admin', 'adminMessagesOnlyActiveUsers', true);
        $settings->set('admin', 'adminMessagesOnlyReportedUsers', false);

        /** @var Account[] $socialAccounts */
        $socialAccounts = Account::find()
            ->joinWith(['user'])
            ->where('user.confirmed_at is null')
            ->groupBy('user_id')
            ->all();
        foreach ($socialAccounts as $account) {
            $account->user->updateAttributes(['confirmed_at' => time()]);
        }

        /** @var ProfileExtra[] $profileExtra */
        $profileExtra = ProfileExtra::find()
            ->joinWith('field')
            ->where(['field_class' => 'app\models\fields\MultiSelect'])
            ->all();
        try {
            foreach ($profileExtra as $extra) {
                if ($extra->value) {
                    $value = unserialize($extra->value);
                    $extra->value = json_encode($value);
                    $extra->save(false);
                }
            }
        } catch (\Exception $e) {
        }

        /**
         * Receive e-mail notifications for new messages by default
         */
        $users = User::find()->all();
        foreach ($users as $user) {
            $settings->set("user.$user->id", 'receiveEmailOnNewMessages', true);
        }
    }

    public function safeDown()
    {
    }
}
