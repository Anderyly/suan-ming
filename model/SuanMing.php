<?php
/**
 * 算命
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */


namespace com\anderyly\calendar;

use Exception;

class SuanMing
{

    /**
     * 十天干
     * @var array
     */
    public array $ctg = ['癸', '甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬'];

    /**
     * 十二地支
     * @var array
     */
    public array $cdz = ['亥', '子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌'];

    /**
     * 生肖
     * @var array|string[]
     */
    public array $animal = ["猪", "鼠", "牛", "虎", "兔", "龙", "蛇", "马", "羊", "猴", "鸡", "狗"];

    /**
     * 廿四节气(从春分开始)
     * @var array
     */
    public array $jq = ['春分', '清明', '谷雨', '立夏', '小满', '芒种', '夏至', '小暑', '大暑', '立秋', '处暑', '白露', '秋分',
        '寒露', '霜降', '立冬', '小雪', '大雪', '冬至', '小寒', '大寒', '立春', '雨水', '惊蛰']; //JieQi


    public static function instance(): SuanMing
    {
        return new self();
    }

    /**
     * 四柱
     * @param $year
     * @param $month
     * @param $day
     * @param $hour
     * @param $ifx
     * @return array
     */
    public function getSiZhu($year, $month, $day, $hour, $minute, $ifx): array
    {
        $shuxiang = [];

        $md = $month * 100 + $day;

        // 获取当年立春时间
        $jqArr = JieQi::instance()->get24JieQi($year)[0];

        if (($jqArr[1] == $month and $jqArr[2] == $day) or ($md >= 204 && $md <= 1231)) {
            $yg = ($year - 3) % 10;
            $yz = ($year - 3) % 12;
        } else if ($md >= 101 && $md <= 203) {
            $yg = ($year - 4) % 10;
            $yz = ($year - 4) % 12;
        }

        $yg1 = $this->ctg[$yg];
        $yz1 = $this->cdz[$yz];

        $mz = $this->getMzQyjs($month, $day, 1);

        if (($mz > 2 && $mz <= 11)) {
            $mg = ($yg * 2 + $mz + 8) % 10;
        } else {
            $mg = ($yg * 2 + $mz) % 10;
        }
        $mg1 = $this->ctg[$mg];
        $mz1 = $this->cdz[$mz];

        //从公元0年到目前年份的天数 yearlast
        $yearlast = ($year - 1) * 5 + floor(($year - 1) / 4) - floor(($year - 1) / 100) + floor(($year - 1) / 400);
        //计算某月某日与当年1月0日的时间差（以日为单位）yearday
        $yearday = 0;
        for ($i = 1; $i <= $month - 1; $i++) {
            switch ($i) {
                case 1:
                case 3:
                case 5:
                case 7:
                case 8:
                case 10:
                case 12:
                    $yearday += 31;
                    break;
                case 4:
                case 6:
                case 9:
                case 11:
                    $yearday += 30;
                    break;
                case 2:
                    if ($year % 4 == 0 && $year % 100 <> 0 || $year % 400 == 0) {
                        $yearday += 29;
                        break;
                    } else {
                        $yearday += 28;
                        break;
                    }
            }
        }
        $yearday = $yearday + $day;

        //计算日的六十甲子数 day60
        $day60 = ($yearlast + $yearday + 6015) % 60;
        //确定 日干 dg  日支  dz
        $dg = $day60 % 10;
        $dz = $day60 % 12;

        $dg1 = $this->ctg[$dg];
        $dz1 = $this->cdz[$dz];

        if ($hour == '') $hour = 0;
        $tz = floor(($hour + 3) / 2) % 12;
        if ($tz == 0 and ($jqArr[1] == $month and $jqArr[2] == $day) and $hour >= $jqArr[3] and $minute >= $jqArr[4]) {
            $tg = ($dg * 2 + $tz) % 10;
        } else {
            $tg = ($dg * 2 + $tz + 8) % 10;
        }

        $tg1 = $this->ctg[$tg];
        $tz1 = $this->cdz[$tz];
        if ($ifx == 0) {
            return array($yg1, $yz1, $mg1, $mz1, $dg1, $dz1, $tg1, $tz1);
        } else {
            return array($yg, $yz, $mg, $mz, $dg, $dz, $tg, $tz);
        }
    }

