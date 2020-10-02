<?php
namespace app\index\model;

use think\Model;
use think\Db;


class BaseModel extends Model {

    // 查询基础数据
    public function baseInfo($district, $city, $province, $street) { 
        $map = ['object_id', 'longitude_bd', 'latitude_bd', 'name','fir_abbreviation', 'city', 'province',
                'sec_abbreviation', 'fullName', 'thi_abbreviation', 'pop_total', 'pop_countryside', 
                'pop_town', 'GDP', 'fir_industry', 'sec_industry', 'thi_industry', 'town_num', 'village_num',
                'climate', 'area', 'min_altitude', 'min_altitude_name', 'max_altitude', 'max_altitude_name', 
                'ave_altitude', 'topographic', 'village_ave_income', 'urban_ave_income', 'pop_detail', 'eco_detail'
        ];
        
        $condition = [
            ['like', $this->base_strsub($street)],
            ['like', $this->base_strsub($district)],
            ['like', $this->base_strsub($city)],
            ['like', $this->base_strsub($province)]
        ];
        $result = Db::table('base')
                    ->field($map)
                    ->where('name',$condition,'OR')
                    ->select();
        return $result;
    }
    private function base_strsub($str) {
        if($str != '') {
            $res = mb_substr($str, 0, mb_strlen($str, 'utf-8')-1, 'utf-8');
            // var_dump($res);
            return $res.'%';
        } else {
            return $str;
        }
    }
    //计算震源处邻近县的距离
    public function getNearCity($pro, $lat, $lon) { 
        $map = [
            'province'   => ['in', $pro],
            'longitude_bd' => ['<>', ''],
            'latitude_bd'   => ['<>', ''],
        ];
        // var_dump($map);
        $result = Db::table('base')
                    ->field("
                            name, 
                            longitude_bd as longitude, 
                            latitude_bd as latitude, 
                            ROUND(area/1000000, 2) as area, 
                            ROUND(GDP/10000, 2) as GDP,
                            ROUND((pop_countryside+pop_town)/10000, 2) as POP_ALL,
                            ROUND(ROUND(6378.138*2*asin(sqrt(pow(sin( (" . $lat . "*pi()/180-latitude_bd*pi()/180)/2),2)
                            +cos(" . $lat . "*pi()/180)*cos(latitude_bd*pi()/180)*pow(sin((" . $lon . "*pi()/180-longitude_bd*pi()/180)/2),2)))*1000)/1000)-1 as distence")
                    ->where($map)
                    ->order('distence')
                    ->limit(4)
                    ->select();
        // var_dump($result);
        return $result;
    }

    //计算距离50公里内的历史地震
    public function MgetNearEarth($lat, $lon) { 
        $sql = "select * from 
            (select Datetime,Location,Magnitude,Depth,Latitude as latitude,Longitude as longitude,
            ROUND(ROUND(6378.138*2*asin(sqrt(pow(sin( (" . $lat . "*pi()/180-Latitude*pi()/180)/2),2)
            +cos(" . $lat . "*pi()/180)*cos(Latitude*pi()/180)*pow(sin((" . $lon . "*pi()/180-Longitude*pi()/180)/2),2)))*1000)/1000)-1 as distence 
            from new_his_earthquake 
            where longitude <> '' and latitude <> ''
            order by distence) as temp
            where temp.distence < 50 and temp.distence > 0";
        $result = Db::query($sql);
        return $result;
    }

    public function getNearCountryside($lat, $lon, $range) { 
        $map = "name like '%县' AND ROUND(6378.138*2*asin(sqrt(pow(sin((" . $lat . "*pi()/180-
                latitude_bd*pi()/180)/2), 2)+cos(" . $lat . "*pi()/180)*cos(latitude_bd*pi()/180)* 
                pow(sin((" . $lon . "*pi()/180-longitude_bd*pi()/180)/2), 2)))*1000)  <=  " . $range;
        $result = Db::table('base')
                    ->field('distinct(name)')
                    ->where($map)
                    ->select();
        return $result;
    }

    //震中附近10公里内居民点
    public function getVillageData($lat, $lon, $range){
        $sql = "select * from 
            (select province,city,district,township,committee,
            lat as latitude,lon as longitude,
            ROUND(ROUND(6378.138*2*asin(sqrt(pow(sin( (" . $lat . "*pi()/180-lat*pi()/180)/2),2)
            +cos(" . $lat . "*pi()/180)*cos(lat*pi()/180)*pow(sin((" . $lon . "*pi()/180-lon*pi()/180)/2),2)))*1000)/1000)-1 as distence 
            from gaode_countryside_data
            where lon <> '' and lat <> ''
            order by distence) as temp
            where temp.distence < 10";// and temp.distence > 0";
        $result = Db::query($sql);
        return $result;
    }

    public function MgetHisSearchData($data){
        // var_dump($data);
        // $sql = "select * from new_his_earthquake where";
        // $result = Db::query($sql);
        // return $result;
    }
}
