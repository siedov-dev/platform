<?php

namespace App\Mail\Promo;

use App\Mail\SgMailable;
use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @deprecated
 */
class VipRetentionEmail extends SgMailable
{
    use Queueable, SerializesModels;

    private MUser $_user;
    private int $_mail_number;
    private string $_subject;
    private string $_promocode;

    private array $_subjects = [
        1 => '100 шансы на победу',
        2 => 'Халявный выигрыш',
        3 => 'Халява продолжается',
        4 => 'И снова подарок',
        5 => 'Беспроигрышный вариант',
        6 => 'Двойное преимущество',
        7 => 'Еще больше шансов на победу',
        8 => 'Больше некуда',
    ];

    /**
     * Create a new message instance.
     *
     * @param MUser $user
     * @param int $mail_number
     * @param string $subject
     * @param string $promocode
     */
    public function __construct(MUser $user, int $mail_number, string $promocode = "")
    {
        $this->_subjects = [
            1 => __('mail.vip_ret_subj1'),
            2 => __('mail.vip_ret_subj2'),
            3 => __('mail.vip_ret_subj3'),
            4 => __('mail.vip_ret_subj4'),
            5 => __('mail.vip_ret_subj5'),
            6 => __('mail.vip_ret_subj6'),
            7 => __('mail.vip_ret_subj7'),
            8 => __('mail.vip_ret_subj8'),
        ];
        //
        $this->_user = $user;
        $this->_mail_number = $mail_number;
        $this->_subject = $this->_subjects[$mail_number]?? '';
        $this->_promocode = $promocode;
        $this->_category = 'Vip retention';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->_subject)
            ->from('info@krakencassino.com')
            ->view('email.promo.vip_retention.ret_'.$this->_mail_number, ['user' => $this->_user, 'promocode' => $this->_promocode]);
    }
}
