package main

import (
	"fmt"
	"kbonus/internal/fsapi/pplayapi"
	"kbonus/internal/fsapi/slegapi"
	"kbonus/internal/logger"
	"os"
	"strconv"
	"time"
)

func main() {
	os.Setenv("TZ", "Europe/Kyiv")
	logger.Get()
	defer logger.Close()
	if len(os.Args) < 3 {
		fmt.Println("No arguments provided")
		fmt.Println("Usage: extfs <provider (sleg, pplay)> <method(create or cancel)> <bonus_id> <player_id> <game_id> [<freespins count>, <bet>]")
		fmt.Println("Example: extfs sleg create bonus123 player456 game789 10 1.0")
		fmt.Println("Note: for cancel method only bonus_id is required")
		fmt.Println("Note: for pplay if no bet provider CreateFRB will be used")
		return
	}
	for _, a := range os.Args[1:] {
		fmt.Println(a)
	}

	if os.Args[1] != "sleg" && os.Args[1] != "pplay" {
		fmt.Println("Unknown provider:", os.Args[1])
		fmt.Println("Usage: extfs <provider (sleg, pplay)> <method(create or cancel)> <bonus_id> <player_id> <game_id> [<freespins count>, <bet>]")
		return
	}

	switch os.Args[2] {
	case "create":
		switch os.Args[1] {
		case "sleg":
			slegFsCreate()
		case "pplay":
			pplayFsCreate()
		}
	case "cancel":
		switch os.Args[1] {
		case "sleg":
			slegFsCancel()
		case "pplay":
			pplayFsCancel()
		}
	default:
		fmt.Println("Unknown method:", os.Args[2])
		fmt.Println("Usage: extfs <provider (sleg, pplay)> <method(create or cancel)> <bonus_id> [<freesspins count>, <bet>]")
	}
}

func pplayFsCreate() {
	if len(os.Args) < 7 {
		fmt.Println("Not enough arguments for create")
		fmt.Println("Usage: extfs <provider (sleg, pplay)> create <bonus_id> <player_id> <game_id> [<freespins count>, <bet>]")
		return
	}
	var err error
	bonus_id := os.Args[3]
	player_id := os.Args[4]
	game_id := os.Args[5]
	count, err := strconv.Atoi(os.Args[6])
	if err != nil {
		fmt.Println("Error parsing freespins count:", err)
		return
	}
	expire := int(time.Now().Add(24 * time.Hour).Unix())
	if len(os.Args) > 7 {
		bet, err := strconv.ParseFloat(os.Args[7], 64)
		if err != nil {
			fmt.Println("Error parsing bet:", err)
			return
		}
		fmt.Printf("Creating VFRB with bet %f\n", bet)
		resp, err := pplayapi.CreatePlayerVFRB(player_id, "RUB", game_id, fmt.Sprintf("%d", count), bonus_id, int64(expire), bet)
		fmt.Printf("Response: %s, err: %v\n", resp, err)
	} else {
		fmt.Println("Creating FRB without bet")
		resp, err := pplayapi.CreateFRB(player_id, "RUB", game_id, fmt.Sprintf("%d", count), bonus_id, int64(expire))
		fmt.Printf("Response: %s, err: %v\n", resp, err)
	}
}

func pplayFsCancel() {
	if len(os.Args) < 4 {
		fmt.Println("Not enough arguments for cancel")
		fmt.Println("Usage: extfs <provider (sleg, pplay)> cancel <bonus_id>")
		return
	}
	resp, err := pplayapi.CancelFRB(os.Args[3])
	fmt.Printf("Response frb cancel: %s, err: %v\n", resp, err)
	resp, err = pplayapi.CancelVFRB(os.Args[3])
	fmt.Printf("Response vfrb cancel: %s, err: %v\n", resp, err)
}

func slegFsCreate() {
	if len(os.Args) < 8 {
		fmt.Println("Not enough arguments for create")
		fmt.Println("Usage: extfs <provider (sleg, pplay)> create <bonus_id> <player_id> <game_id> [<freespins count>, <bet>]")
		return
	}
	var err error
	bonus_id := os.Args[3]
	player_id := os.Args[4]
	game_id := os.Args[5]
	count, err := strconv.Atoi(os.Args[6])
	if err != nil {
		fmt.Println("Error parsing freespins count:", err)
		return
	}
	bet, err := strconv.ParseFloat(os.Args[7], 64)
	if err != nil {
		fmt.Println("Error parsing bet:", err)
		return
	}
	expire := int(time.Now().Add(24 * time.Hour).Unix())
	resp, status, err := slegapi.FreeSpinsSet(bonus_id, game_id, player_id, count, "RUB", &expire, bet)
	fmt.Printf("Response: %s, status: %d, err: %v\n", resp, status, err)
}

func slegFsCancel() {
	if len(os.Args) < 4 {
		fmt.Println("Not enough arguments for cancel")
		fmt.Println("Usage: extfs <provider (sleg, pplay)> cancel <bonus_id>")
		return
	}
	resp, status, err := slegapi.FreeSpinsCancel(os.Args[3])
	fmt.Printf("Response: %s, status: %d, err: %v\n", resp, status, err)
}
