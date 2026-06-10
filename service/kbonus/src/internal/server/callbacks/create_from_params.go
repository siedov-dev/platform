package callbacks

import (
	"encoding/json"
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"kbonus/internal/stypes"
	"log/slog"
	"net/http"
)

func CreateFromParams(msg []byte, w http.ResponseWriter, r *http.Request) {
	var createParams stypes.BonusCreateParams
	if err := json.Unmarshal(msg, &createParams); err != nil {
		resp := GetBonusResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	bonus, err := usecases.CreateFromParams(createParams)
	if err != nil {
		logger.Get().Error("Failed to create bonus", slog.String("err", err.Error()))
		resp := GetBonusResponse{
			Code:  500,
			Error: fmt.Sprintf("Failed to create bonus: %v", err),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	if bonus == nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: "Failed to create bonus",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
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
