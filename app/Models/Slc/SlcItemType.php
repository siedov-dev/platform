<?php

namespace App\Models\Slc;

enum SlcItemType: int
{
    case Bonus = 0;
    case WagerDiminution = 1;
    case DepositPercent = 2;
    case WheelSpin = 3;
    case KeyLittle = 10;
    case KeyMiddle = 11;
    case KeyBig = 12;
    case KeyLost = 13;
    case KeyBlack = 14;
    case KeyBermuda = 15;
    case Slc = 17;
    case BlackKeyPiece = 16;
    case SmallKeyPiece = 18;
    case MiddleKeyPiece = 19;
    case BigKeyPiece = 20;
    case LostKeyPiece = 21;
    case BermudaKeyPiece = 22;
    case Bezdep = 23;
    case Cannonball = 24;
    case GoldCannonball = 25;
    case CashbackPlus = 26;
    case TournamentPrize = 27;
    /**
     * item type for crm
     * @return string
     */
    public function getCrmName()
    {
        return match ($this) {
            SlcItemType::Bonus => "Бонус",
            SlcItemType::WagerDiminution => "Уменьшение вейджера",
            SlcItemType::DepositPercent => "Процент депозита",
            SlcItemType::WheelSpin => "Колесо фортуны",
            SlcItemType::KeyLittle => "Маленький ключ",
            SlcItemType::KeyMiddle => "Средний ключ",
            SlcItemType::KeyBig => "Большой ключ",
            SlcItemType::KeyLost => "Потерянный ключ",
            SlcItemType::KeyBlack => "Чёрный ключ",
            SlcItemType::KeyBermuda => "Бермудский ключ",
            SlcItemType::BlackKeyPiece => "Осколок черного ключа",
            SlcItemType::SmallKeyPiece => "Осколок меленького ключа",
            SlcItemType::MiddleKeyPiece => "Осколок среднего ключа",
            SlcItemType::BigKeyPiece => "Осколок большого ключа",
            SlcItemType::LostKeyPiece => "Осколок потерянного ключа",
            SlcItemType::BermudaKeyPiece => "Осколок бермудского ключа",
            SlcItemType::Slc => "SLC",
            SlcItemType::Bezdep => "Бездеп",
            SlcItemType::Cannonball => "Черное ядро",
            SlcItemType::GoldCannonball => "Золотое ядро",
            SlcItemType::CashbackPlus => "Cashback+",
            SlcItemType::TournamentPrize => "Турнирный приз",
            default => 'Unknown Item!',
        };
    }
    public static function chestKeys(): array
    {
        return [
            SlcItemType::KeyLittle,
            SlcItemType::KeyMiddle,
            SlcItemType::KeyBig,
            SlcItemType::KeyLost,
            SlcItemType::KeyBlack,
            SlcItemType::KeyBermuda,
        ];
    }
}
