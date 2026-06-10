package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/entities"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusPauseOrResume(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req PauseOrResumeBonusRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GetBonusResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	var bonus *entities.Bonus
	var err error
	if req.Pause {
		bonus, err = usecases.BonusPause(req.BonusID)
	} else {
		bonus, err = usecases.BonusResume(req.BonusID)
	}
	if err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: "Failed to process bonus",
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
