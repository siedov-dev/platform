<?php

namespace App\Notifications;

class Notification
{
    private $_title = [];
    private $_content = [];
    private $_imageUrl = null;
    private $_buttons = [];

    public function getTitle(string $lang): string
    {
        return array_key_exists($lang, $this->_title) ? $this->_title[$lang] : '';
    }


    public function setTitle(string $lang, string $content)
    {
        $this->_title[$lang] = $content;
    }


    public function getContent(string $lang): string
    {
        return array_key_exists($lang, $this->_content) ? $this->_content[$lang] : '';
    }


    public function setContent(string $lang, string $content)
    {
        $this->_content[$lang] = $content;
    }


    public function getTitleTranslations(): array
    {
        return $this->_title;
    }


    public function getContentTranslations(): array
    {
        return $this->_content;
    }


    public function isTitleEmpty(): bool
    {
        return empty($this->_title);
    }

    public function setImageUrl(string $url)
    {
        $this->_imageUrl = $url;
    }


    public function getImageUrl():? string
    {
        return $this->_imageUrl;
    }

    public function addButton(NotificationButton $button)
    {
        $this->_buttons []= $button;
    }

    /**
     * @return NotificationButton[]
     */
    public function getButtons(): array
    {
        return $this->_buttons;
    }
}
