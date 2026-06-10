<?php

namespace App\Notifications;

class NotificationButton
{
    private $_text;
    private $_url;
    private $_iconUrl;

    public function __construct(string $text, string $url, string $iconUrl = null)
    {
        $this->_text = $text;
        $this->_url = $url;
        $this->_iconUrl = $iconUrl;
    }


    public function getText(): string
    {
        return $this->_text;
    }


    public function getUrl(): string
    {
        return $this->_url;
    }

    public function getIconUrl():? string
    {
        return $this->_iconUrl;
    }
}
