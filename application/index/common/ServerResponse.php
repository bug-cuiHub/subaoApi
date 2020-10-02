<?php
namespace app\index\common;

use app\index\common\ResponseCode;

class ServerResponse {

    private $arr = array(
        'status' => ResponseCode::SUCCESS,
        'msg'    => '',
        'data'   => null
    );
    
    public function createBySuccess() {
        return json_encode($this->arr);
    }
    
    public function createBySuccessMsg($msg) {
        $this->arr['msg'] = $msg;
        return json_encode($this->arr);
    }

    public function createBySuccessMsgData($msg, $resData) {
        $this->arr['msg'] = $msg;
        $this->arr['data'] = $resData;
        return json_encode($this->arr);
    }

    public function createBySuccessData($resData) {
        $this->arr['data'] = $resData;
        return json_encode($this->arr);
    }

    public function createByError() {
        $this->arr['status'] = ResponseCode::ERROR;
        return json_encode($this->arr);
    }

    public function createByErrorMsg($msg) {
        $this->arr['status'] = ResponseCode::ERROR;
        $this->arr['msg'] = $msg;
        return json_encode($this->arr);
    }

    public function createByErrorCodeMessage($code, $msg) {
        $this->arr['msg'] = $msg;
        $this->arr['status'] = $code;
        return json_encode($this->arr);
    }

    public function createByErrorMsgData($msg, $errData) {
        $this->arr['msg'] = $msg;
        $this->arr['data'] = $errData;
        return json_encode($this->arr);
    }
}