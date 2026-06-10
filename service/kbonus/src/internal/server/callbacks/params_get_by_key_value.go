package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"
	"net/http"
)

func ParamsGetByKeyValue(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetParamByKeyRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GetParamByKeyResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	param, err := container.GetBonusParamsDataProvider().GetParamByKey(req.BonusType, req.CurrencyID, req.Key)
	if err != nil {
		resp := GetParamByKeyResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := GetParamByKeyResponse{
		Param: param,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
