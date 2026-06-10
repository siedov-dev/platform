package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusSegmentAddToName(msg []byte, w http.ResponseWriter, r *http.Request) {
	var data struct {
		BonusID     int64  `json:"bonus_id"`
		SegmentName string `json:"segment_name"`
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

	if err := usecases.BonusSegmentAddToName(data.BonusID, data.SegmentName); err != nil {
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
