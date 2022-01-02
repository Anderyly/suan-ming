<?php
/**
 * 儒略日
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */

namespace com\anderyly\calendar;


class Julian
{

    public static function instance()
    {
        return new self();
    }

    /**
     * 将儒略日历时间转换为公历(格里高利历)时间
     * @param $jd
     * @return array [年,月,日,时,分,秒]
     */
    public function solar($jd): array
    {
        $jd = (float)$jd;

        if ($jd >= 2299160.5) { //1582年10月15日,此日起是儒略日历,之前是儒略历
            $y4h = 146097;
            $init = 1721119.5;
        } else {
            $y4h = 146100;
            $init = 1721117.5;
        }
        $jdr = floor($jd - $init);
        $yh = $y4h / 4;
        $cen = floor(($jdr + 0.75) / $yh);
        $d = floor($jdr + 0.75 - $cen * $yh);
        $ywl = 1461 / 4;
        $jy = floor(($d + 0.75) / $ywl);
        $d = floor($d + 0.75 - $ywl * $jy + 1);
        $ml = 153 / 5;
        $mp = floor(($d - 0.5) / $ml);
        $d = floor(($d - 0.5) - 30.6 * $mp + 1);
        $y = (100 * $cen) + $jy;
        $m = ($mp + 2) % 12 + 1;
        if ($m < 3) {
            $y = $y + 1;
        }
        $sd = floor(($jd + 0.5 - floor($jd + 0.5)) * 24 * 60 * 60 + 0.00005);
        $mt = floor($sd / 60);
        $ss = $sd % 60;
        $hh = floor($mt / 60);
        $mt = $mt % 60;
        $yy = floor($y);
        $mm = floor($m);
        $dd = floor($d);

        return [$yy, $mm, $dd, $hh, $mt, $ss];
    }
}