<?php
/**
 * 节气
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://blog.aaayun.cc
 * @copyright Copyright (c) 2021
 */

namespace com\anderyly\calendar;

class JieQi
{

    /**
     * 均值朔望月长(mean length of synodic month)
     * @var float
     */
    private float $synmonth = 29.530588853;

    /**
     * 因子
     * @var array
     */
    private array $ptsA = [485, 203, 199, 182, 156, 136, 77, 74, 70, 58, 52, 50, 45, 44, 29, 18, 17, 16, 14, 12, 12, 12, 9, 8];

    private array $ptsB = [324.96, 337.23, 342.08, 27.85, 73.14, 171.52, 222.54, 296.72, 243.58, 119.81, 297.17, 21.02, 247.54,
        325.15, 60.93, 155.12, 288.79, 198.04, 199.76, 95.39, 287.11, 320.81, 227.73, 15.45];

    private array $ptsC = [1934.136, 32964.467, 20.186, 445267.112, 45036.886, 22518.443, 65928.934, 3034.906, 9037.513,
        33718.147, 150.678, 2281.226, 29929.562, 31555.956, 4443.417, 67555.328, 4562.452, 62894.029, 31436.921,
        14577.848, 31931.756, 34777.259, 1222.114, 16859.074];

    public static function instance()
    {
        return new self();
    }

    public function Get24JieQi(int $yy): array
    {

        $jq = [];

        $dj = $this->getAdjustedJQ($yy - 1, 21, 23); //求出含指定年立春開始之3個節氣JD值,以前一年的年值代入
        foreach ($dj as $k => $v) {
            if ($k < 21) {
                continue;
            }
            if ($k > 23) {
                continue;
            }
            $jq[] = Julian::instance()->Solar($dj[$k]); //21立春;22雨水;23惊蛰
        }

        $dj = $this->getAdjustedJQ($yy, 0, 20); //求出指定年節氣之JD值,從春分開始
        foreach ($dj as $k => $v) {
            $jq[] = Julian::instance()->Solar($dj[$k]);
        }

        return $jq;
    }

    /**
     * 求出自冬至点为起点的连续15个中气
     * @param int $yy
     * @return array jq[(2*$k+18)%24]
     */
    public function GetZQsinceWinterSolstice(int $yy): array
    {
        $jdzq = array();

        $dj = $this->getAdjustedJQ($yy - 1, 18, 23); // 求出指定年冬至开始之节气JD值,以前一年的值代入
        $jdzq[0] = $dj[18]; //冬至
        $jdzq[1] = $dj[20]; //大寒
        $jdzq[2] = $dj[22]; //雨水

        $dj = $this->getAdjustedJQ($yy, 0, 23); // 求出指定年节气之JD值
        foreach ($dj as $k => $v) {
            if ($k % 2 != 0) {
                continue;
            }
            $jdzq[] = $dj[$k];
        }

        return $jdzq;
    }

