<?php

namespace app\modules\api\components;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\api\components
 */
class ErrorCode
{
    // Common
    const NONE = 0;
    const UNKNOWN_ERROR = 1;
    const HTTPS_ONLY = 2;

    // Auth errors
    const AUTH_ERROR = 100;
    const AUTH_EXPIRED = 101;
    const AUTH_INVALID_REFRESH_TOKEN = 102;
    const AUTH_WRONG_CREDENTIALS = 103;

    // Data/Table errors
    const DATA_ERROR = 200;
    const NOTIFICATION_TOKEN_REGISTERED = 210;
}
