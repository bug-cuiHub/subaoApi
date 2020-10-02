<?php

namespace app\index\model;

use think\Model;
use think\Db;

class PublishedSeismic extends Model
{

    public function insertEqInfo($data)
    {
        $result = Db::table('published_seismic')->insert($data);
        return $result;
    }

    public function wechatPush($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field("sec_abbreviation as district, eq_time as time, longitude as lon, 
                             latitude as lat, magnitude as magn, Intensity as intensity, depth as depth")
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $result;
    }

    public function getWeaData($eqUnique)
    {
        $map = ['ID' =>  ['=', $eqUnique]];
        $result = Db::table('published_seismic')
            ->field('eq_name, province, fir_abbreviation, climate, weather')
            ->where($map)
            ->select();
        return $result[0];
    }

    public function getEconData($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field('eq_name, ROUND(GDP/10000, 2) as GDP, ROUND(fir_industry/10000, 2) as FST_INDUSTRY,
                             ROUND(sec_industry/10000, 2) as SND_INDUSTRY, ROUND(thi_industry/10000, 2) as TRD_INDUSTRY,economic')
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $result;
    }

    public function getEnvirData($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field('eq_name, min_altitude, ave_altitude, max_altitude, topographic, environment')
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $result;
    }

    public function getPeoData($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field('eq_name, ROUND((pop_countryside+pop_town)/10000, 2) as POP_ALL, 
                             ROUND(pop_countryside/10000, 2) as POP_COUNTRYSIDE, ROUND(pop_town/10000, 2) as POP_TOWN,
                             urban_ave_income, village_ave_income, village_num, town_num, pop_detail, people')
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $result;
    }

    public function getEpicenter($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field("sec_abbreviation as district, eq_time as time, 
                             longitude, latitude, magnitude, intensity, depth")
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        Db::table('published_seismic')->where('ID', $eqUnique)->update(['eqcenterInfo' => $result[0]['district']]);
        return $result;
    }

    public function getNearProvince($eqUnique)
    {
        $VirulTable = Db::table('published_seismic')
            ->field('province, x_longitude, x_latitude, magnitude, eq_time')
            ->where("ID = '" . $eqUnique . "'")
            ->buildSql();

        $result = Db::field("ps.province, CONCAT_WS('，', ps.neighbor, ps.province) as neighbor,
                                p.x_longitude, p.x_latitude, p.magnitude, p.eq_time")
            ->table('province as ps, ' . $VirulTable . ' as p')
            ->where('ps.province = p.province')
            ->select();
        // var_dump($result);
        return $result;
    }

    //2019.11.29 崔珂玮增加(修改临近县距离计算bug)
    public function getRealNearProvince($eqUnique)
    {
        $data = Db::table('published_seismic')
            ->field("longitude,latitude")
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $data;
    }

    // 五个字段的资源信息
    public function getSoucreData($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field('weather as city_weather, 
                         people as city_population,
                         economic as city_economics,
                         environment as city_environment,
                         eqcenterInfo as city_name')
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $result[0];
    }

    public function getCode($eqUnique)
    {
        $result = Db::table('published_seismic')
            ->field('eq_time as time')
            ->where("ID = '" . $eqUnique . "'")
            ->select();
        return $result[0]['time'];
    }

    //发布数据管理
    public function MgetPushData()
    {
        // $sql = "select count(*) from published_seismic";
        $result = Db::table('published_seismic')
            ->field('ID,eq_time,longitude,latitude,magnitude,depth,fullName')
            ->order('eq_time desc')
            ->select();
        return $result;
    }

    public function ManalysisPushData($data)
    {
        //用于判断是否属于国内地震
        $all_pro_name = [
            "河北",
            "山西",
            "辽宁",
            "吉林",
            "黑龙江",
            "江苏",
            "浙江",
            "安徽",
            "福建",
            "江西",
            "山东",
            "河南",
            "湖北",
            "湖南",
            "广东",
            "海南",
            "四川",
            "贵州",
            "云南",
            "陕西",
            "甘肃",
            "青海",
            // "台湾",
            "内蒙",
            "广西",
            "西藏",
            "宁夏",
            "新疆",
            "北京",
            "天津",
            "上海",
            "重庆",
        ];
        //从3级地震开始统计
        $start_mgn = 3;

        //统计发布地震省的数据，获取省名
        $provinceTime = array();
        $id = $data["ID"];
        $sql = "select province from published_seismic where ID = '" . $id . "'";
        $province = Db::query($sql); 
        $province = mb_substr($province[0]["province"],0,2);

        //获取省上一年的数据
        $sql1 = "select count(Location) from new_his_earthquake where Location like '" . $province . "%' and Magnitude >= 3 and Datetime like '" . (date("Y") - 1) . "-%'";
        $time_all = Db::query($sql1);
        $provinceTime["past"]["time_all"] = $time_all[0]["count(Location)"];
        for ($i = $start_mgn; $i <= 7; $i++) {
            if ($i != 7) {
                $sql = "select count(Location) from new_his_earthquake where Location like '" . $province . "%' and Magnitude >= " . $i . " and Magnitude < " . ($i + 1) . " and Datetime like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["past"]["time_" . $i] = $time_num[0]["count(Location)"];
            } else {
                $sql = "select count(Location) from new_his_earthquake where Location like '" . $province . "%' and Magnitude >= " . $i . " and Datetime like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["past"]["time_" . $i] = $time_num[0]["count(Location)"];
            }
        }
        //获取省上本年的数据
        $sql2 = "select count(Location) from new_his_earthquake where Location like '" . $province . "%' and Magnitude >= 3 and Datetime like '" . date("Y") . "-%'";
        $time_all = Db::query($sql2);
        $provinceTime["now"]["time_all"] = $time_all[0]["count(Location)"];
        for ($i = $start_mgn; $i <= 7; $i++) {
            if ($i != 7) {
                $sql = "select count(Location) from new_his_earthquake where Location like '" . $province . "%' and Magnitude >= " . $i . " and Magnitude < " . ($i + 1) . " and Datetime like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["now"]["time_" . $i] = $time_num[0]["count(Location)"];
            } else {
                $sql = "select count(Location) from new_his_earthquake where Location like '" . $province . "%' and Magnitude >= " . $i . " and Datetime like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["now"]["time_" . $i] = $time_num[0]["count(Location)"];
            }
        }

        //统计全国数据
        $allYearTime = array();
        //获取省上一年的数据
        $sql3 = "select Location from new_his_earthquake where Magnitude >= 3 and Datetime like '" . (date("Y") - 1) . "-%'";
        $time_all = Db::query($sql3);
        $item_arr_all = array();
        foreach ($time_all as $key => $value) {
            $pro_name = mb_substr($value["Location"], 0, 2);
            if (in_array($pro_name, $all_pro_name)) {
                array_push($item_arr_all, $value["Location"]);
            }
        }
        $allYearTime["past"]["time_all"] = count($item_arr_all);
        for ($i = $start_mgn; $i <= 7; $i++) {
            $item_arr = array();
            if ($i != 7) {
                $sql = "select Location from new_his_earthquake where Magnitude >= " . $i . " and Magnitude < " . ($i + 1) . " and Datetime like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                foreach ($time_num as $key => $value) {
                    $pro_name = mb_substr($value["Location"], 0, 2);
                    if (in_array($pro_name, $all_pro_name)) {
                        array_push($item_arr, $value["Location"]);
                        // var_dump($pro_name);
                    }
                }
                // var_dump($item_arr);
                $allYearTime["past"]["time_" . $i] = count($item_arr);
            } else {
                $sql = "select Location from new_his_earthquake where Magnitude >= " . $i . " and Datetime  like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                foreach ($time_num as $key => $value) {
                    $pro_name = mb_substr($value["Location"], 0, 2);
                    if (in_array($pro_name, $all_pro_name)) {
                        array_push($item_arr, $value["Location"]);
                        // var_dump($pro_name);
                    }
                }
                // var_dump($item_arr);
                $allYearTime["past"]["time_" . $i] = count($item_arr);
            }
        }
        //获取省本年的数据
        $sql4 = "select Location from new_his_earthquake where Magnitude >= 3 and Datetime like '" . date("Y") . "-%'";
        $time_all = Db::query($sql4);
        $item_arr_all = array();
        foreach ($time_all as $key => $value) {
            $pro_name = mb_substr($value["Location"], 0, 2);
            if (in_array($pro_name, $all_pro_name)) {
                array_push($item_arr_all, $value["Location"]);
            }
        }
        $allYearTime["now"]["time_all"] = count($item_arr_all);
        for ($i = $start_mgn; $i <= 7; $i++) {
            $item_arr = array();
            if ($i != 7) {
                $sql = "select Location from new_his_earthquake where Magnitude >= " . $i . " and Magnitude < " . ($i + 1) . " and Datetime like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                foreach ($time_num as $key => $value) {
                    $pro_name = mb_substr($value["Location"], 0, 2);
                    if (in_array($pro_name, $all_pro_name)) {
                        array_push($item_arr, $value["Location"]);
                        // var_dump($pro_name);
                    }
                }
                $allYearTime["now"]["time_" . $i] = count($item_arr);
                // $allYearTime["now"]["time_" . $i] = $time_num[0]["count(*)"];
            } else {
                $sql = "select Location from new_his_earthquake where Magnitude >= " . $i . " and Datetime like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                foreach ($time_num as $key => $value) {
                    $pro_name = mb_substr($value["Location"], 0, 2);
                    if (in_array($pro_name, $all_pro_name)) {
                        array_push($item_arr, $value["Location"]);
                        // var_dump($pro_name);
                    }
                }
                $allYearTime["now"]["time_" . $i] = count($item_arr);
            }
        }
        $allData = [$province, $provinceTime, $allYearTime];
        return $allData;
    }

    public function ManalysisPushDataBeiFen($data)
    {
        //从3级地震开始统计
        $start_mgn = 3;

        //统计发布地震省的数据，获取省名
        $provinceTime = array();
        $id = $data["ID"];
        $sql = "select province from published_seismic where ID = '" . $id . "'";
        $province = Db::query($sql);

        //获取省上一年的数据
        $sql1 = "select count(*) from published_seismic where province = '" . $province[0]["province"] . "' and magnitude >= 4 and eq_time like '" . (date("Y") - 1) . "-%'";
        $time_all = Db::query($sql1);
        $provinceTime["past"]["time_all"] = $time_all[0]["count(*)"];
        for ($i = $start_mgn; $i <= 7; $i++) {
            if ($i != 7) {
                $sql = "select count(*) from published_seismic where province = '" . $province[0]["province"] . "' and magnitude >= " . $i . " and magnitude < " . ($i + 1) . " and eq_time like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["past"]["time_" . $i] = $time_num[0]["count(*)"];
            } else {
                $sql = "select count(*) from published_seismic where province = '" . $province[0]["province"] . "' and magnitude >= " . $i . " and eq_time like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["past"]["time_" . $i] = $time_num[0]["count(*)"];
            }
        }
        //获取省上本年的数据
        $sql2 = "select count(*) from published_seismic where province = '" . $province[0]["province"] . "' and magnitude >= 4 and eq_time like '" . date("Y") . "-%'";
        $time_all = Db::query($sql2);
        $provinceTime["now"]["time_all"] = $time_all[0]["count(*)"];
        for ($i = $start_mgn; $i <= 7; $i++) {
            if ($i != 7) {
                $sql = "select count(*) from published_seismic where province = '" . $province[0]["province"] . "' and magnitude >= " . $i . " and magnitude < " . ($i + 1) . " and eq_time like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["now"]["time_" . $i] = $time_num[0]["count(*)"];
            } else {
                $sql = "select count(*) from published_seismic where province = '" . $province[0]["province"] . "' and magnitude >= " . $i . " and eq_time like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                $provinceTime["now"]["time_" . $i] = $time_num[0]["count(*)"];
            }
        }

        //统计全国数据
        $allYearTime = array();
        //获取省上一年的数据
        $sql3 = "select count(*) from published_seismic where magnitude >= 4 and eq_time  like '" . (date("Y") - 1) . "-%'";
        $time_all = Db::query($sql3);
        $allYearTime["past"]["time_all"] = $time_all[0]["count(*)"];
        for ($i = $start_mgn; $i <= 7; $i++) {
            if ($i != 7) {
                $sql = "select count(*) from published_seismic where magnitude >= " . $i . " and magnitude < " . ($i + 1) . " and eq_time like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                $allYearTime["past"]["time_" . $i] = $time_num[0]["count(*)"];
            } else {
                $sql = "select count(*) from published_seismic where magnitude >= " . $i . " and eq_time  like '" . (date("Y") - 1) . "-%'";
                $time_num = Db::query($sql);
                $allYearTime["past"]["time_" . $i] = $time_num[0]["count(*)"];
            }
        }
        //获取省上本年的数据
        $sql4 = "select count(*) from published_seismic where magnitude >= 4 and eq_time like '" . date("Y") . "-%'";
        $time_all = Db::query($sql4);
        $allYearTime["now"]["time_all"] = $time_all[0]["count(*)"];
        for ($i = $start_mgn; $i <= 7; $i++) {
            if ($i != 7) {
                $sql = "select count(*) from published_seismic where magnitude >= " . $i . " and magnitude < " . ($i + 1) . " and eq_time like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                $allYearTime["now"]["time_" . $i] = $time_num[0]["count(*)"];
            } else {
                $sql = "select count(*) from published_seismic where magnitude >= " . $i . " and eq_time like '" . date("Y") . "-%'";
                $time_num = Db::query($sql);
                $allYearTime["now"]["time_" . $i] = $time_num[0]["count(*)"];
            }
        }
        $allData = [$province[0]["province"], $provinceTime, $allYearTime];
        return $allData;
    }

    //历史地震搜索
    public function Mquery_date($data)
    {
        $sql = "select ID,eq_time,longitude,latitude,magnitude,depth,fullName 
                from published_seismic where eq_time >= 
                '" . $data["start"] . "' and eq_time <= '" . $data["end"] . "'";
        $time_num = Db::query($sql);
        return $time_num;
    }
}
