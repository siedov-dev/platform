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
  createRoot(document.getElementById("root")).render(
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
