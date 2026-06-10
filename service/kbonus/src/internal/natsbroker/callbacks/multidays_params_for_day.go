package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetMultidaysParamsForDay(msg *nats.Msg) {
	request := struct {
		Day int `json:"day"`
	}{}
	if err := json.Unmarshal(msg.Data, &request); err != nil {
		resp := MultidaysMenuParamsResponse{
			Param: nil,
			Code:  400,
			Error: "Invalid request format",
		}
		responseData, _ := json.Marshal(resp)
		msg.Respond(responseData)
		return
	}

	params := usecases.MultiDaysParamsForDay(request.Day)
	if params == nil {
		resp := MultidaysMenuParamsResponse{
			Param: nil,
			Code:  404,
			Error: "No parameters found for the specified day",
		}
		responseData, _ := json.Marshal(resp)
		msg.Respond(responseData)
		return
	}
	resp := MultidaysMenuParamsResponse{
		Param: params,
		Code:  200,
		Error: "",
	}
	responseData, _ := json.Marshal(resp)
	msg.Respond(responseData)
}
