import { useEffect, useRef, useState } from "react";
import styles from "./PlayerRow.module.css";

const A = "/bonus-park/Ui_Icon_Bg_Up";
const FIELD = "/bonus-park/Icn_Field";
const SLOTS = [0, 1, 2, 3, 4, 5];
const SIDE = 3;
const DEFAULT_FACE = `${A}/Avarat_Grey.png`;
const SHOW_MS = 2000;
const DEMO_EVERY_MS = 5000;
const OPEN_SHOW_MS = 2000;
const OPEN_EVERY_MS = DEMO_EVERY_MS;
const OPEN_OFFSET_MS = 2600;
const OPEN_SLOT_SHIFT = 2;

const OPENED = [
  { id: "safe", safe: true },
  { id: "cards", src: `${FIELD}/Icn_Card_S.png` },
  { id: "wheel", src: `${FIELD}/Icn_Wheel_S.png` },
  { id: "pyramid", src: `${FIELD}/Icn_Pyramid_S.png` },
  { id: "joker", src: `${FIELD}/Icn_Joker_S.png` },
];

const openById = (id) => OPENED.find((o) => o.id === id);

const SafeIcn = () => (
  <svg className={styles.openSvg} viewBox="0 0 24 24" aria-hidden="true">
    <rect x="1.6" y="3.2" width="20.8" height="17.6" rx="3" fill="#2b2f3d" stroke="#e8c063" strokeWidth="2" />
    <rect x="5" y="6.2" width="13.4" height="11.6" rx="2" fill="#3a4055" stroke="#c9a04a" strokeWidth="1.3" />
    <circle cx="11.7" cy="12" r="3.2" fill="#1e2230" stroke="#f0cd78" strokeWidth="1.6" />
    <circle cx="11.7" cy="12" r="0.9" fill="#f0cd78" />
    <path d="M11.7 8.6V7.3M11.7 16.7v-1.3M8.5 12H7.2M16.2 12h-1.3" stroke="#f0cd78" strokeWidth="1.2" strokeLinecap="round" />
    <rect x="19.4" y="10.3" width="2.2" height="3.4" rx="1" fill="#e8c063" />
  </svg>
);

const normalize = (list) =>
  SLOTS.map((i) => {
    const p = list[i] || {};
    return {
      id: p.id ?? p.userId ?? i,
      name: p.name || p.nickname || p.login || "Player_Name",
      face: p.avatar || p.face || p.photo || DEFAULT_FACE,
    };
  });

