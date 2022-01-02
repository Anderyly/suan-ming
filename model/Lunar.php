<?php
/**
 * 农历
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */


namespace com\anderyly\calendar;

class Lunar
{

    /**
     * 中文数字
     */
    public array $chinese_year = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];

    public array $chinese_hour = ['子', '丑', '丑', '寅', '寅', '卯', '卯', '辰', '辰', '巳', '巳', '午', '午', '未', '未', '申', '申', '酉', '酉', '戌', '戌', '亥', '亥', '子'
    ];

    public array $chinese_number = ['日', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];

    /**
     * 农历月份常用称呼
     * @var array
     */
    public array $chinese_month = ['正', '二', '三', '四', '五', '六', '七', '八', '九', '十', '冬', '腊'];

    /**
     * 农历日期常用称呼
     * @var array
     */
    public array $chinese_day = ['初', '十', '廿', '卅'];

    public static function instance()
    {
        return new self();
    }

    /**
     * 农历月份常用名称
     * @param int $mm
     * @return string
     */
    public function monthChinese(int $mm): string
    {
        $k = $mm - 1;
        return $this->chinese_month[$k];
    }

    /**
     * 时辰转大写
     * @param $h
     * @return string
     */
    public function hourChinese($h): string
    {
        return $this->chinese_hour[$h];
    }

    /**
     * 农历日期数字返回汉字表示法
     * @param int $dd
     * @return string
     */
    public function dayChinese(int $dd): string
    {
        $str = '';

        switch ($dd) {
            case 10:
                $str = $this->chinese_day[0] . $this->chinese_number[10];
                break;
            case 20:
                $str = $this->chinese_day[2] . $this->chinese_number[10];
                break;
            case 30:
                $str = $this->chinese_day[3] . $this->chinese_number[10];
                break;
            default:
                $k = intval(floor($dd / 10));
                $m = $dd % 10;
                $str = $this->chinese_day[$k] . $this->chinese_number[$m];
        }

        return $str;
    }

    /**
     * 将农历时间转换成公历时间
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @param int $isLeap 是否闰月
     * @return array|false
     */
    public function solar(int $yy, int $mm, int $dd, int $isLeap): bool|array
    {
        if ($yy < -7000 || $yy > 7000) { //超出計算能力
            return false;
        }
        if ($yy < -1000 || $yy > 3000) { //适用于西元-1000年至西元3000年,超出此范围误差较大
            return false;
        }
        if ($mm < 1 || $mm > 12) { //輸入月份必須在1-12月之內
            return false;
        }
        if ($dd < 1 || $dd > 30) { //輸入日期必須在1-30日之內
            return false;
        }

        list($jdzq, $jdnm, $mc) = JieQi::instance()->getMonthCode($yy);

        $leap = 0; //若閏月旗標為0代表無閏月
        for ($j = 1; $j <= 14; $j++) { //確認指定年前一年11月開始各月是否閏月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是,則將此閏月代碼放入閏月旗標內
                $leap = floor($mc[$j] + 0.5); //leap=0對應阴曆11月,1對應阴曆12月,2對應阴曆隔年1月,依此類推.
                break;
            }
        }

        $mm = $mm + 2; //11月對應到1,12月對應到2,1月對應到3,2月對應到4,依此類推

        for ($i = 0; $i <= 14; $i++) { //求算阴曆各月之大小,大月30天,小月29天
            $nofd[$i] = floor($jdnm[$i + 1] + 0.5) - floor($jdnm[$i] + 0.5); //每月天數,加0.5是因JD以正午起算
        }

        $jd = 0; //儒略日历时间
        $er = 0; //若輸入值有錯誤,er值將被設定為非0

        if ($isLeap) { //若是閏月
            if ($leap < 3) { //而旗標非閏月或非本年閏月,則表示此年不含閏月.leap=0代表無閏月,=1代表閏月為前一年的11月,=2代表閏月為前一年的12月
                $er = 1; //此年非閏年
            } else { //若本年內有閏月
                if ($leap != $mm) { //但不為輸入的月份
                    $er = 2; //則此輸入的月份非閏月,此月非閏月
                } else { //若輸入的月份即為閏月
                    if ($dd <= $nofd[$mm]) { //若輸入的日期不大於當月的天數
                        $jd = $jdnm[$mm] + $dd - 1; //則將當月之前的JD值加上日期之前的天數
                    } else { //日期超出範圍
                        $er = 3;
                    }
                }
            }
        } else { //若沒有勾選閏月則
            if ($leap == 0) { //若旗標非閏月,則表示此年不含閏月(包括前一年的11月起之月份)
                if ($dd <= $nofd[$mm - 1]) { //若輸入的日期不大於當月的天數
                    $jd = $jdnm[$mm - 1] + $dd - 1; //則將當月之前的JD值加上日期之前的天數
                } else { //日期超出範圍
                    $er = 4;
                }
            } else { //若旗標為本年有閏月(包括前一年的11月起之月份) 公式nofd(mx - (mx > leap) - 1)的用意為:若指定月大於閏月,則索引用mx,否則索引用mx-1
                if ($dd <= $nofd[$mm + ($mm > $leap) - 1]) { //若輸入的日期不大於當月的天數
                    $jd = $jdnm[$mm + ($mm > $leap) - 1] + $dd - 1; //則將當月之前的JD值加上日期之前的天數
                } else { //日期超出範圍
                    $er = 4;
                }
            }
        }

        return $er ? false : array_slice(Julian::instance()->solar($jd), 0, 3);
    }


    /**
     * 年份转大写
     * @param $year
     * @return string
     */
    public function yearChinese($year): string
    {
        $str = '';
        $yearArr = str_split($year);

        foreach ($yearArr as $v) {
            $str .= $this->chinese_year[$v];
        }
        return $str;
    }

    /**
     * 阴历小写转大写
     * @param $yin_li
     * @return array
     */
    public function toUp($yin_li): array
    {
        $yin_year = $this->yearChinese($yin_li[0]);
        $yin_month = $this->MonthChinese($yin_li[1]);
        $yin_day = $this->DayChinese($yin_li[2]);
        if ($yin_li[3] == 1) {
            $str = $yin_year . '年' . '闰' . $yin_month . '月' . $yin_day;
        } else {
            $str = $yin_year . '年' . $yin_month . '月' . $yin_day;
        }

        return [
            $yin_year,
            $yin_month,
            $yin_day,
            $str
        ];
    }

    /**
     * 获取农历某个月有多少天
     * @param int $yy
     * @param int $mm
     * @param int $isLeap
     * @return number
     */
    public function getDays(int $yy, int $mm, int $isLeap): int
    {
        if ($yy < -1000 || $yy > 3000) { //适用于西元-1000年至西元3000年,超出此范围误差较大
            return 0;
        }
        if ($mm < 1 || $mm > 12) { //輸入月份必須在1-12月之內
            return 0;
        }
        list($jdzq, $jdnm, $mc) = JieQi::instance()->getMonthCode($yy);

        $leap = 0; //若閏月旗標為0代表無閏月
        for ($j = 1; $j <= 14; $j++) { //確認指定年前一年11月開始各月是否閏月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是,則將此閏月代碼放入閏月旗標內
                $leap = floor($mc[$j] + 0.5); //leap=0對應阴曆11月,1對應阴曆12月,2對應阴曆隔年1月,依此類推.
                break;
            }
        }

        $mm = $mm + 2; //11月對應到1,12月對應到2,1月對應到3,2月對應到4,依此類推

        for ($i = 0; $i <= 14; $i++) { //求算阴曆各月之大小,大月30天,小月29天
            $nofd[$i] = floor($jdnm[$i + 1] + 0.5) - floor($jdnm[$i] + 0.5); //每月天數,加0.5是因JD以正午起算
        }

        $dy = 0; //当月天数
        $er = 0; //若輸入值有錯誤,er值將被設定為非0

        if ($isLeap) { //若是閏月
            if ($leap < 3) { //而旗標非閏月或非本年閏月,則表示此年不含閏月.leap=0代表無閏月,=1代表閏月為前一年的11月,=2代表閏月為前一年的12月
                $er = 1; //此年非閏年
            } else { //若本年內有閏月
                if ($leap != $mm) { //但不為輸入的月份
                    $er = 2; //則此輸入的月份非閏月,此月非閏月
                } else { //若輸入的月份即為閏月
                    $dy = $nofd[$mm];
                }
            }
        } else { //若沒有勾選閏月則
            if ($leap == 0) { //若旗標非閏月,則表示此年不含閏月(包括前一年的11月起之月份)
                $dy = $nofd[$mm - 1];
            } else { //若旗標為本年有閏月(包括前一年的11月起之月份) 公式nofd(mx - (mx > leap) - 1)的用意為:若指定月大於閏月,則索引用mx,否則索引用mx-1
                $dy = $nofd[$mm + ($mm > $leap) - 1];
            }
        }
        return (int)$dy;
    }

    /**
     * 获取农历某年的闰月,0为无闰月
     * @param int $yy
     * @return number
     */
    public function GetLeap(int $yy): int
    {
        list($jdzq, $jdnm, $mc) = JieQi::instance()->getMonthCode($yy);

        $leap = 0; //若閏月旗標為0代表無閏月
        for ($j = 1; $j <= 14; $j++) { //確認指定年前一年11月開始各月是否閏月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是,則將此閏月代碼放入閏月旗標內
                $leap = floor($mc[$j] + 0.5); //leap=0對應阴曆11月,1對應阴曆12月,2對應阴曆隔年1月,依此類推.
                break;
            }
        }
        return (int)max(0, $leap - 2);
    }

}