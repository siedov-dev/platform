package usecases

import (
	"errors"
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"
	"math/rand"
	"sync"
	"time"
)

var mux sync.Mutex

const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"

func generateRandomString(length int) string {
	seededRand := rand.New(rand.NewSource(time.Now().UnixNano()))
	b := make([]byte, length)
	for i := range b {
		b[i] = charset[seededRand.Intn(len(charset))]
	}
	return string(b)
}

func GetRefCode(user_id uint64) (*models.UserReferral, error) {
	referral, err := container.GetUserReferralRepository().GetUserReferralByUserID(user_id)
	if err != nil {
		return nil, err
	}
	if referral != nil {
		return referral, nil
	}
	mux.Lock()
	defer mux.Unlock()

	refCode := generateRandomString(16)
	tries := 3

	for alreadyExists, _ := container.GetUserReferralRepository().GetUserReferralByRefCode(refCode); alreadyExists != nil; tries-- {
		if tries <= 0 {
			return nil, errors.New("can't create ref code")
		}
		refCode = generateRandomString(16)
	}

	referral = &models.UserReferral{
		UserID:  &user_id,
		RefCode: refCode,
	}

	err = container.GetUserReferralRepository().CreateUserReferral(referral)
	if err != nil {
		return nil, err
	}

	return container.GetUserReferralRepository().GetUserReferralByUserID(user_id)
}