    public function getMzQyjs($month, $day, $mz_qyjs)
    {
        $md = $month * 100 + $day;
        if ($md >= 204 && $md <= 305) {
            $mz = 3;
            $qyjs = floor((($month - 2) * 30 + $day - 4) / 3);
        }

        if ($md >= 306 && $md <= 404) {
            $mz = 4;
            $qyjs = floor((($month - 3) * 30 + $day - 6) / 3);
        }

        if ($md >= 405 && $md <= 504) {
            $mz = 5;
            $qyjs = floor((($month - 4) * 30 + $day - 5) / 3);
        }

        if ($md >= 505 && $md <= 605) {
            $mz = 6;
            $qyjs = floor((($month - 5) * 30 + $day - 5) / 3);
        }

        if ($md >= 606 && $md <= 706) {
            $mz = 7;
            $qyjs = floor((($month - 6) * 30 + $day - 6) / 3);
        }

        if ($md >= 707 && $md <= 807) {
            $mz = 8;
            $qyjs = floor((($month - 7) * 30 + $day - 7) / 3);
        }

        if ($md >= 808 && $md <= 907) {
            $mz = 9;
            $qyjs = floor((($month - 8) * 30 + $day - 8) / 3);
        }

        if ($md >= 908 && $md <= 1007) {
            $mz = 10;
            $qyjs = floor((($month - 9) * 30 + $day - 8) / 3);
        }

        if ($md >= 1008 && $md <= 1106) {
            $mz = 11;
            $qyjs = floor((($month - 10) * 30 + $day - 8) / 3);
        }

        if ($md >= 1107 && $md <= 1207) {
            $mz = 0;
            $qyjs = floor((($month - 11) * 30 + $day - 7) / 3);
        }

        if ($md >= 1208 && $md <= 1231) {
            $mz = 1;
            $qyjs = floor(($day - 8) / 3);
        }

        if ($md >= 101 && $md <= 105) {
            $mz = 1;
            $qyjs = floor((30 + $day - 4) / 3);
        }

        if ($md >= 106 && $md <= 203) {
            $mz = 2;
            $qyjs = floor((($month - 1) * 30 + $day - 6) / 3);
        }


        if ($mz_qyjs == 1) {
            return $mz;
        } else {
            return $qyjs;
        }
    }

    /**
     * 计算字符串出现的字符次数
     * @param $array
     * @param $wh
     * @return string
     */
    public function getWordNum($array, $wh): string
    {
        $b = array_count_values($array);
        $c = $b[$wh];
        if ($c == '') {
            $c = 0;
        }
        return $c;
    }

    /**
     * 生肖
     * @param $zhi
     * @return mixed|string
     */
    public function animal($zhi)
    {
        $dizhi = $this->cdz;
        $i = array_search($zhi, $dizhi);
        return $this->animal[$i];
    }

