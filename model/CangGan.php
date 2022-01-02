<?php
/**
 * 藏干
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */

namespace com\anderyly\calendar;

class CangGan
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
     * 十二地支对应
     * @var array
     */
    public array $tdz = ["壬甲", "癸", "癸己辛", "丙甲戊", "乙", "乙戊癸", "戊丙庚", "己丁", "丁己乙", "壬庚戊", "辛", "辛戊丁"];

    private array $bz;

    private array $value;

    private array $desc = [];

    public static function instance()
    {
        return new self();
    }

    /**
     * 获取藏干
     * @param array $bz [四柱的序号]
     * @return CangGan
     */
    public function param(array $bz): CangGan
    {
        $this->bz = $bz;
        $this->value = [
            $this->tdz[$bz[1]],
            $this->tdz[$bz[3]],
            $this->tdz[$bz[5]],
            $this->tdz[$bz[7]],
        ];

        return $this;
    }

    private function getDesc($field, $index)
    {
        $arr = $this->desc;
        $value = substr($this->value[$index], 0, 3);
        $arr[$field][] = [
            'type' => SuanMing::instance()->wuXing($value)['type'],
            'value' => $value,
            'attar' => TenGod::instance()->get(array_search($value, $this->ctg), $this->bz[5])
        ];

        $value = substr($this->value[$index], 3, 3);
        if (empty($value)) goto End;
        $arr[$field][] = [
            'type' => SuanMing::instance()->wuXing($value)['type'],
            'value' => $value,
            'attar' => TenGod::instance()->get(array_search($value, $this->ctg), $this->bz[5])
        ];

        $value = substr($this->value[$index], 6, 3);
        if (empty($value)) goto End;
        $arr[$field][] = [
            'type' => SuanMing::instance()->wuXing($value)['type'],
            'value' => $value,
            'attar' => TenGod::instance()->get(array_search($value, $this->ctg), $this->bz[5])
        ];

        End:
            $this->desc = $arr;

        return $this;
    }

    public function get()
    {
        $this->getDesc('year', 0);
        $this->getDesc('month', 1);
        $this->getDesc('day', 2);
        $this->getDesc('hour', 3);
        return $this->desc;
    }


}