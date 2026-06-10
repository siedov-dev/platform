<?php
return [
    //TelegramUser
    'resend_email' => "\u{1F4E8} Opätovné odoslanie e-mailového kódu",
    'resend_timeout' => "Je možné opäť odosielať sms prostredníctvom: :secs с.",
    'confirm_code' => "Potvrdzovací kód Telegram: :code",
    'code_sent' => "Kód bol odoslaný",

    //TelegramBot
    'email_not_found' => 'Zadaný e-mail nebol nájdený',
    'email_not_found2' => "Zadaný e-mail nebol nájdený ❌",
    'email_already_used' => 'Zadaný e-mail je registrovaný iným používateľom',
    'waiting_verification' => "Čakanie na overenie",
    'enter_email_code' => 'Zadajte overovací kód zaslaný na zadanú e-mailovú adresu',
    'phone_not_found' => "Telefónne číslo nebolo nájdené ❌",
    'phone_not_found2' => "Zadané telefónne číslo nebolo nájdené",
    'phone_already_used' => 'Zadané telefónne číslo je zaregistrované iným používateľom',
    'enter_phone_code' => 'Zadajte potvrdzovací kód odoslaný na zadané telefónne číslo',
    'use_context_menu' => 'Ak chcete pokračovať, použite kontextové menu.',

    //////
    'cmd_get_promo' => "\u{1F381} Získajte propagačný kód",
    'cmd_inline_login' => "\u{1F4E7} Registrácia",
    'cmd_yes' => "\u{2705} áno",
    'cmd_no' => "\u{274C} zrušenie",

    'cmd_cancel' => "\u{2B05}\u{FE0F} Zrušenie",
    'cmd_settings' => "\u{2699} \u{FE0F} Nastavenia",
    'cmd_share_phone' => "\u{1F4F2} Moje číslo.",
    'cmd_resend_sms' => "\u{1F4E8} Opätovné odoslanie kódu SMS",
    'ptn_resend_sms' => '#.{1,5}Opätovné odoslanie kódu SMS$#',

    //parser
    'cmd_language' => "\u{1F30D} Jazyk",
    'cmd_connect_acc' => "\u{1F4E7} Prepojenie účtu",
    'ptn_connect_acc' => '#.{1,5}Prepojenie účtu$#',
    'cmd_balance' => "\u{1F4B5} Bilancia",
    'ptn_balance' => '#.{1,5}Bilancia$#',
    'cmd_bonuses' => "\u{1F381} Bonusy",
    'ptn_bonuses' => '#.{1,5}Bonus$#',
    'cmd_activities' => "\u{2728} Aktuálne aktivity",
    'ptn_activities' => '#.{1,5}Aktuálne aktivity$#',
    'cmd_promocode' => "\u{1F39F} Propagačné kódy$",
    'ptn_promocode' => '#.{1,5}Промокоды$#',
    'cmd_bezdep' => "\u{1F39F} Bonus bez vkladu",
    'ptn_bezdep' => '#.{1,5}Bonus bez {1,3}vkladu$#',
    'cmd_manager_bonus' => "\u{1F39F} Získajte bonus od manažéra",
    'ptn_manager_bonus' => '#.{1,5}Získajte bonus od manažéra$#',
    'cmd_personal_manager' => "\u{1F468} Osobný manažér",
    'ptn_personal_manager' => "#.{1,5}Osobný manažér$#",
    'cmd_mirrors' => "\u{1FA9E} Zrkadlá",
    'ptn_mirrors' => '#.{1,5}Zrkadlá$#',

    'link_personal_manager' => "Ak chcete kontaktovať svojho osobného manažéra, kliknite na odkaz nižšie",
    'link_manager_bonus' => "Ak chcete získať bonus od manažéra, kliknite na odkaz nižšie",

    'command_not_found' => 'Príkaz nebol nájdený',

    //commands
    'user_not_found' => 'Používateľ nebol nájdený',
    'user_not_identified' => 'Používateľ nie je identifikovaný',
    'user_not_connected' => 'Odpojený používateľ, zadajte svoju e-mailovú adresu na stránke pomocou príkazu /login',
    'bonus_available' => 'K dispozícii je bonus',
    'no_bonuses' => 'V tejto chvíli nemáte k dispozícii žiadne bonusy.',

    'active_tnt' => 'Aktívny turnaj ',
    'active_event' => 'Aktívna činnosť ',
    'next_tnt' => 'Ďalší turnaj ',
    'event_soon' => 'Akcia sa blíži ',
    'no_tnt_events' => 'V súčasnosti neprebiehajú žiadne propagačné akcie ani turnaje.',

    'already_connected' => 'Váš telegram je už prepojený s vaším účtom Krakencassino.',
    'enter_email_or_phone' => 'Zadajte svoj e-mail alebo telefónne číslo účtu Krakencassino',

    'no_promocodes' => 'Nie sú k dispozícii žiadne promo kódy',
    'verified_email_required' => 'Ak chcete získať propagačný kód, musíte overiť svoj e-mail',
    'channel_subs_required' => 'Musíte byť prihlásení na kanál, aby ste dostali promo kód ',
    'got_promo' => "Dostali ste propagačný kód s bonusom :bonus_name!\n<b>:promocode</b> \n Пройдите по <a href=\":link\">odkaz</a> alebo kliknite na tlačidlo promo kódu v sekcii \"Bonusy\" vo vašej osobnej skrinke.",

    'welcome_text' => 'Vitajte na oficiálnom kasínovom bote Kraken🐙.',
    'welcome_text2' => 'Ak ste sa ešte nezaregistrovali, poponáhľajte sa, zaregistrujte sa a po prepojení svojho účtu s botom si vyžiadajte bonus 💸NON-DEPOSIT💸.🤑 https://krakencasino.club',

    //states
    'account_connected' => '✅ Vaše konto je pripojené.',
    'wrong_confirm_code' => "Nesprávny potvrdzovací kód!",
    'wrong_email_or_phone' => 'Nesprávne zadaný e-mail alebo telefónne číslo',
    'connect_acc' => 'Prepojenie účtu',

    'balance' => 'Bilancia',
    
    'subs_1' => "Vaše šťastie čaká! 🔥🎰\n \nZačnite točiť a vyhrávať hneď teraz! \n\n💸🌟 Získajte 450 zatočení zadarmo a jedinečné bonusy každý deň! 🎁🆓 \nNestrácajte čas, urobte prvý krok k úspechu práve teraz! 💪🚀",
    'subs_2' => "Vitajte vo svete víťazstiev a vzrušenia!🔥🎰\n\nSte pripravení vziať svoje šťastie do svojich rúk? Poznáme tajomstvá úspešnej hry, ktorá vás privedie k veľkým výhram!\n \nZískajte 450 zatočení zadarmo a originálne bonusy každý deň! 🎁🆓\n\nNepremeškajte svoju šancu na neuveriteľné výhry. Pridajte sa k nám a začnite svoju cestu k úspechu! 💪🤑",
    'got_subs_bonus' => "už ste dostali bonus, kontaktujte manažéra pre získanie nového bonusu",

    'no_mirrors' => 'K dispozícii nie sú žiadne zrkadlá',

];
