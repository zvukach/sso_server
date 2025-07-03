const { createAdapter } = require('@socket.io/redis-adapter');
const { createClient } = require('redis');
const jwt = require('jsonwebtoken');
const { Server } = require('socket.io');
const { instrument } = require('@socket.io/admin-ui');

// === Подключение к Redis  ===
const pubClient = createClient({ url: 'redis://redis:6379' });
const subClient = pubClient.duplicate();

Promise.all([pubClient.connect(), subClient.connect()])
    .then(() => {
        console.log('✅ Redis успешно подключен');

        // === Сервер для Admin UI (порт 3001) ===
        const adminIo = new Server(3001, {
            cors: {
                origin: [
                    'http://localhost',
                    'http://localhost:8080',
                    'https://admin.socket.io ',
                ],
                credentials: true,
            },
        });

        adminIo.adapter(createAdapter(pubClient, subClient));

        instrument(adminIo, {
            auth: false,
            mode: 'development',
        });

        console.log('🎛️  Admin UI запущен на ws://localhost:3001');


        // === Основной сервер (порт 3000) ===
        const io = new Server(3000, {
            cors: {
                origin: '*',
                methods: ['GET', 'POST'],
            },
        });

        io.adapter(createAdapter(pubClient, subClient));


        // // Middleware JWT
        // io.use((socket, next) => {
        //     const token = socket.handshake.auth.token;
        //     if (!token) return next(new Error('Authentication error: token required'));
        //
        //     try {
        //         const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your_jwt_secret_key');
        //         socket.user = decoded;
        //         next();
        //     } catch (err) {
        //         next(new Error('Authentication error: invalid token'));
        //     }
        // });


        // События
        io.on('connection', (socket) => {
            const userId = socket.user.id;
            console.log(`[Подключение] Пользователь ${userId} (${socket.id}) подсоединен`);

            socket.join(userId);

            socket.on('join', (room) => {
                socket.join(room);
                console.log(`[Комната] Пользователь ${userId} присоединился к "${room}"`);
            });

            socket.on('leave', (room) => {
                socket.leave(room);
                console.log(`[Комната] Пользователь ${userId} покинул "${room}"`);
            });

            socket.on('message', (data) => {
                const { room, text } = data;
                const from = socket.user.username || socket.id;

                console.log(`[Сообщение] От ${from} в "${room}": ${text}`);

                io.to(room).emit('response', {
                    from,
                    text,
                    timestamp: new Date().toISOString(),
                });
            });

            socket.on('disconnect', () => {
                console.log(`[Отключение] Пользователь ${userId} отсоединился`);
            });
        });

        console.log('🟢 Основной WebSocket сервер запущен на ws://localhost:3000');
    })
    .catch((err) => {
        console.error('❌ Ошибка подключения к Redis:', err.message);
    });
