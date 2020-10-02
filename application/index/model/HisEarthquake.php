<?php
namespace app\index\model;

use think\Model;
use think\Db;


class HisEarthquake extends Model {
    
    public function getWorldHis() {
        $result = Db::table('his_earthquake')
                    ->order('EarthquakeDate')
                    ->where('isChina','=', 0)
                    ->select();
        return $result;
    }

    public function getChinaHis() {
        $map_mag['Magnitude'] = array('gt', 5);
        $result = Db::table('his_earthquake')
                    ->order('Magnitude')
                    ->where($map_mag)
                    ->where('isChina','=', 1)
                    ->select();
        return $result;
    }
}
