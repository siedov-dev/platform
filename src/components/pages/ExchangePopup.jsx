import { useState } from "react";
import styles from "./ExchangePopup.module.css";

const A = "/bonus-park/Exchange";
const UI = "/bonus-park/Ui_Icon_Bg_Up";
const PRESETS = [100, 300, 500, 1000];
const RATE = 100;

export default function ExchangePopup({ wallet = {}, onExchange, onClose }) {
  const [amount, setAmount] = useState(100);
  const [confirm, setConfirm] = useState(false);
  const coinsOut = Math.floor(amount / RATE);
  const canPay = coinsOut > 0 && (wallet.cash || 0) >= amount;

  const step = (d) => setAmount((a) => Math.min(99999999, Math.max(RATE, a + d * RATE)));

  const onAmountInput = (e) => {
    const digits = e.target.value.replace(/\D/g, "").slice(0, 8);
    setAmount(digits ? parseInt(digits, 10) : 0);
  };

  const doYes = () => {
    onExchange?.(amount, coinsOut);
    setConfirm(false);
  };

  return (
    <div className={styles.overlay}>
      <div className={styles.popup}>
        <img className={styles.frameTop} src={`${A}/Ex_Bg_top.png`} alt="" draggable={false} />
        <div className={styles.frameMid} />
        <img className={styles.frameBot} src={`${A}/Ex_Bg_bot.png`} alt="" draggable={false} />

        <div className={styles.content}>
          <div className={styles.title}>EXCHANGE</div>

          <button type="button" className={styles.close} onClick={onClose}>
            <img className={styles.closeBg} src={`${A}/Btn_round_Red_c.png`} alt="" draggable={false} />
            <img className={styles.closeIcn} src={`${A}/Icn_Cress.png`} alt="" draggable={false} />
          </button>

          <div className={styles.row}>
            <div className={styles.chip}>
              <img className={styles.chipIcn} src={`${UI}/Icn_Coin_Front.png`} alt="" draggable={false} />
              <span className={styles.chipVal}>{wallet.coins || 0}</span>
            </div>
            <img className={styles.arrow} src={`${A}/Ex_Arrow_t.png`} alt="" draggable={false} />
            <div className={`${styles.chip} ${styles.chipCash}`}>
              <img className={styles.chipIcn} src="/bonus-park/Ui_Exchange_HUD/Icn_Currency.png" alt="" draggable={false} />
              <span className={styles.chipVal}>{wallet.cash || 0}</span>
              <span className={styles.chipPlus}>
                <img className={styles.chipPlusBg} src={`${UI}/Btn_Plus.png`} alt="" draggable={false} />
                <img className={styles.chipPlusIcn} src={`${UI}/Icn_Plus.png`} alt="" draggable={false} />
              </span>
            </div>
          </div>

          <div className={styles.selectRow}>
            <img className={styles.selectOrn} src={`${A}/Ex_Orn.png`} alt="" draggable={false} />
            <span className={styles.selectLabel}>Select Amount</span>
            <img className={styles.selectOrn} src={`${A}/Ex_Orn.png`} alt="" draggable={false} />
          </div>

          <div className={styles.stepRow}>
            <button type="button" className={styles.round} onClick={() => step(-1)}>
              <span className={styles.minusBar} />
            </button>
            <div className={`${styles.amountBox} ${String(amount).length > 6 ? styles.amountSmall : ""}`}>
              <span>$</span>
              <input
                className={styles.amountInput}
                type="text"
                inputMode="numeric"
                value={amount}
                onChange={onAmountInput}
                style={{ width: `${String(amount).length}ch` }}
              />
            </div>
            <button type="button" className={styles.round} onClick={() => step(1)}>
              <img className={styles.plusIcn} src={`${A}/Icn_plus.png`} alt="" draggable={false} />
            </button>
          </div>

          <div className={styles.presets}>
            {PRESETS.map((p) => (
              <button
                key={p}
                type="button"
                className={`${styles.preset} ${amount === p ? styles.presetOn : ""}`}
                onClick={() => setAmount(p)}
              >
                ${p}
              </button>
            ))}
          </div>

          <div className={styles.receive}>
            You receive:
            <img className={styles.coinIcn} src={`${UI}/Icn_Coin_Front.png`} alt="" draggable={false} />
            <span className={styles.receiveNum}>{coinsOut} coins</span>
          </div>

          <img className={styles.line} src={`${A}/Ex_Line.png`} alt="" draggable={false} />

          <button
            type="button"
            className={`${styles.exBtn} ${canPay ? "" : styles.exBtnOff}`}
            onClick={() => {
              if (!canPay) return;
              setAmount(coinsOut * RATE);
              setConfirm(true);
            }}
          >
            Exchange
          </button>
        </div>
      </div>

      {confirm && (
        <div className={styles.confirmWrap}>
          <img className={styles.confirmLight} src={`${A}/Cntr_Light_PopUp.png`} alt="" draggable={false} />
          <div className={styles.confirmPanel}>
            <div className={styles.confirmInner}>
              <div className={styles.confirmTitle}>Are you Sure?</div>
              <img className={styles.confirmLine} src={`${A}/Ex_Line.png`} alt="" draggable={false} />
              <div className={styles.confirmText}>
                You Exchange <span className={styles.confirmNum}>{amount}$</span>
              </div>
              <div className={styles.receive}>
                You receive:
                <img className={styles.coinIcn} src={`${UI}/Icn_Coin_Front.png`} alt="" draggable={false} />
                <span className={styles.receiveNum}>{coinsOut} coins</span>
              </div>
              <div className={styles.confirmBtns}>
                <button type="button" className={`${styles.cBtn} ${styles.cNo}`} onClick={() => setConfirm(false)}>
                  No
                </button>
                <button type="button" className={`${styles.cBtn} ${styles.cYes}`} onClick={doYes}>
                  Yes
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
