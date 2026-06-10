package entities

import (
	"database/sql"
	"encoding/json"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"sync"
	"time"
)

type AvailableBonuses struct {
	avBonus        *models.BonusAvailable
	allowedUsers   map[stypes.UserID]struct{}
	usersActivated map[stypes.UserID]struct{}
	usersCanceled  map[stypes.UserID]struct{}
	mx             sync.RWMutex
}

func (ab *AvailableBonuses) LoadFromModel(model *models.BonusAvailable) error {
	ab.mx.Lock()
	defer ab.mx.Unlock()
	ab.avBonus = model
	ab.allowedUsers = make(map[stypes.UserID]struct{})
	ab.usersActivated = make(map[stypes.UserID]struct{})
	ab.usersCanceled = make(map[stypes.UserID]struct{})

	if model.AllowedUsers.Valid {
		users_data := []stypes.UserID{}
		err := json.Unmarshal([]byte(model.AllowedUsers.String), &users_data)
		if err != nil {
			return err
		}
		for _, userID := range users_data {
			ab.allowedUsers[userID] = struct{}{}
		}
	}
	if model.UsersActivated.Valid {
		users_data := []stypes.UserID{}
		err := json.Unmarshal([]byte(model.UsersActivated.String), &users_data)
		if err != nil {
			return err
		}
		for _, userID := range users_data {
			ab.usersActivated[userID] = struct{}{}
		}
	}
	if model.UsersCanceled.Valid {
		users_data := []stypes.UserID{}
		err := json.Unmarshal([]byte(model.UsersCanceled.String), &users_data)
		if err != nil {
			return err
		}
		for _, userID := range users_data {
			ab.usersCanceled[userID] = struct{}{}
		}
	}
	return nil
}

func (ab *AvailableBonuses) StoreDataAndGetBonusAvailable() models.BonusAvailable {
	ab.mx.Lock()
	defer ab.mx.Unlock()
	allowedUsers := []int64{}
	for userID := range ab.allowedUsers {
		allowedUsers = append(allowedUsers, int64(userID))
	}
	encodedAllowedUsers, _ := json.Marshal(allowedUsers)
	ab.avBonus.AllowedUsers = sql.NullString{String: string(encodedAllowedUsers), Valid: true}
	usersActivated := []int64{}
	for userID := range ab.usersActivated {
		usersActivated = append(usersActivated, int64(userID))
	}
	encodedUsersActivated, _ := json.Marshal(usersActivated)
	ab.avBonus.UsersActivated = sql.NullString{String: string(encodedUsersActivated), Valid: true}
	usersCanceled := []int64{}
	for userID := range ab.usersCanceled {
		usersCanceled = append(usersCanceled, int64(userID))
	}
	encodedUsersCanceled, _ := json.Marshal(usersCanceled)
	ab.avBonus.UsersCanceled = sql.NullString{String: string(encodedUsersCanceled), Valid: true}
	return *ab.avBonus
}

func (ab *AvailableBonuses) IsActive() bool {
	ab.mx.RLock()
	defer ab.mx.RUnlock()
	return ab.avBonus != nil && ab.avBonus.AvailableTo.After(time.Now())
}

func (ab *AvailableBonuses) GetID() int64 {
	ab.mx.RLock()
	defer ab.mx.RUnlock()
	if ab.avBonus != nil {
		return ab.avBonus.ID
	}
	return 0
}

func (ab *AvailableBonuses) GetCurrencyID() int8 {
	ab.mx.RLock()
	defer ab.mx.RUnlock()
	if ab.avBonus != nil {
		return ab.avBonus.CurrencyID
	}
	return 0
}

func (ab *AvailableBonuses) IsUserAllowed(userID stypes.UserID) bool {
	ab.mx.RLock()
	defer ab.mx.RUnlock()
	if len(ab.usersActivated) > 0 {
		_, ok := ab.usersActivated[userID]
		if ok {
			return false
		}
	}
	if len(ab.usersCanceled) > 0 {
		_, ok := ab.usersCanceled[userID]
		if ok {
			return false
		}
	}
	_, ok := ab.allowedUsers[userID]
	if !ok && len(ab.allowedUsers) > 0 {
		// Если пользователь не в списке разрешенных, но список не пустой, значит, он не разрешен
		return false
	}
	return true
}

func (ab *AvailableBonuses) AddActivatedUser(userID stypes.UserID) {

	ab.mx.Lock()
	ab.usersActivated[userID] = struct{}{}
	ab.mx.Unlock()
	ba := ab.StoreDataAndGetBonusAvailable()
	container.GetBonusAvailableDeferredStorage().Add(&ba)
}

func (ab *AvailableBonuses) AddCanceledUser(userID stypes.UserID) {
	ab.mx.Lock()
	ab.usersCanceled[userID] = struct{}{}
	ab.mx.Unlock()
	ba := ab.StoreDataAndGetBonusAvailable()
	container.GetBonusAvailableDeferredStorage().Add(&ba)
}

func (ab *AvailableBonuses) GetParams() stypes.BonusAvailableParams {
	ab.mx.RLock()
	defer ab.mx.RUnlock()
	return stypes.BonusAvailableParams{
		BonusAvailableID:   ab.avBonus.ID,
		BonusType:          ab.avBonus.BonusType,
		CurrencyID:         ab.avBonus.CurrencyID,
		Params:             ab.avBonus.Params.String,
		Games:              ab.avBonus.Games.String,
		Title:              ab.avBonus.Title.String,
		ImgURL:             ab.avBonus.ImgURL.String,
		Descr:              ab.avBonus.Descr.String,
		WagerGameSegmentID: ab.avBonus.WagerGamesSegmentID.Int64,
	}
}
