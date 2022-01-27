<?php

namespace installer\forms;

use app\models\Admin;
use app\models\Profile;
use app\models\User;
use Yii;
use yii\base\Model;
use yii\base\Security;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package installer\forms
 */
class ConfigForm extends Model
{
    const MAILER_FILE = 'file';
    const MAILER_SENDMAIL = 'sendmail';
    const MAILER_SMTP = 'smtp';
    const MAILER_PHP_MAIL = 'mail';

    public $adminUsername = 'admin';
    public $adminEmail;
    public $adminPassword;
    public $adminPrefix = 'admin';
    public $appUrl;
    public $appMailerTransport;
    public $appMailerHost;
    public $appMailerUsername;
    public $appMailerPassword;
    public $appMailerPort = 465;
    public $appMailerEncryption = 'ssl';
    public $appMailFrom;
    public $facebookAppId;
    public $facebookAppSecret;
    public $twitterConsumerKey;
    public $twitterConsumerSecret;
    public $vkAppId;
    public $vkAppSecret;
    public $dbHost;
    public $dbUsername;
    public $dbPassword;
    public $dbDatabase;
    public $dbCharset;

    public $writableDirectories = [
        'application/runtime',
        'content/assets',
        'content/cache',
        'content/images',
        'content/pages',
        'content/photos',
    ];

    private $envTemplate;

    public function rules()
    {
        return [
            [['adminUsername', 'adminEmail', 'adminPassword'], 'required'],
            [['adminUsername'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username'],
            [['adminEmail'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
            [['adminUsername'], 'string', 'min' => 3],
            [['adminPassword'], 'string', 'min' => 6],
            [['adminEmail', 'appMailFrom'], 'email'],
            [['appUrl'], 'string', 'max' => 255],
            [['appMailerTransport', 'appMailFrom', 'adminPrefix'], 'required'],
            [['appMailerTransport', 'adminPrefix'], 'required'],
            [['appMailerTransport'], 'in', 'range' => [
                self::MAILER_FILE,
                self::MAILER_PHP_MAIL,
                self::MAILER_SENDMAIL,
                self::MAILER_SMTP,
            ]],
            [['appMailerHost', 'appMailerUsername', 'appMailerPassword', 'appMailerPort', 'appMailerEncryption'],
                'required',
                'when' => function(ConfigForm $model) {
                    return $model->appMailerTransport == self::MAILER_SMTP;
                }
            ],
            [['dbHost', 'dbDatabase', 'dbUsername', 'dbPassword', 'dbCharset'], 'safe'],
            [['facebookAppId', 'facebookAppSecret', 'twitterConsumerKey', 'twitterConsumerSecret', 'vkAppId', 'vkAppSecret'], 'safe'],
        ];
    }

    public function setTemplate($template)
    {
        $this->envTemplate = $template;
    }

    /**
     * @return static
     * @throws \yii\base\Exception
     */
    public function getConfig()
    {
        $vars = [];
        preg_match_all('^\{{(.*?)\}}^', $this->envTemplate, $vars);

        $config = $this->envTemplate;

        foreach ($vars[0] as $variable) {
            $attribute = str_replace('{', '', $variable);
            $attribute = str_replace('}', '', $attribute);
            if (isset($this->$attribute)) {
                $config = str_replace($variable, $this->$attribute, $config);
            }
            if ($attribute == 'generateRandom') {
                $random = (new Security())->generateRandomString(32);
                $config = str_replace($variable, $random, $config);
            }
        }

        return $config;
    }

    public function updateSettings()
    {
        $settings = Yii::$app->settings;

        if (!empty($this->facebookAppId)) {
            $settings->set('common', 'facebookEnabled', 1);
            $settings->set('common', 'facebookAppId', $this->facebookAppId);
            $settings->set('common', 'facebookAppSecret', $this->facebookAppSecret);
        }

        if (!empty($this->twitterConsumerKey)) {
            $settings->set('common', 'twitterEnabled', 1);
            $settings->set('common', 'twitterConsumerKey', $this->twitterConsumerKey);
            $settings->set('common', 'twitterConsumerSecret', $this->twitterConsumerSecret);
        }

        if (!empty($this->vkAppId)) {
            $settings->set('common', 'vkEnabled', 1);
            $settings->set('common', 'vkAppId', $this->vkAppId);
            $settings->set('common', 'vkAppSecret', $this->vkAppSecret);
        }
    }

    public function createUser()
    {
        $user = new User();
        $user->username = $this->adminUsername;
        $user->email = $this->adminEmail;
        $user->password = $this->adminPassword;
        $user->confirmed_at = time();
        $user->save();
        $user->refresh();

        $profile = $user->profile;
        $profile->status = Profile::STATUS_NOT_SET;
        $profile->is_verified = true;
        $profile->save(false);

        $admin = new Admin();
        $admin->link('user', $user);
    }

    public function makeWritable()
    {
        foreach ($this->writableDirectories as $directory) {
            $this->chmodRecursive(Yii::$app->params['basePath'] . '/' . $directory);
        }
    }

    /**
     * @param $path
     */
    private function chmodRecursive($path)
    {
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $item) {
            chmod($item->getPathname(), 0775);
            if ($item->isDir() && !$item->isDot()) {
                $this->chmodRecursive($item->getPathname());
            }
        }
    }
}