    /**
     * 获取纳音
     * @param string $gz [干支]
     * @return string
     */
    public function getNaYin($gz): string
    {
        $gzu = ["甲子", "丙寅", "戊辰", "庚午", "壬申", "甲戌", "丙子", "戊寅", "庚辰", "壬午", "甲申", "丙戌", "戊子", "庚寅", "壬辰", "甲午", "丙申", "戊戌", "庚子", "壬寅", "甲辰", "丙午", "戊申", "庚戌", "壬子", "甲寅", "丙辰", "戊午", "庚申", "壬戌"];
        $zzu = ["乙丑", "丁卯", "己巳", "辛未", "癸酉", "乙亥", "丁丑", "己卯", "辛巳", "癸未", "乙酉", "丁亥", "己丑", "辛卯", "癸巳", "乙未", "丁酉", "己亥", "辛丑", "癸卯", "乙巳", "丁未", "已酉", "辛亥", "癸丑", "乙卯", "丁巳", "己未", "辛酉", "癸亥"];
        $nyzu = ["海中金", "炉中火", "大林木", "路旁土", "剑锋金", "山头火", "涧下水", "城头土", "白腊金", "杨柳木 ", "泉中水", "屋上土", "霹雳火", "松柏木", "长流水", "砂石金", "山下火", "平地木", "壁上土", "金薄金", "覆灯火", "天河水", "大驿土", "钗环金", "桑柘木", "大溪水", "沙中土", "天上火", "石榴木", "大海水"];
        $z1 = array_search($gz, $gzu);
        if ($z1 != false) {
            return $nyzu[$z1];
        } else {
            return $nyzu[array_search($gz, $zzu)];
        }
    }

    /**
     * 获取旬空
     * @param $gz [干支]
     * @return string
     */
    public function xunKong($gz): string
    {
        $xkarr = [
            ["甲子", "乙丑", "丙寅", "丁卯", "戊辰", "己巳", "庚午", "辛未", "壬申", "癸酉", "戌亥"],
            ["甲戌", "乙亥", "丙子", "丁丑", "戊寅", "己卯", "庚辰", "辛巳", "壬午", "癸未", "申酉"],
            ["甲申", "乙酉", "丙戌", "丁亥", "戊子", "己丑", "庚寅", "辛卯", "壬辰", "癸巳", "午未"],
            ["甲午", "乙未", "丙申", "丁酉", "戊戌", "己亥", "庚子", "辛丑", "壬寅", "癸卯", "辰巳"],
            ["甲辰", "乙巳", "丙午", "丁未", "戊申", "己酉", "庚戌", "辛亥", "壬子", "癸丑", "寅卯"],
            ["甲寅", "乙卯", "丙辰", "丁巳", "戊午", "己未", "庚申", "辛酉", "壬戌", "癸亥", "子丑"]
        ];
        $tag = 0;
        for ($i = 0; $i <= 5; $i++) {
            for ($j = 0; $j <= 10; $j++) {
                if ($xkarr[$i][$j] == $gz) {
                    $xunk = $xkarr[$i][10];
                    $tag = 1;
                    break;
                }
            }
            if ($tag == 1) break;
        }
        return ($xunk);
    }

    /**
     * 五行
     * @param $value
     * @return array
     * @throws Exception
     */
    public function wuXing($value): array
    {
        switch ($value) {
            case "子":
            case "壬":
                return [
                    'type' => 3, // 金木水火土 序号
                    'attar' => 2, // 1阴 2阳
                    'value' => '水'
                ];
            case "癸":
            case "亥":
                return [
                    'type' => 3,
                    'attar' => 1,
                    'value' => '水'
                ];

            case "甲":
            case "寅":
                return [
                    'type' => 2,
                    'attar' => 1,
                    'value' => '木'
                ];
            case "乙":
            case "卯":
                return [
                    'type' => 2,
                    'attar' => 2,
                    'value' => '木'
                ];
            case "丙":
            case "午":
                return [
                    'type' => 4,
                    'attar' => 1,
                    'value' => '火'
                ];
            case "丁":
            case "巳":
                return [
                    'type' => 4,
                    'attar' => 2,
                    'value' => '火'
                ];
            case "庚":
            case "申":
                return [
                    'type' => 1,
                    'attar' => 2,
                    'value' => '金'
                ];
            case "辛":
            case "酉":
                return [
                    'type' => 1,
                    'attar' => 1,
                    'value' => '金'
                ];


            case "戊":
            case "辰":
            case "戌":
                return [
                    'type' => 5,
                    'attar' => 1,
                    'value' => '土'
                ];
            case "己":
            case "丑":
            case "未":
                return [
                    'type' => 5,
                    'attar' => 2,
                    'value' => '土'
                ];
            default:
                throw new Exception('Unexpected value');
        }
    }

}