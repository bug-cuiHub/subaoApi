<?php
namespace app\index\util;

use think\Session;
use app\index\util\RpcClient;
use app\index\util\ReceiveLogsTopic;

class IntegratePattern {

    // 地震信息 消息
    public function sendEarthInfo($longitude, $latitude, $magnitude, $depth, $time, $address) {
        Session::delete('code');
        // $rpcClient = new RpcClient();
        $post_data = array(
            "type" => "earthquakeInfo",
            "timestamp" => time(),
            "message" => array(
                "code" => "CD".$time,
                "longitude" => $longitude,
                "latitude" => $latitude,
                "magnitude" => $magnitude,
                "depth" => $depth,
                "time" => $time,
                "city_name" => $address,
                "version" => 1
            )
        );
        // $response = $rpcClient->call($post_data);
        Session::set('code',$time);
        // var_dump($response);
    }

    // 地震日志消息
    public function sendLogInfo($info) {
        // $rpcClient = new RpcClient();
        $time = Session::get('code');
        $post_data = array(
            "type" => "logger",
            "timestamp" => time(),
            "message" => array(
                "code" => "CD".$time,
                "level" => "INFO",
                "data" => $info,
                "version" => 1
            )
        );
        // $response = $rpcClient->call($post_data);
    }
    public function sendLogInfoByTime($time, $msg) {
        // $rpcClient = new RpcClient();
        $post_data = array(
            "type" => "logger",
            "timestamp" => time(),
            "message" => array(
                "code" => "CD".$time,
                "level" => "INFO",
                "data" => $msg,
                "version" => 1
            )
        );
        // $response = $rpcClient->call($post_data);
    }

    // 模型消息
    public function sendModelInfo($time, $modelData) {
        // $rpcClient = new RpcClient();
        $post_data = [
            "type" => "processor",
            "timestamp" => time(),
            "message" => [
                "status" => "NEW",
                "describe" => "地震震中城市速报数据主要信息",
                "code" => "CD".$time,
                "version" => 1,
                "data" => $modelData
            ]
        ];
        // $response = $rpcClient->call($post_data);
        return $response;
    }

    // 接收总线广播来的消息
    public function monitorSystem() {
        // $receiveLogsTopic = new ReceiveLogsTopic();
        // $receiveLogsTopic->rereceiveLogs();
    }
}