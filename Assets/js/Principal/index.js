"use strict";

/* =========================================================
   REFERENCIAS GENERALES
========================================================= */

const projectFilters = document.getElementById("projectFilters");
const projectsGrid = document.getElementById("projectsGrid");

/**
 * Indica si el usuario tiene activada la reducción de movimiento.
 */
function prefersReducedMotion() {
  return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

/* =========================================================
   PROYECTOS
========================================================= */

/**
 * Anima las tarjetas visibles después de aplicar un filtro.
 *
 * @param {NodeList|HTMLElement[]|null} elements
 */
function animateProjectCards(elements = null) {
  if (!window.gsap || prefersReducedMotion()) {
    return;
  }

  const cards = elements
    ? [...elements]
    : [...document.querySelectorAll(".project-grid-item:not([hidden])")];

  if (cards.length === 0) {
    return;
  }

  window.gsap.killTweensOf(cards);

  window.gsap.fromTo(
    cards,
    {
      autoAlpha: 0,
      y: 22,
    },
    {
      autoAlpha: 1,
      y: 0,
      duration: 0.5,
      stagger: 0.07,
      ease: "power2.out",
      clearProps: "opacity,visibility,transform",
    },
  );
}

/**
 * Inicializa los filtros de proyectos.
 */
function initializeProjectFilters() {
  if (!projectFilters || !projectsGrid) {
    return;
  }

  projectFilters.addEventListener("click", (event) => {
    const button = event.target.closest("[data-filter]");

    if (!button) {
      return;
    }

    const selectedFilter = button.dataset.filter || "all";

    projectFilters.querySelectorAll("[data-filter]").forEach((filterButton) => {
      const isActive = filterButton === button;

      filterButton.classList.toggle("active", isActive);

      filterButton.setAttribute("aria-pressed", String(isActive));
    });

    const visibleCards = [];

    projectsGrid.querySelectorAll(".project-grid-item").forEach((card) => {
      const categories = (card.dataset.categories || "")
        .split(/\s+/)
        .filter(Boolean);

      const isVisible =
        selectedFilter === "all" || categories.includes(selectedFilter);

      card.hidden = !isVisible;

      if (isVisible) {
        visibleCards.push(card);
      }
    });

    animateProjectCards(visibleCards);

    if (window.ScrollTrigger) {
      window.ScrollTrigger.refresh();
    }
  });
}

/**
 * Abre el modal asociado a una tarjeta.
 *
 * @param {HTMLElement} card
 */
function openProjectModal(card) {
  if (!card || !window.bootstrap) {
    return;
  }

  const selector = card.dataset.projectModal;

  if (!selector) {
    return;
  }

  const modalElement = document.querySelector(selector);

  if (!modalElement) {
    console.warn(`[projects] No se encontró el modal ${selector}`);

    return;
  }

  window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
}
/**
 * Inicializa clic y teclado en las tarjetas.
 */
function initializeProjectCards() {
  if (!projectsGrid) {
    return;
  }

  projectsGrid.addEventListener("click", (event) => {
    /*
     * Los enlaces y botones internos no deben abrir
     * el modal de detalles del proyecto.
     */
    const action = event.target.closest("[data-project-action]");

    if (action) {
      event.stopPropagation();

      const privateTarget = action.dataset.privateTarget;

      if (privateTarget) {
        const target = document.querySelector(privateTarget);

        if (target) {
          const isVisible = target.classList.toggle("show");

          action.setAttribute("aria-expanded", String(isVisible));
        }
      }

      return;
    }

    const card = event.target.closest("[data-project-modal]");

    if (card) {
      openProjectModal(card);
    }
  });

  projectsGrid.addEventListener("keydown", (event) => {
    /*
     * Un enlace o botón interno debe conservar su
     * comportamiento normal de teclado.
     */
    const action = event.target.closest("[data-project-action]");

    if (action) {
      return;
    }

    const card = event.target.closest("[data-project-modal]");

    if (!card || (event.key !== "Enter" && event.key !== " ")) {
      return;
    }

    event.preventDefault();

    openProjectModal(card);
  });
}

/**
 * Inicializa las galerías de todos los modales.
 */
function initializeProjectGalleries() {
  document.addEventListener("click", (event) => {
    const thumbnail = event.target.closest(
      "[data-gallery-image][data-gallery-target]",
    );

    if (!thumbnail) {
      return;
    }

    const targetSelector = thumbnail.dataset.galleryTarget;

    const imageUrl = thumbnail.dataset.galleryImage;

    if (!targetSelector || !imageUrl) {
      return;
    }

    const target = document.querySelector(targetSelector);

    if (!target) {
      return;
    }

    const modal = thumbnail.closest(".modal");

    modal?.querySelectorAll(".gallery-thumb").forEach((item) => {
      item.classList.remove("active");
    });

    thumbnail.classList.add("active");

    target.src = imageUrl;
  });
}

/* =========================================================
   ANIMACIONES
========================================================= */

/**
 * Inicializa GSAP, ScrollTrigger, terminal y Anime.js.
 */
function initializeAnimations() {
  const terminalLines = [...document.querySelectorAll(".terminal-line")];

  const counters = [...document.querySelectorAll("[data-counter]")];

  /**
   * Hace visibles los componentes cuando las
   * animaciones están desactivadas o no disponibles.
   */
  const showElementsWithoutAnimation = () => {
    terminalLines.forEach((line) => {
      line.style.opacity = "1";
      line.style.visibility = "visible";
      line.style.transform = "none";
    });

    document
      .querySelectorAll(".reveal-section, .tech-item, .project-grid-item")
      .forEach((element) => {
        element.style.opacity = "1";
        element.style.visibility = "visible";
        element.style.transform = "none";
      });

    counters.forEach((counter) => {
      const target = Number(counter.dataset.counter);

      if (Number.isFinite(target)) {
        counter.textContent = String(Math.round(target));
      }
    });
  };

  /*
   * Accesibilidad: no ejecutar movimientos si el
   * usuario solicita reducción de animaciones.
   */
  if (prefersReducedMotion()) {
    showElementsWithoutAnimation();
    return;
  }

  /* =======================================================
     GSAP
  ======================================================= */

  if (window.gsap) {
    const gsap = window.gsap;

    const hasScrollTrigger = typeof window.ScrollTrigger !== "undefined";

    if (hasScrollTrigger) {
      gsap.registerPlugin(window.ScrollTrigger);
    }

    /*
     * Ocultar las líneas antes de iniciar la
     * animación progresiva de la terminal.
     */
    if (terminalLines.length > 0) {
      gsap.set(terminalLines, {
        autoAlpha: 0,
        x: 14,
      });
    }

    /*
     * Animación inicial del Hero.
     */
    const heroTimeline = gsap.timeline({
      defaults: {
        ease: "power3.out",
      },
    });

    heroTimeline
      .from(".hero-kicker", {
        autoAlpha: 0,
        y: 18,
        duration: 0.45,
      })
      .from(
        ".hero-title",
        {
          autoAlpha: 0,
          y: 42,
          duration: 0.8,
        },
        "-=0.15",
      )
      .from(
        ".hero-copy",
        {
          autoAlpha: 0,
          y: 22,
          duration: 0.55,
        },
        "-=0.35",
      )
      .from(
        ".hero-actions .cp-btn",
        {
          autoAlpha: 0,
          y: 16,
          duration: 0.42,
          stagger: 0.1,
        },
        "-=0.25",
      )
      .from(
        ".hero-terminal",
        {
          autoAlpha: 0,
          x: 42,
          duration: 0.75,
        },
        "-=0.7",
      );

    if (terminalLines.length > 0) {
      heroTimeline.to(
        terminalLines,
        {
          autoAlpha: 1,
          x: 0,
          duration: 0.25,
          stagger: 0.22,
          clearProps: "transform",
        },
        "-=0.25",
      );
    }

    /*
     * Animaciones que dependen del desplazamiento.
     */
    if (hasScrollTrigger) {
      gsap.utils.toArray(".reveal-section").forEach((element) => {
        gsap.from(element, {
          scrollTrigger: {
            trigger: element,
            start: "top 84%",
            once: true,
          },
          autoAlpha: 0,
          y: 34,
          duration: 0.75,
          ease: "power2.out",
          clearProps: "opacity,visibility,transform",
        });
      });

      gsap.utils.toArray(".tech-item").forEach((element, index) => {
        gsap.from(element, {
          scrollTrigger: {
            trigger: element,
            start: "top 90%",
            once: true,
          },
          autoAlpha: 0,
          y: 20,
          duration: 0.45,
          delay: (index % 4) * 0.05,
          ease: "power2.out",
          clearProps: "opacity,visibility,transform",
        });
      });

      gsap.utils
        .toArray(".project-grid-item:not([hidden])")
        .forEach((element, index) => {
          gsap.from(element, {
            scrollTrigger: {
              trigger: element,
              start: "top 92%",
              once: true,
            },
            autoAlpha: 0,
            y: 24,
            duration: 0.5,
            delay: (index % 3) * 0.06,
            ease: "power2.out",
            clearProps: "opacity,visibility,transform",
          });
        });

      counters.forEach((counter) => {
        const target = Number(counter.dataset.counter);

        if (!Number.isFinite(target)) {
          return;
        }

        const state = {
          value: 0,
        };

        gsap.to(state, {
          scrollTrigger: {
            trigger: counter,
            start: "top 92%",
            once: true,
          },
          value: target,
          duration: 1.4,
          ease: "power2.out",
          onUpdate: () => {
            counter.textContent = String(Math.round(state.value));
          },
        });
      });
    } else {
      /*
       * GSAP está disponible, pero ScrollTrigger no.
       * Los elementos deben permanecer visibles.
       */
      document
        .querySelectorAll(".reveal-section, .tech-item, .project-grid-item")
        .forEach((element) => {
          gsap.set(element, {
            autoAlpha: 1,
            y: 0,
          });
        });

      counters.forEach((counter) => {
        const target = Number(counter.dataset.counter);

        if (Number.isFinite(target)) {
          counter.textContent = String(Math.round(target));
        }
      });
    }
  } else {
    showElementsWithoutAnimation();
  }

  /* =======================================================
     ANIME.JS
  ======================================================= */

  if (!window.anime) {
    return;
  }

  const floatingNodes = document.querySelectorAll(".floating-node");

  if (floatingNodes.length > 0) {
    window.anime({
      targets: floatingNodes,
      translateY: [
        {
          value: -18,
          duration: 1600,
        },
        {
          value: 18,
          duration: 1600,
        },
      ],
      translateX: [
        {
          value: 8,
          duration: 2200,
        },
        {
          value: -8,
          duration: 2200,
        },
      ],
      rotate: "1turn",
      easing: "easeInOutSine",
      direction: "alternate",
      loop: true,
      delay: window.anime.stagger(260),
    });
  }

  const glitchElement = document.querySelector(".glitch");

  if (!glitchElement) {
    return;
  }

  window.setInterval(() => {
    glitchElement.classList.add("is-glitching");

    window.anime({
      targets: glitchElement,
      translateX: [
        {
          value: -2,
          duration: 45,
        },
        {
          value: 3,
          duration: 45,
        },
        {
          value: 0,
          duration: 45,
        },
      ],
      skewX: [
        {
          value: -4,
          duration: 45,
        },
        {
          value: 5,
          duration: 45,
        },
        {
          value: 0,
          duration: 45,
        },
      ],
      easing: "linear",
      complete: () => {
        glitchElement.classList.remove("is-glitching");
      },
    });
  }, 3800);
}

/* =========================================================
   NAVEGACIÓN
========================================================= */

/**
 * Inicializa navegación activa y menú móvil.
 */
function initializeNavigation() {
  const navLinks = [...document.querySelectorAll(".nav-link[href^='#']")];

  if (navLinks.length === 0) {
    return;
  }

  const sections = navLinks
    .map((link) => {
      const selector = link.getAttribute("href");

      if (!selector || selector === "#") {
        return null;
      }

      try {
        return document.querySelector(selector);
      } catch (error) {
        return null;
      }
    })
    .filter(Boolean);

  if ("IntersectionObserver" in window && sections.length > 0) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          navLinks.forEach((link) => {
            link.classList.toggle(
              "active",
              link.getAttribute("href") === `#${entry.target.id}`,
            );
          });
        });
      },
      {
        rootMargin: "-35% 0px -55% 0px",
        threshold: 0,
      },
    );

    sections.forEach((section) => {
      observer.observe(section);
    });
  }

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      const navbarCollapse = document.getElementById("mainNavbar");

      if (
        !navbarCollapse ||
        !navbarCollapse.classList.contains("show") ||
        !window.bootstrap
      ) {
        return;
      }

      window.bootstrap.Collapse.getOrCreateInstance(navbarCollapse).hide();
    });
  });
}

