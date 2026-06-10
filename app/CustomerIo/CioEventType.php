<?php

namespace App\CustomerIo;

enum CioEventType
{
    case Login;
    case SlotMachineSpin;
    case Deposit;
    case NewRank;
    case CloseGame;
    case CannonShot;
    case AchievementDone;
    case WbActivated;
    case WbDepBonusDone;
    case BeforePayout;
    case DepositFail;
    case TournamentAward;
    case Register;
    case EmailVerify;
    case PasswordReset;
    case Cashback;
    case FdFail;
    case GameWin;
    case GameLose;
}
