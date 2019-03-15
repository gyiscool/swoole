<?php
namespace Libs;

use Libs\SafeCode;

class Websocket
{

    public      $port; // 端口号
    public      $table;//全记录
    protected   $key;
    protected   $safeObj;//负责加密解密的对象 实例化必须在前面
    protected   $type = array(
        '1'=>'客户端向服务器发送消息，服务器返回数据',
        '2'=>'服务器像客户端推送消息',
    );

    function __construct($port = 9501,$key=123)
    {
        $this->createTable();
        $this->key = $key;
        $this->port = $port;
        $this->safeObj = new SafeCode($key);
    }


    public function run()
    {

        $ws = new \swoole_websocket_server("0.0.0.0", $this->port);

        $ws->on('open', [$this,'open']);//建立连接


        $ws->on('message',[$this,'message']);//通信

        $ws->on('close', [$this,'close']);//关闭连接

        $ws->start();
    }

    /**
     * 需要判断是否合法的连接  需要处理json
     * @param \swoole_websocket_server $server
     * @param \swoole_http_request $request
     */
    public function open(\swoole_websocket_server $server,\swoole_http_request $request)
    {
/*
        $user = array(
            'fd'=>$request->fd,
            'userid'=>$request->fd,
        );

        $this->table->set($request->fd, $user);
*/

        if(empty($request->get['sid'])){ //为空返回错误

        }
        var_dump($request);
        $safe = $this->serializes($request->get['sid']);
        var_dump($safe);
        if(!$safe){
            $server->push($request->fd, json_encode(array('message'=>'连接不合法','status'=>0,'type'=>1),JSON_UNESCAPED_UNICODE));
            $server->close($request->fd);
        }else{
            $user = array(
                'fd'=>$request->fd,
                'userid'=>$safe['user'],
            );

            $this->table->set($request->fd, $user);
            //$server->push($request->fd, json_encode(array('message'=>'success connect','status'=>1,'type'=>1),JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 发送信息 解密
     * @param \swoole_websocket_server $server
     * @param $frame
     */
    public function message(\swoole_websocket_server $server,$frame)
    {
        echo "Message: {$frame->data}\n";

        $json   = json_decode($frame->data,true);

        if(!empty($json) && !empty($json['to']) && !empty($json['data'])){ //参数是否合法
            foreach ($this->table as $row) {

                if ($frame->fd == $row['fd']) { //发送给我本身的连接
                    $server->push($row['fd'], json_encode(array('message'=>'ok','status'=>1,'type'=>1),JSON_UNESCAPED_UNICODE));
                    continue;
                }

                foreach($json['to'] as $val){

                    if($val == $row['userid']){
                        $server->push($row['fd'], json_encode(array('data'=>$json['data'],'status'=>1,'type'=>2),JSON_UNESCAPED_UNICODE));
                    }

                }
            }
        }else{
            $server->push($frame->fd, json_encode(array('message'=>'参数错误','status'=>0,'type'=>1),JSON_UNESCAPED_UNICODE));
        }
    }

    //根据 userId 提取

    /**
     * 关闭连接
     * @param swoole_websocket_server $server
     * @param $frame
     */
    public function close(\swoole_websocket_server $server,$frame)
    {
        $this->table->del($frame);
        echo "client-{$frame} is closed\n";

    }


    private function createTable()
    {
        $this->table = new \swoole_table(1024);
        $this->table->column('fd', \swoole_table::TYPE_INT);
        $this->table->column('userid', \swoole_table::TYPE_INT);
        $this->table->create();
    }

    /**
     * 解密 处理json
     * @param $txt
     * @param $key
     * @return bool
     */
    public function serializes($txt)
    {
        $code = $this->safeObj->passport_decrypt(strtr( $txt,' ','+'));var_dump($code);
        $code_json = json_decode($code,true);
        if(empty($code_json['type']) || empty($code_json['user']))
        {
            return false;
        }
        return $code_json;
    }
/*
    //解密函数
    function passport_decrypt($txt, $key) {
        $txt = $this->passport_key(base64_decode($txt), $key);
        $tmp = '';
        for($i = 0;$i < strlen($txt); $i++) {
            $md5 = $txt[$i];
            $tmp .= $txt[++$i] ^ $md5;
        }
        return $tmp;
    }

    function passport_key($txt, $encrypt_key) {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }
*/

}


