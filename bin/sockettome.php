#!/usr/bin/env php
<?php
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use MyApp\SocketToMe;

require dirname(__DIR__) . '/vendor/autoload.php';

$server = IoServer::factory(
	new WsServer(
		new SocketToMe()
	)
  , 8080
);

$server->run();
