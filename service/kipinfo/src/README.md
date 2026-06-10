# Сервис IpInfo

Используеться длы быстрого доступа к информации о IP и проверки его на блокировку.  
Обмен осуществляется в видде http запросов.  
Список заблокированных стран устанавлявается в crm напрямую в БД (таблица ip_country_blocks).
Информация об ip хранится в таблице ip_info.  

Система использует может использовать несколько поставщиков данных ip. 
Поставщики находятся в пакете `dataproviders/ipdataproviders` и реализуют интерфейс `container.IPDataProvider`.  
При этом поставщик данных ип можно использовать в друх режимах - получение основной информации или уточнение информации:
```go
	// Инициализация IP провайдеров данных
	// Основные провайдеры данных IP
	container.SetIPDataProviders(
		ipdataprovider.NewIpApiCoProvider(),     // выдает больше информации включая валюту страны
		ipdataprovider.NewIpApiIsDataProvider(), // выдает больше информации включая валюту страны
	)
	// Провайдеры данных IP для более точной/расширенной информации
	container.SetIPDataEnrichmentProviders(
		ipdataprovider.NewIpInfoProvider(), // более правильно выдает местоположения
	)
```
Система будет использовать данный первого удачного запроса поставщика основных данных и перезаписывать данные из поставщиков уточненных данных.

# Авторизация
Каждый запрос должен содержать заголовок `Authorization: Bearer <token>`


# API Маршруты сервиса kipinfo

## Проверка работоспособности сервера

**GET /**

- **Описание:** Проверяет, работает ли сервер.
- **Ответ:**  
  - `200 OK` — Сервер работает.

---

## Получение информации по IP

**GET /ip/{ip}**

- **Параметры:**
  - `ip` — IP-адрес (строка)
- **Описание:** Получает информацию об IP-адресе из кэша или базы данных.
- **Ответ:**  
  - `200 OK` — Информация об IP-адресе.

`/ip/5.45.66.203`

```json
{
    "status":200,
    "data":{
        "ip":"5.45.66.203",
        "continent":"EU",
        "country":"The Netherlands",
        "country_code":"NL",
        "region":"North Holland",
        "city":"Amsterdam",
        "lat":"52.3908",
        "long":"4.8668",
        "currency":"EUR",
        "isp":"Scalaxy B.V.",
        "org":"",
        "created_at":"2025-05-15T08:55:39+03:00",
        "updated_at":"2025-05-15T08:55:41+03:00",
        "block_to":null,
        "whitelist":false
    }
}
```
---

## Получение информации по IP без кеша

**GET /ipnocache/{ip}**

- **Параметры:**
  - `ip` — IP-адрес (строка)
- **Описание:** Получает актуальную информацию об IP-адресе, минуя кэш.
- **Ответ:**  
  - `200 OK` — Информация об IP-адресе.

`/ip/5.45.66.203`

```json
{
    "status":200,
    "data":{
        "ip":"5.45.66.203",
        "continent":"EU",
        "country":"The Netherlands",
        "country_code":"NL",
        "region":"North Holland",
        "city":"Amsterdam",
        "lat":"52.3908",
        "long":"4.8668",
        "currency":"EUR",
        "isp":"Scalaxy B.V.",
        "org":"",
        "created_at":"2025-05-15T08:55:39+03:00",
        "updated_at":"2025-05-15T08:55:41+03:00",
        "block_to":null,
        "whitelist":false
    }
}
```
---

## Проверка, заблокирован ли IP

**GET /isblocked/{ip}**

- **Параметры:**
  - `ip` — IP-адрес (строка)
- **Описание:** Проверяет, заблокирован ли указанный IP-адрес.
- **Ответ:**  
  - `200 OK` — Результат проверки.

***не заблокирован***
```json
{"status":200,"blocked":false,"reason":""}
```
***заблокирован по ip***

```json
{"status":200,"blocked":true,"reason":"IP"}
```
---

## Блокировка IP

**GET /block/{ip}/{block_to}**

- **Параметры:**
  - `ip` — IP-адрес (строка)
  - `block_to` — Дата окончания блокировки (формат: `YYYY-MM-DD HH:MM:SS`)
- **Описание:** Блокирует IP-адрес до указанной даты.
- **Ответ:**  
  - `200 OK` — Информация о заблокированном IP.

`/block/5.45.66.203/2025-10-01%2012:00:00`

```json
{
    "status":200,
    "data":{
        "ip":"5.45.66.203",
        "continent":"EU",
        "country":"The Netherlands",
        "country_code":"NL",
        "region":"North Holland",
        "city":"Amsterdam",
        "lat":"52.3908",
        "long":"4.8668",
        "currency":"EUR",
        "isp":"Scalaxy B.V.",
        "org":"",
        "created_at":"2025-05-15T08:55:39+03:00",
        "updated_at":"2025-05-20T15:55:51.088599078+03:00",
        "block_to":"2025-10-01T12:00:00Z",
        "whitelist":false
    }
}
```
---

## Разблокировка IP

**GET /unblock/{ip}**

- **Параметры:**
  - `ip` — IP-адрес (строка)
- **Описание:** Разблокирует указанный IP-адрес.
- **Ответ:**  
  - `200 OK` — Информация о разблокированном IP.

`/unblock/5.45.66.203`

```json
{
    "status":200,
    "data":{
        "ip":"5.45.66.203",
        "continent":"EU",
        "country":"The Netherlands",
        "country_code":"NL",
        "region":"North Holland",
        "city":"Amsterdam",
        "lat":"52.3908",
        "long":"4.8668",
        "currency":"EUR",
        "isp":"Scalaxy B.V.",
        "org":"",
        "created_at":"2025-05-15T08:55:39+03:00",
        "updated_at":"2025-05-15T08:55:41+03:00",
        "block_to":null,
        "whitelist":false
    }
}
```

---

## Установка белого списка IP

**GET /whitelist/{ip}/{set}**

- **Параметры:**
  - `ip` — IP-адрес (строка)
  - `set` — Флаг белого списка (boolean), может быть 1, 0, true, false
- **Описание:** Устанавливает/снимает признак белого списка IP-адреса. 
IP в белом списке не могут быть заблокированы и игнорируют блокировку по стане или IP.
- **Ответ:**  
  - `200 OK` — Информация о IP.

`/whitelist/5.45.66.203/1`

```json
{
    "status":200,
    "data":{
        "ip":"5.45.66.203",
        "continent":"EU",
        "country":"The Netherlands",
        "country_code":"NL",
        "region":"North Holland",
        "city":"Amsterdam",
        "lat":"52.3908",
        "long":"4.8668",
        "currency":"EUR",
        "isp":"Scalaxy B.V.",
        "org":"",
        "created_at":"2025-05-15T08:55:39+03:00",
        "updated_at":"2025-05-15T08:55:41+03:00",
        "block_to":null,
        "whitelist":true
    }
}
```

---

## Перезагрузка списка заблокированных стран

**GET /reload/BlockedCountries**

- **Описание:** Перезагружает список заблокированных стран из репозитория.
- **Ответ:**  
  - `200 OK` — Результат перезагрузки.

```json
{"status":200,"message":"Blocked countries reloaded successfully"}
```