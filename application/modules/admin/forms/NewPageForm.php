<?php

namespace app\modules\admin\forms;

use app\helpers\Html;
use Yii;
use yii\base\Model;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\forms
 */
class NewPageForm extends Model
{
    /**
     * @var string
     */
    public $pageTitle;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $templateFile = '@app/data/page.php';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pageTitle', 'fileName'], 'required'],
            [['pageTitle', 'fileName'], 'string', 'max' => 64],
            ['fileName', 'match', 'pattern' => '/^[a-zA-Z0-9-]+$/'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'pageTitle' => Yii::t('app', 'Page title'),
            'fileName' => Yii::t('app', 'File name'),
        ];
    }

    /**
     * @return bool|string
     */
    public function create()
    {
        $file = Yii::getAlias('@content/pages/' . $this->fileName . '.php');
        $content = file_get_contents(Yii::getAlias($this->templateFile));
        $content = str_replace('{{pageTitle}}', Html::encode($this->pageTitle), $content);

        file_put_contents($file, $content);

        return $file;
    }
}
