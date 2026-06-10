package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"net/http"
)

func SaveBonusFromModel(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req BonusDataRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GetBonusResponse{
			Code:  400,
			Error: err.Error(),
		}
		logger.Get().Error("SaveBonusFromModel: invalid request format", "err", err)
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	bonus, err := usecases.SaveBonusFromModel(&req.Attributes)
	if err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	if bonus == nil {
		resp := GetBonusResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusNotFound)
		w.Write(respData)
		return
	}

	resp := GetBonusResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
