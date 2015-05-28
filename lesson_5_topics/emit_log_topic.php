<?php
/*
	Тип exchange topic.
	Логика работы topic такая же как и у direct — сообщения доходят до тех очередей, 
	binding key которых совпадает с routing key сообщения. 
	Но есть 2 специальные возможности для topic:
	* (star) может быть заменено на ровно 1 слово;
	# (hash) может быть заменено на 0 или более слов.
*/
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$channel->exchange_declare('topic_logs', 'topic', false, false, false);

$routing_key = $argv[1];
if(empty($routing_key)) $routing_key = "anonymous.info";
$data = implode(' ', array_slice($argv, 2));
if(empty($data)) $data = "Hello World!";

$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'topic_logs', $routing_key);

echo " [x] Sent ",$routing_key,':',$data," \n";

$channel->close();
$connection->close();

?>