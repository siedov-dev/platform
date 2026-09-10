import styles from "./FerrisEvent.module.css";

const A = "/bonus-park/Evnt_FW";

const CABINS = [
  { img: "Evnt_FW_Cabin_Red.png", n: 1 },
  { img: "Evnt_FW_Cabin__Green.png", n: 6 },
  { img: "Evnt_FW_Cabin_Blue.png", n: 7 },
  { img: "Evnt_FW_Cabin_Purple.png", n: 3 },
  { img: "Evnt_FW_Cabin_Red.png", n: 2 },
  { img: "Evnt_FW_Cabin__Green.png", n: 5 },
  { img: "Evnt_FW_Cabin_Blue.png", n: 8 },
  { img: "Evnt_FW_Cabin_Purple.png", n: 4 },
];

export const CABIN_SLOT = CABINS.map((c) => c.n);

export default function FerrisEvent({ angle = 0, spinning = false, prize = null }) {
  const ease = spinning ? "transform 3s cubic-bezier(.17,.67,.2,1)" : "none";
  const winIdx = prize == null ? -1 : CABIN_SLOT.indexOf(Number(prize));

  return (
    <div className={styles.stage}>
      <img className={styles.legL} src={`${A}/Evnt_FW_Leg.png`} alt="" draggable={false} />
      <img className={styles.legR} src={`${A}/Evnt_FW_Leg.png`} alt="" draggable={false} />

      <div className={styles.ring} style={{ transform: `translate(-50%, -50%) rotate(${angle}deg)`, transition: ease }}>
        {CABINS.map((c, i) => (
          <div key={i} className={styles.arm} style={{ transform: `translate(-50%, -50%) rotate(${i * 45}deg)` }}>
            <div
              className={`${styles.cab} ${i === winIdx ? styles.cabWin : ""}`}
              style={{ transform: `translate(-50%, -46%) rotate(${-(angle + i * 45)}deg)`, transition: ease }}
            >
              <img src={`${A}/${c.img}`} alt="" draggable={false} />
              <span className={styles.num}>{c.n}</span>
            </div>
          </div>
        ))}

        <img className={styles.wheel} src={`${A}/Evnt_FW_wheel.png`} alt="" draggable={false} />
      </div>

      <img className={styles.hub} src={`${A}/Evnt_FW_Centr.png`} alt="" draggable={false} />
      <img className={styles.base} src={`${A}/Evnt_FW_Base.png`} alt="" draggable={false} />

      <img
        className={`${styles.pointer} ${spinning ? "" : styles.pointerIdle}`}
        src={`${A}/${spinning ? "Arrow_Evnt_Ferris wheel.png" : "Arrow_Evnt_Ferris wheel_Active.png"}`}
        alt=""
        draggable={false}
      />

      {prize != null && (
        <div key={`p${prize}`} className={styles.ticket}>
          <span className={styles.ticketBob}>
            <span className={styles.ticketBox}>
              <img className={styles.ticketGlow} src={`${A}/Evnt_FW_Light_ticket.png`} alt="" draggable={false} />
              <img className={styles.ticketIcn} src="/bonus-park/Ui_Icon_Bg_Up/Icn_Tiket.png" alt="" draggable={false} />
            </span>
            <span className={styles.ticketNum}>+{prize}</span>
          </span>
        </div>
      )}
    </div>
  );
}
