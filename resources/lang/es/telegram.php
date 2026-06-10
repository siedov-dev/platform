<?php
return [
    //TelegramUser
    'resend_email' => "\u{1F4E8} Reenviar código de correo electrónico",
    'resend_timeout' => "Es posible volver a enviar sms a través de: :secs s.",
    'confirm_code' => "Código de confirmación de telegram: :code",
    'code_sent' => "Se ha enviado el código",

    //TelegramBot
    'email_not_found' => 'Correo electrónico introducido no encontrado',
    'email_not_found2' => "Correo electrónico introducido no encontrado ❌",
    'email_already_used' => 'El correo electrónico introducido está registrado por otro usuario',
    'waiting_verification' => "A la espera de verificación",
    'enter_email_code' => 'Introduzca el código de verificación enviado a la dirección de correo electrónico especificada',
    'phone_not_found' => "Número de teléfono no encontrado ❌",
    'phone_not_found2' => "No se ha encontrado el número de teléfono introducido",
    'phone_already_used' => 'El número de teléfono introducido está registrado por otro usuario',
    'enter_phone_code' => 'Introduzca el código de confirmación enviado al número de teléfono especificado',
    'use_context_menu' => 'Utilice el menú contextual para continuar.',

    //////
    'cmd_get_promo' => "\u{1F381} Obtener código promocional",
    'cmd_inline_login' => "\u{1F4E7} Inscripción",
    'cmd_yes' => "\u{2705} sí",
    'cmd_no' => "\u{274C} cancelación",

    'cmd_cancel' => "\u{2B05}\u{FE0F} Anulación",
    'cmd_settings' => "\u{2699} \u{FE0F} Ajustes",
    'cmd_share_phone' => "\u{1F4F2} Mi número",
    'cmd_resend_sms' => "\u{1F4E8} Reenviar el código SMS",
    'ptn_resend_sms' => '#.{1,5}Reenviar el código SMS$#',

    //parser
    'cmd_language' => "\u{1F30D} Idioma",
    'cmd_connect_acc' => "\u{1F4E7} Vincular una cuenta",
    'ptn_connect_acc' => '#.{1,5}Vincular una cuenta$#',
    'cmd_balance' => "\u{1F4B5} Saldo",
    'ptn_balance' => '#.{1,5}Saldo$#',
    'cmd_bonuses' => "\u{1F381} Saldo",
    'ptn_bonuses' => '#.{1,5}Saldo$#',
    'cmd_activities' => "\u{2728} Actividades en curso",
    'ptn_activities' => '#.{1,5}Actividades en curso$#',
    'cmd_promocode' => "\u{1F39F} Códigos promocionales$",
    'ptn_promocode' => '#.{1,5}Códigos promocionales$#',
    'cmd_bezdep' => "\u{1F39F} Bonificación sin depósito",
    'ptn_bezdep' => '#.{1,5}Bonificación sin {1,3}depósito$#',
    'cmd_manager_bonus' => "\u{1F39F} Obtenga una bonificación del gerente",
    'ptn_manager_bonus' => '#.{1,5}Obtenga una bonificación del gerente$#',
    'cmd_personal_manager' => "\u{1F468} Gerente de personal",
    'ptn_personal_manager' => "#.{1,5}Gerente de personal$#",
    'cmd_mirrors' => "\u{1FA9E} Espejos",
    'ptn_mirrors' => '#.{1,5}Espejos$#',

    'link_personal_manager' => "Para ponerse en contacto con su administrador personal, siga el siguiente enlace",
    'link_manager_bonus' => "Para recibir una bonificación del gerente, siga el enlace a continuación",

    'command_not_found' => 'Comando no encontrado',

    //commands
    'user_not_found' => 'Usuario no encontrado',
    'user_not_identified' => 'Usuario no identificado',
    'user_not_connected' => 'Usuario no vinculado, especifique su dirección de correo electrónico en el sitio, con el comando /login',
    'bonus_available' => 'Bonificación disponible',
    'no_bonuses' => 'No tiene ninguna bonificación disponible en este momento.',

    'active_tnt' => 'Torneo activo ',
    'active_event' => 'Acción activa ',
    'next_tnt' => 'Próximo torneo ',
    'event_soon' => 'La acción está al caer ',
    'no_tnt_events' => 'En este momento no hay promociones ni torneos.',

    'already_connected' => 'Su telegrama ya está vinculado a su cuenta Krakencassino.',
    'enter_email_or_phone' => 'Introduzca el correo electrónico o el número de teléfono de su cuenta de Krakencassino',

    'no_promocodes' => 'No hay códigos promocionales disponibles',
    'verified_email_required' => 'Debe verificar su correo electrónico para recibir el código promocional',
    'channel_subs_required' => 'Debes estar suscrito al canal para recibir el código promocional ',
    'got_promo' => "Has recibido un código promocional con una bonificación :bonus_name!\n<b>:promocode</b> \n Ir a través de <a href=\":link\">ссылке</a> o haz clic en el botón de código promocional de la sección \"Bonificaciones\" de tu gabinete personal.",

    'welcome_text' => 'Bienvenido al bot oficial del casino Kraken🐙.',
    'welcome_text2' => 'Si aún no te has registrado, date prisa en hacerlo y reclama tu 💸NON-DEPOSIT💸 bonificación tras vincular tu cuenta al bot.🤑 https://krakencasino.club',

    //states
    'account_connected' => '✅ Su cuenta está conectada.',
    'wrong_confirm_code' => "Código de confirmación incorrecto",
    'wrong_email_or_phone' => 'Correo electrónico o número de teléfono incorrectos',
    'connect_acc' => 'Vincular una cuenta',

    'balance' => 'Saldo',
    
    'subs_1' => "¡Tu suerte te está esperando! 🔥🎰\n \n¡Empieza a girar y ganar ahora mismo! \n\n💸🌟 ¡Obtén 450 giros gratis gratis y bonificaciones únicas todos los días! 🎁🆓 \nNo pierdas el tiempo, ¡da el primer paso hacia el éxito ahora mismo! 💪🚀",
     'subs_2' => "¡Bienvenido al mundo de las ganancias y la emoción!🔥🎰\n\n¿Estás listo para tomar tu suerte en tus manos? ¡Conocemos los secretos de un juego exitoso que te llevará a grandes ganancias!\n \ n¡Obtén 450 giros gratis y bonificaciones originales todos los días!🎁🆓\n\nNo pierdas la oportunidad de obtener ganancias increíbles. ¡Únete a nosotros ahora mismo y comienza tu camino hacia el éxito!💪🤑",
     'got_subs_bonus' => "ya recibió un bono, comuníquese con su gerente para recibir un nuevo bono",

     'no_mirrors' => 'No hay espejos disponibles',

];
