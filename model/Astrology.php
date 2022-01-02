<?php
/**
 * 星运 自坐
 * 星运用日干 当前地支
 * 自坐用当前天干地支
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */

namespace com\anderyly\calendar;

use DateTime;
use Exception;

class Astrology
{
    private array $table = [
        '甲' => [
            '子' => '沐浴',
            '丑' => '冠带',
            '寅' => '临官',
            '卯' => '帝旺',
            '辰' => '衰',
            '巳' => '病',
            '午' => '死',
            '未' => '墓',
            '申' => '绝',
            '酉' => '胎',
            '戌' => '养',
            '亥' => '长生',
        ],
        '乙' => [
            '子' => '病',
            '丑' => '衰',
            '寅' => '帝旺',
            '卯' => '临官',
            '辰' => '冠带',
            '巳' => '沐浴',
            '午' => '长生',
            '未' => '养',
            '申' => '胎',
            '酉' => '绝',
            '戌' => '墓',
            '亥' => '死',
        ],
        '丙' => [
            '子' => '胎',
            '丑' => '养',
            '寅' => '长生',
            '卯' => '沐浴',
            '辰' => '冠带',
            '巳' => '临官',
            '午' => '帝旺',
            '未' => '衰',
            '申' => '病',
            '酉' => '死',
            '戌' => '墓',
            '亥' => '绝',
        ],
        '丁' => [
            '子' => '绝',
            '丑' => '墓',
            '寅' => '死',
            '卯' => '病',
            '辰' => '衰',
            '巳' => '帝旺',
            '午' => '临官',
            '未' => '冠带',
            '申' => '沐浴',
            '酉' => '长生',
            '戌' => '养',
            '亥' => '胎',
        ],
        '戊' => [
            '子' => '胎',
            '丑' => '养',
            '寅' => '长生',
            '卯' => '沐浴',
            '辰' => '冠带',
            '巳' => '临官',
            '午' => '帝旺',
            '未' => '衰',
            '申' => '病',
            '酉' => '死',
            '戌' => '墓',
            '亥' => '绝',
        ],
        '己' => [
            '子' => '绝',
            '丑' => '墓',
            '寅' => '死',
            '卯' => '病',
            '辰' => '衰',
            '巳' => '帝旺',
            '午' => '临官',
            '未' => '冠带',
            '申' => '沐浴',
            '酉' => '长生',
            '戌' => '养',
            '亥' => '胎',
        ],
        '庚' => [
            '子' => '死',
            '丑' => '墓',
            '寅' => '绝',
            '卯' => '胎',
            '辰' => '养',
            '巳' => '长生',
            '午' => '沐浴',
            '未' => '冠带',
            '申' => '临官',
            '酉' => '帝旺',
            '戌' => '衰',
            '亥' => '病',
        ],
        '辛' => [
            '子' => '长生',
            '丑' => '养',
            '寅' => '胎',
            '卯' => '绝',
            '辰' => '墓',
            '巳' => '死',
            '午' => '病',
            '未' => '衰',
            '申' => '帝旺',
            '酉' => '临官',
            '戌' => '冠带',
            '亥' => '沐浴',
        ],
        '壬' => [
            '子' => '帝旺',
            '丑' => '衰',
            '寅' => '病',
            '卯' => '死',
            '辰' => '墓',
            '巳' => '绝',
            '午' => '胎',
            '未' => '养',
            '申' => '长生',
            '酉' => '沐浴',
            '戌' => '冠带',
            '亥' => '临官',
        ],
        '癸' => [
            '子' => '临官',
            '丑' => '冠带',
            '寅' => '沐浴',
            '卯' => '长生',
            '辰' => '养',
            '巳' => '胎',
            '午' => '绝',
            '未' => '墓',
            '申' => '死',
            '酉' => '病',
            '戌' => '衰',
            '亥' => '帝旺',
        ],
    ];

    public static function instance()
    {
        return new self();
    }

    public function get($gan, $zhi)
    {
        return $this->table[$gan][$zhi];
    }

    public function getVal($gan, $zx): string
    {
        $arr1 = ["长生", "沐浴", "冠带", "临官", "帝旺", "衰", "病", "死", "墓", "绝", "胎", "养"];
        switch ($gan) {
            case "甲":
                $xu = $zx;
                break;
            case "戊":
            case "丙":
                $xu = (12 + $zx - 3) % 12;
                break;
            case "庚":
                $xu = (12 + $zx - 6) % 12;
                break;
            case "壬":
                $xu = (12 + $zx - 9) % 12;
                break;
            case "乙":
                $xu = (24 - $zx - 5) % 12;
                break;
            case "己":
            case "丁":
                $xu = (24 - $zx - 2) % 12;
                break;
            case "辛":
                $xu = (24 - $zx - 11) % 12;
                break;
            case "癸":
                $xu = (24 - $zx - 8) % 12;
                break;
        }
        return $arr1[$xu];
    }

}