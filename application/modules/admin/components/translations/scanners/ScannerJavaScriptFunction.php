<?php

namespace app\modules\admin\components\translations\scanners;

use yii\helpers\Console;
use app\modules\admin\components\translations\Scanner;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\components\translations\scanners
 */
class ScannerJavaScriptFunction extends ScannerFile
{
    /**
     * Extension of JavaScript files.
     */
    const EXTENSION = '*.js';

    /**
     * Start scanning JavaScript files.
     *
     * @param string $route
     * @param array $params
     * @inheritdoc
     */
    public function run($route, $params = [])
    {
        $this->scanner->stdout('Detect JavaScriptFunction - BEGIN', Console::FG_YELLOW);
        foreach (self::$files[static::EXTENSION] as $file) {
            if ($this->containsTranslator($this->module->jsTranslators, $file)) {
                $this->extractMessages($file, [
                    'translator' => (array) $this->module->jsTranslators,
                    'begin' => '(',
                    'end' => ')',
                ]);
            }
        }

        $this->scanner->stdout('Detect JavaScriptFunction - END', Console::FG_YELLOW);
    }

    /**
     * @inheritdoc
     */
    protected function getLanguageItem($buffer)
    {
        if (isset($buffer[0][0]) && $buffer[0][0] === T_CONSTANT_ENCAPSED_STRING) {
            foreach ($buffer as $data) {
                if (isset($data[0], $data[1]) && $data[0] === T_CONSTANT_ENCAPSED_STRING) {
                    $message = stripcslashes($data[1]);
                    $messages[] = mb_substr($message, 1, mb_strlen($message) - 2);
                } elseif ($data === ',') {
                    break;
                }
            }

            $message = implode('', $messages);

            return [
                [
                    'category' => Scanner::CATEGORY_JAVASCRIPT,
                    'message' => $message,
                ],
            ];
        }

        return null;
    }
}
