package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"
	"net/http"
)

func ParamsLowestMinValue(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req ParamsLowestMinValueRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := ParamsLowestMinValueResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	value, err := container.GetBonusParamsDataProvider().GetLowestMinValue(req.BonusType, req.CurrencyID)
	if err != nil {
		resp := ParamsLowestMinValueResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := ParamsLowestMinValueResponse{
		Value: value,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
