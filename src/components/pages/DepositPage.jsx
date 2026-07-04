import { useState } from "react";
import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import { DepositArrow, WithdrawArrow } from "@/components/ui/Icons";
import styles from "./DepositPage.module.css";

function PayLogo({ type }) {
  if (type === "crypto") return <div className={styles.logoRow}><div className={styles.cryptoIcon}>¢</div><div className={styles.logoText}>CRYPTO<br /><span className={styles.accent}>CURRENCY</span></div></div>;
  if (type === "card") return <div className={styles.logoRow}><span className={styles.visa}>VISA</span><span className={styles.mc}><span className={styles.mcR} /><span className={styles.mcY} /></span><span className={styles.p2p}>P2P</span></div>;
  if (type === "bank") return <div className={styles.logoRow}><div className={styles.kaspiIcon}>K</div><span className={styles.kaspiText}>Kaspi<span className={styles.accent}>.kz</span></span></div>;
  return null;
}

export default function DepositPage() {
  const [mode, setMode] = useState("deposit");
  const [selected, setSelected] = useState(null);
  const [amount, setAmount] = useState("");
  const [busy, setBusy] = useState(false);
  const { paymentMethods, submitDeposit, submitWithdraw, refreshBalance } = useApp();
  const { t } = useI18n();

  const fmt = (n) => n.toLocaleString("en-US");

  const onConfirm = async () => {
    const value = Number(amount);
    if (!selected || !value || value <= 0) return;
    setBusy(true);
    const res = mode === "deposit"
      ? await submitDeposit(selected.id, value)
      : await submitWithdraw(selected.id, value, {});
    setBusy(false);
    if (res) {
      await refreshBalance();
      setAmount("");
      setSelected(null);
    }
  };

  return (
    <>
      <div className={styles.bg} />
      <div className={styles.scroll}>
        <div className={styles.tabs}>
          <button className={mode === "deposit" ? styles.tabActive : styles.tab} onClick={() => { setMode("deposit"); setSelected(null); }}>
            <span className={styles.tabIcon}><DepositArrow /></span>{t("deposit.deposit")}
          </button>
          <button className={mode === "withdraw" ? styles.tabActive : styles.tab} onClick={() => { setMode("withdraw"); setSelected(null); }}>
            <span className={styles.tabIcon}><WithdrawArrow /></span>{t("deposit.withdraw")}
          </button>
        </div>

        {!selected ? (
          <div className={styles.grid}>
            {paymentMethods.map((m) => (
              <button key={m.id} className={styles.card} onClick={() => setSelected(m)}>
                <div className={styles.cardLogo}><PayLogo type={m.type} /></div>
                <div className={styles.limit}>{fmt(m.minDeposit)} – {fmt(m.maxDeposit)}</div>
                <div className={styles.commission}>{t("deposit.commission")}: {m.commission}%</div>
              </button>
            ))}
          </div>
        ) : (
          <div className={styles.form}>
            <button className={styles.back} onClick={() => setSelected(null)}>‹ {t("deposit.back")}</button>
            <div className={styles.formCard}>
              <div className={styles.cardLogo}><PayLogo type={selected.type} /></div>
              <div className={styles.limit}>{fmt(selected.minDeposit)} – {fmt(selected.maxDeposit)}</div>
            </div>
            <label className={styles.fieldLabel}>{t("deposit.amount")}</label>
            <input
              className={styles.amountInput}
              type="number"
              inputMode="decimal"
              placeholder={t("deposit.amount")}
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
            />
            <button className={styles.confirm} disabled={busy} onClick={onConfirm}>
              {busy ? t("status.loading") : (mode === "deposit" ? t("deposit.deposit") : t("deposit.withdraw"))}
            </button>
          </div>
        )}
      </div>
    </>
  );
}
