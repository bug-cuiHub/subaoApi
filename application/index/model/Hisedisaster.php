<?php
namespace app\index\model;

use think\Model;
use think\Db;


class Hisedisaster extends Model {
    
    public function getDisaster($lat, $lon, $range) {
        $map = "ROUND(6378.138*2*asin(sqrt(pow(sin((" . $lat . "*pi()/180-Latitude*pi()/180)/2),2)+
                cos(" . $lat . "*pi()/180)*cos(Latitude*pi()/180)* pow(sin((" . $lon . "*pi()/180-
                Longitude*pi()/180)/2),2)))*1000) <= " . $range;
        $field = "objectId, EarthquakeDate, Macro_epicenter, Magnitude, CrackDegree, Death, DirectEconomicLoss, Latitude, Longitude";
        $result = Db::table('hisedisaster')
                    ->field($field)
                    ->where($map)
                    ->select();
        return $result;
    }
}
