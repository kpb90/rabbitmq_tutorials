<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*
	Схема работы данного примера: 
	
	Producer:
		Подключается к брокеру, создает очередь с определенным именем, если она не существует.
		Кладем сообщение в созданную очередь.
		
	Consumer:
		Подключается к брокеру, создает очередь с определенным именем (если не существует).
		Слушает очередь с заданным именем
	
	
*/

// Подключаемся к броккеру сообщений
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

/*
	Перед отправкой сообщения мы должны убедиться, что очередь, получающая сообщение, существует. 
	Если отправить сообщение в несуществующую очередь, RabbitMQ его проигнорирует. 
*/
$channel->queue_declare('hello', false, false, false, false);
/*
	В RabbitMQ сообщения не отправляются непосредственно в очередь, 
	они должны пройти через exchange - позволяет определять, в какую именно очередь отправлено сообщение..
	
	Если не создан exchange, то по умолчанию сообщение направляется в ту очередь, 
	чье имя совпадает с "routing_key" - третий параметр basic_publish.
*/
$msg = new AMQPMessage('Hello World!');
//                         exchange routing_key
$channel->basic_publish($msg, '', 'hello');

echo " [x] Sent 'Hello World!'\n";

/*
	Перед выходом из программы необходимо убедиться, что буфер был очищен и сообщение дошло до RabbitMQ.
	В этом можно быть уверенным, если использовать безопасное закрытие соединения с брокером.
*/
$channel->close();
$connection->close();

?>