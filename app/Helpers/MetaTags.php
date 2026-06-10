<?php

namespace App\Helpers;

class MetaTags
{
    public $title;
    public $description;
    public $keywords;
    public $image;

    public function render() : string
    {
        return $this->renderTitle()
            .$this->renderDescription()
            .$this->renderKeywords()
            .$this->renderImage();
    }


    public function renderTitle() : string
    {
        return '<title>'.e($this->title)
            .'</title><meta name="og:title" content="'.e($this->title).'">';
    }

    public function renderDescription() : string
    {
        return '<meta name="description" content="'.e($this->description).'"><meta name="og:description" content="'.e($this->description).'">';
    }

    public function renderKeywords() : string
    {
        return '<meta name="keywords" content="'.e($this->keywords).'">';
    }

    public function renderImage() : string
    {
        return '<meta name="og:image" content="'.e($this->image).'">';
    }
}
