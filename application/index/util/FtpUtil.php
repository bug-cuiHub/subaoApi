<?php

namespace app\index\util;


/**
 * Created by PhpStorm.
 */
class FtpUtil {
    private $host = ''; //远程服务器地址
    private $user = ''; //ftp用户名
    private $pass = ''; //ftp密码
    private $port = 21; //ftp登录端口
    private $error = ''; //最后失败时的错误信息
    protected $conn; //ftp登录资源
    private $conn_id;

    /**
     * 可以在实例化类的时候配置数据，也可以在下面的connect方法中配置数据
     * Ftp constructor.
     * @param array $config
     */
    public function __construct(array $config = []) {
        empty($config) or $this->initialize($config);
    }

    /**
     * 初始化数据
     * @param array $config 配置文件数组
     */
    public function initialize(array $config = []) {
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->pass = $config['pass'];
        $this->port = isset($config['port']) ?: 21;
    }

    /**
     * 连接及登录ftp
     * @param array $config 配置文件数组
     * @return bool
     */
    public function connect(array $config = []) {
        empty($config) or $this->initialize($config);
        if (FALSE == ($this->conn = @ftp_connect($this->host))) {
            $this->error = "主机连接失败";
            return FALSE;
        }

        if (!$this->_login()) {
            $this->error = "服务器登录失败";
            return FALSE;
        }
        $res = ftp_pasv($this->conn, true);
        return TRUE;
    }

    /**
     * 上传文件到ftp服务器
     * @param string $local_file 本地文件路径
     * @param string $remote_file 服务器文件地址
     * @param bool $permissions 文件夹权限
     * @param string $mode 上传模式(ascii和binary其中之一)
     * @return bool
     */
    public function upload($local_file, $remote_file, $mode = 'auto', $permissions = NULL) {
        if (!file_exists($local_file)) {
            $this->error = "本地文件不存在";
            // return $local_file.$remote_file;
            return FALSE;
        }
        if ($mode == 'auto') {
            $ext = $this->_get_ext($local_file);
            $mode = $this->_set_type($ext);
        }
        //创建文件夹
        $this->_create_remote_dir($remote_file);
        $mode = FTP_BINARY;
        //$mode =  FTP_BINARY;
        $result = @ftp_put($this->conn, $remote_file, $local_file, $mode); //同步上传
        if ($result === FALSE) {
            $this->error = "文件上传失败";
            return FALSE;
        }
        return TRUE;
    }


    /**
     * ftp创建多级目录
     * @param string $remote_file 要上传的远程图片地址
     */
    private function _create_remote_dir($remote_file, $permissions = NULL) {
        $remote_dir = dirname($remote_file);
        $path_arr = explode('/', $remote_dir); // 取目录数组
        //$file_name = array_pop($path_arr); // 弹出文件名
        // var_dump($path_arr);
        $path_div = count($path_arr); // 取层数
        foreach ($path_arr as $val) // 创建目录
        {
            if (@ftp_chdir($this->conn, $val) == FALSE) {
                $tmp = @ftp_mkdir($this->conn, $val); //此处创建目录时不用使用绝对路径(不要使用:2018-02-20/ceshi/ceshi2，这种路径)，因为下面ftp_chdir已经已经把目录切换成当前目录
                if ($tmp == FALSE) {
                    return "目录创建失败，请检查权限及路径是否正确！";
                    exit;
                }
                if ($permissions !== NULL) {
                    //修改目录权限
                    $this->_chmod($val, $permissions);
                }
                @ftp_chdir($this->conn, $val);
            }
        }

        for ($i = 0; $i < $path_div; $i++) // 回退到根,因为上面的目录切换导致当前目录不在根目录
        {
            @ftp_cdup($this->conn);
        }
    }

    /**
     * 获取文件的后缀名
     * @param string $local_file
     * @return bool|string
     */
    private function _get_ext($local_file = '') {
        return (($dot = strrpos($local_file, '.')) == FALSE) ? 'txt' : substr($local_file, $dot + 1);
    }

    /**
     * 根据文件后缀获取上传编码
     * @param string $ext
     * @return string
     */
    private function _set_type($ext = '') {
        //如果传输的文件是文本文件，可以使用ASCII模式，如果不是文本文件，最好使用BINARY模式传输。
        return in_array($ext, ['txt', 'text', 'php', 'phps', 'php4', 'js', 'css', 'htm', 'html', 'phtml', 'shtml', 'log', 'xml'], TRUE) ? 'ascii' : 'binary';
    }

    /**
     * 修改目录权限
     * @param string $path 目录路径
     * @param int $mode 权限值
     * @return bool
     */
    private function _chmod($path, $mode = 0755) {
        if (FALSE == @ftp_chmod($this->conn, $path, $mode)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 登录Ftp服务器
     */
    private function _login() {
        return @ftp_login($this->conn, $this->user, $this->pass);
    }

    /**
     * 获取上传错误信息
     */
    public function get_error_msg() {
        return $this->error;
    }

    /**
     * 关闭ftp连接
     * @return bool
     */
    public function close() {
        return $this->conn ? @ftp_close($this->conn_id) : FALSE;
    }
}