export default function PlayerRow({ players = [], subscribe, demo, icons = false }) {
  const [list, setList] = useState(() => normalize(players));
  const [hot, setHot] = useState(null);
  const [open, setOpen] = useState(null);
  const hideRef = useRef(0);
  const openHideRef = useRef(0);
  const rrRef = useRef(0);
  const hotSlotRef = useRef(-1);
  const listRef = useRef(list);

  const playersKey = JSON.stringify(players);

  useEffect(() => {
    const next = normalize(JSON.parse(playersKey));
    listRef.current = next;
    setList(next);
  }, [playersKey]);

  useEffect(() => {
    const show = (i, val) => {
      clearTimeout(hideRef.current);
      hotSlotRef.current = i;
      setHot({ i, val, key: `${i}-${val}-${performance.now()}` });
      hideRef.current = setTimeout(() => {
        hotSlotRef.current = -1;
        setHot(null);
      }, SHOW_MS);
    };

    const push = (e) => {
      if (!e) return;
      const val = Number(e.amount ?? e.win ?? e.value ?? e.sum);
      if (!Number.isFinite(val) || val <= 0) return;
      const cur = listRef.current;
      let i = -1;
      if (e.id != null) i = cur.findIndex((p) => String(p.id) === String(e.id));
      if (i < 0 && e.name) i = cur.findIndex((p) => p.name === e.name);
      if (i < 0 && Number.isInteger(e.index)) i = e.index;
      if (i < 0 || i >= SLOTS.length) {
        i = rrRef.current % SLOTS.length;
        rrRef.current += 1;
      }
      if (e.name || e.avatar) {
        const next = cur.slice();
        next[i] = {
          id: e.id ?? next[i].id,
          name: e.name || next[i].name,
          face: e.avatar || e.face || next[i].face,
        };
        listRef.current = next;
        setList(next);
      }
      show(i, Math.round(val));
    };

    if (typeof subscribe === "function") {
      const off = subscribe(push);
      window.parkPlayerWin = push;
      return () => {
        clearTimeout(hideRef.current);
        delete window.parkPlayerWin;
        if (typeof off === "function") off();
      };
    }

    window.parkPlayerWin = push;

    if (demo === false) {
      return () => {
        clearTimeout(hideRef.current);
        delete window.parkPlayerWin;
      };
    }

    let idx = 0;
    const fire = () => {
      push({ index: idx % SLOTS.length, amount: 10 + Math.floor(Math.random() * 491) });
      idx += 1;
    };
    fire();
    const iv = setInterval(fire, DEMO_EVERY_MS);
    return () => {
      clearInterval(iv);
      clearTimeout(hideRef.current);
      delete window.parkPlayerWin;
    };
  }, [subscribe, demo]);

  useEffect(() => {
    if (!icons) {
      setOpen(null);
      return undefined;
    }
    const showOpen = (i, id) => {
      const item = openById(id) || OPENED[0];
      clearTimeout(openHideRef.current);
      setOpen({ i, id: item.id, key: `${i}-${item.id}-${performance.now()}` });
      openHideRef.current = setTimeout(() => setOpen(null), OPEN_SHOW_MS);
    };

    const pushOpen = (e) => {
      if (!e) return;
      const cur = listRef.current;
      let i = -1;
      if (e.id != null) i = cur.findIndex((p) => String(p.id) === String(e.id));
      if (i < 0 && e.name) i = cur.findIndex((p) => p.name === e.name);
      if (i < 0 && Number.isInteger(e.index)) i = e.index;
      if (i < 0 || i >= SLOTS.length) i = 0;
      showOpen(i, e.kind || e.game || e.icon);
    };

    window.parkPlayerOpen = pushOpen;

    if (demo === false) {
      return () => {
        clearTimeout(openHideRef.current);
        delete window.parkPlayerOpen;
      };
    }

    let step = OPEN_SLOT_SHIFT;
    const fire = () => {
      let slot = step % SLOTS.length;
      if (slot === hotSlotRef.current) slot = (slot + 1) % SLOTS.length;
      showOpen(slot, OPENED[step % OPENED.length].id);
      step += 1;
    };
    let iv = 0;
    const start = setTimeout(() => {
      fire();
      iv = setInterval(fire, OPEN_EVERY_MS);
    }, OPEN_OFFSET_MS);
    return () => {
      clearTimeout(start);
      clearInterval(iv);
      clearTimeout(openHideRef.current);
      delete window.parkPlayerOpen;
    };
  }, [icons, demo]);

  const cell = (p, i) => {
    const active = hot && hot.i === i;
    const opened = icons && open && open.i === i ? openById(open.id) : null;
    const lit = active || !!opened;
    return (
      <span key={p.id ?? i} className={`${styles.slot} ${lit ? styles.slotOn : ""}`}>
        {lit && <img className={styles.shine} src={`${A}/Avatar_Active_Shine_Fx.png`} alt="" draggable={false} />}
        <img className={styles.frame} src={`${A}/Bg_Avatar.png`} alt="" draggable={false} />
        <img
          className={styles.face}
          src={p.face}
          alt=""
          draggable={false}
          onError={(e) => {
            if (e.currentTarget.src !== DEFAULT_FACE) e.currentTarget.src = DEFAULT_FACE;
          }}
        />
        {lit && <img className={styles.activeFrame} src={`${A}/Avatar_Active_Frame.png`} alt="" draggable={false} />}
        {active && (
          <span key={hot.key} className={styles.winTag}>
            <span className={styles.plus}>+</span>
            <span className={styles.winVal}>{hot.val}</span>
          </span>
        )}
        {opened && (
          <span key={open.key} className={styles.openTag}>
            {opened.src ? (
              <img className={styles.openIcn} src={opened.src} alt="" draggable={false} />
            ) : (
              <SafeIcn />
            )}
          </span>
        )}
        <span className={styles.nameWrap}>
          <img className={styles.ribbon} src={`${A}/Avatar_Name_Ribbow.png`} alt="" draggable={false} />
          <span className={styles.name}>{p.name}</span>
        </span>
      </span>
    );
  };

  return (
    <div className={styles.row}>
      <div className={styles.group}>{list.slice(0, SIDE).map((p, i) => cell(p, i))}</div>
      <div className={styles.group}>{list.slice(SIDE).map((p, i) => cell(p, i + SIDE))}</div>
    </div>
  );
}
