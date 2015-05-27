<?php
/*
	Схема работы данного примера: 
	
	Будем посылать сообщения, соответствующие ресурсоемким задачам.
	Сложность задачи будет определяться количеством точек в строке сообщения.

	По умолчанию, RabbitMQ будет передавать каждое новое сообщение следующему подписчику. 
	Таким образом, все подписчики получат одинаковое количество сообщений. 
	Такой способ распределения сообщений называется циклический [алгоритм round-robin]. 
	В данном примере мы исправим данное поведение и загрузка обработчиков будет более равномерная
*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('192.168.10.102', 5672, 'guest', 'guest');
$channel = $connection->channel();


$channel->queue_declare('task_queue', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) $data = "Hello World!";
$msg = new AMQPMessage($data,
                        array('delivery_mode' => 2) # помечаем что это сообщение устойчивое к падению сервера
                      );

$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

?>