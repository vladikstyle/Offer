<?php

namespace app\commands;

use app\models\User;
use app\traits\managers\UserManagerTrait;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\commands
 */
class UserController extends \app\base\Command
{
    use UserManagerTrait;

    /**
     * @param $user
     * @param $password
     * @throws \yii\base\Exception
     */
    public function actionPassword($user, $password)
    {
        $model = User::find()->where(['or',
            ['id' => $user],
            ['username' => $user],
            ['email' => $user]
        ])->one();

        if ($model === null) {
            return $this->stdout("User not found\n");
        }

        $this->stdout(sprintf("- User #%d, %s %s\n", $model->id, $model->username, $model->email));
        if ($this->confirm('Change password?')) {
            $model->resetPassword($password);
        }
    }

    /**
     * @param $ip
     */
    public function actionUnban($ip)
    {
        if ($this->userManager->removeBanByIP($ip)) {
            $this->stdout("$ip has been removed from bans\n");
        } else {
            $this->stdout("$ip not found\n");
        }
    }
}
