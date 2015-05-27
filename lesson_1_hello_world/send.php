<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Подключаемся к броккеру сообщений
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

/*
	Перед отправкой сообщения мы должны убедиться, что очередь, получающая сообщение, существует. 
	Если отправить сообщение в несуществующую очередь, RabbitMQ его проигнорирует. 
	Давайте создадим очередь, в которую будет отправлено сообщение, назовем ее «hello»:
*/
$channel->queue_declare('hello', false, false, false, false);
/*
	Теперь все готово для отправки сообщения. Наше первое сообщение будет содержать строку «Hello World!» 
	и будет отправлено в очередь с именем «hello».

	Вообще, в RabbitMQ сообщения не отправляются непосредственно в очередь, 
	они должны пройти через exchange.

	exchange позволяет определять, в какую именно очередь отправлено сообщение.
	Имя очереди должно быть определено в параметре routing_key:

	В нашем случае exchange - пустая строка
*/
$msg = new AMQPMessage('Hello World!');
$channel->basic_publish($msg, '', 'hello');

echo " [x] Sent 'Hello World!'\n";

/*
	Перед выходом из программы необходимо убедиться, что буфер был очищен и сообщение дошло до RabbitMQ.
	В этом можно быть уверенным, если использовать безопасное закрытие соединения с брокером.
*/
$channel->close();
$connection->close();

?>