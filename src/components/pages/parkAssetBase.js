export const PARK_DESK =
  typeof window !== "undefined" && typeof window.matchMedia === "function"
    ? window.matchMedia("(hover: hover) and (pointer: fine)").matches
    : false;

export const PARK_ASSET = "/bonus-park";
