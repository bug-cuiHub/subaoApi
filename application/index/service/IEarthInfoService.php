<?php
namespace app\index\service;

interface IEarthInfoService {

    // 天气信息
    public function weaData($eqUnique);

    // 经济信息
    public function econData($eqUnique);

    // 环境信息
    public function envirData($eqUnique);

    // 人口信息
    public function peoData($eqUnique);

    // 三维信息
    public function tdData($eqUnique);

    // 震中信息
    public function epicenter($eqUnique);

    // 世界历史地震信息
    public function worldHis();

    // 中国历史地震信息
    public function chinaHis();

    // 地震数据管理
    public function SgetPushData();

    //发布数据管理_速报推送统计
    public function SanalysisPushData($data);
    
    //发布数据管理_根据日期查询地震
    public function Squery_date($data);

    //计算距离最近的历史地震
    public function SgetNearEarth($data);

    //获取历史地震搜索后的经纬度
    public function SgetHisSearchData($data);

    //获取搜索的历史地震震中附近的信息
    public function SgetSearchThdData($data);

}