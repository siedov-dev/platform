async function generateFingerprint() {
    const components = {
        // Базовая информация о системе
        platform: navigator.platform,
        userAgent: navigator.userAgent,
        language: navigator.language,
        languages: navigator.languages,
        hardwareConcurrency: navigator.hardwareConcurrency,
        deviceMemory: navigator.deviceMemory,

        // Экран
        screen: {
            colorDepth: screen.colorDepth,
            pixelDepth: screen.pixelDepth,
            width: screen.width,
            height: screen.height,
            availWidth: screen.availWidth,
            availHeight: screen.availHeight
        },

        // Временная зона
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,

        // Проверка хранилищ
        storageAvailable: {
            localStorage: (() => {
                try {
                    localStorage.setItem('test', 'test');
                    localStorage.removeItem('test');
                    return true;
                } catch (e) {
                    return false;
                }
            })(),
            sessionStorage: (() => {
                try {
                    sessionStorage.setItem('test', 'test');
                    sessionStorage.removeItem('test');
                    return true;
                } catch (e) {
                    return false;
                }
            })(),
            indexedDB: !!window.indexedDB
        },

        // Canvas fingerprint
        canvas: await generateCanvasFingerprint(),

        // Аудио fingerprint
        // audio: await generateAudioFingerprint(),

        // Шрифты
        fonts: await detectFonts(),

        // WebGL информация
        webgl: getWebGLInfo()
    };

    // Создаем строку из всех компонентов для хеширования
    const fingerprintString = JSON.stringify(components);

    return {
        components,
        hash: murmurHash3.x64.hash128(fingerprintString)
    };
}

async function generateSha256Hash(str) {
    // Преобразуем строку в массив байтов
    const msgBuffer = new TextEncoder().encode(str);
    // Получаем хеш
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
    // Преобразуем массив байтов в шестнадцатеричную строку
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hashHex;
}



// Функция для генерации отпечатка canvas
function generateCanvasFingerprint() {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 200;
    canvas.height = 200;

    // Рисуем текст
    ctx.textBaseline = "top";
    ctx.font = "14px 'Arial'";
    ctx.textBaseline = "alphabetic";
    ctx.fillStyle = "#f60";
    ctx.fillRect(125,1,62,20);
    ctx.fillStyle = "#069";
    ctx.fillText("Hello, world!", 2, 15);
    ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
    ctx.fillText("Test string", 4, 45);

    return canvas.toDataURL();
}

// Функция для генерации аудио отпечатка
async function generateAudioFingerprint() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const analyser = audioContext.createAnalyser();
        const gainNode = audioContext.createGain();
        const scriptProcessor = audioContext.createScriptProcessor(4096, 1, 1);

        gainNode.gain.value = 0; // Отключаем звук
        oscillator.type = 'triangle';
        oscillator.connect(analyser);
        analyser.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.start(0);

        return new Promise((resolve) => {
            scriptProcessor.onaudioprocess = (e) => {
                const array = new Float32Array(analyser.frequencyBinCount);
                analyser.getFloatFrequencyData(array);
                resolve(array[0]); // Берем первое значение как отпечаток
                oscillator.stop();
                audioContext.close();
            };
        });
    } catch (e) {
        return null;
    }
}

// Функция для определения доступных шрифтов
async function detectFonts() {
    const baseFonts = ['monospace', 'sans-serif', 'serif'];
    const fontList = [
        'Arial', 'Arial Black', 'Arial Narrow', 'Calibri',
        'Cambria', 'Cambria Math', 'Comic Sans MS', 'Courier',
        'Courier New', 'Georgia', 'Helvetica', 'Impact',
        'Times', 'Times New Roman', 'Trebuchet MS', 'Verdana'
    ];

    const span = document.createElement('span');
    span.innerHTML = 'mmmmmmmmmmlli';
    span.style.fontSize = '72px';
    document.body.appendChild(span);

    const baseFontWidth = {};
    baseFonts.forEach(baseFont => {
        span.style.fontFamily = baseFont;
        baseFontWidth[baseFont] = span.offsetWidth;
    });

    const detected = [];
    for (const font of fontList) {
        let match = false;
        for (const baseFont of baseFonts) {
            span.style.fontFamily = `${font},${baseFont}`;
            if (span.offsetWidth !== baseFontWidth[baseFont]) {
                match = true;
                break;
            }
        }
        if (match) {
            detected.push(font);
        }
    }

    document.body.removeChild(span);
    return detected;
}

// Функция для получения информации о WebGL
function getWebGLInfo() {
    const canvas = document.createElement('canvas');
    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');

    if (!gl) {
        return null;
    }

    return {
        vendor: gl.getParameter(gl.VENDOR),
        renderer: gl.getParameter(gl.RENDERER),
        version: gl.getParameter(gl.VERSION),
        shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION),
        extensions: gl.getSupportedExtensions()
    };
}
