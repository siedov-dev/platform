package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"net/http"
)

func GetAvailableBonusCount(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetUserBonusesRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GetAvailableBonusCountResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	count := 0
	if req.CurrencyID == 0 {
		// If CurrencyID is 0, count all bonuses
		cnt, err := usecases.GetUserBonusesCount(stypes.UserID(req.UserID), currencyid.CurrencyID(req.CurrencyID))
		if err != nil {
			resp := GetAvailableBonusCountResponse{
				Code:  500,
				Error: err.Error(),
			}
			respData, _ := json.Marshal(resp)
			w.WriteHeader(http.StatusBadRequest)
			w.Write(respData)
			return
		}
		count = cnt
	} else {
		bonuses, err := usecases.GetUserBonuses(req.UserID, currencyid.CurrencyID(req.CurrencyID))
		if err != nil {
			resp := GetAvailableBonusCountResponse{
				Code:  500,
				Error: err.Error(),
			}
			respData, _ := json.Marshal(resp)
			w.WriteHeader(http.StatusBadRequest)
			w.Write(respData)
			return
		}
		count = len(bonuses)
	}
	resp := GetAvailableBonusCountResponse{
		Count: count,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
