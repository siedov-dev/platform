class WebSocketClient {
    constructor(options = {}) {
        // Настройки по умолчанию
        // this.port = options.port || 8082;
        this.path = options.path || '/ws/connect';
        this.reconnectInterval = options.reconnectInterval || 5000; // 5 секунд
        this.socket = null;

        // Коллбэки
        this.onMessageCallback = null;
        this.onOpenCallback = null;
        this.onCloseCallback = null;
    }

    /**
     * Устанавливает функцию для получения токена
     * @param {Function} tokenProvider - Асинхронная функция, возвращающая Promise<string>
     */
    setTokenProvider(tokenProvider) {
        this.tokenProvider = tokenProvider;
    }

    /**
     * Формирует URL и открывает соединение
     */
    async connect(token = null) {
        // Если токен не передан явно, пробуем получить его через провайдер
        if (!token && this.tokenProvider) {
            try {
                token = await this.tokenProvider();
            } catch (e) {
                console.error('Не удалось получить токен для WebSocket:', e);
                // Пробуем переподключиться позже, если не удалось получить токен
                setTimeout(() => this.connect(), this.reconnectInterval);
                return;
            }
        }

        const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
        // const protocol = 'ws';
        const host = window.location.hostname;
        // const host = "127.0.0.1";
        // const url = `${protocol}://${host}:${this.port}${this.path}?token=${token}`;
        const url = `${protocol}://${host}${this.path}?token=${token}`;

        console.log(`Connecting to WebSocket: ${url}`);

        this.socket = new WebSocket(url);

        this.socket.onopen = (event) => this._handleOpen(event);
        this.socket.onmessage = (event) => this._handleMessage(event);
        this.socket.onclose = (event) => this._handleClose(event);
        this.socket.onerror = (error) => this._handleError(error);
    }

    /**
     * Отправка данных на сервер
     * @param {Object|String} data - Данные для отправки
     */
    send(data) {
        if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
            console.warn('WebSocket не подключен. Невозможно отправить сообщение.');
            return;
        }

        // Если передали объект, преобразуем в JSON
        const payload = typeof data === 'object' ? JSON.stringify(data) : data;
        this.socket.send(payload);
    }

    /**
     * Установка обработчика входящих сообщений
     * @param {Function} callback
     */
    onMessage(callback) {
        this.onMessageCallback = callback;
    }

    /**
     * Установка обработчика открытия соединения
     * @param {Function} callback
     */
    onOpen(callback) {
        this.onOpenCallback = callback;
    }

    /**
     * Внутренний метод обработки открытия
     */
    _handleOpen(event) {
        console.log('WebSocket соединение установлено');
        if (this.onOpenCallback) {
            this.onOpenCallback(event);
        }
    }

    /**
     * Внутренний метод обработки сообщений
     */
    _handleMessage(event) {
        let data = event.data;

        // Попытка распарсить JSON, если это JSON
        try {
            data = JSON.parse(data);
        } catch (e) {
            // Если не JSON, оставляем как есть (строка)
        }

        if (this.onMessageCallback) {
            this.onMessageCallback(data);
        }
    }

    /**
     * Внутренний метод обработки закрытия (с авто-реконнектом)
     */
    _handleClose(event) {
        console.log('WebSocket соединение закрыто. Попытка переподключения...', event);

        if (this.onCloseCallback) {
            this.onCloseCallback(event);
        }

        // Автоматическое переподключение
        setTimeout(() => {
            this.connect();
        }, this.reconnectInterval);
    }

    /**
     * Внутренний метод обработки ошибок
     */
    _handleError(error) {
        console.error('WebSocket ошибка:', error);
    }
}
