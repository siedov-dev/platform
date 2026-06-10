<?php
return [
    //TelegramUser
    'resend_email' => "\u{1F4E8} Resend email code",
    'resend_timeout' => "SMS can be resent in: :secs s.",
    'confirm_code' => "Telegram confirmation code: :code",
    'code_sent' => "Code sent",

    //TelegramBot
    'email_not_found' => 'Provided email was not found.',
    'email_not_found2' => "Provided email was not found ❌",
    'email_already_used' => 'Provided email is registered by another user',
    'waiting_verification' => "Waiting for verification",
    'enter_email_code' => 'Enter the verification code sent to the specified email',
    'phone_not_found' => "Phone number not found ❌",
    'phone_not_found2' => "Provided phone number not found",
    'phone_already_used' => 'Phone is registered by another user',
    'enter_phone_code' => 'Enter the confirmation code sent to the specified phone number',
    'use_context_menu' => 'Use the context menu to continue.',

    //////
    'cmd_get_promo' => "\u{1F381} Get promo code",
    'cmd_inline_login' => "\u{1F4E7} Registration",
    'cmd_yes' => "\u{2705} yes",
    'cmd_no' => "\u{274C} cancel",

    'cmd_cancel' => "\u{2B05}\u{FE0F} Cancel",
    'cmd_settings' => "\u{2699} \u{FE0F} Settings",
    'cmd_share_phone' => "\u{1F4F2} Share phone number",
    'cmd_resend_sms' => "\u{1F4E8} Resend sms code",
    'ptn_resend_sms' => '#.{1,5}Resend sms code$#',

    //parser
    'cmd_language' => "\u{1F30D} Language",
    'cmd_connect_acc' => "\u{1F4E7} Bind account",
    'ptn_connect_acc' => '#.{1,5}Bind account$#',
    'cmd_balance' => "\u{1F4B5} Balance",
    'ptn_balance' => '#.{1,5}Balance$#',
    'cmd_bonuses' => "\u{1F381} Bonuses",
    'ptn_bonuses' => '#.{1,5}Bonuses$#',
    'cmd_activities' => "\u{2728} Current activities",
    'ptn_activities' => '#.{1,5}Current activities$#',
    'cmd_promocode' => "\u{1F39F} Promocodes$",
    'ptn_promocode' => '#.{1,5}Promocodes$#',
    'cmd_bezdep' => "\u{1F39F} No dep. bonus",
    'ptn_bezdep' => '#.{1,5}No dep.{1,3}bonus$#',
    'cmd_manager_bonus' => "\u{1F39F} Get a bonus from the manager",
    'ptn_manager_bonus' => '#.{1,5}Get a bonus from the manager$#',
    'cmd_personal_manager' => "\u{1F468} Personal manager",
    'ptn_personal_manager' => "#.{1,5}Personal manager$#",
    'cmd_mirrors' => "\u{1FA9E} Mirrors",
    'ptn_mirrors' => '#.{1,5}Mirrors$#',

    'link_personal_manager' => "To contact your personal manager, follow the link below",
    'link_manager_bonus' => "To receive a bonus from the manager, follow the link below",


    'command_not_found' => 'Command not found',

    //commands
    'user_not_found' => 'User not found',
    'user_not_identified' => 'User not identified',
    'user_not_connected' => 'Account is not bind, use menu or /login command to bind',
    'bonus_available' => 'Bonus available',
    'no_bonuses' => 'You have no bonuses available at th moment.',

    'active_tnt' => 'Active tournament ',
    'active_event' => 'Stock active ',
    'next_tnt' => 'Next tournament ',
    'event_soon' => 'Stock soon ',
    'no_tnt_events' => 'There are no activities at the moment.',

    'already_connected' => 'Your telegram is already bind to Krakencassino account.',
    'enter_email_or_phone' => 'Enter your Krakencassino account email or phone number',

    'no_promocodes' => 'No promo codes available',
    'verified_email_required' => 'email verification required to get the promo code',
    'channel_subs_required' => 'To get the promo code you need to subscribe to channel ',
    'got_promo' => "You have received a promotional code with a bonus :bonus_name!\n<b>:promocode</b> \n Follow the <a href=\":link\">link</a> or click the promo code button in the \"Bonuses\" section of your personal account",

    'welcome_text' => 'Welcome to the official Kraken casino bot🐙.',
    'welcome_text2' => 'If you are not registered yet, register faster and claim your 💸BONUS💸 after binding your account to the bot🤑 https://krakencasino.club',

    //states
    'account_connected' => '✅ Your account is successfully bind.',
    'wrong_confirm_code' => "Wrong confirmation code!",
    'wrong_email_or_phone' => 'Invalid email or phone number provided',
    'connect_acc' => 'Bind account',

    'balance' => 'Balance',
    
    'subs_1' => "Your luck is waiting! 🔥🎰\n \nStart spinning and winning right now! \n\n💸🌟 Get 450 free spins for free and unique bonuses every day! 🎁🆓 \nDon't waste time, take the first step to success right now! 💪🚀",
     'subs_2' => "Welcome to the world of winning and excitement!🔥🎰\n\nAre you ready to take your luck into your hands? We know the secrets of a successful game that will lead you to big wins!\n \nGet 450 free spins and original bonuses every day!🎁🆓\n\nDon't miss your chance for incredible winnings.Join us right now and start your path to success!💪🤑",
     'got_subs_bonus' => "you have already received a bonus, please contact your manager to receive a new bonus",

     'no_mirrors' => 'No mirrors available',
];
