package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"kbonus/internal/utils/currencyid"
	"net/http"
)

func GetUserCompletedBonuses(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetUserBonusesRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GetUserBonusesResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	bonuses, err := usecases.GetUserCompletedBonuses(req.UserID, currencyid.CurrencyID(req.CurrencyID))
	if err != nil {
		resp := GetUserBonusesResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	var bonusData []BonusData
	for _, bonus := range bonuses {
		bonusInfo := bonus.GetBonusInfo()
		bonusData = append(bonusData, BonusData{
			Attributes: *bonus.UserBonus,
			Meta:       bonusInfo,
		})
	}

	resp := GetUserBonusesResponse{
		Bonuses: bonusData,
		Code:    200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