/* =========================================================
   FORMULARIO DE CONTACTO
========================================================= */

/**
 * Inicializa el formulario de contacto.
 */
function initializeContactForm() {
  const form = document.getElementById("contactForm");

  const toastElement = document.getElementById("contactToast");

  if (!form || !toastElement || !window.bootstrap) {
    return;
  }

  const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement, {
    delay: 5000,
  });

  const dateInput = document.getElementById("meetingDate");

  const startedAtInput = document.getElementById("contactFormStartedAt");

  const sendButton = document.getElementById("btnSendContact");

  const toastTitle = toastElement.querySelector(".toast-header strong");

  const toastIcon = toastElement.querySelector(".toast-header i");

  const toastBody = toastElement.querySelector(".toast-body");

  function configureDate() {
    if (!dateInput) {
      return;
    }

    const today = new Date();

    const timezoneOffset = today.getTimezoneOffset() * 60000;

    dateInput.min = new Date(today.getTime() - timezoneOffset)
      .toISOString()
      .split("T")[0];
  }

  function resetStartedAt() {
    if (!startedAtInput) {
      return;
    }

    startedAtInput.value = String(Math.floor(Date.now() / 1000));
  }

  function showToast(message, success = true) {
    if (toastTitle) {
      toastTitle.textContent = success
        ? "Solicitud registrada"
        : "No fue posible enviar";
    }

    if (toastBody) {
      toastBody.textContent = message;
    }

    if (toastIcon) {
      toastIcon.className = success
        ? "fa-solid fa-circle-check me-2 text-success"
        : "fa-solid fa-triangle-exclamation me-2 text-danger";
    }

    toast.show();
  }

  configureDate();
  resetStartedAt();

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    event.stopPropagation();

    form.classList.add("was-validated");

    if (!form.checkValidity()) {
      return;
    }

    const originalContent = sendButton ? sendButton.innerHTML : "";

    if (sendButton) {
      sendButton.disabled = true;

      sendButton.innerHTML = `
          <i class="fa-solid fa-circle-notch fa-spin"></i>
          <span>Enviando...</span>
        `;
    }

    try {
      const endpoint = form.dataset.endpoint;

      if (!endpoint) {
        throw new Error("No se configuró el endpoint del formulario.");
      }

      const response = await fetch(endpoint, {
        method: "POST",
        body: new FormData(form),
        credentials: "same-origin",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json();

      if (!response.ok || data.status === false) {
        throw new Error(data.msg || "No fue posible registrar la solicitud.");
      }

      showToast(data.msg || "Tu solicitud fue registrada correctamente.", true);

      form.reset();

      form.classList.remove("was-validated");

      configureDate();
      resetStartedAt();
    } catch (error) {
      showToast(
        error.message || "No fue posible conectar con el servidor.",
        false,
      );
    } finally {
      if (sendButton) {
        sendButton.disabled = false;
        sendButton.innerHTML = originalContent;
      }
    }
  });
}

/* =========================================================
   INICIALIZACIÓN
========================================================= */

function initializeSite() {
  const currentYear = document.getElementById("currentYear");

  if (currentYear) {
    currentYear.textContent = String(new Date().getFullYear());
  }

  initializeProjectFilters();
  initializeProjectCards();
  initializeProjectGalleries();

  initializeAnimations();
  initializeNavigation();
  initializeContactForm();
}

/*
 * El archivo se carga al final del body, pero esta
 * comprobación permite que también funcione con defer.
 */
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeSite, {
    once: true,
  });
} else {
  initializeSite();
}
