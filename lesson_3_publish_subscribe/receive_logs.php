<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

/*
	Создадим exchange с именем logs типа fanout 
	
	Тип fanout – копирует все сообщения которые поступают к нему во все очереди, 
	которые ему доступны

	sudo rabbitmqctl list_exchanges - посмотреть все точки доступа

	По умолчанию или безымянная точка доступа: сообщение направляется в очередь, 
	идентифицированную через ключ “routing_key” - третий параметр basic_publish

	$channel->exchange_declare('logs', 'fanout', false, false, false);
	$channel->basic_publish($msg, 'logs');
*/
$channel->exchange_declare('logs', 'fanout', false, false, false);

/*

Временные очереди:

Всё это время мы использовали наименование очередей (“hello“ или “task_queue”). 
Возможность давать наименования помогает указать обработчикам (workers) определенную очередь, 
а также делить очередь между продюсерами и подписчиками.

Но наша система логирования требует, чтобы в очередь поступали все сообщения, а не только часть. 
Также мы хотим, чтобы сообщения были актуальными, а не старыми. Для этого нам понадобиться 2 вещи:

— Каждый раз когда мы соединяемся с Rabbit, мы создаем новую очередь, или даем создать 
  серверу случайное наименование;
— Каждый раз когда подписчик отключается от Rabbit, мы удаляем очередь.

В php-amqplib клиенте, когда мы обращаемся к очереди без наименовании, 
мы создаем временную очередь и автоматически сгенерированным наименованием:

Метод вернет автоматически сгенерированное имя очереди. 
Она может быть такой – ‘amq.gen-JzTY20BRgKO-HjmUJj0wLg.’.
Когда заявленное соединение оборвется, очередь автоматически удалиться.

*/

list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

/*
	У нас есть точка доступа с типом fanout и очередь. 
	Сейчас мы должны связать exchange и очередь.
	Это связь называется bindings.
	
	Между очередью и exchange может быть несколько bindings.
	Чтобы эти bindings можно было различать у queue_bind есть третий параметр название этой связи (binding_key).
	Но для fanout exchange этот параметр просто игнорируется
	
	rabbitmqctl list_bindings - посмотреть все очереди
*/
$channel->queue_bind($queue_name, 'logs');

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

$callback = function($msg){
  echo ' [x] ', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>