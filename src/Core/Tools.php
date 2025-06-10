<?php


namespace Core;


use DateTime;


class Tools {

    public static function fileContains($fileName, $content){
        $res = FALSE;
        if (file_exists($fileName)) {
            $logData = file_get_contents($fileName);

            if (strpos($logData, $content) !== FALSE) {
                $res = TRUE;
            }
        }
        return $res;
    }



    public static function isFirstDayOfMonth(){
        $timer = new Timer();
        return ($timer->getYear() . "-" . $timer->getMonth() . "-01") == $timer->getDate();
    }

    public static function isBillDay(){
        $timer = new Timer();
        return ($timer->getYear() . "-" . $timer->getMonth() . "-05") == $timer->getDate();
    }

    /**
     * Imprime la cantidad $value en formato de moneda española
     * @param float $value
     * @return string
     */
    public static function printCurrencyES($value) {
        $formatter = new \NumberFormatter('es_ES', \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($value, 'EUR');
    }



    public static function toMoney($qty){
        return round($qty, 2, PHP_ROUND_HALF_UP);
    }

    public static function maskBankAccount($num)
    {
        $num = (string) preg_replace("/[^A-Za-z0-9]/", "", $num); // Remove all non alphanumeric characters

        return str_pad(substr($num, -4), strlen($num), "X", STR_PAD_LEFT);
    }

    public static function removeInvalidDecimals($value){
        return preg_replace('~\.0+$~','',$value);
    }

    public static function null2Zero($value){
        $res = $value;
        if (is_null($value)){
            $res= 0;
        }

        return $res;
    }

    public static function zerofill ($num, $zerofill = 5)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }


    public static function utf8_encode_deep(&$input) {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                self::utf8_encode_deep($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                self::utf8_encode_deep($input->$var);
            }
        }
    }

    public static function replace_accents($key) {
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        $key = utf8_decode ( $key );
        $key = strtr ( $key, utf8_decode ( $originales ), $modificadas );
        $key = strtolower ( $key );
        return utf8_encode ( $key );
    }

    public static function iconv_utf8($string){
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
    }


    public static function isBetween($value, $minValue, $maxValue){
        return $value >= $minValue && $value < $maxValue;
    }

    public static function isDateBetween($targetDate, $startDate, $endDate){
        $paymentDate = DateTime::createFromFormat('Y-m-d', $targetDate);
        $contractDateBegin = DateTime::createFromFormat('Y-m-d', $startDate);
        $contractDateEnd = DateTime::createFromFormat('Y-m-d', $endDate);

        return ($paymentDate >= $contractDateBegin && $paymentDate <= $contractDateEnd);

    }


    public static function filterFile($fileName, $pattern){

        $pattern = "/\b$pattern\b/i";
        $fileArray = file($fileName);
        return preg_grep($pattern, $fileArray);
    }
    public static function sanitize($str, $replace=array(), $delimiter='_') {

        if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }


    public static function getAlphaNumericChars($string){
        $string = str_replace("\n", '', $string); // remove new lines
        $string = str_replace("\r", '', $string); // remove carriage returns
        $string= str_replace("'", "", $string);
        return str_replace('"', "", $string);

    }





    public static function getStringBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }



    public static function getLuhn($luhn)
    {
        $sum = 0;
        $lunhLength = strlen($luhn);
        for ($i=0; $i < $lunhLength; $i++)
        {
            $sum += intval(substr($luhn, $i, 1));
        }

        $delta = array(0,1,2,3,4,-4,-3,-2,-1,0);
        for ($i=$lunhLength-1; $i>=0; $i-=2 )
        {
            $deltaIndex = intval(substr($luhn,$i,1));
            $deltaValue = $delta[$deltaIndex];
            $sum += $deltaValue;
        }
        $mod10 = $sum % 10;
        $mod10 = 10 - $mod10;
        if ($mod10 == 10)
        {
            $mod10=0;
        }
        return $mod10;
    }

    public static function validateLuhn($luhn)
    {
        $lunhLength = strlen($luhn);
        $luhnDigit = intval(substr($luhn, $lunhLength - 1,$lunhLength));
        $luhnLess = substr($luhn,0,$lunhLength-1);
        if (self::getLuhn($luhnLess)==intval($luhnDigit))
        {
            return true;
        }
        return false;
    }

    public static function remove_utf8_bom($text)
    {
        $bom = pack("CCC",0xef,0xbb,0xbf);
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    public static function getBetween($content,$start,$end){
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }

    public static function notNullNotEmpty($str){
        return !is_null($str) && $str != "";
    }

    public static function isTimeFormat($str){
        return preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#', $str);
    }

    public static function time2Seconds($str){

        if (self::isTimeFormat($str)){
            list($h, $m, $s) = explode(":", $str);
            return ($h * 3600) + ($m * 60) + $s;
        }else{
            return 0;
        }
    }

    public static function nullOrEmpty($str){
        return (!isset($str) || trim($str)==='');
    }

    public static function isPeriod($strDate){
        $res = FALSE;
        $date = date_parse($strDate);
        if ($date["error_count"] == 0 && checkdate($date["month"], "01", $date["year"])){
            $res = TRUE;
        }

        return $res;
    }

    public static function isDate($strDate){
        $res = FALSE;

        $date = date_parse($strDate);

        if ($date["error_count"] == 0 && checkdate($date["month"], $date["day"], $date["year"])){
            $res = TRUE;
        }

        return $res;
    }

    public static function startsWith($haystack, $needle) {


        // search backwards starting from haystack length characters from the end
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }


    public static function containSome($haystack, $needle, $preffix = "", $suffix = ""){
        foreach($haystack as $key => $value){
            //echo "\nValue:" . $preffix.$value,$suffix . " #Needle:$needle";
            if (strpos($preffix . $value . $suffix, $needle) !== false) {
                //echo "\n$preffix$value$suffix";
                return true;
            }
        }
        return false;
    }



    public static function stringContains($sourceString, $searchedString){
        $res = FALSE;

        if (strpos($sourceString, $searchedString) !== FALSE) {
            $res = TRUE;
        }
        return $res;

    }





    public static function getLastYearPeriods(){

        for ($i = 0; $i < 12; $i++) {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
        }
        return $months;
    }

    public static function dateOlderThan($date, $interval){
        return strtotime($date) < strtotime( (string) (-1 * $interval ) .' days');

    }

    public static function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }

}

