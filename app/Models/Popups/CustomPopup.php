<?php

namespace App\Models\Popups;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $category
 * @property string $img
 * @property string $locale_texts
 * @property string $button_type
 * @property string $button_url
 * @property boolean $show_no_more
 */
class CustomPopup extends Model
{
    use HasFactory;

    public function getId(): int
    {
        return $this->id?? 0;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCategory(): string
    {
        return $this->category?? '';
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getImg(): string
    {
        return $this->img?? '';
    }

    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    public function getLocaleTexts(): string
    {
        return $this->locale_texts?? '';
    }

    public function setLocaleTexts(string $locale_texts): void
    {
        $this->locale_texts = $locale_texts;
    }

    public function getButtonType(): string
    {
        return $this->button_type?? '';
    }

    public function setButtonType(string $button_type): void
    {
        $this->button_type = $button_type;
    }

    public function getButtonUrl(): string
    {
        return $this->button_url?? '';
    }

    public function setButtonUrl(string $button_url): void
    {
        $this->button_url = $button_url;
    }

    public function isShowNoMore(): bool
    {
        return $this->show_no_more;
    }

    public function setShowNoMore(bool $show_no_more): void
    {
        $this->show_no_more = $show_no_more;
    }

    public function render(?string $lang = null): string
    {
        if ($lang === null) $lang = app()->getLocale();
        $texts = json_decode($this->getLocaleTexts(), true);

        return view('common.popups.custom', [
            'img' => $this->getImg(),
            'title' => $texts[$lang]['title']?? $texts['en']['title'],
            'msg' => $texts[$lang]['message']?? $texts['en']['message'],
            'btn_text' => $texts[$lang]['button']?? $texts['en']['button'],
            'btn_type' => $this->getButtonType(),
            'url' => $this->getButtonUrl(),
            'unsub' => $this->isShowNoMore(),
            'category' => $this->getCategory(),
        ])->render();
    }

}
