package natsbroker

import (
	"fmt"
	"kbonus/internal/config"
	"kbonus/internal/logger"
	"kbonus/internal/natsbroker/callbacks"
	"log/slog"
	"os"
	"runtime/debug"
	"time"

	"github.com/nats-io/nats.go"
)

// cbListener - обработчик входящих сообщений из NATS.
// Обрабатывает входящие сообщения из NATS и отправляет ответы на запросы.
func cbListener(msg *nats.Msg) {

	// Обработка паники
	defer func() {
		if rec := recover(); rec != nil {
			fmt.Fprintf(os.Stderr, "recover %s", fmt.Sprintf("%v", rec))
			fmt.Fprintf(os.Stderr, "tracelog %s", string(debug.Stack()))
			logger.Get().Error("NATS", slog.String("recover", fmt.Sprintf("%v", rec)))
			logger.Get().Error("NATS", slog.String("tracelog", string(debug.Stack())))
		}
	}()

	// Логируем запрос
	logger.Get().Info("request", slog.String("subject", msg.Subject))
	logger.Get().Info("request", slog.String("data", string(msg.Data)))

	// Если в dev окружении, выводим запросы в консоль и логируем время выполнения
	if config.Get().Env == "dev" {
		fmt.Println("request", msg.Subject, string(msg.Data))
		startTime := time.Now()
		defer func() {
			duration := time.Since(startTime)
			logger.Get().Info("request duration", slog.String("subject", msg.Subject), slog.Duration("duration", duration))
		}()
	}

	// Обрабатываем запрос
	switch msg.Subject {
	case "bonus.ping": // Тест соединения с NATS
		callbacks.Ping(msg)
	case "bonus.getBonus": // Запрос на получение бонуса
		callbacks.GetBonus(msg)
	case "bonus.getUserBonuses": // Запрос на получение бонусов пользователя
		callbacks.GetUserBonuses(msg)
	case "bonus.getUserCompletedBonuses": // Запрос на получение завершенных бонусов пользователя
		callbacks.GetUserCompletedBonuses(msg)
	case "bonus.getFirstActiveBonus": // Запрос на получение первого активного бонуса пользователя
		callbacks.GetFirstActiveBonus(msg)
	case "bonus.getAvailableBonusCount": // Запрос на получение количества доступных бонусов пользователя
		callbacks.GetAvailableBonusCount(msg)
	case "bonus.getFirstNotActiveBonus": // Запрос на получение первого неактивированного бонуса пользователя по типу
		callbacks.GetFirstNotActiveBonus(msg)
	case "bonus.getFirstWaitingDepositBonus": // Запрос на получение первого ожидающего депозит бонуса пользователя
		callbacks.GetFirstWaitingDepositBonus(msg)
	case "bonus.hasCancelableOnPayout": // Запрос на проверку возможности отмены бонуса при выплате
		callbacks.HasCancelableOnPayout(msg)
	case "bonus.actualizeUserBonuses": // Запрос на актуализацию бонусов пользователя
		callbacks.ActualizeUserBonuses(msg)
	case "bonus.createFromParams": // Запрос на создание бонуса из параметров
		callbacks.CreateFromParams(msg)
	case "bonus.cancelBonus": // Запрос на отмену бонуса
		callbacks.CancelBonus(msg)
	case "bonus.activateBonus": // Запрос на активацию бонуса
		callbacks.ActivateBonus(msg)
	case "bonus.dropBonus": // Запрос на сброс бонуса
		callbacks.DropBonusCallback(msg)
	case "bonus.getBonusBalanceInCents": // Запрос на получение баланса бонуса в центах
		callbacks.GetBonusBalanceInCents(msg)
	case "bonus.GetBonusParamsByTypeAndCurrency":
		callbacks.GetBonusParamsByTypeAndCurrency(msg)
	case "bonus.FindBonusParamInRange":
		callbacks.FindBonusParamInRange(msg)
	case "bonus.ParamsGetByKeyValue":
		callbacks.ParamsGetByKeyValue(msg)
	case "bonus.ParamsLowestMinValue":
		callbacks.ParamsLowestMinValue(msg)
	case "bonus.confirmPayment": // Запрос на подтверждение платежа для бонуса
		callbacks.ConfirmPayment(msg)
	case "bonus.onDeposit": // Запрос на подтверждение депозита для бонуса
		callbacks.OnDeposit(msg)
	case "bonus.getBonusAvailable": // Запрос на получение доступного бонуса по ID
		callbacks.GetBonusAvailable(msg)
	case "bonus.createBonusFromAvailable": // Запрос на создание бонуса из доступного бонуса
		callbacks.CreateBonusFromAvailable(msg)
	case "bonus.cancelBonusFromAvailable": // Запрос на отмену доступного бонуса для пользователя
		callbacks.CancelBonusFromAvailable(msg)
	case "bonus.createFromPatternParams": // Запрос на создание бонуса из параметров шаблона
		callbacks.CreateFromPatternParams(msg)
	case "bonus.saveBonus": // Запрос на сохранение бонуса
		callbacks.SaveBonusFromModel(msg)
	case "bonus.getBonusPatternById": // Запрос на получение шаблона бонуса по ID
		callbacks.GetPatternById(msg)
	case "bonus.getRegPattern": // Запрос на получение регистрационного шаблона бонуса по типу
		callbacks.GetRegPattern(msg)
	case "bonus.getRegPatternIds": // Запрос на получение идентификаторов регистрационного шаблона бонуса
		callbacks.GetRegPatternIds(msg)
	case "bonus.getRegFsPatternIds": // Запрос на получение идентификаторов регистрационного шаблона бонуса по типу
		callbacks.GetRegFsPatternIds(msg)
	case "bonus.getRegNonDepPatternIds": // Запрос на получение идентификаторов регистрационного шаблона бонуса без депозита
		callbacks.GetRegNonDepPatternIds(msg)
	case "bonus.getPatternByName": // Запрос на получение шаблона бонуса по имени
		callbacks.GetPatternByName(msg)
	case "bonus.addToSegmentIds": // Запрос на добавление бонуса в сегмент по идентификаторам
		callbacks.BonusSegmentAddToIds(msg)
	case "bonus.addToSegmentName": // Запрос на добавление бонуса в сегмент по имени
		callbacks.BonusSegmentAddToName(msg)
	case "bonus.getBonusByPatternID": // Запрос на получение бонуса пользователя по ID шаблона
		callbacks.GetBonusByPatternID(msg)
	case "bonus.cancelBonusesOnPayout": // Запрос на отмену бонусов пользователя при выплате
		callbacks.CancelBonusesOnPayout(msg)
	case "bonus.multidaysGetParamsForDay": // Запрос на получение параметров многодневного бонуса по дню
		callbacks.GetMultidaysParamsForDay(msg)
	case "bonus.transferBonusMoney": // Запрос на перевод бонусных средств пользователю
		callbacks.TransferBonusMoney(msg)
	// Игровые операции с бонусом
	case "bonus.bonusBalance": // Запрос на получение баланса бонуса с учетом процента вейджера
		callbacks.BonusBalance(msg)
	case "bonus.bonusAddMoney": // Запрос на добавление средств к балансу бонуса с учетом процента вейджера
		callbacks.BonusAddMoney(msg)
	case "bonus.bonusRemMoney": // Запрос на списание средств с баланса бонуса с учетом процента вейджера
		callbacks.BonusRemMoney(msg)
	case "bonus.bonusExtFsBet": // Запрос на списание средств с баланса бонуса для внешнего фриспина
		callbacks.BonusExtFsBet(msg)
	case "bonus.bonusExtFsWin": // Запрос на добавление средств к балансу бонуса для внешнего фриспина
		callbacks.BonusExtFsWin(msg)
	case "bonus.bonusExtFsCompleted": // Запрос на списание средств с баланса бонуса для игры
		callbacks.BonusExtFsCompleted(msg)
	case "bonus.bonusSpinBet": // Запрос на списание средств с баланса бонуса для спина
		callbacks.BonusSpinBet(msg)
	case "bonus.bonusSpinWin": // Запрос на добавление средств к балансу бонуса для спина
		callbacks.BonusSpinWin(msg)
	case "bonus.bonusActualize": // Запрос на актуализацию бонуса
		callbacks.BonusActualize(msg)
	// Неизвестный запрос
	default:
		msg.Respond([]byte("unknown request"))
	}
}
