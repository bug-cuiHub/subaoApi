<?php
namespace app\index\service\impl;

use think\Session;

use app\index\service\IReleaseEqService;
use app\index\service\impl\EarthInfoServiceImpl;
use app\index\model\PublishedSeismic;
use app\index\model\BaseModel;
use app\index\util\WechatPush;
use app\index\util\IntegratePattern;
use app\index\common\ServerResponse;
    

class ReleaseEqServiceImpl implements IReleaseEqService {

    public function releaseEq($data) {

        $EarthInfoServiceImpl = new EarthInfoServiceImpl();
        $publishedSeismic = new PublishedSeismic();
        $baseModel = new BaseModel();
        $serverResponse = new ServerResponse();
        $integratePattern = new IntegratePattern();
        
        $mindistance=999999;
        $i=0;
        $flag=0;

        $street = $data['street']; //街道名称
		$district = $data['district']; //县名称
		$eqTime = $data['time']; //发震时间
		$longitude = $data['longitude']; //经度
		$latitude = $data['latitude']; //纬度
		$magnitude = $data['magnitude']; //震级
		$depth = $data['depth']; //震源深度
		$city = $data['city']; //城市名称
		$province = $data['province']; //省名称
		$intensity = $data['intensity']; //烈度
        $range = '100'; //范围
        


        $results = $baseModel->baseInfo($district, $city, $province, $street);
        // var_dump($results);
        // 判断是否传入县名 判断一下最近的那个地区
		foreach($results as $value) {
			$d = sqrt(pow($value['longitude_bd']-$longitude, 2) + pow($value['latitude_bd']-$latitude, 2));
			if($mindistance > $d) {
				$mindistance = $d;
				$flag = $i;
			}
			$i++;
        }
		if($results) {
            $ID = md5(time().mt_rand(1,1000000));        
            $object_id    = $results[$flag]['object_id'];
            $longitude_bd = $results[$flag]['longitude_bd'];
            $latitude_bd  = $results[$flag]['latitude_bd'];
            $name         = $results[$flag]['name'];
            $area         = $results[$flag]['area'];
            $fir_abbreviation = $results[$flag]['fir_abbreviation'];
            $fullName     = $results[$flag]['fullName'];
            $city         = $results[$flag]['city'];
            $province     = $results[$flag]['province'];
            $sec_abbreviation = $results[$flag]['sec_abbreviation'];
            $thi_abbreviation = $results[$flag]['thi_abbreviation'];
            $pop_total      = $results[$flag]['pop_total'];
            $pop_countryside = $results[$flag]['pop_countryside'];
            $pop_town     = $results[$flag]['pop_town'];
            $GDP          = $results[$flag]['GDP'];
            $fir_industry = $results[$flag]['fir_industry'];
            $sec_industry = $results[$flag]['sec_industry'];
            $thi_industry = $results[$flag]['thi_industry'];
            $town_num     = $results[$flag]['town_num'];
            $village_num  = $results[$flag]['village_num'];
            $climate      = $results[$flag]['climate'];

            // 在外网环境下需要屏蔽掉这几行，因为无法访问10网段就无法请求海拔信息
//            $elevation = $this->req_elevation_info($longitude, $latitude);
//            if($elevation && $elevation['min']) {
//                $min_altitude       = floor($elevation['min']);
//                $ave_altitude = floor($elevation['mean']);
//                $max_altitude       = floor($elevation['max']);
//            } else {
                $min_altitude = floor($results[$flag]['min_altitude']);
                $max_altitude = floor($results[$flag]['max_altitude']);
                $ave_altitude = floor($results[$flag]['ave_altitude']);
//            }

            $min_altitude_name = $results[$flag]['min_altitude_name'];
            $max_altitude_name = $results[$flag]['max_altitude_name'];
            $topographic   = $results[$flag]['topographic'];
            $village_ave_income = $results[$flag]['village_ave_income'];
            $urban_ave_income = $results[$flag]['urban_ave_income'];
            $pop_detail    = $results[$flag]['pop_detail'];
            $eco_detail     = $results[$flag]['eco_detail'];
            
            // 向服务端发消息【RabbitMQ---RPC】
            $integratePattern->sendEarthInfo($longitude, $latitude, $magnitude, $depth, $eqTime, $sec_abbreviation);

            $economicInfoContent = $EarthInfoServiceImpl->econInfoCombine($name,$GDP,$fir_industry,$sec_industry,$thi_industry);
            $populationInfoContent = $EarthInfoServiceImpl->peoInfoCombine($name,$pop_total,$pop_town,$pop_countryside);
            $params = array(
                'ID' => $ID,
                'eq_time' => $eqTime,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'magnitude' => $magnitude,
                'depth' => $depth,
                'intensity' => $intensity,
                'eq_range' => $range,
                'object_id' => $object_id,
                'x_longitude' => $longitude_bd,
                'x_latitude' => $latitude_bd,
                'eq_name' => $name,
                'fir_abbreviation' => $fir_abbreviation,
                'city' => $city,
                'province' => $province,
                'sec_abbreviation' => $sec_abbreviation,
                'fullName' => $fullName,
                'thi_abbreviation' => $thi_abbreviation,
                'pop_total' => $pop_total,
                'pop_countryside' => $pop_countryside,
                'pop_town' => $pop_town,
                'GDP' => $GDP,
                'fir_industry' => $fir_industry,
                'sec_industry' => $sec_industry,
                'thi_industry' => $thi_industry,
                'town_num' => $town_num,
                'village_num' => $village_num,
                'climate' => $climate,
                'area' => $area,
                'min_altitude' => $min_altitude,
                'min_altitude_name' => $min_altitude_name,
                'max_altitude' => $max_altitude,
                'max_altitude_name' => $max_altitude_name,
                'ave_altitude' => $ave_altitude,
                'topographic' => $topographic,
                'village_ave_income' => $village_ave_income,
                'urban_ave_income' => $urban_ave_income,
                'pop_detail' => $pop_detail,
                'eco_detail' => $eco_detail,
                'weather' => $climate,
                'people' => $populationInfoContent,
                'economic' => $economicInfoContent,
                'environment' => $topographic,
                'eqcenterInfo' => $sec_abbreviation
            );
            
            $insertid = $publishedSeismic->insertEqInfo($params);
            if($insertid) {
                Session::set('eqUnique', $ID);
                $msg = "发布地震成功！";
                $integratePattern->sendLogInfo($msg);
                return $serverResponse->createBySuccessMsgData($msg, $insertid);
            }
            $msg = "发布地震失败！";
            $integratePattern->sendLogInfo($msg);
            return $serverResponse->createByErrorMsgData($msg, $insertid);
		}
	}

    // 海拔库里的不准确，所以利用经纬度去请求python提供的一个海拔接口
	private function req_elevation_info($lng, $lat) {
        $result = '';
        $url = "http://10.5.160.121/api/data/get_district_elevation/";
        $arr = array (
            'longitude' => $lng, 
            'latitude' => $lat,
            'format' => 'json'
        );
        $opts = array (
			'http' => array (
			    'method' => "GET"
		    )
        );
        
        $url .= empty($arr) ? "" : "?" . http_build_query($arr);
        $context = stream_context_create($opts);
		try {
			$result =  file_get_contents($url, false, $context);    
		} catch(Exception $e) {
			$result = '';
		}
		return json_decode($result,true);
	}
}