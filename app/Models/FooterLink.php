<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Страницы футера.
 *
 * @property int $id
 * @property int $link_group
 * @property string $locale
 * @property string $name
 * @property string $page
 */
class FooterLink extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Возвращает массив ссылок на страницы.
     * @param string $locale
     * @return array<int, array<int, string>> array with group_id => {{link id => name}...}
     */
    public static function getLinks(string $locale): array
    {
        if (Cache::has('footer_links_' . $locale)) {
            return Cache::get('footer_links_' . $locale);
        }
        $links = FooterLink::query()
            ->select('id', 'name', 'link_group')
            ->where('locale', $locale)
            ->get();
        $grouped = $links->mapToGroups(function (FooterLink $item, int $key) {
            return [$item['link_group'] => [$item['id'] => $item['name']]];
        });
        $resp = $grouped->toArray();
        Cache::put('footer_links_' . $locale, $resp, 60);
        return $resp;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get link group
     * @return int
     */
    public function getLinkGroup(): int
    {
        return $this->link_group;
    }

    public function setLinkGroup(int $link_group): void
    {
        $this->link_group = $link_group;
    }

    /**
     * Get link locale
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get link name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /** Get the HTML page
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    public function setPage(string $page): void
    {
        $this->page = $page;
    }

}
