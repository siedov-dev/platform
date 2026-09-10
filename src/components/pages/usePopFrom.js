import { useLayoutEffect } from "react";

const DUR = 520;

export default function usePopFrom(ref, from, ready = true) {
  useLayoutEffect(() => {
    const el = ref.current;
    if (!ready || !el || !from || typeof el.animate !== "function") return undefined;
    const r = el.getBoundingClientRect();
    if (!r.width || !r.height) return undefined;

    const k = el.offsetWidth ? r.width / el.offsetWidth : 1;
    const dx = (from.x - (r.left + r.width / 2)) / (k || 1);
    const dy = (from.y - (r.top + r.height / 2)) / (k || 1);
    const s = Math.max(0.06, Math.min(0.55, (from.w || 36) / r.width));

    const mid = 0.6;
    const sm = s + (1 - s) * 0.9;

    const anim = el.animate(
      [
        {
          transform: `translate(${dx}px, ${dy}px) scale(${s})`,
          opacity: 0,
          offset: 0,
          easing: "cubic-bezier(0.4, 0, 0.2, 1)",
        },
        {
          transform: `translate(${dx * 0.1}px, ${dy * 0.1}px) scale(${sm})`,
          opacity: 1,
          offset: mid,
          easing: "cubic-bezier(0.2, 0.7, 0.3, 1)",
        },
        {
          transform: "translate(0px, 0px) scale(1.04)",
          opacity: 1,
          offset: 0.82,
          easing: "cubic-bezier(0.4, 0, 0.3, 1)",
        },
        { transform: "translate(0px, 0px) scale(1)", opacity: 1, offset: 1 },
      ],
      { duration: DUR, fill: "backwards" }
    );

    return () => anim.cancel();
  }, [from, ref, ready]);
}

const OUT_MS = 420;

export function usePopTo(ref, from, closing) {
  useLayoutEffect(() => {
    const el = ref.current;
    if (!closing || !el || !from || typeof el.animate !== "function") return undefined;
    const r = el.getBoundingClientRect();
    if (!r.width || !r.height) return undefined;

    const k = el.offsetWidth ? r.width / el.offsetWidth : 1;
    const dx = (from.x - (r.left + r.width / 2)) / (k || 1);
    const dy = (from.y - (r.top + r.height / 2)) / (k || 1);
    const s = Math.max(0.06, Math.min(0.55, (from.w || 36) / r.width));

    const anim = el.animate(
      [
        { transform: "translate(0px, 0px) scale(1)", opacity: 1, offset: 0 },
        { transform: `translate(${dx}px, ${dy}px) scale(${s})`, opacity: 0, offset: 1 },
      ],
      { duration: OUT_MS, easing: "cubic-bezier(0.5, 0, 0.78, 0.32)", fill: "forwards" }
    );

    return () => anim.cancel();
  }, [closing, from, ref]);
}
