<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

// Подключаемся к броккеру сообщений
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

/*
	Команда queue_declare не будет создавать новую очередь, если она уже существует, 
	поэтому сколько бы раз не была вызвана эта команда, все-равно будет создана только одна очередь.
*/

$channel->queue_declare('hello', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

/*
	Получение осуществляется при помощи подписки с использованием callback функции. 
	При получении каждого сообщения вызывается callback функция. 
*/
$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
};
/*
	Далее, нам нужно обозначить, что callback функция будет получать сообщения из очереди с именем «hello»:

	здесь в true параметр no_ack
*/

$channel->basic_consume('hello', '', false, true, false, false, $callback);

// запуск бесконечного процесса, который ожидает сообщения из очереди и вызывает callback функцию, 
// когда это необходимо.
while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>