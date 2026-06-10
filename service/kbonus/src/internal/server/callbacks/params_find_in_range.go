package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"
	"net/http"
)

func FindBonusParamInRange(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req FindBonusParamInRangeRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := FindBonusParamInRangeResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	param, err := container.GetBonusParamsDataProvider().FindParamInRange(req.BonusType, req.CurrencyID, req.MinValue)
	if err != nil {
		resp := FindBonusParamInRangeResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := FindBonusParamInRangeResponse{
		Param: param,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
