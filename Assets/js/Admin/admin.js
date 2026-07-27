"use strict";

(() => {
  const root = document.documentElement;
  const body = document.body;
  const sidebar = document.getElementById("adminSidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const mobileMenuButton = document.getElementById("mobileMenuButton");
  const themeButtons = [
    document.getElementById("sidebarThemeToggle"),
    document.getElementById("topThemeToggle"),
  ].filter(Boolean);
  const clock = document.getElementById("systemClock");
  const storageKey = "cyberpunk-admin-theme";

  const getStoredTheme = () => {
    try {
      return localStorage.getItem(storageKey);
    } catch (error) {
      return null;
    }
  };

  const storeTheme = (theme) => {
    try {
      localStorage.setItem(storageKey, theme);
    } catch (error) {
      // El tema seguirá funcionando durante la sesión aunque el navegador bloquee localStorage.
    }
  };

  const updateThemeButtons = (theme) => {
    const isDark = theme === "dark";

    themeButtons.forEach((button) => {
      const icon = button.querySelector("i");
      const text = button.querySelector(".theme-toggle__text");
      const nextThemeLabel = isDark
        ? "Cambiar a modo claro"
        : "Cambiar a modo oscuro";

      button.setAttribute("aria-label", nextThemeLabel);
      button.setAttribute("title", nextThemeLabel);

      if (icon) {
        icon.className = isDark ? "fa-solid fa-sun" : "fa-solid fa-moon";
      }

      if (text) {
        text.textContent = isDark ? "Modo claro" : "Modo oscuro";
      }
    });
  };

  const applyTheme = (theme) => {
    const safeTheme = theme === "light" ? "light" : "dark";

    root.dataset.theme = safeTheme;
    root.dataset.bsTheme = safeTheme;

    body.dataset.theme = safeTheme;
    body.dataset.bsTheme = safeTheme;

    storeTheme(safeTheme);
    updateThemeButtons(safeTheme);
  };

  applyTheme(getStoredTheme() || root.dataset.theme || "dark");

  themeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      applyTheme(root.dataset.theme === "dark" ? "light" : "dark");
    });
  });

  const setMobileSidebar = (isOpen) => {
    if (!sidebar || !overlay || !mobileMenuButton) {
      return;
    }

    sidebar.classList.toggle("is-mobile-open", isOpen);
    overlay.classList.toggle("is-visible", isOpen);
    mobileMenuButton.setAttribute("aria-expanded", String(isOpen));
    body.classList.toggle("has-open-sidebar", isOpen);
  };

  mobileMenuButton?.addEventListener("click", () => {
    setMobileSidebar(!sidebar?.classList.contains("is-mobile-open"));
  });

  overlay?.addEventListener("click", () => setMobileSidebar(false));

  sidebar?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      if (window.matchMedia("(max-width: 991px)").matches) {
        setMobileSidebar(false);
      }
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      setMobileSidebar(false);
    }
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 991) {
      setMobileSidebar(false);
    }
  });

  const updateClock = () => {
    if (!clock) {
      return;
    }

    const now = new Date();
    clock.dateTime = now.toISOString();
    clock.textContent = new Intl.DateTimeFormat("es-MX", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    }).format(now);
  };

  updateClock();
  window.setInterval(updateClock, 1000);
})();
