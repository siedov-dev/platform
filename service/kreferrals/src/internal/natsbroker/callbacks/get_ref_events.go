package callbacks

import (
	"encoding/json"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

// GetReferralEvents возвращает список событий рефералов
// Параметры: ref_id - ID реферала, offset - смещение для пагинации, limit - количество записей на странице
func GetReferralEvents(msg *nats.Msg) {
	var req getRefEventsRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling request", "error", err.Error())
		resp := getRefEventsResponse{
			Code:  500,
			Error: "Error unmarshalling request",
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
			return
		}
		msg.Respond(jsonResp)
		return
	}

	offset := req.Page * req.PerPage
	events, rows, err := usecases.GetReferralEvents(req.RefID, offset, req.PerPage)
	if err != nil {
		resp := getRefEventsResponse{
			Code:  500,
			Error: err.Error(),
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
			return
		}
		msg.Respond(jsonResp)
		return
	}

	pages := int(rows / req.PerPage)
	if uint64(pages)*uint64(req.PerPage) < rows {
		pages++
	}

	resp := getRefEventsResponse{
		Code:   200,
		Error:  "",
		Pages:  pages,
		Page:   int(req.Page),
		Rows:   rows,
		Events: events,
	}

	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("Error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)
}

type getRefEventsRequest struct {
	RefID   uint64 `json:"ref_id"`
	Page    uint64 `json:"page"`
	PerPage uint64 `json:"per_page"`
}

type getRefEventsResponse struct {
	Code   int                     `json:"cd"`
	Error  string                  `json:"err"`
	Pages  int                     `json:"pages"`
	Page   int                     `json:"page"`
	Rows   uint64                  `json:"total_rows"`
	Events []*models.ReferralEvent `json:"events"`
}
