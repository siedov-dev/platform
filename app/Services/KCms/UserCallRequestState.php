<?php

namespace App\Services\KCms;

enum UserCallRequestState: string
{
    case Pending = 'pending'; // Ожидает звонка
    case None = 'none'; // Не было запросов
    case HadRequests = 'had-call-request'; // Были запросы
}
