<?php
return [
	'server' => env('NATS_SERVER', '127.0.0.1:4222'),
	'nkey' => env('NATS_NKEY', ''),
	'stream' => env('NATS_STREAM', 'backend'),
	'subject' => env('NATS_SUBJECT', 'backend.topic1'),
];
