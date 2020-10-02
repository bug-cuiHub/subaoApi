<?php
namespace app\index\util;

require __DIR__.'/../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RpcClient {

    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;

    public function __construct()
    {
        // $this->connection = new AMQPStreamConnection('192.168.16.21', 5672, 'snn', '@huqiao123', 'snn_vhost');
        $this->connection = new AMQPStreamConnection('192.168.43.248', 5672, 'admin', 'admin', 'test_vhosts');     //rabbitmq所在的ip
        // $this->connection = new AMQPStreamConnection('192.168.16.48', 5672, 'admin', 'admin', 'test_vhosts');
        $this->channel = $this->connection->channel();
        list($this->callback_queue, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );
        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            true,
            false,
            false,
            array(
                $this,
                'onResponse'
            )
        );
    }

    public function onResponse($rep) {
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    public function call($arr) {
        $this->response = null;
        $this->corr_id = uniqid();

        $body = json_encode($arr);

        $tale = new AMQPTable();
        $tale->set('msgFrom', 'dzyj.dzqr');
        $tale->set('msgTo', 'dzyj.xtjc');
        $tale->set('msg', $body);

        $msg = new AMQPMessage(
            $body,
            [
                'application_headers' => $tale,
                'correlation_id' => $this->corr_id,
                'reply_to' => $this->callback_queue,
            ]
        );
        $this->channel->basic_publish($msg, '', 'rpc_queue');
        while (!$this->response) {
            $this->channel->wait();
        }
        return $this->response;
    }
}