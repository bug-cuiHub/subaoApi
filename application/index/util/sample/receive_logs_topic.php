<?php

require_once '../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$exchange_name = 'topic_logs';
$connection = new AMQPStreamConnection('localhost', 5672, 'snn', '@huqiao123', 'snn_vhost');
$channel = $connection->channel();


$channel->exchange_declare($exchange_name, 'topic', false, false, false);
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

// $binding_keys = array_slice($argv, 1);
$binding_keys = "test.*";
// var_dump($binding_keys);
// if (empty($binding_keys)) {
//     file_put_contents('php://stderr', "please input a parames\n");
//     exit(1);
// }

// foreach ($binding_keys as $binding_key) {
    $channel->queue_bind($queue_name, $exchange_name, $binding_keys);
// }
echo "=====Waiting for logs. To exit press CTRL+C\n";
$callback = function ($msg) {
    echo ' ==== ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}
$channel->close();
$connection->close();



?>
