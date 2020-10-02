<?php

require __DIR__.'/../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ReceiveLogsTopic {

    private $connection;
    private $channel;
    private $exchange_name = 'DZYJ';
    private $queue_name = '';
    private $binding_key = "dzyj.dzqr";

    public function __construct() {
        // $this->connection = new AMQPStreamConnection('192.168.16.21', 5672, 'snn', '@huqiao123', 'snn_vhost');        
        $this->connection = new AMQPStreamConnection('192.168.43.248', 5672, 'admin', 'admin', 'test_vhosts');   //rabbitmq所在的ip
        // $this->connection = new AMQPStreamConnection('192.168.16.48', 5672, 'admin', 'admin', 'test_vhosts');
        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare($this->exchange_name, 'topic', false, false, false);
        list($this->queue_name, ,) = $this->channel->queue_declare("", false, false, true, false);
        $this->channel->queue_bind($this->queue_name, $this->exchange_name, $this->binding_key);
    }

    public function receiveLogs() {
        echo "=====Waiting for logs. To exit press CTRL+C\n";
        $callback = function ($msg) {
            var_dump(' ==== ', $msg->delivery_info['routing_key'], ':', $msg->body);
            echo "success";
        };
        $this->channel->basic_consume($this->queue_name, '', false, true, false, false, $callback);

        while($this->channel->is_consuming()) {
            $this->channel->wait();
        }
        
        $this->channel->close();
        $this->connection->close();
    }

}

$receiveLogsTopic = new ReceiveLogsTopic();
$receiveLogsTopic->receiveLogs();


?>
