package test

import (
	dataproviders "kbonus/internal/data_providers"
	"kbonus/internal/db/models"
	"kbonus/internal/db/repository"
	bonuscreator "kbonus/internal/factories/bonus_creator"
	"kbonus/internal/service/container"
	"kbonus/internal/utils/currencyid"
	"testing"

	_ "github.com/go-sql-driver/mysql"
)

func TestPSNonDepFSCreator(t *testing.T) {
	// Initialize the database and repositories
	container.SetBonusCurrencyConfigRepository(repository.NewBonusCurrencyConfigDbRepository())
	container.SetBonusCurrencyConfigDataProvider(dataproviders.NewCurrencyConfigDataProviderImpl())
	container.GetBonusCurrencyConfigDataProvider().LoadBonusCurrencyConfigs()
	// Initialize the bonus creator factory
	creatorFactory := bonuscreator.NewBonusCreatorFactoryImpl()
	bonusCreator := creatorFactory.GetBonusCreator(models.TYPE_PS_NON_DEP)
	currency, err := currencyid.GetCurrencyID("RUB")
	if err != nil {
		t.Fatalf("failed to get currency ID: %v", err)
	}
	currencyID := int8(currency)

	// Test with valid parameters
	jsonParams := `{"wager": 10, "free_spins": 20, "max_win": 1000, "active_days": 5}`
	userBonus, err := bonusCreator.CreateBonus(models.TYPE_PS_NON_DEP, jsonParams, currencyID, 12345, false)
	if err != nil {
		t.Fatalf("expected no error, got %v", err)
	}
	if userBonus.UserID != 12345 || userBonus.Multiplier != 10 || userBonus.SpinsAvailable != 20 || userBonus.MaxWin.Float64 != 1000 {
		t.Errorf("unexpected user bonus: %+v", userBonus)
	}

	// Test with empty jsonParams
	_, err = bonusCreator.CreateBonus(models.TYPE_PS_NON_DEP, "", currencyID, 12345, true)
	if err == nil {
		t.Fatal("expected error for empty jsonParams, got nil")
	}

	// Test with invalid jsonParams
	_, err = bonusCreator.CreateBonus(models.TYPE_PS_NON_DEP, "{invalid_json}", currencyID, 12345, true)
	if err == nil {
		t.Fatal("expected error for invalid jsonParams, got nil")
	}

	// Test with specified currency params
	jsonParams = `{"wager": 10, "free_spins": 20, "max_win": 1000, "active_days": 5,
		"params_RUB":{"wager": 20, "free_spins": 30, "max_win": 500, "active_days": 3}
	}`
	userBonus, err = bonusCreator.CreateBonus(models.TYPE_PS_NON_DEP, jsonParams, currencyID, 12345, false)
	if err != nil {
		t.Fatalf("expected no error, got %v", err)
	}
	if userBonus.Multiplier != 20 || userBonus.SpinsAvailable != 30 || userBonus.MaxWin.Float64 != 500 {
		t.Errorf("unexpected user bonus: %+v", userBonus)
	}

}
