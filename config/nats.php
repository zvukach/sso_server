<?php
return [
	'server' => env('NATS_SERVER', '127.0.0.1:4222'),
	'nkey' => env('NATS_NKEY', ''),
	'port' => env('NATS_PORT', 4222),
	'subject' => env('NATS_SUBJECT', 'backend.topic1'),
	'stream' => env('NATS_STREAM', 'backend'),
];
