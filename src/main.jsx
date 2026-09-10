console.log("[park] build 2026-08-27 13:12");
window.__PARK_BUILD__ = "2026-08-27 13:12";
import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { I18nProvider } from "@/i18n/I18nProvider";
import { AppProvider } from "@/context/AppContext";
import App from "./App";
import "./styles/global.css";

async function enableMocking() {
  if (import.meta.env.VITE_ENABLE_MOCKS !== "true") return;
  const { worker } = await import("@/mocks/browser");
  await worker.start({ onUnhandledRequest: "bypass" });
}

function render() {
  let host = document.getElementById("root");
  if (!host) {
    host = document.createElement("div");
    host.id = "root";
    document.body.appendChild(host);
  }
  createRoot(host).render(
    <StrictMode>
      <I18nProvider initialLang="en">
        <AppProvider>
          <App />
        </AppProvider>
      </I18nProvider>
    </StrictMode>
  );
}

enableMocking()
  .catch((err) => console.warn("[mock] init failed, rendering anyway:", err))
  .finally(render);
