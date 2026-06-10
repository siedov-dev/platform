package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"
	"net/http"
)

func GetBonusParamsByTypeAndCurrency(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req BonusTypeAndCurrencyRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BonusParamsResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	params, err := container.GetBonusParamsDataProvider().GetParamsByBonusTypeAndCurrency(req.BonusType, req.CurrencyID)
	if err != nil {
		resp := BonusParamsResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := BonusParamsResponse{
		Params: params,
		Code:   200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
