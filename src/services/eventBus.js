export function sendEvent(eventType, payload) {
  const event = { type: eventType, payload, timestamp: Date.now() };
  window.dispatchEvent(new CustomEvent("casi:event", { detail: event }));
}

export function onCommand(callback) {
  const handler = (e) => callback(e.detail);
  window.addEventListener("casi:command", handler);
  return () => window.removeEventListener("casi:command", handler);
}

export function dispatchCommand(command) {
  window.dispatchEvent(new CustomEvent("casi:command", { detail: command }));
}

if (typeof window !== "undefined") {
  window.__casiDispatch = dispatchCommand;
}
