<?php

namespace NDatabase;

class NTimeZones
{
    public static function getList()
    {
        return [
            ['timeZone' => 'Europe/Dublin', 'offset' => '+00:00'],
            ['timeZone' => 'Europe/Guernsey', 'offset' => '+00:00'],
            ['timeZone' => 'Europe/Isle_of_Man', 'offset' => '+00:00'],
            ['timeZone' => 'Europe/Jersey', 'offset' => '+00:00'],
            ['timeZone' => 'Europe/Lisbon', 'offset' => '+00:00'],
            ['timeZone' => 'Europe/London', 'offset' => '+00:00'],
            ['timeZone' => 'Europe/Amsterdam', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Andorra', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Belgrade', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Berlin', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Bratislava', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Brussels', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Budapest', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Busingen', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Copenhagen', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Gibraltar', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Ljubljana', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Luxembourg', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Madrid', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Malta', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Monaco', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Oslo', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Paris', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Podgorica', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Prague', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Rome', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/San_Marino', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Sarajevo', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Skopje', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Stockholm', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Tirane', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Vaduz', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Vatican', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Vienna', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Warsaw', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Zagreb', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Zurich', 'offset' => '+01:00'],
            ['timeZone' => 'Europe/Athens', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Bucharest', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Chisinau', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Helsinki', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Istanbul', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Kiev', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Mariehamn', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Riga', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Simferopol', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Sofia', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Tallinn', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Uzhgorod', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Vilnius', 'offset' => '+02:00'],
            ['timeZone' => 'Europe/Kaliningrad', 'offset' => '+03:00'],
            ['timeZone' => 'Europe/Minsk', 'offset' => '+03:00'],
            ['timeZone' => 'Europe/Moscow', 'offset' => '+03:00'],
            ['timeZone' => 'Europe/Samara', 'offset' => '+04:00'],
            ['timeZone' => 'Europe/Volgograd', 'offset' => '+04:00'],
            ['timeZone' => 'Asia/Amman', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Beirut', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Damascus', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Gaza', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Hebron', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Jerusalem', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Nicosia', 'offset' => '+02:00'],
            ['timeZone' => 'Asia/Aden', 'offset' => '+03:00'],
            ['timeZone' => 'Asia/Baghdad', 'offset' => '+03:00'],
            ['timeZone' => 'Asia/Bahrain', 'offset' => '+03:00'],
            ['timeZone' => 'Asia/Kuwait', 'offset' => '+03:00'],
            ['timeZone' => 'Asia/Qatar', 'offset' => '+03:00'],
            ['timeZone' => 'Asia/Riyadh', 'offset' => '+03:00'],
            ['timeZone' => 'Asia/Tehran', 'offset' => '+03:30'],
            ['timeZone' => 'Asia/Kabul', 'offset' => '+04:30'],
            ['timeZone' => 'Asia/Baku', 'offset' => '+04:00'],
            ['timeZone' => 'Asia/Dubai', 'offset' => '+04:00'],
            ['timeZone' => 'Asia/Muscat', 'offset' => '+04:00'],
            ['timeZone' => 'Asia/Tbilisi', 'offset' => '+04:00'],
            ['timeZone' => 'Asia/Yerevan', 'offset' => '+04:00'],
            ['timeZone' => 'Asia/Colombo', 'offset' => '+05:30'],
            ['timeZone' => 'Asia/Kolkata', 'offset' => '+05:30'],
            ['timeZone' => 'Asia/Kathmandu', 'offset' => '+05:45'],
            ['timeZone' => 'Asia/Aqtau', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Aqtobe', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Ashgabat', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Dushanbe', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Karachi', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Oral', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Samarkand', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Tashkent', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Almaty', 'offset' => '+06:00'],
            ['timeZone' => 'Asia/Bishkek', 'offset' => '+06:00'],
            ['timeZone' => 'Asia/Dhaka', 'offset' => '+06:00'],
            ['timeZone' => 'Asia/Qyzylorda', 'offset' => '+06:00'],
            ['timeZone' => 'Asia/Thimphu', 'offset' => '+06:00'],
            ['timeZone' => 'Asia/Yekaterinburg', 'offset' => '+05:00'],
            ['timeZone' => 'Asia/Rangoon', 'offset' => '+06:30'],
            ['timeZone' => 'Asia/Bangkok', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Ho_Chi_Minh', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Hovd', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Jakarta', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Novokuznetsk', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Novosibirsk', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Omsk', 'offset' => '+06:00'],
            ['timeZone' => 'Asia/Tomsk', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Phnom_Penh', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Pontianak', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Vientiane', 'offset' => '+07:00'],
            ['timeZone' => 'Asia/Brunei', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Choibalsan', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Chongqing', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Harbin', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Hong_Kong', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Kashgar', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Krasnoyarsk', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Kuala_Lumpur', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Kuching', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Macau', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Makassar', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Manila', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Shanghai', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Singapore', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Taipei', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Ulaanbaatar', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Urumqi', 'offset' => '+08:00'],
            ['timeZone' => 'Asia/Dili', 'offset' => '+09:00'],
            ['timeZone' => 'Asia/Irkutsk', 'offset' => '+09:00'],
            ['timeZone' => 'Asia/Jayapura', 'offset' => '+09:00'],
            ['timeZone' => 'Asia/Pyongyang', 'offset' => '+09:00'],
            ['timeZone' => 'Asia/Seoul', 'offset' => '+09:00'],
            ['timeZone' => 'Asia/Tokyo', 'offset' => '+09:00'],
            ['timeZone' => 'Asia/Khandyga', 'offset' => '+10:00'],
            ['timeZone' => 'Asia/Yakutsk', 'offset' => '+10:00'],
            ['timeZone' => 'Asia/Sakhalin', 'offset' => '+11:00'],
            ['timeZone' => 'Asia/Ust-Nera', 'offset' => '+11:00'],
            ['timeZone' => 'Asia/Vladivostok', 'offset' => '+11:00'],
            ['timeZone' => 'Asia/Anadyr', 'offset' => '+12:00'],
            ['timeZone' => 'Asia/Kamchatka', 'offset' => '+12:00'],
            ['timeZone' => 'Asia/Magadan', 'offset' => '+12:00']
        ];
    }

    public static function getAllByOffset($offset)
    {
        $timeZones = self::getList();
        $result = [];

        foreach ($timeZones as $timeZone) {
            if ($timeZone['offset'] == $offset) {
                $result[] = $timeZone['timeZone'];
            }
        }
        return $result;
    }

    public static function getByOffset($offset)
    {
        $timeZones = self::getAllByOffset($offset);

        if (count($timeZones) > 0) {
            return $timeZones[0];
        } else {
            return null;
        }
    }

    public static function isTimeZoneValid($checkTimeZone)
    {
        if ($checkTimeZone == 'SYSTEM') {
            return true;
        }
        $timeZones = self::getList();
        $isValid = false;
        foreach ($timeZones as $timeZone) {
            if ($timeZone['timeZone'] == $checkTimeZone) {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }
}
