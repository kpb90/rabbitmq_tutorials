<?php
/*
	Задача. Отправлять получателю только часть сообщений. 
	Например, мы сможем сохранять на диске только сообщения с критическими ошибками (экономия места на диске),
	а в консоли будем отображать все сообщения.

	Binding — это связь между точкой доступа и очередью. 
	
	queue_bind -  binding_key (id связи, которая создается между exchange и очередью)
	basic_publish  - routing_key (id связи на которую летит сообщение)
		
	Значение binding_key зависит от типа exchange. exchange с типом fanout просто проигнорирует его.

	$binding_key = 'black';
	$channel->queue_bind($queue_name, $exchange_name, $binding_key);
	
	Точка доступа Direct

	Расширим нашу систему, чтобы фильтровать сообщения по степени важности. 
	Для примера мы сделаем так, чтобы скрипт, записывающий логи на диск не тратил своё место на собщения 
	с типом warning или info.

	Будем использовать тип direct. 
	Его алгоритм очень прост — сообщения идут в ту очередь, binding_key которой совпадает с routing key 
	сообщения.
*/
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

$severity = $argv[1];
if(empty($severity)) $severity = "info";

$data = implode(' ', array_slice($argv, 2));
if(empty($data)) $data = "Hello World!";

$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'direct_logs', $severity);

echo " [x] Sent ",$severity,':',$data," \n";

$channel->close();
$connection->close();

?>