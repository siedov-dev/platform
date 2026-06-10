package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"

	"github.com/go-chi/render"
)

func GetMultidaysParamsForDay(msg []byte, w http.ResponseWriter, r *http.Request) {
	request := struct {
		Day int `json:"day"`
	}{}
	if err := json.Unmarshal(msg, &request); err != nil {
		resp := MultidaysMenuParamsResponse{
			Param: nil,
			Code:  400,
			Error: "Invalid request format",
		}
		render.JSON(w, r, resp)
		return
	}

	params := usecases.MultiDaysParamsForDay(request.Day)
	if params == nil {
		resp := MultidaysMenuParamsResponse{
			Param: nil,
			Code:  404,
			Error: "No parameters found for the specified day",
		}
		render.JSON(w, r, resp)
		return
	}
	resp := MultidaysMenuParamsResponse{
		Param: params,
		Code:  200,
		Error: "",
	}
	render.JSON(w, r, resp)
}
