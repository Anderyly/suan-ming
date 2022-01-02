<?php
/**
 * 公历
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */


namespace com\anderyly\calendar;

class Solar
{

    public static function instance()
    {
        return new self();
    }

    /**
     * 将公历时间转换成农历时间
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @return bool|array(年,月,日,是否闰月)
     */
    public function lunar(int $yy, int $mm, int $dd): bool|array
    {

        if (!Solar::instance()->ValidDate($yy, $mm, $dd)) { // 验证输入的日期是否正确
            return false;
        }


        $prev = 0; //是否跨年了,跨年了则减一
        $isLeap = 0;//是否闰月

        list($jdzq, $jdnm, $mc) = JieQi::instance()->getMonthCode($yy);

        $jd = Solar::instance()->julian($yy, $mm, $dd, 12, 0, 0); //求出指定年月日之JD值
        if (floor($jd) < floor($jdnm[0] + 0.5)) {
            $prev = 1;
            list($jdzq, $jdnm, $mc) = JieQi::instance()->getMonthCode($yy - 1);
        }
        for ($i = 0; $i <= 14; $i++) { //指令中加0.5是為了改為從0時算起而不從正午算起
            if (floor($jd) >= floor($jdnm[$i] + 0.5) && floor($jd) < floor($jdnm[$i + 1] + 0.5)) {
                $mi = $i;
                break;
            }
        }

        if ($mc[$mi] < 2 || $prev == 1) { //年
            $yy = $yy - 1;
        }

        if (($mc[$mi] - floor($mc[$mi])) * 2 + 1 != 1) { //因mc(mi)=0對應到前一年阴曆11月,mc(mi)=1對應到前一年阴曆12月,mc(mi)=2對應到本年1月,依此類推
            $isLeap = 1;
        }
        $mm = intval((floor($mc[$mi] + 10) % 12) + 1); //月

        $dd = intval(floor($jd) - floor($jdnm[$mi] + 0.5) + 1); //日,此處加1是因為每月初一從1開始而非從0開始

        return array($yy, $mm, $dd, $isLeap);
    }

    /**
     * 將公历时间转换为儒略日历时间
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @param int $hh [0-23]
     * @param int $mt [0-59]
     * @param int $ss [0-59]
     * @return float|bool|int
     */
    public function julian($yy, $mm, $dd, $hh = 0, $mt = 0, $ss = 0): float|bool|int
    {
        if (!$this->ValidDate($yy, $mm, $dd)) {
            return false;
        }
        if ($hh < 0 || $hh >= 24) {
            return false;
        }
        if ($mt < 0 || $mt >= 60) {
            return false;
        }
        if ($ss < 0 || $ss >= 60) {
            return false;
        }

        $yp = $yy + floor(($mm - 3) / 10);
        if (($yy > 1582) || ($yy == 1582 && $mm > 10) || ($yy == 1582 && $mm == 10 && $dd >= 15)) { //这一年有十天是不存在的
            $init = 1721119.5;
            $jdy = floor($yp * 365.25) - floor($yp / 100) + floor($yp / 400);
        }
        if (($yy < 1582) || ($yy == 1582 && $mm < 10) || ($yy == 1582 && $mm == 10 && $dd <= 4)) {
            $init = 1721117.5;
            $jdy = floor($yp * 365.25);
        }
        // if (!$init) {
        //     return false;
        // }
        $mp = floor($mm + 9) % 12;
        $jdm = $mp * 30 + floor(($mp + 1) * 34 / 57);
        $jdd = $dd - 1;
        $jdh = ($hh + ($mt + ($ss / 60)) / 60) / 24;
        return $jdy + $jdm + $jdd + $jdh + $init;
    }

    /**
     * 判断公历日期是否有效
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @return boolean
     */
    public function ValidDate($yy, $mm, $dd): bool
    {
        if ($yy < -1000 || $yy > 3000) { //适用于西元-1000年至西元3000年,超出此范围误差较大
            return false;
        }

        if ($mm < 1 || $mm > 12) { //月份超出範圍
            return false;
        }

        if ($yy == 1582 && $mm == 10 && $dd >= 5 && $dd < 15) { //这段日期不存在.所以1582年10月只有20天
            return false;
        }

        $ndf1 = -($yy % 4 == 0); //可被四整除
        $ndf2 = (($yy % 400 == 0) - ($yy % 100 == 0)) && ($yy > 1582);
        $ndf = $ndf1 + $ndf2;
        $dom = 30 + ((abs($mm - 7.5) + 0.5) % 2) - intval($mm == 2) * (2 + $ndf);
        if ($dd <= 0 || $dd > $dom) {
            return false;
        }

        return true;
    }

    /**
     * 获取公历某个月有多少天
     * @param int $yy
     * @param int $mm
     * @return number
     */
    public function getDays(int $yy, int $mm): int
    {
        if ($yy < -1000 || $yy > 3000) { //适用于西元-1000年至西元3000年,超出此范围误差较大
            return 0;
        }

        if ($mm < 1 || $mm > 12) { //月份超出範圍
            return 0;
        }
        $ndf1 = -($yy % 4 == 0); //可被四整除
        $ndf2 = (($yy % 400 == 0) - ($yy % 100 == 0)) && ($yy > 1582);
        $ndf = $ndf1 + $ndf2;
        return 30 + ((abs($mm - 7.5) + 0.5) % 2) - intval($mm == 2) * (2 + $ndf);
    }
}