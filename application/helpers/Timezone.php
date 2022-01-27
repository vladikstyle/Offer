<?php

namespace app\helpers;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\helpers
 */
class Timezone
{
    /**
     * Get all of the time zones with the offsets sorted by their offset
     * @return array
     */
    public static function getAll()
    {
        $timeZones = [];
        $timeZoneIdentifiers = \DateTimeZone::listIdentifiers();
        foreach ($timeZoneIdentifiers as $timeZone) {
            $date = new \DateTime('now', new \DateTimeZone($timeZone));
            $offset = $date->getOffset();
            $tz = ($offset > 0 ? '+' : '-') . gmdate('H:i', abs($offset));
            $timeZones[] = [
                'timezone' => $timeZone,
                'name' => "{$timeZone} (UTC {$tz})",
                'offset' => $offset
            ];
        }
        \yii\helpers\ArrayHelper::multisort($timeZones, 'offset', SORT_DESC, SORT_NUMERIC);

        return $timeZones;
    }
}
