package stypes

type BonusInfo struct {
	IsDeposit            bool    `json:"is_deposit"`            // Определяет, является ли бонус депозитным
	IsNonDeposit         bool    `json:"is_non_deposit"`        // Определяет, является ли бонус бездепозитным
	IsFreeSpins          bool    `json:"is_free_spins"`         // Определяет, является ли бонус фриспинами
	IsPaymentRequired    bool    `json:"is_payment_required"`   // Определяет, требуется ли оплата для активации бонуса
	RequiredSum          float64 `json:"required_sum"`          // Сумма, необходимая для активации бонуса
	IsAgreementRequired  bool    `json:"is_agreement_required"` // Определяет, требуется ли активация бонуса
	IsProgressAvailable  bool    `json:"is_progress_available"` // Определяет, доступен ли прогресс выполнения бонуса
	IsCancelable         bool    `json:"is_cancelable"`         // Определяет, может ли бонус быть отменен
	IsActive             bool    `json:"is_active"`             // Определяет, активен ли бонус
	IsDone               bool    `json:"is_done"`               // Определяет, завершен ли бонус
	IsCanceled           bool    `json:"is_canceled"`           // Определяет, отменен ли бонус
	IsPending            bool    `json:"is_pending"`            // Определяет, находится ли бонус в ожидании
	IsCompleted          bool    `json:"is_completed"`          // Определяет, завершен ли бонус
	IsExpired            bool    `json:"is_expired"`            // Определяет, истек ли бонус
	IsListeningDeposit   bool    `json:"is_listening_deposit"`  // Определяет, слушает ли бонус депозит
	TotalMoneyWon        float64 `json:"total_money_won"`       // Общая сумма выигрыша в бонусе
	IsForceToPlay        bool    `json:"is_force_to_play"`      // Определяет, принудительно ли игрок должен играть в бонус когда он активен
	IsNotPlayable        bool    `json:"is_not_playable"`       // Определяет, является ли бонус играбельным в играх
	IsLocked             bool    `json:"is_locked"`             // Определяет, заблокирован ли бонус (доступен после завершения других бонусов)
	IsCancelebleOnPayout bool    `json:"is_canceled_on_payout"` // Определяет, отменяется ли бонус при выплате
	IsActivatedByMenu    bool    `json:"is_activated_by_menu"`  // Определяет, активируется ли бонус через бонусное меню
	IsProcessPayment     bool    `jsob:"is_process_payment"`    // Определяет, обрабатывается ли платеж для бонуса
	IsPaused             bool    `json:"is_paused"`             // Определяет, приостановлен ли бонус
	CanPause             bool    `json:"can_pause"`             // Определяет, может ли бонус быть приостановлен
}

type BonusInfoProvider interface {
	// GetBonusInfo возвращает информацию о бонусе в виде структуры BonusInfo.
	GetBonusInfo() BonusInfo
}
