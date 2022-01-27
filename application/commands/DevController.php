<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\VarDumper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\commands
 */
class DevController extends Controller
{
    public function actionExportDatabaseStructure()
    {
        $db = Yii::$app->db;
        $schema = $db->getSchema();
        $tableSchemas = $schema->getTableSchemas();
        $output = [];

        foreach ($tableSchemas as $tableSchema) {
            $output[$tableSchema->name] = $tableSchema->foreignKeys;
        }

        file_put_contents(
            Yii::getAlias('@app/data/foreign-keys.php'),
            '<?php return ' . VarDumper::export($output) . ";\n"
        );
    }
}
