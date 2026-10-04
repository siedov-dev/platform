const cache = new Map();
let host = null;

const hostEl = () => {
  if (host && host.isConnected) return host;
  host = document.createElement("div");
  host.setAttribute("aria-hidden", "true");
  host.style.cssText =
    "position:fixed;left:0;top:0;width:1px;height:1px;overflow:hidden;opacity:0.001;pointer-events:none;z-index:-1";
  document.body.appendChild(host);
  return host;
};

export function jokerVideo(src) {
  let v = cache.get(src);
  if (v) return v;
  v = document.createElement("video");
  v.muted = true;
  v.defaultMuted = true;
  v.playsInline = true;
  v.loop = false;
  v.setAttribute("muted", "");
  v.setAttribute("playsinline", "");
  v.setAttribute("webkit-playsinline", "");
  v.disablePictureInPicture = true;
  v.preload = "auto";
  v.src = src;
  v.load();
  cache.set(src, v);
  hostEl().appendChild(v);
  return v;
}

export function releaseJoker(v) {
  if (v && host) hostEl().appendChild(v);
}

const ready = (v) =>
  v.readyState >= 3
    ? Promise.resolve()
    : new Promise((res) => {
        const done = () => {
          v.removeEventListener("canplaythrough", done);
          v.removeEventListener("error", done);
          res();
        };
        v.addEventListener("canplaythrough", done);
        v.addEventListener("error", done);
        setTimeout(done, 12000);
      });

export async function warmJoker(src) {
  const v = jokerVideo(src);
  await ready(v);
  try {
    await v.play();
    v.pause();
    v.currentTime = 0;
  } catch {
    /* noop */
  }
  return v;
}
