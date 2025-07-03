<!DOCTYPE html>
<html>
<head>
    <title>Socket.IO Test</title>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js "></script>
</head>
<body>
<h1>WebSocket тест</h1>
<script>
    const socket = io('http://localhost:3000');

    socket.on('connect', () => {
        console.log('Подключено к серверу');
        socket.emit('join', 'test_room');
    });

    socket.on('response', (msg) => {
        console.log('Получено:', msg);
    });

    setInterval(() => {
        socket.emit('message', {
            room: 'test_room',
            text: 'Привет от клиента!',
            user: 'TestUser'
        });
    }, 5000);
</script>
</body>
</html>
