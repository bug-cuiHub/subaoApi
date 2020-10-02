<?php

declare(strict_types=1);

namespace app\index\service\impl;

use think\Session;

use app\index\service\impl\EarthInfoServiceImpl;
use app\index\service\ScreenService;
use app\index\util\FtpUtil;
use app\index\util\IntegratePattern;
use app\index\common\ServerResponse;
use app\index\model\PublishedSeismic;


class ScreenServiceImpl implements ScreenService {


    // FTP上传文件需要在php扩展中开启php_ftp支持
    private $ip = '39.106.58.40';
    private $port = 8082;
    private $config = [
        'host' => '39.106.58.40',
        'user' => 'YZZ',
        'pass' => '751563'
    ];

    //生成bat文件 创建图片存放文件夹
    public function writeBat() {
        $serverResponse = new ServerResponse();
        $eqUnique = Session::get('eqUnique');
        //python路径查询
        if($eqUnique == 'null') {
            $msg = 'Session中没有相关的值！';
            return $serverResponse->createByErrorMsg($msg);
        }
        $where_python = exec("where python", $log, $status);
        if ($status != 0) {
            $msg = '你的电脑无权限！请查看您的执行权限与python环境！';
            return $serverResponse->createByErrorMsg($msg);
        }
        // 拼接bat文件内容
        $string_bat = $where_python . ' .\\auto.py ' . $eqUnique;
        // 生成bat文件
        $auto_bat = fopen($eqUnique . ".bat", "w") or die("Unable to open file!");
        fwrite($auto_bat, $string_bat);
        fclose($auto_bat);

        $local_path = 'static/uploads/image/' . $eqUnique;
        if (!is_dir($local_path)) {
            //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
            $create_path  = mkdir(iconv("UTF-8", "GBK", $local_path), 0777, true);
            if (!$create_path) {
                $msg = "目录 ".$create_path."  创建失败, 您可能没有权限或路径有错！";
                return $serverResponse->createByErrorMsg($msg);
            }
        }
        return $this->runBat($eqUnique);
    }

    // 通过请求执行bat文件
    private function runBat($eqUnique) {
        $serverResponse = new ServerResponse();
        set_time_limit(0);
        ignore_user_abort(true);
        exec($eqUnique . ".bat", $log, $status);
        if ($status != 0) {
            $msg = 'bat执行失败！请查看你的python路径是否正确或是否有执行权限！';
            return $serverResponse->createByErrorMsg($msg);
        }
        unlink($eqUnique . ".bat");
        return $this->upFileToFtp($eqUnique);
    }

    // 获取Ftp的url
    private function getFtpUrl($ip, $eqUnique) {

        $imgNameList = array(
            // 'cthd', 'cseismicinfo', 'cweatherinfo', 'cpopulationinfo', 'ceconomicinfo',
            // 'cenvironmeninfo', 'dashboard', 'worldEq', 'seismicinfo', 'thd', 'nearfz',
            'populationinfo', 'economicinfo', 'environmeninfo', 'weatherinfo'
        );
        $imgField = ["ftp_population", "ftp_economics", "ftp_environment", "ftp_weather"];

        for ($i = 0; $i < count($imgNameList); $i++) {
            $ftpUrl[$imgField[$i]] = 'ftp://' . $ip . '/static/uploads/image/' .
                $eqUnique . '/' . $imgNameList[$i] . '.png';
        }
        return $ftpUrl;
    }

    // 获取Nginx托管的静态资源的url
    private function getHttpUrl($ip, $port, $eqUnique) {
        $imgNameList = array(
            // 'cthd', 'cseismicinfo', 'cweatherinfo', 'cpopulationinfo', 'ceconomicinfo',
            // 'cenvironmeninfo', 'dashboard', 'worldEq', 'seismicinfo', 'thd', 'nearfz',
            'populationinfo', 'economicinfo', 'environmeninfo', 'weatherinfo'
        );
        $imgField = ["url_population", "url_economics", "url_environment", "url_weather"];
        for ($i = 0; $i < count($imgNameList); $i++) {
            $httpUrl[$imgField[$i]] = 'http://' . $ip . ':' . $port . '/' .
                $eqUnique . '/' . $imgNameList[$i] . '.png';

        }
        return $httpUrl;
    }

    // 上传文件至FTP服务器
    private function upFileToFtp($eqUnique) {
        $serverResponse = new ServerResponse();
        $TimeOut = '/' . $eqUnique . '/';
        $ftp = new FtpUtil($this->config);
        $result = $ftp->connect();
        if (!$result) {
            $msg = $ftp->get_error_msg();
            return $serverResponse->createByErrorMsg($msg);
        }

        $local_file = 'static/uploads/image';
        $remote_file = 'static/uploads/image';
        //上传文件
        $imgNameList = array(
            'cthd', 'cseismicinfo', 'cweatherinfo', 'cpopulationinfo', 'ceconomicinfo',
            'cenvironmeninfo', 'dashboard', 'worldEq', 'seismicinfo', 'thd', 'weatherinfo',
            'populationinfo', 'economicinfo', 'environmeninfo', 'nearfz'
        );

        $file_num = 0;
        for ($i = 0; $i < count($imgNameList); $i++) {
            if ($ftp->upload(
                $local_file . $TimeOut . $imgNameList[$i] . '.png',
                $remote_file . $TimeOut . $imgNameList[$i] . '.png'
            )) {
                $file_num++;
            }
        }

        // 发送模型消息
        $this->sendModelInfo($eqUnique);
        
        $msg = $file_num . "张上传成功！";
        return $serverResponse->createBySuccessMsg($msg);
    }

    // 图片上传完成后发送的模型消息
    private function sendModelInfo($eqUnique) {
        $earthInfoServiceImpl = new EarthInfoServiceImpl();
        $integratePattern = new IntegratePattern();
        $publishedSeismic = new PublishedSeismic();

        $time = $publishedSeismic->getCode($eqUnique);
        $fieldInfo = $earthInfoServiceImpl->getSoucreData($eqUnique);
        $FtpURI = $this->getFtpUrl($this->ip, $eqUnique);
        $NginxURI = $this->getHttpUrl($this->ip, $this->port, $eqUnique);
        $modelData = array_merge($fieldInfo, $FtpURI, $NginxURI);
        
        $integratePattern->sendModelInfo($time, $modelData);
        $msg = "速报数据处理完成！";
        $integratePattern->sendLogInfoByTime($time, $msg);
    }
}
