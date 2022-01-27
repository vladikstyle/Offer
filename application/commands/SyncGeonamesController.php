<?php

namespace app\commands;

use app\models\Country;
use app\models\Geoname;
use Yii;
use yii\db\Connection;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Geonames data import. See https://github.com/codigofuerte/GeoNames-MySQL-DataImport
 *
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\commands
 */
class SyncGeonamesController extends Controller
{
    public $host;
    public $database;
    public $username;
    public $password;

    public function actionIndex()
    {
        if (isset($this->host)) {
            $this->host = env('DB_HOST');
        }
        if (!isset($this->database)) {
            $this->database = env('DB_DATABASE');
        }
        if (!isset($this->username)) {
            $this->username = env('DB_USERNAME');
        }
        if (!isset($this->password)) {
            $this->password = env('DB_PASSWORD');
        }

        $config = $this->getDsnConfig($this->host, $this->database, $this->username, $this->password);

        /** @var Connection $db */
        $db = Yii::createObject($config);

//        $this->syncCountriesTranslations($db);
        $this->syncGeonamesTranslations($db);
    }


    /**
     * @param $actionID
     * @return array|string[]
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'host',
            'database',
            'username',
            'password',
        ]);
    }

    /**
     * @param $host
     * @param $database
     * @param $username
     * @param null $password
     * @return array
     */
    private function getDsnConfig($host, $database, $username, $password = null)
    {
        return [
            'class' => Connection::class,
            'dsn' => sprintf('mysql:host=%s;dbname=%s', $host, $database),
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'enableSchemaCache' => false,
            'attributes' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));",
            ],
        ];
    }

    private function syncCountriesTranslations(Connection $db)
    {
        /** @var Country[] $countries */
        $countries = Country::find()->all();

        $readCmd = (new Query())
            ->select('*')
            ->from('alternatename')
            ->where("geonameid = :geonameId and isHistoric = 0 and isColloquial = 0 and isoLanguage <> '' and isoLanguage <> 'link'")
            ->groupBy('isoLanguage')
            ->addParams([':geonameId' => null])
            ->createCommand($db);

        $i = 0;
        $counter = 0;
        Console::startProgress($i, count($countries), 'Countries Translation');
        foreach ($countries as $country) {
            $readCmd->bindValue('geonameId', $country->geoname_id);
            $rows = $readCmd->queryAll();
            foreach ($rows as $row) {
                try {
                    Yii::$app->db->createCommand()
                        ->upsert('country_translation', [
                            'country' => $country->country,
                            'language' => $row['isoLanguage'],
                            'translation' => $row['alternateName'],
                        ])->execute();
                    $counter++;
                } catch (\Exception $e) {
                    $this->stderr($e->getMessage() . "\n");
                }
            }
            Console::updateProgress($i, count($countries), 'Countries Translation');
            $i++;
        }
        Console::endProgress(true, false);

        $this->stdout("Done $counter\n");
    }

    private function syncGeonamesTranslations(Connection $db)
    {
        $totalGeonamesCount = Geoname::find()->count();
        $geonamesQuery =  (new Query())
            ->select('geoname_id')
            ->from('geoname');

        $readCmd = (new Query())
            ->select('*')
            ->from('alternatename')
            ->where("geonameid = :geonameId and isHistoric = 0 and isColloquial = 0 and isoLanguage <> ''")
            ->andWhere(['not in', 'isoLanguage', [
                'unlc',
                'wkdt',
                'iata',
                'link',
                'post',
            ]])
            ->groupBy('isoLanguage')
            ->addParams([':geonameId' => null])
            ->createCommand($db);

        $updateCmd = Yii::$app->db->createCommand();

        $i = 0;
        $counter = 0;
        Console::startProgress($i, $totalGeonamesCount, 'Geonames Translations');
        foreach ($geonamesQuery->batch(500) as $geonames) {
            foreach ($geonames as $geoname) {
                $readCmd->bindValue('geonameId', $geoname['geoname_id']);
                $rows = $readCmd->queryAll();
                foreach ($rows as $row) {
                    try {
                        $language = explode('-', $row['isoLanguage']);
                        $language = $language[0];
                        $updateCmd->upsert('geoname_translation', [
                            'geoname_id' => $geoname['geoname_id'],
                            'language' => $language,
                            'name' => $row['alternateName'],
                        ])->execute();
                        $counter++;
                    } catch (\Exception $e) {
                        $this->stderr($e->getMessage() . "\n");
                    }
                }
                $i++;
            }
            Console::updateProgress($i, $totalGeonamesCount, 'Geonames Translations');
        }
        Console::endProgress(true, false);

        $this->stdout("Done $counter\n");
    }
}
