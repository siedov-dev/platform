package handlers

import (
	"io"
	"kbonus/internal/logger"
	"kbonus/internal/server/callbacks"
	"log/slog"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

func APIHandler(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	method := chi.URLParam(r, "method")
	body, err := io.ReadAll(r.Body)
	logger.Get().Debug("APIHandler", slog.String("method", method), slog.String("body", string(body)))
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		resp := callbacks.GenericResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		render.JSON(w, r, resp)
		return
	}
	switch method {
	case "multidaysGetParamsForDay":
		callbacks.GetMultidaysParamsForDay(body, w, r)
	case "getRegNonDepPatternIds":
		callbacks.GetRegNonDepPatternIds(body, w, r)
	case "getRegFsPatternIds":
		callbacks.GetRegFsPatternIds(body, w, r)
	case "getRegPatternIds":
		callbacks.GetRegPatternIds(body, w, r)
	case "getRegPattern":
		callbacks.GetRegPattern(body, w, r)
	case "createFromPatternParams":
		callbacks.CreateFromPatternParams(body, w, r)
	case "createFromParams":
		callbacks.CreateFromParams(body, w, r)
	case "getBonus":
		callbacks.GetBonus(body, w, r)
	case "getUserBonuses":
		callbacks.GetUserBonuses(body, w, r)
	case "getUserCompletedBonuses":
		callbacks.GetUserCompletedBonuses(body, w, r)
	case "getPatternById":
		callbacks.GetPatternById(body, w, r)
	case "getPatternByName":
		callbacks.GetPatternByName(body, w, r)
	case "getAvailableBonusCount":
		callbacks.GetAvailableBonusCount(body, w, r)
	case "getFirstNotActiveBonus":
		callbacks.GetFirstNotActiveBonus(body, w, r)
	case "getFirstActiveBonus":
		callbacks.GetFirstActiveBonus(body, w, r)
	case "getBonusBalanceInCents":
		callbacks.GetBonusBalanceInCents(body, w, r)
	case "getBonusAvailable": // Получение информации о доступном бонусе по ID бонуса и ID валюты
		callbacks.GetBonusAvailable(body, w, r)
	case "createBonusFromAvailable": // Создание бонуса из доступного
		callbacks.CreateBonusFromAvailable(body, w, r)
	case "cancelBonusFromAvailable": // Отмена бонуса из доступного
		callbacks.CancelBonusFromAvailable(body, w, r)
	case "getFirstWaitingDepositBonus": // Получение первого ожидающего депозит бонуса по userID и currencyID
		callbacks.GetFirstWaitingDepositBonus(body, w, r)
	case "hasCancelableOnPayout": // Проверка наличия у пользователя бонусов с возможностью отмены при выплате
		callbacks.HasCancelableOnPayout(body, w, r)
	case "actualizeUserBonuses": // Актуализация бонусов пользователя
		callbacks.ActualizeUserBonuses(body, w, r)
	case "activateBonus": // Активация бонуса
		callbacks.ActivateBonus(body, w, r)
	case "saveBonus": // Сохранение бонуса
		callbacks.SaveBonusFromModel(body, w, r)
	case "cancelBonus": // Отмена бонуса
		callbacks.CancelBonus(body, w, r)
	case "dropBonus": // Сброс бонуса
		callbacks.DropBonusCallback(body, w, r)
	case "getBonusByPatternID": // Получение бонуса по userID и patternID
		callbacks.GetBonusByPatternID(body, w, r)
	case "confirmPayment": // Подтверждение платежа
		callbacks.ConfirmPayment(body, w, r)
	case "onDeposit": // Обработка депозита
		callbacks.OnDeposit(body, w, r)
	case "transferBonusMoney": // Перевод средств с бонуса на основной баланс (для бонуса 2)
		callbacks.TransferBonusMoney(body, w, r)
	case "getBonusParamsByTypeAndCurrency": // Получение параметров бонуса по типу и валюте
		callbacks.GetBonusParamsByTypeAndCurrency(body, w, r)
	case "paramsFindInRange": // Поиск параметров бонусов в диапазоне
		callbacks.FindBonusParamInRange(body, w, r)
	case "paramsGetByKeyValue": // Получение параметров бонусов по ключу и значению
		callbacks.ParamsGetByKeyValue(body, w, r)
	case "paramsLowestMinValue": // Получение параметров бонусов с наименьшим минимальным значением
		callbacks.ParamsLowestMinValue(body, w, r)
	case "addToSegmentIds": // Добавление бонуса к сегментам по их ID
		callbacks.BonusSegmentAddToIds(body, w, r)
	case "addToSegmentName": // Добавление бонуса к сегментам по их именам
		callbacks.BonusSegmentAddToName(body, w, r)
	case "cancelBonusesOnPayout": // Отмена бонусов пользователя при выплате
		callbacks.CancelBonusesOnPayout(body, w, r)
	case "bonusPauseOrResume": // Пауза или возобновление бонуса
		callbacks.BonusPauseOrResume(body, w, r)
	// Игровые апи
	case "bonusBalance":
		callbacks.BonusBalance(body, w, r)
	case "bonusAddMoney": // Запрос на добавление средств к балансу бонуса с учетом процента вейджера
		callbacks.BonusAddMoney(body, w, r)
	case "bonusRemMoney": // Запрос на списание средств с баланса бонуса с учетом процента вейджера
		callbacks.BonusRemMoney(body, w, r)
	case "bonusExtFsBet": // Запрос на списание средств с баланса бонуса для внешнего фриспина
		callbacks.BonusExtFsBet(body, w, r)
	case "bonusExtFsWin": // Запрос на добавление средств к балансу бонуса для внешнего фриспина
		callbacks.BonusExtFsWin(body, w, r)
	case "bonusExtFsCompleted": // Запрос на списание средств с баланса бонуса для игры
		callbacks.BonusExtFsCompleted(body, w, r)
	case "bonusSpinBet": // Запрос на списание средств с баланса бонуса для спина
		callbacks.BonusSpinBet(body, w, r)
	case "bonusSpinWin": // Запрос на добавление средств к балансу бонуса для спина
		callbacks.BonusSpinWin(body, w, r)
	case "bonusActualize": // Запрос на актуализацию бонуса
		callbacks.BonusActualize(body, w, r)
	case "bonusGetStatusPad": // Запрос на получение статуса бонуса для отображения плашки
		callbacks.BonusStatusPad(body, w, r)
	// Админские апи
	case "reloadService": // Перезагрузка сервиса (обновление кэша)
		callbacks.ReloadService(body, w, r)
	case "reloadBonusAvailable": // Перезагрузка доступного бонуса по ID
		callbacks.ReloadBonusAvailable(body, w, r)
	case "reloadBonusIfCached": // Перезагрузка бонуса в кэше по ID бонуса и ID пользователя
		callbacks.ReloadBonusIfCached(body, w, r)
	// Неизвестный метод
	default:
		http.Error(w, "Unknown method", http.StatusNotFound)
	}

}
