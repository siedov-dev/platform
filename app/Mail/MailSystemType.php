<?php

namespace App\Mail;

enum MailSystemType: string
{
    case Kraken = "kraken";
    case SendGrid = "sengrid";
    case FakeMail = "fakemail";
    case Unisender = "unisender";
    case CustomerIo = "customer_io";
}
