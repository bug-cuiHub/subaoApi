<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Request;

use app\index\common\ServerResponse;
use app\index\service\impl\EarthInfoServiceImpl;


class EarthInfoController extends Controller {

    public function getEqData() {
        $serverResponse = new ServerResponse();
        $earthInfoServiceImpl = new EarthInfoServiceImpl();

        $request = Request::instance();
        $data = $request->param();
        $handel = trim($data['handel']);
        $eqUnique = $data['eqUnique'];

        // 手机端可能无法访问，因为没有session 
        // if($eqUnique && $eqUnique == Session::get('eqUnique')) {
        if($eqUnique) {
            switch($handel) {
            
                case 'weather':
                    $msg = "天气信息";
                    $exportData = $earthInfoServiceImpl->weaData($eqUnique);
                    break;

                case 'economic':
                    $msg = "经济信息";
                    $exportData = $earthInfoServiceImpl->econData($eqUnique);
                    break;

                case 'environment':
                    $msg = "环境信息";
                    $exportData = $earthInfoServiceImpl->envirData($eqUnique);
                    break;

                case 'population':
                    $msg = "人口信息";
                    $exportData = $earthInfoServiceImpl->peoData($eqUnique);
                    break;

                case 'threeDimensional':
                    $msg = "三维信息";
                    $exportData = $earthInfoServiceImpl->tdData($eqUnique);
                    break;

                case 'epicenter':
                    $msg = "震中信息";
                    $exportData = $earthInfoServiceImpl->epicenter($eqUnique);
                    break;

                case 'worldEq':
                    $msg = "世界历史地震信息"; 
                    $exportData = $earthInfoServiceImpl->worldHis();
                    break;

                case 'chinaEq':
                    $msg = "中国历史地震信息";
                    $exportData = $earthInfoServiceImpl->chinaHis();
                    break;

                default:
                    $msg = "无信息";
                    $exportData = null;
                    break;
            }
            return $serverResponse->createBySuccessMsgData($msg, $exportData);
        } else {
            return $serverResponse->createByErrorMsg("未能获取所发布的地震信息，请先发布一个地震");
        }
    }

    //发布数据管理
    public function getPushData(){
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $data = $earthInfoServiceImpl->SgetPushData();
        return json_encode($data);
    }
    //发布数据管理_速报推送统计
    public function analysisPushData(){
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $request = Request::instance();
        $data = $request->param();
        $data = $earthInfoServiceImpl->SanalysisPushData($data);
        return json_encode($data);
    }
    //发布数据管理_根据日期查询地震
    public function query_date(){
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $request = Request::instance();
        $data = $request->param();
        $data = $earthInfoServiceImpl->Squery_date($data);
        return json_encode($data);
    }
    //计算距离最近的历史地震
    public function getNearEarth(){
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $request = Request::instance();
        $data = $request->param();
        $data = $earthInfoServiceImpl->SgetNearEarth($data);
        return json_encode($data);
    }
    //获取历史地震搜索后的经纬度
    public function getHisSearchData(){
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $request = Request::instance();
        $data = $request->param();
        // $data = $earthInfoServiceImpl->SgetHisSearchData($data);
        $data = [
            "longitude"=> "103.33",
            "latitude"=> "27.11",
            "magnitude"=> 6,
            "depth"=>12,
            "time"=>"2014-08-03 16:30",
            "district"=>"云南鲁甸",
            "intensity"=>"Ⅶ"
        ];
        return json_encode($data);
    }
    //获取搜索的历史地震震中附近的信息
    public function getSearchThdData(){
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $request = Request::instance();
        $data = $request->param();
        $data = $earthInfoServiceImpl->SgetSearchThdData($data);
        return json_encode($data);
    }
}