package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusSegmentAddToIds(msg []byte, w http.ResponseWriter, r *http.Request) {
	var data struct {
		BonusID    int64   `json:"bonus_id"`
		SegmentIDs []int64 `json:"segment_ids"`
	}
	if err := json.Unmarshal(msg, &data); err != nil {
		resp := BoolResponse{
			Data:  false,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	if err := usecases.BonusSegmentAddToIds(data.BonusID, data.SegmentIDs); err != nil {
		resp := BoolResponse{
			Data:  false,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	if err := usecases.BonusSegmentAddToIds(data.BonusID, data.SegmentIDs); err != nil {
		resp := BoolResponse{
			Data:  false,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := BoolResponse{
		Data:  true,
		Error: "",
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
