<?php
namespace app\index\service\impl;

use app\index\service\IEarthInfoService;
use app\index\model\PublishedSeismic;
use app\index\model\BaseModel;
use app\index\model\Hisedisaster;
use app\index\model\HisEarthquake;
use app\index\common\ServerResponse;
use app\index\util\IntegratePattern;


class EarthInfoServiceImpl implements IEarthInfoService {

    // 天气信息
    public function weaData($eqUnique) {
        $publishedSeismic = new PublishedSeismic();
        $serverResponse = new ServerResponse();
        $integratePattern = new IntegratePattern();  
        $result = $publishedSeismic->getWeaData($eqUnique);

        $climate = $result['climate'];
        $weather = $result['weather'];
        $province = trim($result['province']);
        $name = trim($result['fir_abbreviation']);
        $eq_name = trim($result['eq_name']);
        
        $proarr = ['北京市', '上海市', '天津市', '重庆市']; 
        if(mb_substr($climate, mb_strlen($climate, 'utf-8')-1, mb_strlen($climate, 'utf-8'), 'utf-8') != "。") {
            $climate = $climate."。";
        }
        if(in_array($province, $proarr)) {
            $city = $province;
        }else {
            $city = $eq_name;
        }
        $data = $this->getReadData($city, $climate, $name, $weather);

        $msg = "成功获取天气信息！";             
        $integratePattern->sendLogInfo($msg);

        if(isset($data['climateData']['error'])) {
            $city = $name; 
            $data = $this->getReadData($city, $climate, $name, $weather);
            // return $serverResponse->createBySuccessData($data);
            return $data;
        } else {
            return $data;
            // return $serverResponse->createBySuccessData($data);
        }
    }
    private function getReadData($city, $climate, $name, $weather) {
        $publishedSeismic = new PublishedSeismic();

        $url='http://wthrcdn.etouch.cn/WeatherApi?city='.$city;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_ENCODING ,'gzip');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        $climateData=json_decode(json_encode(simplexml_load_string($file_contents, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $data = [
            'climateDes' => $climate,
            'climateData' =>  $climateData,
            'name' => $name,
            'weather' => $weather
        ];
        return $data;
    }

    // 经济信息 
    public function econData($eqUnique) {
        $publishedSeismic = new PublishedSeismic();
        $integratePattern = new IntegratePattern();
        $msg = "成功获取经济信息！";
        $integratePattern->sendLogInfo($msg);
        return $publishedSeismic->getEconData($eqUnique);
    }
    //拼接经济信息文本
    public function econInfoCombine($name,$GDP,$fir_industry,$sec_industry,$thi_industry){
        $GDP = round($GDP/10000, 2);
        $fir_industry = round($fir_industry/10000, 2);
        $sec_industry = round($sec_industry/10000, 2);
        $thi_industry = round($thi_industry/10000, 2);
        $ecotitle = "据《中国经济年鉴2014》".$name."在2014年的生产总值达";
        $gdp = $GDP ? $GDP . "亿元。" : "0亿元";
        $first = $fir_industry ? "第一产业生产总值为" . $fir_industry . "亿元," : "";
        $second = $sec_industry ? "第二产业生产总值为" . $sec_industry . "亿元," : "";
        $third = $thi_industry ? "第三产业生产总值为" . $thi_industry . "亿元。" : "";
        $economicInfoContent = $ecotitle.$gdp.$first.$second.$third;
        return $economicInfoContent;
    }


    // 环境信息
    public function envirData($eqUnique) {
        $publishedSeismic = new PublishedSeismic();
        $integratePattern = new IntegratePattern();
        $msg = "成功获取环境信息！";            
        $integratePattern->sendLogInfo($msg);
        return $publishedSeismic->getEnvirData($eqUnique);
    }

    // 人口信息
    public function peoData($eqUnique) {
        $publishedSeismic = new PublishedSeismic();
        $integratePattern = new IntegratePattern();            
        $msg = "成功获取人口信息！";               
        $integratePattern->sendLogInfo($msg);
        return $publishedSeismic->getPeoData($eqUnique);
    }
    //拼接人口信息文本
    public function peoInfoCombine($name,$pop_total,$pop_town,$pop_countryside){
        $pop_total = round(($pop_town+$pop_countryside)/10000, 2);
        $pop_countryside = round($pop_countryside/10000, 2);
        $pop_town = round($pop_town/10000, 2);
        

        $poptitle = "据《中国2010年人口普查分县资料》，" . $name;
        $one = "在2010年的总人口约" . $pop_total;
        $two = "万人。在总人口中，城镇人口约" . $pop_town; 
        $three = "万人，占总人口的" . round(100*$pop_town/$pop_total, 2);        
        $four = "%，农业人口约" . $pop_countryside;
        $five = "万人，占总人口的" . round(100*$pop_countryside/$pop_total, 2)."%。";
        $populationInfoContent = $poptitle.$one.$two.$three.$four.$five; 
        return $populationInfoContent;
    }

    // 震中信息
    public function epicenter($eqUnique) {
        $publishedSeismic = new PublishedSeismic();
        $integratePattern = new IntegratePattern();
        $msg = "成功获取震中信息！";
        $integratePattern->sendLogInfo($msg);
        return $publishedSeismic->getEpicenter($eqUnique);
    }

    // 世界历史地震信息
    public function worldHis() {
        $hisEarthquake = new HisEarthquake();
        $integratePattern = new IntegratePattern();
        $msg = "成功获取历史世界地震信息！";
        $integratePattern->sendLogInfo($msg);
        return $hisEarthquake->getWorldHis();
    }

    // 中国历史地震信息
    public function chinaHis() {
        $hisEarthquake = new HisEarthquake();
        $integratePattern = new IntegratePattern();
        $msg = "成功获取中国历史地震信息！";
        $integratePattern->sendLogInfo($msg);
        return $hisEarthquake->getChinaHis();
    }
    
    // 三维信息
    public function tdData($eqUnique) {
        $publishedSeismic = new PublishedSeismic();
        $integratePattern = new IntegratePattern();
        $result = $publishedSeismic->getNearProvince($eqUnique);
        $realPos = $publishedSeismic->getRealNearProvince($eqUnique);//2019.11.29 崔珂玮增加(修改临近县距离计算bug)

        //此处有一个隐患数据库里的分隔符采用的是中文的逗号
        if(count($result) > 0) {
            $provinces = explode('，', $result[0]["neighbor"]);
            array_push($provinces, $result[0]["province"]);
            $magn   = $result[0]["magnitude"];
            $eq_time = $result[0]["eq_time"];

            //2019.11.29 崔珂玮增加(修改临近县距离计算bug)
            $lat = $realPos[0]["latitude"];
            $lon = $realPos[0]["longitude"];

            $nearCityData = $this->findNearCityData($provinces, $lat, $lon);
            $hisEqData = $this->findHisEqData($magn, $lat, $lon);
            $countrysideData = $this->findNearCountrysideData($lat, $lon);
            $villageData = $this->getVillageData($lat, $lon);
        } else {
            $nearCityData = null;
            $hisEqData    = null;
            $countrysideData = null;
            $villageData = null;
        }
        $data = [
            'nearCityData' => $nearCityData,
            'hisEqData'    => $hisEqData,
            'countrysideData' => $countrysideData,
            'villageData' => $villageData
        ];
        $msg = "成功获取三维属性信息！";                      
        $integratePattern->sendLogInfo($msg);
        return $data;
    }

    //得到发布地震消息的五个值 weather,people,economic,environment,eqcenterInfo
    public function getSoucreData($eqUnique){
        $publishedSeismic = new PublishedSeismic();
        $sourceInfo = $publishedSeismic->getSoucreData($eqUnique);
        return $sourceInfo;
    }

    private function findNearCityData($pro, $lat, $lon) {
        $baseModel = new BaseModel();
        return $baseModel->getNearCity($pro, $lat, $lon);
    }
    private function findHisEqData($magn, $lat, $lon) {
        $range = $this->getRange($magn);
        $hisedisaster = new Hisedisaster();
        return $hisedisaster->getDisaster($lat, $lon, $range);
    }
    private function findNearCountrysideData($lat, $lon) {
        $unit = 1000; // 单位(千米)
        $range = 300 * $unit;
        $baseModel = new BaseModel();
        return $baseModel->getNearCountryside($lat, $lon, $range);
    }
    private function getRange($magn) {
        $unit = 1000; //单位(千米)
        $range = 0;
        if (0 < $magn && $magn <= 5.0) {
            $range = 20 * $unit;
        } else if ($magn > 5.0 && $magn <= 5.9) {
            $range = 40 * $unit;
        } else if ($magn > 5.9 && $magn <= 6.9) {
            $range = 100 * $unit;
        } else if ($magn > 6.9 && $magn <= 7.9) {
            $range = 200 * $unit;
        } else if ($magn > 7.9) {
            $range = 400 * $unit;
        }
        return $range;
    }

    private function getVillageData($lat, $lon){
        $range = 10;
        $baseModel = new BaseModel();
        return $baseModel->getVillageData($lat, $lon, $range);
    }

    //发布数据管理
    public function SgetPushData(){
        $publishedSeismic = new PublishedSeismic();
        $data = $publishedSeismic->MgetPushData();
        return $data;
    }
    //发布数据管理_速报推送统计
    public function SanalysisPushData($data){
        $publishedSeismic = new PublishedSeismic();
        $data = $publishedSeismic->ManalysisPushData($data);
        return $data;
    }
    //发布数据管理_根据日期查询地震
    public function Squery_date($data){
        $publishedSeismic = new PublishedSeismic();
        $data = $publishedSeismic->Mquery_date($data);
        return $data;
    }
    //计算距离最近的历史地震
    public function SgetNearEarth($data){
        $BaseModel = new BaseModel();
        $data = $BaseModel->MgetNearEarth($data["latitude"],$data["longitude"]);
        return $data;
    }

    //获取历史地震搜索后的经纬度
    public function SgetHisSearchData($data){
        $baseModel = new BaseModel();
        return $baseModel->MgetHisSearchData($data);
    }
    //获取搜索的历史地震震中附近的信息
    public function SgetSearchThdData($data){
        $lat = $data["lat"];
        $lon = $data["lon"];
        $countrysideData = $this->findNearCountrysideData($lat, $lon);
        $villageData = $this->getVillageData($lat, $lon);
        $data = [
            // 'nearCityData' => $nearCityData,
            // 'hisEqData'    => $hisEqData,
            'countrysideData' => $countrysideData,
            'villageData' => $villageData
        ];
        return $data;
    }
}