    /**
     * 以比较日期法求算冬月及其余各月名称代码,包含闰月,冬月为0,腊月为1,正月为2,其余类推.闰月多加0.5
     * @param int $yy
     * @return array
     */
    public function getMonthCode(int $yy): array
    {
        $mc = [];

        $jdzq = JieQi::instance()->GetZQsinceWinterSolstice($yy); // 取得以前一年冬至为起点之连续15个中气
        $jdnm = JieQi::instance()->GetSMsinceWinterSolstice($yy, $jdzq[0]); // 求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月點
        $yz = 0; // 设定旗标,0表示未遇到闰月,1表示已遇到闰月
        if (floor($jdzq[12] + 0.5) >= floor($jdnm[13] + 0.5)) { // 若第13个中气jdzq(12)大于或等于第14个新月jdnm(13)
            for ($i = 1; $i <= 14; $i++) { // 表示此两个冬至之间的11个中气要放到12个朔望月中,
                // 至少有一个朔望月不含中气,第一个不含中气的月即为闰月
                // 若阴历腊月起始日大於冬至中气日,且阴历正月起始日小于或等于大寒中气日,则此月为闰月,其余同理
//                if ((floor($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
                if ((($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; //标示遇到闰月
                } else {
                    $mc[$i] = $i - $yz; // 遇到闰月开始,每个月号要减1
                }
            }
        } else { // 否则表示两个连续冬至之间只有11个整月,故无闰月
            for ($i = 0; $i <= 12; $i++) { // 直接赋予这12个月月代码
                $mc[$i] = $i;
            }
            for ($i = 13; $i <= 14; $i++) { //处理次一置月年的11月与12月,亦有可能含闰月
                // 若次一阴历腊月起始日大于附近的冬至中气日,且阴历正月起始日小于或等于大寒中气日,则此月为腊月,次一正月同理.
//                if (floor(($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
                if ((($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; // 标示遇到闰月
                } else {
                    $mc[$i] = $i - $yz; // 遇到闰月开始,每个月号要减1
                }
            }
        }
        return [$jdzq, $jdnm, $mc];
    }

    /**
     * 获取指定年的春分开始作Perturbaton调整后的24节气,可以多取2个
     * @param int $yy
     * @param int $start 0-25
     * @param int $end 0-25
     * @return array
     */
    private function getAdjustedJQ(int $yy, int $start, int $end): array
    {
        if ($start < 0 || $start > 25) {
            return [];
        }
        if ($end < 0 || $end > 25) {
            return [];
        }

        $jq = [];

        $jqjd = $this->getYearJQ($yy); // 获取该年春分开始的24节气时间点
        foreach ($jqjd as $k => $jd) {
            if ($k < $start) {
                continue;
            }
            if ($k > $end) {
                continue;
            }
            $ptb = $this->Perturbation($jd); // 取得受perturbation影响所需微调
            $dt = $this->DeltaT($yy, intval(floor(($k + 1) / 2) + 3)); // 修正dynamical time to Universal time
            $jq[$k] = $jd + $ptb - $dt / 60 / 24; // 加上摄动调整值ptb,减去对应的Delta T值(分钟转换为日)
            $jq[$k] = $jq[$k] + 1 / 3; // 因中国(北京、重庆、上海)时间比格林威治时间先行8小时，即1/3日
        }
        return $jq;
    }

    /**
     * 获取指定年的春分开始的24节气,另外多取2个确保覆盖完一个公历年
     * @param int $yy [年]
     * @return array
     */
    private function getYearJQ(int $yy): array
    {
        if (!$jd = $this->VE($yy)) { // 该年的春分點
            return [];
        }
        $ty = $this->VE($yy + 1) - $jd; // 该年的回归年长

        $num = 24 + 2; //另外多取2个确保覆盖完一个公历年

        $ath = 2 * pi() / 24;
        $tx = ($jd - 2451545) / 365250;
        $e = 0.0167086342 - 0.0004203654 * $tx - 0.0000126734 * $tx * $tx + 0.0000001444 * $tx * $tx * $tx - 0.0000000002 * $tx * $tx * $tx * $tx + 0.0000000003 * $tx * $tx * $tx * $tx * $tx;
        $tt = $yy / 1000;
        $vp = 111.25586939 - 17.0119934518333 * $tt - 0.044091890166673 * $tt * $tt - 4.37356166661345E-04 * $tt * $tt * $tt + 8.16716666602386E-06 * $tt * $tt * $tt * $tt;
        $rvp = $vp * 2 * pi() / 360;
        $peri = array();
        for ($i = 0; $i < $num; $i++) {
            $flag = 0;
            $th = $ath * $i + $rvp;
            if ($th > pi() && $th <= 3 * pi()) {
                $th = 2 * pi() - $th;
                $flag = 1;
            }
            if ($th > 3 * pi()) {
                $th = 4 * pi() - $th;
                $flag = 2;
            }
            $f1 = 2 * atan((sqrt((1 - $e) / (1 + $e)) * tan($th / 2)));
            $f2 = ($e * sqrt(1 - $e * $e) * sin($th)) / (1 + $e * cos($th));
            $f = ($f1 - $f2) * $ty / 2 / pi();
            if ($flag == 1) {
                $f = $ty - $f;
            }
            if ($flag == 2) {
                $f = 2 * $ty - $f;
            }
            $peri[$i] = $f;
        }
        $jq = [];
        for ($i = 0; $i < $num; $i++) {
            $jq[$i] = $jd + $peri[$i] - $peri[0];
        }

        return $jq;
    }

    /**
     * 地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation)
     * @param float $jd
     * @return float|int 返回某时刻(儒略日历)的摄动偏移量
     */
    private function Perturbation($jd): float|int
    {
        $t = ($jd - 2451545) / 36525;
        $s = 0;
        for ($k = 0; $k <= 23; $k++) {
            $s = $s + $this->ptsA[$k] * cos($this->ptsB[$k] * 2 * pi() / 360 + $this->ptsC[$k] * 2 * pi() / 360 * $t);
        }
        $w = 35999.373 * $t - 2.47;
        $l = 1 + 0.0334 * cos($w * 2 * pi() / 360) + 0.0007 * cos(2 * $w * 2 * pi() / 360);
        return 0.00001 * $s / $l;
    }

    /**
     * 计算指定年(公历)的春分点(vernal equinox),
     * 但因地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation),必须将此现象产生的偏移量加入.
     * @param int $yy
     * @return float|bool|int 返回儒略日历格林威治时间
     */
    private function VE(int $yy): float|bool|int
    {
        if ($yy < -8000) {
            return false;
        }
        if ($yy > 8001) {
            return false;
        }
        if ($yy >= 1000 && $yy <= 8001) {
            $m = ($yy - 2000) / 1000;
            return 2451623.80984 + 365242.37404 * $m + 0.05169 * $m * $m - 0.00411 * $m * $m * $m - 0.00057 * $m * $m * $m * $m;
        }
        if ($yy >= -8000 && $yy < 1000) {
            $m = $yy / 1000;
            return 1721139.29189 + 365242.1374 * $m + 0.06134 * $m * $m + 0.00111 * $m * $m * $m - 0.00071 * $m * $m * $m * $m;
        }

        return false;
    }

    /**
     * 求∆t
     * @param int $yy 年份
     * @param int $mm 月份
     * @return float|int
     */
    private function DeltaT(int $yy, int $mm): float|int
    {

        $y = $yy + ($mm - 0.5) / 12;

        if ($y <= -500) {
            $u = ($y - 1820) / 100;
            $dt = (-20 + 32 * $u * $u);
        } else {
            if ($y < 500) {
                $u = $y / 100;
                $dt = (10583.6 - 1014.41 * $u + 33.78311 * $u * $u - 5.952053 * $u * $u * $u - 0.1798452 * $u * $u * $u * $u + 0.022174192 * $u * $u * $u * $u * $u + 0.0090316521 * $u * $u * $u * $u * $u * $u);
            } else {
                if ($y < 1600) {
                    $u = ($y - 1000) / 100;
                    $dt = (1574.2 - 556.01 * $u + 71.23472 * $u * $u + 0.319781 * $u * $u * $u - 0.8503463 * $u * $u * $u * $u - 0.005050998 * $u * $u * $u * $u * $u + 0.0083572073 * $u * $u * $u * $u * $u * $u);
                } else {
                    if ($y < 1700) {
                        $t = $y - 1600;
                        $dt = (120 - 0.9808 * $t - 0.01532 * $t * $t + $t * $t * $t / 7129);
                    } else {
                        if ($y < 1800) {
                            $t = $y - 1700;
                            $dt = (8.83 + 0.1603 * $t - 0.0059285 * $t * $t + 0.00013336 * $t * $t * $t - $t * $t * $t * $t / 1174000);
                        } else {
                            if ($y < 1860) {
                                $t = $y - 1800;
                                $dt = (13.72 - 0.332447 * $t + 0.0068612 * $t * $t + 0.0041116 * $t * $t * $t - 0.00037436 * $t * $t * $t * $t + 0.0000121272 * $t * $t * $t * $t * $t - 0.0000001699 * $t * $t * $t * $t * $t * $t + 0.000000000875 * $t * $t * $t * $t * $t * $t * $t);
                            } else {
                                if ($y < 1900) {
                                    $t = $y - 1860;
                                    $dt = (7.62 + 0.5737 * $t - 0.251754 * $t * $t + 0.01680668 * $t * $t * $t - 0.0004473624 * $t * $t * $t * $t + $t * $t * $t * $t * $t / 233174);
                                } else {
                                    if ($y < 1920) {
                                        $t = $y - 1900;
                                        $dt = (-2.79 + 1.494119 * $t - 0.0598939 * $t * $t + 0.0061966 * $t * $t * $t - 0.000197 * $t * $t * $t * $t);
                                    } else {
                                        if ($y < 1941) {
                                            $t = $y - 1920;
                                            $dt = (21.2 + 0.84493 * $t - 0.0761 * $t * $t + 0.0020936 * $t * $t * $t);
                                        } else {
                                            if ($y < 1961) {
                                                $t = $y - 1950;
                                                $dt = (29.07 + 0.407 * $t - $t * $t / 233 + $t * $t * $t / 2547);
                                            } else {
                                                if ($y < 1986) {
                                                    $t = $y - 1975;
                                                    $dt = (45.45 + 1.067 * $t - $t * $t / 260 - $t * $t * $t / 718);
                                                } else {
                                                    if ($y < 2005) {
                                                        $t = $y - 2000;
                                                        $dt = (63.86 + 0.3345 * $t - 0.060374 * $t * $t + 0.0017275 * $t * $t * $t + 0.000651814 * $t * $t * $t * $t + 0.00002373599 * $t * $t * $t * $t * $t);
                                                    } else {
                                                        if ($y < 2050) {
                                                            $t = $y - 2000;
                                                            $dt = (62.92 + 0.32217 * $t + 0.005589 * $t * $t);
                                                        } else {
                                                            if ($y < 2150) {
                                                                $u = ($y - 1820) / 100;
                                                                $dt = (-20 + 32 * $u * $u - 0.5628 * (2150 - $y));
                                                            } else {
                                                                $u = ($y - 1820) / 100;
                                                                $dt = (-20 + 32 * $u * $u);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($y < 1955 || $y >= 2005) {
            $dt = $dt - (0.000012932 * ($y - 1955) * ($y - 1955));
        }
        return $dt / 60; // 将秒转换为分
    }

    /**
     * 对于指定日期时刻所属的朔望月,求出其均值新月点的月序数
     * @param float $jd
     * @return array
     */
    private function MeanNewMoon($jd): array
    {
        // $kn为从2000年1月6日14时20分36秒起至指定年月日之阴历月数,以synodic month为单位
        $kn = floor(($jd - 2451550.09765) / $this->synmonth); // 2451550.09765为2000年1月6日14时20分36秒之JD值.
        $jdt = 2451550.09765 + $kn * $this->synmonth;
        // Time in Julian centuries from 2000 January 0.5.
        $t = ($jdt - 2451545) / 36525; // 以100年为单位,以2000年1月1日12时为0点
        $thejd = $jdt + 0.0001337 * $t * $t - 0.00000015 * $t * $t * $t + 0.00000000073 * $t * $t * $t * $t;
        // 2451550.09765为2000年1月6日14时20分36秒,此为2000年后的第一个均值新月
        return [$kn, $thejd];
    }

    /**
     * 求算以含冬至中气为阴历11月开始的连续16个朔望月
     * @param int $yy 年份
     * @param float $jdws 冬至的儒略日历时间
     * @return array
     */
    public function GetSMsinceWinterSolstice(int $yy, $jdws): array
    {

        $tjd = [];
        $jd = Solar::instance()->julian($yy - 1, 11, 1, 0, 0, 0); //求年初前兩個月附近的新月點(即前一年的11月初)
        list($kn, $thejd) = $this->MeanNewMoon($jd); //求得自2000年1月起第kn個平均朔望日及其JD值
        for ($i = 0; $i <= 19; $i++) { //求出連續20個朔望月
            $k = $kn + $i;
            $mjd = $thejd + $this->synmonth * $i;
            $tjd[$i] = $this->TrueNewMoon($k) + 1 / 3; //以k值代入求瞬時朔望日,因中國比格林威治先行8小時,加1/3天
            //下式為修正dynamical time to Universal time
            $tjd[$i] = $tjd[$i] - $this->DeltaT($yy, $i - 1) / 1440; //1為1月,0為前一年12月,-1為前一年11月(當i=0時,i-1=-1,代表前一年11月)
        }
        for ($j = 0; $j <= 18; $j++) {
            if (floor($tjd[$j] + 0.5) > floor($jdws + 0.5)) {
                break;
            } // 已超過冬至中氣(比較日期法)
        }

        $jdnm = [];
        for ($k = 0; $k <= 15; $k++) { // 取上一步的索引值
            $jdnm[$k] = $tjd[$j - 1 + $k]; // 重排索引,使含冬至朔望月的索引為0
        }
        return $jdnm;
    }

    /**
     * 求出实际新月点
     * 以2000年初的第一个均值新月点为0点求出的均值新月点和其朔望月之序數 k 代入此副程式來求算实际新月点
     * @param int $k
     * @return float|int
     */
    private function TrueNewMoon($k): float|int
    {
        $jdt = 2451550.09765 + $k * $this->synmonth;
        $t = ($jdt - 2451545) / 36525; // 2451545为2000年1月1日正午12时的JD
        $t2 = $t * $t; // square for frequent use
        $t3 = $t2 * $t; // cube for frequent use
        $t4 = $t3 * $t; // to the fourth
        // mean time of phase
        $pt = $jdt + 0.0001337 * $t2 - 0.00000015 * $t3 + 0.00000000073 * $t4;
        // Sun's mean anomaly(地球绕太阳运行均值近点角)(从太阳观察)
        $m = 2.5534 + 29.10535669 * $k - 0.0000218 * $t2 - 0.00000011 * $t3;
        // Moon's mean anomaly(月球绕地球运行均值近点角)(从地球观察)
        $mprime = 201.5643 + 385.81693528 * $k + 0.0107438 * $t2 + 0.00001239 * $t3 - 0.000000058 * $t4;
        // Moon's argument of latitude(月球的纬度参数)
        $f = 160.7108 + 390.67050274 * $k - 0.0016341 * $t2 - 0.00000227 * $t3 + 0.000000011 * $t4;
        // Longitude of the ascending node of the lunar orbit(月球绕日运行轨道升交点之经度)
        $omega = 124.7746 - 1.5637558 * $k + 0.0020691 * $t2 + 0.00000215 * $t3;
        // 乘式因子
        $es = 1 - 0.002516 * $t - 0.0000074 * $t2;
        // 因perturbation造成的偏移：
        $apt1 = -0.4072 * sin((pi() / 180) * $mprime);
        $apt1 += 0.17241 * $es * sin((pi() / 180) * $m);
        $apt1 += 0.01608 * sin((pi() / 180) * 2 * $mprime);
        $apt1 += 0.01039 * sin((pi() / 180) * 2 * $f);
        $apt1 += 0.00739 * $es * sin((pi() / 180) * ($mprime - $m));
        $apt1 -= 0.00514 * $es * sin((pi() / 180) * ($mprime + $m));
        $apt1 += 0.00208 * $es * $es * sin((pi() / 180) * (2 * $m));
        $apt1 -= 0.00111 * sin((pi() / 180) * ($mprime - 2 * $f));
        $apt1 -= 0.00057 * sin((pi() / 180) * ($mprime + 2 * $f));
        $apt1 += 0.00056 * $es * sin((pi() / 180) * (2 * $mprime + $m));
        $apt1 -= 0.00042 * sin((pi() / 180) * 3 * $mprime);
        $apt1 += 0.00042 * $es * sin((pi() / 180) * ($m + 2 * $f));
        $apt1 += 0.00038 * $es * sin((pi() / 180) * ($m - 2 * $f));
        $apt1 -= 0.00024 * $es * sin((pi() / 180) * (2 * $mprime - $m));
        $apt1 -= 0.00017 * sin((pi() / 180) * $omega);
        $apt1 -= 0.00007 * sin((pi() / 180) * ($mprime + 2 * $m));
        $apt1 += 0.00004 * sin((pi() / 180) * (2 * $mprime - 2 * $f));
        $apt1 += 0.00004 * sin((pi() / 180) * (3 * $m));
        $apt1 += 0.00003 * sin((pi() / 180) * ($mprime + $m - 2 * $f));
        $apt1 += 0.00003 * sin((pi() / 180) * (2 * $mprime + 2 * $f));
        $apt1 -= 0.00003 * sin((pi() / 180) * ($mprime + $m + 2 * $f));
        $apt1 += 0.00003 * sin((pi() / 180) * ($mprime - $m + 2 * $f));
        $apt1 -= 0.00002 * sin((pi() / 180) * ($mprime - $m - 2 * $f));
        $apt1 -= 0.00002 * sin((pi() / 180) * (3 * $mprime + $m));
        $apt1 += 0.00002 * sin((pi() / 180) * (4 * $mprime));

        $apt2 = 0.000325 * sin((pi() / 180) * (299.77 + 0.107408 * $k - 0.009173 * $t2));
        $apt2 += 0.000165 * sin((pi() / 180) * (251.88 + 0.016321 * $k));
        $apt2 += 0.000164 * sin((pi() / 180) * (251.83 + 26.651886 * $k));
        $apt2 += 0.000126 * sin((pi() / 180) * (349.42 + 36.412478 * $k));
        $apt2 += 0.00011 * sin((pi() / 180) * (84.66 + 18.206239 * $k));
        $apt2 += 0.000062 * sin((pi() / 180) * (141.74 + 53.303771 * $k));
        $apt2 += 0.00006 * sin((pi() / 180) * (207.14 + 2.453732 * $k));
        $apt2 += 0.000056 * sin((pi() / 180) * (154.84 + 7.30686 * $k));
        $apt2 += 0.000047 * sin((pi() / 180) * (34.52 + 27.261239 * $k));
        $apt2 += 0.000042 * sin((pi() / 180) * (207.19 + 0.121824 * $k));
        $apt2 += 0.00004 * sin((pi() / 180) * (291.34 + 1.844379 * $k));
        $apt2 += 0.000037 * sin((pi() / 180) * (161.72 + 24.198154 * $k));
        $apt2 += 0.000035 * sin((pi() / 180) * (239.56 + 25.513099 * $k));
        $apt2 += 0.000023 * sin((pi() / 180) * (331.55 + 3.592518 * $k));
        return $pt + $apt1 + $apt2;
    }

}