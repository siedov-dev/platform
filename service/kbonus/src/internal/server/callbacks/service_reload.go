package callbacks

import (
	"kbonus/internal/service/usecases"
	"net/http"
)

func ReloadService(msg []byte, w http.ResponseWriter, r *http.Request) {
	if err := usecases.ReloadService(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	w.WriteHeader(http.StatusOK)
	w.Write([]byte(`{"code":200,"message":"Service reloaded successfully"}`))
}
