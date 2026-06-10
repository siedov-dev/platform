package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/container"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

// GetReferralUsers возвращает список пользователей, которые зарегистрировались по реферальной ссылке
func GetReferralUsers(msg *nats.Msg) {
	var req getRefUsersRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling request", "error", err.Error())
		resp := getRefUsersResponse{
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

	total, err := usecases.GetReferralUsersCount(req.RefID)
	if err != nil {
		resp := getRefUsersResponse{
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
	offset := req.Page * req.PerPage
	users, rows, err := usecases.GetReferralUsers(req.RefID, offset, req.PerPage)
	if err != nil {
		resp := getRefUsersResponse{
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

	resp := getRefUsersResponse{
		Code:       200,
		Error:      "",
		Pages:      pages,
		TotalUsers: total,
		Page:       int(req.Page),
		Rows:       rows,
		Users:      users,
	}

	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("Error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)
}

type getRefUsersRequest struct {
	RefID   uint64 `json:"ref_id"`
	Page    uint64 `json:"page"`
	PerPage uint64 `json:"per_page"`
}

type getRefUsersResponse struct {
	Code       int                        `json:"cd"`
	Error      string                     `json:"err"`
	Pages      int                        `json:"pages"`
	Page       int                        `json:"page"`
	TotalUsers uint64                     `json:"total_users"`
	Rows       uint64                     `json:"total_rows"`
	Users      []*container.ReferralMUser `json:"users"`
}
