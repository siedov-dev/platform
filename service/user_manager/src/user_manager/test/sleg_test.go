package test

import (
	"testing"
	"user-manager/internal/slotegrator/slegapi"
)

func TestSleg(t *testing.T) {
	t.Log("Sleg test")
	rs, err := slegapi.SelfValidate()
	if err != nil {
		t.Error(err)
	}
	t.Log(rs)

	rs, err = slegapi.GetFreespinsLimits()
	if err != nil {
		t.Error(err)
	}
	t.Log(rs)

	rs, err = slegapi.FreeSpinsGet("12")
	if err != nil {
		t.Error(err)
	}
	t.Log(rs)

	rs, err = slegapi.FreeSpinsCancel("12")
	if err != nil {
		t.Error(err)
	}
	t.Log(rs)
}
