<?php

class Lms_LangHelpers
{
    static public function detectNames($names)
    {
        $pureNames["international_name"] = "";
        $pureNames["name"] = "";
        foreach ($names as $name) {
            $eng = 0;
            $rus = 0;
            for ($i = 0; $i < strlen($name);$i++) {
                $num = ord($name{$i});
                if ($num >= 65 && $num <= 122) $eng++;
                if ($num >= 192 && $num <= 255) $rus++;
            }
            if ($rus > $eng) {
                if (strlen($pureNames["name"]) < strlen($name)) {
                    $pureNames["name"] = $name;
                }
            } else if (strlen($pureNames["international_name"])<strlen($name)) {
                $pureNames["international_name"] = $name;
            }
        }
        return $pureNames;
    }

    static public function translit($cyrillicString)
    {
        static $tr = array(
            "Ґ" => "G", "Ё" => "YO", "Є" => "E", "Ї" => "YI", "І" => "I",
            "і" => "i", "ґ" => "g", "ё" => "yo", "№" => "#", "є" => "e",
            "ї" => "yi", "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ж" => "ZH", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "zh",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "'",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya"
        );
        return strtr($cyrillicString, $tr);
    }

}
