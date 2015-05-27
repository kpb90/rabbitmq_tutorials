<?php
/*
	Задача. Каждое сообщение - каждому подписчику.
	
	Решение.
	
	Точки обмена(exchanges)

	Producer всегда направляет сообщение в exchange.
	Фактически, довольно часто Producer не знает, дошло ли его сообщение до конкретной очереди. 
	
	Если exchange не указан как в предыдущих уроках, то она сообщение отправляюся в exchange по умолчанию.
	
	Exchange выполняет две функции:
	
	— получает сообщения от поставщика;
	— отправляет эти сообщения в очередь.

	Exchange может отправить сообщение в конкретную очередь, либо в несколько очередей, 
	либо не отправлять никому и удалить его.
	
	Эти правила описываются в типе точки доступа (exchange type).

	Существуют несколько типов: direct, topic, headers и fanout
	 
	Схема работы:
	
	Направляем сообщения в exchange c именем 'logs'.
	Для exchange с типом fanout нет необходимости указывать название очереди (routing_key) в basic_publish и 
	создавать очереди функцией queue_declare (в Producer), так как сообщения будут
	отправляться во все очереди, поэтому в этом примере basic_publish не имеет третьего параметра.

*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

/*
	 после установки соединения мы создаем точку доступа. 
	 Этот шаг необходим, так как использование несуществующей точки доступа – запрещено. 

	 Сообщение в точке доступа будут теряться, пока ни одна очередь не связана с точкой доступа. 
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