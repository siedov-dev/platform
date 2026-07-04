import { createContext, useContext, useState, useCallback, useMemo } from "react";
import translations from "./translations";

const I18nContext = createContext(null);

export function I18nProvider({ children, initialLang = "en" }) {
  const [lang, setLang] = useState(initialLang);

  const t = useCallback(
    (key) => translations[lang]?.[key] || translations.en?.[key] || key,
    [lang]
  );

  const availableLanguages = useMemo(() => Object.keys(translations), []);

  const value = useMemo(
    () => ({ lang, setLang, t, availableLanguages }),
    [lang, t, availableLanguages]
  );

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n() {
  const ctx = useContext(I18nContext);
  if (!ctx) throw new Error("useI18n must be inside I18nProvider");
  return ctx;
}
