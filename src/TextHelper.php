<?php
    /**
     * @author nomelodic
     */
    
    namespace app\components;

    use Exception;

    /**
     * Класс со вспомогательными функциями по обработке текста
     *
     * @package app\components
     */
    class TextHelper
    {
        public const UNIX_YEAR   = 31536000;
        public const UNIX_MONTH  = 2592000;
        public const UNIX_WEEK   = 640800;
        public const UNIX_DAY    = 86400;
        public const UNIX_HOUR   = 3600;
        public const UNIX_MINUTE = 60;

        public const IPv4 = 'https://2ip.ru/whois/?ip=%ip';
        public const IPv6 = 'http://ipv6.my-addr.com/check-ipv6-geolocatio.php?ip=%ip';
    
        /**
         * Промежуток времени в текстовом виде
         *
         * @param  int  $start     Начальное время UNIX
         * @param  int  $finish    Конечное время UNIX (менее $start - текущее время) [optional: -1 - Текущее время]
         * @param  bool $is_long   Полные текстовые окончания? [optional: false]
         * @param  bool $with_time Включить часы и минуты? [optional: false]
         *
         * @return string
         */
        public static function interval2string(int $start, int $finish = -1, bool $is_long = false, bool $with_time = false): string
        {
            $_year    = $is_long ? ['год', 'года', 'лет'] : ['г', 'г', 'л'];
            $_month   = $is_long ? ['месяц', 'месяца', 'месяцев'] : 'м';
            $_week    = $is_long ? ['неделя', 'недели', 'недель'] : 'н';
            $_days    = $is_long ? ['день', 'дня', 'дней'] : 'д';
            $_hours   = $is_long ? ['час', 'часа', 'часов'] : 'ч';
            $_minutes = $is_long ? ['минута', 'минуты', 'минут'] : 'мин';
            $_seconds = $is_long ? ['секунда', 'секунды', 'секунд'] : 'сек';
            
            if ($finish < $start) $finish = time();

            $count = $finish - $start;
            $_c = $count;
        
            if ($count > 0)
            {
                $year = floor($_c / self::UNIX_YEAR);  // Считаем количество лет
                $_c -= $year * self::UNIX_YEAR;
            
                $month = floor($_c / self::UNIX_MONTH); // Считаем количество месяцев
                $_c -= $month * self::UNIX_MONTH;
            
                $week = floor($_c / self::UNIX_WEEK);  // Считаем количество недель
                $_c -= $week * self::UNIX_WEEK;
            
                $days = floor($_c / self::UNIX_DAY);  // Считаем количество дней

                if ($with_time)
                {
                    $_c -= $days * self::UNIX_DAY;

                    $hours = floor($_c / self::UNIX_HOUR);  // Считаем количество часов
                    $_c -= $hours * self::UNIX_HOUR;

                    $minutes = floor($_c / self::UNIX_MINUTE);  // Считаем количество минут
                    $_c -= $minutes * self::UNIX_MINUTE;

                    $seconds = floor($_c);  // Считаем количество секунд
                }
            
                $return = '';
                if ($year > 0) $return .= $year > 0 ? static::word_form($year, $_year) . ' ' : '';
                if ($month > 0) $return .= $month > 0 ? static::word_form($month, $_month) . ' ' : '';
                if ($year < 1 || ($month < 1 && $year > 0)) $return .= $week > 0 ? static::word_form($week, $_week) . ' ' : '';
                if (($year < 1 && $month < 1) || ($month < 1 && $week < 1 && $year > 0) || ($month > 0 && $week < 1 && $year < 1)) $return .= $days > 0 ? static::word_form($days, $_days) : '';
                if ($with_time) $return .= (!empty($return) ? ' ' : '') . static::word_form($hours, $_hours) . ' ' . static::word_form($minutes, $_minutes) . ' ' . static::word_form($seconds, $_seconds);
            
                return $return ? trim($return) : ($is_long ? 'менее 1 дня' : '< 1 д');
            }
        
            return  $is_long ? 'менее 1 секунды' : '< 1 сек';
        }
    
        /**
         * Получить нужную словоформу по числу
         *
         * @param  int             $count    Число
         * @param  string|string[] $variants Массив окончаний
         * @param  bool            $simple   Выводить просто окончание без числа? [optional: false]
         *
         * @return string
         */
        public static function word_form(int $count, $variants, bool $simple = false): string
        {
            $r = !$simple ? $count . ' ' : '';
        
            if (is_string($variants)) return $r . $variants;
        
            if ($count === 1 || ($count % 10 === 1 && $count > 20)) $r .= $variants[0];
            else if (($count > 1 && $count < 5) || ($count % 10 > 2 && $count % 10 < 5 && $count > 20)) $r .= $variants[1];
            else $r .= $variants[2];
        
            return $r;
        }
    
        /**
         * Разбить строку на массив
         *
         * @param  int $str  Строка
         * @param  int $step Шаг [optional: 0]
         *
         * @return string[]
         */
        public static function mb_split(int $str, int $step = 0): array
        {
            if ($step > 0)
            {
                $ret = [];
                $len = mb_strlen($str, "UTF-8");
            
                for ($i = 0; $i < $len; $i += $step)
                {
                    $ret[] = mb_substr($str, $i, $step, "UTF-8");
                }
            
                return $ret;
            }
        
            return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
        }
    
        /**
         * Аналог функции ucfirst() для кириллицы
         *
         * @param  string $str      Строка
         * @param  string $encoding Кодировка [optional: 'UTF-8']
         *
         * @return string
         */
        public static function mb_ucfirst(string $str, string $encoding = 'UTF-8'): string
        {
            $str = mb_ereg_replace('^[\ ]+', '', $str);
            $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, mb_strlen($str), $encoding);
        
            return $str;
        }
    
        /**
         * Аналог функции ucfirst() для кириллицы для каждого слова
         *
         * @param  string $str      Строка
         * @param  string $encoding Кодировка [optional: 'UTF-8']
         *
         * @return string
         */
        public static function mb_ucfirst_all(string $str, string $encoding = 'UTF-8'): string
        {
            $array = mb_strpos($str, ' ') !== false ? explode(' ', mb_strtolower($str)) : [mb_strtolower($str)];
            $cnt = count($array);
        
            for ($i = 0; $i < $cnt; $i++)
            {
                $array[$i] = static::mb_ucfirst($array[$i], $encoding);
            }
        
            return implode(' ', $array);
        }
    
        /**
         * Ссылка для проверки IP
         *
         * @param  string $ip IP-адрес (v4 или v6)
         *
         * @return string Возвращает ссылку на проверку IP-адреса
         */
        public static function ip_link(string $ip): string
        {
            $ip_links = [
                0 => static::IPv4,
                1 => static::IPv6
            ];
        
            return str_replace('%ip', $ip, $ip_links[(int) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)]);
        }
    
        /**
         * Обрезка строки с окончанием
         *
         * @param  string $string     Строка
         * @param  int    $start      Начальная позиция [optional: 0]
         * @param  int    $width      Длина строки [optional: 120]
         * @param  string $trimmarker Окончание [optional: '...']
         *
         * @return string
         */
        public static function trim_str(string $string, int $start = 0, int $width = 120, string $trimmarker = '...'): string
        {
            $len = mb_strlen(trim($string));
        
            return $len > $width && $width > 0 ? rtrim(mb_strimwidth($string, $start, $width - mb_strlen($trimmarker))) . $trimmarker : $string;
        }

        /**
         * Сокращение строки
         *
         * @param  string  $string     Строка
         * @param  int     $width      Длина строки [optional: -1 - полная длина]
         * @param  bool    $with_title Добавить title при наведении? [optional: false]
         *
         * @return string
         */
        public static function reduction(string $string, int $width = -1, bool $with_title = false): string
        {
            $string = trim($string);
            if ($width < 0) return $string;

            $len = mb_strlen($string);
            $part = ceil($width/2);

            return $len > $width+3 && $width > 0 ? ($with_title ? '<span title="' . $string . '">' : '') . mb_strimwidth($string, 0, $part) . '...' . mb_substr($string, $len - ($width - $part)) . ($with_title ? '</span>' : '') : $string;
        }
    
        /**
         * Преобразование числа в строку с ведущими нулями
         *
         * @param  int $num   Число
         * @param  int $nulls Количество символов в строке [optional: 2]
         *
         * @return string
         */
        public static function num2str(int $num, int $nulls = 2): string
        {
            return str_pad($num, $nulls, '0', STR_PAD_LEFT);
        }
    
        /**
         * Нормализация текста
         *
         * @param  string $str Строка
         *
         * @return string
         * @link   https://ru.wikipedia.org/wiki/Узбекская_письменность
         * @link   https://ru.wikipedia.org/wiki/Казахская_письменность
         * @link   https://ru.wikipedia.org/wiki/Немецкий_алфавит
         * @link   https://ru.wikipedia.org/wiki/Украинский_алфавит
         * @link   https://unicode-table.com/ru/
         */
        public static function normalize_text(string $str): string
        {
            $converted_pairs = [
                'ə' => 'а', 'à' => 'а', 'á' => 'а', 'â' => 'а', 'ã' => 'а', 'ä' => 'а', 'ӓ' => 'а', 'å' => 'а', 'æ' => 'а', 'ā' => 'а', 'ă' => 'а', 'ą' => 'а', 'œ' => 'ае', 'ß' => 'в', 'ç' => 'с', 'ć' => 'с', 'ĉ' => 'с', 'ċ' => 'с', 'č' => 'с', 'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'ê' => 'e', 'ë' => 'ё', 'ē' => 'е', 'ĕ' => 'е', 'ė' => 'е', 'ę' => 'е', 'ě' => 'е', 'è' => 'e', 'é' => 'e', 'є' => 'е', 'ғ' => 'ф', 'ğ' => 'g', 'ǵ' => 'g', 'ĝ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h', 'ң' => 'н', 'ⱨ' => 'h', 'ҥ' => 'н', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'i' => 'i', 'ĩ' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'і' => 'i', 'ї' => 'i', 'ĳ' => 'ij', 'ɉ' => 'j', 'ĵ' => 'j', 'қ' => 'к', 'ⱪ' => 'k', 'ķ' => 'k', 'ĸ' => 'к', 'ĺ' => 'l', 'ļ' => 'l', 'ľ' => 'l', 'ŀ' => 'l', 'ł' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n', 'ň' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ꞑ' => 'n', 'ø' => 'о', 'ö' => 'o', 'ө' => 'o', 'ó' => 'o', 'ӧ' => 'о', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ō' => 'о', 'ŏ' => 'о', 'ő' => 'о', 'þ' => 'p', 'ƣ' => 'q', 'ŕ' => 'r', 'ŗ' => 'r', 'ř' => 'r', 'ş' => 's', 'ś' => 's', 'ŝ' => 's', 'š' => 's', 'ţ' => 't', 'ť' => 't', 'ŧ' => 't', 'ú' => 'u', 'ū' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ũ' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ҳ' => 'х', 'ў' => 'у', 'ұ' => 'у', 'ý' => 'y', 'ӱ' => 'у', 'ÿ' => 'y', 'ŷ' => 'y', 'ƶ' => 'z', 'ź' => 'z', 'ż' => 'z', 'ž' => 'z',
            
                'Ə' => 'А', 'À' => 'А', 'Á' => 'А', 'Â' => 'А', 'Ã' => 'А', 'Ä' => 'А', 'Ӓ' => 'А', 'Å' => 'А', 'Æ' => 'А', 'Ā' => 'А', 'Ă' => 'А', 'Ą' => 'А', 'Œ' => 'АЕ', 'ẞ' => 'В', 'Ç' => 'C', 'Ć' => 'С', 'Ĉ' => 'С', 'Ċ' => 'С', 'Č' => 'С', 'Ď' => 'D', 'Đ' => 'D', 'Ð' => 'D', 'Ê' => 'E', 'Ë' => 'Е', 'Ē' => 'Е', 'Ĕ' => 'Е', 'Ė' => 'Е', 'Ę' => 'Е', 'Ě' => 'Е', 'È' => 'E', 'É' => 'E', 'Є' => 'Е', 'Ғ' => 'Ф', 'Ğ' => 'G', 'Ǵ' => 'G', 'Ĝ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ң' => 'Н', 'Ⱨ' => 'Н', 'Ҥ' => 'Н', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'İ' => 'I', 'Ĩ' => 'I', 'Ī' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'I' => 'I', 'І' => 'I', 'Ї' => 'I', 'Ĳ' => 'Ij', 'Ɉ' => 'J', 'Ĵ' => 'J', 'Қ' => 'К', 'Ⱪ' => 'К', 'Ķ' => 'К', 'Ĺ' => 'L', 'Ļ' => 'L', 'Ľ' => 'L', 'Ŀ' => 'L', 'Ł' => 'L', 'Ñ' => 'N', 'Ń' => 'N', 'Ņ' => 'N', 'Ň' => 'N', 'Ŋ' => 'N', '№' => 'N', 'Ø' => 'О', 'Ö' => 'O', 'Ө' => 'O', 'Ó' => 'O', 'Ӧ' => 'О', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ō' => 'О', 'Ŏ' => 'О', 'Ő' => 'О', 'Þ' => 'P', 'Ƣ' => 'Q', 'Ŕ' => 'R', 'Ŗ' => 'R', 'Ř' => 'R', 'Ş' => 'S', 'Ś' => 'S', 'Ŝ' => 'S', 'Š' => 'S', '§' => 'S', 'Ţ' => 'T', 'Ť' => 'T', 'Ŧ' => 'T', 'Ú' => 'U', 'Ū' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ů' => 'U', 'Ű' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ҳ' => 'Х', 'Ў' => 'У', 'Ұ' => 'у', 'Ý' => 'Y', 'Ӱ' => 'У', 'Ÿ' => 'У', 'Ŷ' => 'Y', '¥' => 'Y', 'Ƶ' => 'Z', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z',
            
                'ґ' => 'г', 'җ' => 'ж', 'ҷ' => 'ч', 'Ґ' => 'Г', 'Җ' => 'Ж', 'Ҷ' => 'Ч', '́' => '\'', 'ʻ' => '\'', '`' => '\'', '’' => '\'', '«' => '', '»' => '', '—' => '-', '–' => '-',
            ];
        
            return strtr($str, $converted_pairs);
        }
    
        /**
         * Функция транслитерации
         *
         * @param  string $str  Строка
         * @param  bool   $lite Заменять нераспознанные символы на '-'? [optional: false]
         *
         * @return string
         */
        public static function translit(string $str, bool $lite = false): string
        {
            $converted_pairs = [
                'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            
                'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
            
                '\'' => ''
            ];
        
            $str = static::normalize_text($str);
            $str = strtr($str, $converted_pairs);
            $str = strtolower($str);
            $str = preg_replace('~&#([0-9]+);~U', '', $str);
        
            if (!$lite)
            {
                $str = preg_replace('~[^-a-z\d]~u', '-', $str);
                $str = preg_replace('~[-]+~u', '-', $str);
                $str = trim($str, '-');
            }
        
            return $str;
        }
    
        /**
         * Чистим Useragent от возможных XSS-вставок
         *
         * @param  string $useragent Юзерагент
         *
         * @return string
         */
        public static function ua_prepare(string $useragent): string
        {
            return str_replace(['<', '>', '$', 'eval', 'atob', 'btoa'], '', $useragent);
        }
    
        /**
         * Генератор uuid
         *
         * @return string Вернет случайно сгенерированную строку в формате uuid
         * @link   https://ru.wikipedia.org/wiki/UUID
         */
        public static function generate_uuid(): string
        {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }

        /**
         * Функция генерации случайной строки
         *
         * @param  int  $count       Количество символов [optional: 64]
         * @param  bool $use_symbols Использовать спец. символы? [optional: false]
         *
         * @return string Вернет случайно сгенерированную строку
         * @throws Exception
         */
        public static function generate_rnd_str(int $count = 64, bool $use_symbols = false): string
        {
            $sym = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' . ($use_symbols ? '-_/*+|:;.,?!@#$%^(){}[]~<>=' : ''));
            $cnt = count($sym);
            $n_key = '';
        
            for ($i = 0; $i < $count; $i++)
            {
                $n_key .= $sym[random_int(0, $cnt - 1)];
            }
        
            return $n_key;
        }
    
        /**
         * Функция преобразования UNIX в дату
         *
         * @param  int $timestamp
         *
         * @return string
         */
        public static function date(int $timestamp): string
        {
            return date('d.m.Y', $timestamp);
        }
    
        /**
         * Функция преобразования UNIX в дату и время
         *
         * @param  int $timestamp
         *
         * @return string
         */
        public static function datetime(int $timestamp): string
        {
            return date('d.m.Y H:i:s', $timestamp);
        }

        /**
         * Функция получения названия месяца
         *
         * @param  int $num Номер месяца (0 - январь)
         *
         * @return string
         */
        public static function get_month(int $num): string
        {
            $list = [
                'январь',
                'февраль',
                'март',
                'апрель',
                'май',
                'июнь',
                'июль',
                'август',
                'сентябрь',
                'октябрь',
                'ноябрь',
                'декабрь'
            ];

            return $list[(int) $num - 1] ?? 'неизвестно';
        }

        /**
         * Вывод числа с плавающей запятой
         *
         * @param  int|float $num
         *
         * @return string
         */
        public static function float_prepare($num)
        {
            if (empty($num)) $num = 0;

            return number_format((float) $num, 0, ',', '&nbsp;');
        }

        /**
         * Преобразовать строку в массив по разделителю
         *
         * @param  string $str
         * @param  string $delimiter
         *
         * @return string[]
         */
        public static function string2array(string $str, string $delimiter = "\n"): array
        {
            $explode = explode($delimiter, $str);

            foreach ($explode as $key => $value)
            {
                $value = trim($value);
                if (!empty($value)) $explode[$key] = $value;
            }

            return $explode;
        }
    }