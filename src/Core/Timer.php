<?php


namespace Core;


class Timer
{
    private $initialTime = NULL;


    function __construct() {
        $time = explode(' ', microtime());
        $time = $time[1] + $time[0];
        $this->initialTime = $time;
    }

    public function getTimePassed(){
        $time = explode(" ", microtime());
        $time = $time[1] + $time[0];
        $endtime = $time;
        return ($endtime - $this->initialTime);
    }

    public function getDate(){
        return date("Y-m-d");
    }

    public function getPreviousDate(){
        return date("Y-m-d",strtotime("-1 days"));
    }

    public function getPreviousDateTime(){
        return date("Y-m-d H:i:s",strtotime("-1 days"));
    }

    public function getYear(){
        return date("Y");
    }

    public function getMonth(){
        return date("m");
    }

    public function getDay(){
        return date("d");
    }
    public function getDateTime(){
        return date("Y-m-d H:i:s");
    }

    public function getTime(){
        return date("H:i:s");
    }

    public function getCurMonthNumDays(){
        return date("t");
    }

    public function getCurMonthRemainingDays(){
        return $this->getCurMonthNumDays() - $this->getDay();
    }

    public function getMonthName(){
        return date("F");
    }

    public function getDateFormat($format){
        return date($format);
    }

    public function getDueDate(){
        return date("Y-m-d",strtotime("+ 30 days"));
    }

    public function getFirstDatePreviousMonth(){
        $datestring = $this->getDate() . ' first day of last month';
        $dt=date_create($datestring);
        return $dt->format('Y-m-d');
    }

    public function getLastDatePreviousMonth(){
        $datestring = $this->getDate() . ' last day of last month';
        $dt=date_create($datestring);
        return $dt->format('Y-m-d');
    }

    public function getFirstDateCurrentMonth(){

        $datestring = $this->getDate() . ' first day of this month';
        $dt=date_create($datestring);
        return $dt->format('Y-m-d');
    }

    public function getLastDateCurrentMonth(){

        $datestring = $this->getDate() . ' last day of this month';
        $dt=date_create($datestring);
        return $dt->format('Y-m-d');
    }

    public function  getLastYearPreviousMonth(){
        $datestring = $this->getDate() . ' last day of last month';
        $dt=date_create($datestring);
        return $dt->format('Y');
    }

    public function  getLastMonthPreviousMonth(){
        $datestring = $this->getDate() . ' last day of last month';
        $dt=date_create($datestring);
        return $dt->format('m');
    }

    public function getTotalDaysOfMonth($year, $month){
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public function getLastDayOfDate($date){
        return date("Y-m-t", strtotime($date));
    }
}