<?php
namespace app\index\util;

require_once '../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'snn', '@huqiao123', 'snn_vhost');
$channel = $connection->channel();

$channel->queue_declare('rpc_req_queue', false, false, false, false);


echo " [x] Awaiting RPC requests\n";

$callback = function ($req) {
    // var_dump($req->body);
    $data = $req->get_properties();
    // var_dump($data["application_headers"]->get('data'));
    // $head = $req->get('application_headers');
    // var_dump($head);
    var_dump(get_object_vars($data));
    $msg = new AMQPMessage(
        'success',
        array('correlation_id' => $req->get('correlation_id'))
    );
    $req->delivery_info['channel']->basic_publish(
        $msg,
        '',
        $req->get('reply_to')
    );
    $req->delivery_info['channel']->basic_ack(
        $req->delivery_info['delivery_tag']
    );
};
$channel->basic_qos(null, 1, null);
$channel->basic_consume('rpc_req_queue', '', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}
$channel->close();
$connection->close();
?>