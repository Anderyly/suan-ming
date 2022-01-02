<?php
/**
 * 十神
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */

namespace com\anderyly\calendar;

class TenGod
{

    public static function instance()
    {
        return new self();
    }

    /**
     * @param $tg [天干]
     * @param $rz [日支]
     * @return string
     */
    public function get($tg, $rz): string
    {
        if ($rz == 0) $rz = 10;
        if ($tg == 0) $tg = 10;
        $cha = (int)$rz - (int)$tg;
        echo $cha;
        if ($cha >= 0) {
            switch ($cha) {
                case 0:
                    $str = "比肩";
                    break;
                case 1:
                    if ($rz % 2 == 0) {
                        $str = "劫财";
                    } else {
                        $str = "正印";
                    }
                    break;
                case 2:
                    $str = "偏印";
                    break;
                case 3:
                    if ($rz % 2 == 0) {
                        $str = "正印";
                    } else {
                        $str = "正官";
                    }
                    break;
                case 4:
                    $str = "偏官";
                    break;
                case 5:
                    if ($rz % 2 == 0) {
                        $str = "正官";
                    } else {
                        $str = "正财";
                    }
                    break;
                case 6:
                    $str = "偏财";
                    break;
                case 7:
                    if ($rz % 2 == 0) {
                        $str = "正财";
                    } else {
                        $str = "伤官";
                    }
                    break;
                case 8:
                    $str = "食神";
                    break;
                case 9:
                    $str = "伤官";
                    break;
            }
        } else {
            switch (abs($cha)) {
                case 1:
                    if ($rz % 2 == 1) {
                        $str = "劫财";
                    } else {
                        $str = "伤官";
                    }
                    break;
                case 2:
                    $str = "食神";
                    break;
                case 3:
                    if ($rz % 2 == 1) {
                        $str = "伤官";
                    } else {
                        $str = "正财";
                    }
                    break;
                case 4:
                    $str = "偏财";
                    break;
                case 5:
                    if ($rz % 2 == 1) {
                        $str = "正财";
                    } else {
                        $str = "正官";
                    }
                    break;
                case 6:
                    $str = "七杀";
                    break;
                case 7:
                    if ($rz % 2 == 1) {
                        $str = "正官";
                    } else {
                        $str = "正印";
                    }
                    break;
                case 8:
                    $str = "偏印";
                    break;
                case 9:
                    $str = "正印";
                    break;
            }
        }
        return $str;
    }
}