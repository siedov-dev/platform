package tvaluesaver

import (
	"context"
	"ktournaments/internal/db/models"
	"ktournaments/internal/logger"
	"ktournaments/internal/service/container"
	"ktournaments/pkg/gstructs"
	"log/slog"
)

// deferredValueSaver - сохранение значений турнира в мапу с таймаутом
// Используется для сохранения значений турнира в базу данных с задержкой
// для уменьшения количества запросов к базе данных
// При обновлении значения в мапе, оно сохраняется в базу данных
// с задержкой в 2 секунды. Когда таймаут истекает, значение сохраняется в базу данных.
// Каждое обновление значения увеличивает таймаут на 2 секунды.
// Если значение обновляется более 10 раз, оно удаляется из мапы
// и сохраняется в базу данных.
type deferredValueSaver struct {
	DeferredValuesPersistance gstructs.TimeoutMap[uint64, *models.TournamentValueModel]
}

// Save - сохранение значения турнира в мапу с таймаутом
func (s *deferredValueSaver) Save(value *models.TournamentValueModel) (uint64, error) {
	if value.ID == 0 {
		id, err := container.GetValueRepository().Insert(*value)
		if err != nil {
			return 0, err
		}
		value.ID = id
		return id, nil
	}
	// Сохраняем значение в мапу с ключом - ID турнира
	s.DeferredValuesPersistance.Set(value.TournamentID, value)
	return value.ID, nil
}

func (s *deferredValueSaver) Flush() {
	// Очищаем мапу
	s.DeferredValuesPersistance.Clear()
}

func NewDeferredSaver(ctx context.Context) container.TValueSaver {
	s := &deferredValueSaver{}
	// Инициализируем мапу с таймаутом в 2 секунды
	s.DeferredValuesPersistance = gstructs.NewTimeoutMap[uint64, *models.TournamentValueModel](2, ctx)
	// Устанавливаем лимит на 10 задержек
	// Это означает, что если значение задерживается более 10 раз, то его сохранение в базу данных не будет отложено
	s.DeferredValuesPersistance.SetPostponeLimit(10)
	// Устанавливаем функцию очистки мапы
	// Эта функция вызывается при очистке значения
	// Она обновляет значение турнира в базе данных
	s.DeferredValuesPersistance.SetClearFunc(func(key uint64, value *models.TournamentValueModel) {
		err := container.GetValueRepository().Update(*value)
		if err != nil {
			logger.Get().Error("Failed to update tournament value", slog.String("error", err.Error()))
			return
		}
	})
	return s
}
