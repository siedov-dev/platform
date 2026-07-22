export const EASINGS = {
  easeInOutQuart: "cubic-bezier(0.76, 0, 0.24, 1)",
  easeInOutExpo: "cubic-bezier(0.87, 0, 0.13, 1)",
};

export const DEFAULT_EASE = "easeInOutExpo";
export const EASE_KEY = "bonuspark.ease";

export function applyEase(name) {
  const bezier = EASINGS[name] || EASINGS[DEFAULT_EASE];
  document.documentElement.style.setProperty("--ease", bezier);
}
