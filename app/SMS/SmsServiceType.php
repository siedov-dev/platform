<?php

namespace App\SMS;

enum SmsServiceType: string
{
    case ACSolutions = 'acsolutions';
    case Atomic = 'atomic';
    case BSG = 'BSG';
    case FakeSms = 'FakeSms';
    case SMSC = 'SMSC';
    case UnisenderSms = 'unisender';
    case Zadarma = 'zadarma';
    case P1sms = 'p1sms';
}
