<?php
/*
	Мы направляем сообщения в нашу именованную точку доступа ‘logs’, 
	вместо точки доступа по умолчанию. 
	Нам нужно было указать имя очереди при отправки сообщения. 
	Но для точки доступа с типом fanout в этом нет необходимости.
*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

/*
	 после установки соединения мы создаем точку доступа. 
	 Этот шаг необходим, так как использование несуществующей точки доступа – запрещено. 

	 Сообщение в точке доступа будут потеряны, пока ни одна очередь не связана с точкой доступа. 
	 Но это хорошо для нас: пока нет ни одного подписчика нашей точки доступа, все сообщения могут 
	 безопасно удалятся.
*/
$channel->exchange_declare('logs', 'fanout', false, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) $data = "info: Hello World!";
$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'logs');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

?>