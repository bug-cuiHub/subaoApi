<?php
namespace app\index\model;

use think\Model;
use think\Db;
use app\index\service\impl\UserServiceImpl;

class UserModel extends Model {
    
    protected $pk = 'id';
    
    public function verifyLogin($username) {
        $res = Db::table('user')->where('username', $username)->value('userkey');
        return $res;
    }

}