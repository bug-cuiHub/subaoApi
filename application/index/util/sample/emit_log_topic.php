<?php

require_once '../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$exchange_name = 'topic_logs';
$connection = new AMQPStreamConnection('localhost', 5672, 'snn', '@huqiao123', 'snn_vhost');
$channel = $connection->channel();
$channel->exchange_declare($exchange_name, 'topic', false, false, false);
$routing_key = "test.info";
// $routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
$data = "Send a data...";
// $data = implode(' ', array_slice($argv, 2));

$msg = new AMQPMessage($data);
$channel->basic_publish($msg, $exchange_name, $routing_key);
echo '==== Send ', $routing_key, ':', $data, "\n";


$channel->close();
$connection->close();



?>