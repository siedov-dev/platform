package logger

import (
	"fmt"
	"io"
	"os"
	"sync"
	"time"
)

// _ используется для проверки того, что DailyLogFile реализует интерфейс io.WriteCloser на этапе компиляции.
var _ io.WriteCloser = &DailyLogFile{}

// cacheSize определяет максимальный размер буфера для временного хранения данных перед записью в файл.
const cacheSize = 5000

// DailyLogFile представляет собой структуру для работы с ежедневными лог-файлами.
// Реализует интерфейс io.WriteCloser.
// Позволяет писать данные в файл с ежедневной ротацией и буферизацией.
// Поддерживает два независимых буфера для записи в файл.
// Содержит методы для явной записи буферов в файл и закрытия файла.
type DailyLogFile struct {
	fileName string
	file     *os.File
	mut      sync.Mutex
	buf1     []byte
	buf1mut  sync.Mutex
	buf2     []byte
	buf2mut  sync.Mutex
	currBuf  int
}

// NewDailyLogFile создает и возвращает новый лог-файл с ежедневной ротацией и двойным буфером для записи.
// Использует текущую дату для имени файла и сохраняет файлы в директорию "./logs".
// Возвращает DailyLogFile и возможную ошибку при создании директории или открытии файла.
func NewDailyLogFile() (o *DailyLogFile, err error) {
	outLog := DailyLogFile{}
	outLog.fileName = time.Now().Format("2006-01-02")
	outLog.buf1 = make([]byte, 0, cacheSize)
	outLog.buf2 = make([]byte, 0, cacheSize)
	outLog.currBuf = 1

	os.MkdirAll("./logs", 0777)
	outLog.file, err = os.OpenFile("./logs/"+outLog.fileName+".log", os.O_WRONLY|os.O_CREATE|os.O_APPEND, 0666)
	if err != nil {
		return nil, err
	}
	return &outLog, nil
}

// Close implements io.WriteCloser.
func (f *DailyLogFile) Close() error {
	if f.file != nil {
		f.flushBuf(1)
		f.flushBuf(2)
		return f.file.Close()
	}
	return nil
}

// Write implements io.WriteCloser.
func (f *DailyLogFile) Write(p []byte) (n int, err error) {
	f.mut.Lock()
	defer f.mut.Unlock()
	//Проверяем текущее время, создаем новый лог файл при необходимости
	if f.fileName != time.Now().Format("2006-01-02") {
		// сброс буферов, закрытие файла
		f.flushBuf(1)
		f.flushBuf(2)
		f.Close()
		// создание нового файла
		f.fileName = time.Now().Format("2006-01-02")
		err := os.MkdirAll("./logs", 0777)
		if err != nil {
			fmt.Fprintf(os.Stderr, "error creating directory : %s", err.Error())
			return 0, err
		}
		f.file, err = os.OpenFile("./logs/"+f.fileName+".log", os.O_WRONLY|os.O_CREATE|os.O_APPEND, 0666)
		if err != nil {
			fmt.Fprintf(os.Stderr, "error creating log file : %s", err.Error())
			return 0, err
		}
	}

	// Если буфер заполнен до лимита,
	// переключаем буфер и записываем буфер в лог файл
	if f.currBuf == 1 {
		if len(f.buf1)+len(p) > cacheSize {
			f.currBuf = 2
			go f.flushBuf(1)
		}
	} else {
		if len(f.buf2)+len(p) > cacheSize {
			f.currBuf = 1
			go f.flushBuf(2)
		}
	}

	// Пишем сообщение в активный буфер
	if f.currBuf == 1 {
		f.buf1mut.Lock()
		f.buf1 = append(f.buf1, p...)
		f.buf1mut.Unlock()
	} else {
		f.buf2mut.Lock()
		f.buf2 = append(f.buf2, p...)
		f.buf2mut.Unlock()
	}
	return len(p), nil
}

// flushBuf записывает буфер с указанным номером buf_n в файл и очищает его содержимое.
// Использует блокировки для потокобезопасной работы.
// Если запись в файл завершается с ошибкой, выводит сообщение в стандартный поток ошибок.
func (f *DailyLogFile) flushBuf(buf_n int) {
	if buf_n == 1 {
		f.buf1mut.Lock()
		defer f.buf1mut.Unlock()
		_, err := f.file.Write(f.buf1[:len(f.buf1)])
		if err != nil {
			fmt.Fprintln(os.Stderr, err.Error())
		}
		f.buf1 = f.buf1[0:0]
	} else {
		f.buf2mut.Lock()
		defer f.buf2mut.Unlock()
		_, err := f.file.Write(f.buf2[:len(f.buf2)])
		if err != nil {
			fmt.Fprintln(os.Stderr, err.Error())
		}
		f.buf2 = f.buf2[0:0]
	}
}

// Flush записывает данные из обоих буферов в файл и очищает их содержимое, используя блокировки для потокобезопасности.
func (f *DailyLogFile) Flush() {
	f.mut.Lock()
	defer f.mut.Unlock()
	f.flushBuf(1)
	f.flushBuf(2)
}
