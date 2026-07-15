("use strict");

/*
 * Los arreglos technologies, categories y projects simulan la respuesta
 * que más adelante entregará el backend PHP mediante controladores/API.
 */

const technologies = [
  {
    id: 1,
    name: "HTML5",
    color: "#ff5f32",
    icon: "fa-brands fa-html5",
    level: "Frontend",
  },
  {
    id: 2,
    name: "CSS3",
    color: "#35a8ff",
    icon: "fa-brands fa-css3-alt",
    level: "Frontend",
  },
  {
    id: 3,
    name: "JavaScript",
    color: "#f8f32b",
    icon: "fa-brands fa-js",
    level: "Frontend",
  },
  {
    id: 4,
    name: "PHP",
    color: "#8993e8",
    icon: "fa-brands fa-php",
    level: "Backend",
  },
  {
    id: 5,
    name: "Bootstrap",
    color: "#a56bff",
    icon: "fa-brands fa-bootstrap",
    level: "UI Framework",
  },
  {
    id: 6,
    name: "MySQL",
    color: "#00c8ff",
    icon: "fa-solid fa-database",
    level: "Base de datos",
  },
  {
    id: 7,
    name: "Docker",
    color: "#2f9dff",
    icon: "fa-brands fa-docker",
    level: "DevOps",
  },
  {
    id: 8,
    name: "Linux",
    color: "#f1f1f1",
    icon: "fa-brands fa-linux",
    level: "Sistemas",
  },
  {
    id: 9,
    name: "C#",
    color: "#b76cff",
    icon: "fa-solid fa-code",
    level: "Escritorio",
  },
  {
    id: 10,
    name: "Java",
    color: "#ff6347",
    icon: "fa-brands fa-java",
    level: "Aplicaciones",
  },
  {
    id: 11,
    name: "Git",
    color: "#ff5b45",
    icon: "fa-brands fa-git-alt",
    level: "Control de versiones",
  },
  {
    id: 12,
    name: "Redes",
    color: "#69ff97",
    icon: "fa-solid fa-network-wired",
    level: "Infraestructura",
  },
];

const categories = [
  {
    id: "all",
    name: "Todos",
  },
  {
    id: "web",
    name: "Web",
  },
  {
    id: "desktop",
    name: "Escritorio",
  },
  {
    id: "mobile",
    name: "Móvil",
  },
  {
    id: "infrastructure",
    name: "Infraestructura",
  },
  {
    id: "security",
    name: "Ciberseguridad",
  },
];

const projects = [
  {
    id: 1,
    slug: "pacificnort",
    title: "PacificNort Suite",
    category: "web",
    categoryName: "Sistema web",
    excerpt:
      "Plataforma logística para administrar operaciones marítimas y ferroviarias.",
    description:
      "Sistema empresarial construido con arquitectura MVC para gestionar operaciones, clientes, contenedores, documentos, estatus y trazabilidad logística.",
    image: "https://picsum.photos/seed/pacificnort/1200/800",
    gallery: [
      "https://picsum.photos/seed/pacificnort-1/1200/800",
      "https://picsum.photos/seed/pacificnort-2/1200/800",
      "https://picsum.photos/seed/pacificnort-3/1200/800",
    ],
    technologies: [1, 2, 3, 4, 5, 6],
    github: {
      private: true,
      url: "",
    },
    liveUrl: "",
    challenge:
      "Modelar flujos logísticos complejos, evitar duplicidades, validar saldos de mercancía y mantener integridad entre módulos.",
    result:
      "Centralización de la operación, reducción de captura duplicada y mayor visibilidad del estado de cada embarque.",
    client: "Proyecto empresarial privado",
    hasVideo: false,
  },
  {
    id: 2,
    slug: "mechanical-system",
    title: "Mechanical System",
    category: "web",
    categoryName: "Sistema web",
    excerpt:
      "Plataforma para prevalidación, generación y envío de certificados técnicos.",
    description:
      "Sistema de certificación con gestión de folios concurrentes, validación documental, evidencias fotográficas, generación PDF/ZIP e integración con servicios externos.",
    image: "https://picsum.photos/seed/mechanical/1200/800",
    gallery: [
      "https://picsum.photos/seed/mechanical-1/1200/800",
      "https://picsum.photos/seed/mechanical-2/1200/800",
      "https://picsum.photos/seed/mechanical-3/1200/800",
    ],
    technologies: [1, 2, 3, 4, 5, 6],
    github: {
      private: true,
      url: "",
    },
    liveUrl: "",
    challenge:
      "Garantizar consistencia de folios durante procesos concurrentes y asegurar que cada expediente cumpla con todas las evidencias obligatorias.",
    result:
      "Flujo digital trazable desde la captura hasta la emisión del certificado y el envío a plataformas externas.",
    client: "Mechanical System",
    hasVideo: true,
  },
  {
    id: 3,
    slug: "nexus-homelab",
    title: "Nexus Homelab",
    category: "infrastructure",
    categoryName: "Infraestructura",
    excerpt:
      "Clúster doméstico con Docker Swarm, almacenamiento, DNS y servicios multimedia.",
    description:
      "Arquitectura distribuida de tres nodos para ejecutar servicios internos, almacenamiento compartido, proxy inverso, DNS local, monitoreo y aplicaciones multimedia.",
    image: "https://picsum.photos/seed/nexus-cluster/1200/800",
    gallery: [
      "https://picsum.photos/seed/nexus-1/1200/800",
      "https://picsum.photos/seed/nexus-2/1200/800",
      "https://picsum.photos/seed/nexus-3/1200/800",
    ],
    technologies: [7, 8, 11, 12],
    github: {
      private: false,
      url: "https://github.com/",
    },
    liveUrl: "",
    challenge:
      "Distribuir cargas, persistir datos y mantener comunicación entre nodos con recursos de hardware heterogéneos.",
    result:
      "Plataforma modular, escalable y documentada para desplegar servicios y proyectos personales.",
    client: "",
    hasVideo: true,
  },
  {
    id: 4,
    slug: "sstv-cipher",
    title: "SSTV Cipher Lab",
    category: "desktop",
    categoryName: "Escritorio",
    excerpt:
      "Aplicación experimental en C# para transformar datos e imágenes en señales.",
    description:
      "Proyecto de aprendizaje orientado a procesamiento de señales, codificación, cifrado y construcción progresiva de una interfaz de escritorio.",
    image: "https://picsum.photos/seed/sstv/1200/800",
    gallery: [
      "https://picsum.photos/seed/sstv-1/1200/800",
      "https://picsum.photos/seed/sstv-2/1200/800",
      "https://picsum.photos/seed/sstv-3/1200/800",
    ],
    technologies: [9, 11],
    github: {
      private: false,
      url: "https://github.com/",
    },
    liveUrl: "",
    challenge:
      "Comprender el tratamiento binario de imágenes y la conversión de información a una representación de audio.",
    result:
      "Base técnica para evolucionar de una aplicación de consola hacia una interfaz gráfica y un demostrador web.",
    client: "Proyecto personal",
    hasVideo: false,
  },
  {
    id: 5,
    slug: "mobile-field-app",
    title: "Field Ops Mobile",
    category: "mobile",
    categoryName: "Aplicación móvil",
    excerpt:
      "Prototipo de aplicación para captura de evidencias y seguimiento en campo.",
    description:
      "Concepto de aplicación móvil que permite registrar visitas, geolocalización aproximada, fotografías, observaciones y sincronización posterior.",
    image: "https://picsum.photos/seed/mobile-field/1200/800",
    gallery: [
      "https://picsum.photos/seed/mobile-field-1/1200/800",
      "https://picsum.photos/seed/mobile-field-2/1200/800",
      "https://picsum.photos/seed/mobile-field-3/1200/800",
    ],
    technologies: [3, 10, 6],
    github: {
      private: true,
      url: "",
    },
    liveUrl: "",
    challenge:
      "Mantener una experiencia útil con conectividad limitada y sincronizar información al recuperar acceso a red.",
    result:
      "Prototipo funcional para validar el flujo antes de construir la versión productiva.",
    client: "Demostración conceptual",
    hasVideo: true,
  },
  {
    id: 6,
    slug: "security-monitor",
    title: "Security Monitor",
    category: "security",
    categoryName: "Ciberseguridad",
    excerpt:
      "Panel conceptual para revisar eventos, accesos y alertas de infraestructura.",
    description:
      "Dashboard enfocado en visibilidad de eventos, autenticaciones, disponibilidad y señales básicas de riesgo en servicios internos.",
    image: "https://picsum.photos/seed/security-monitor/1200/800",
    gallery: [
      "https://picsum.photos/seed/security-monitor-1/1200/800",
      "https://picsum.photos/seed/security-monitor-2/1200/800",
      "https://picsum.photos/seed/security-monitor-3/1200/800",
    ],
    technologies: [1, 2, 3, 4, 6, 8, 12],
    github: {
      private: false,
      url: "https://github.com/",
    },
    liveUrl: "",
    challenge:
      "Consolidar señales de diferentes fuentes y presentarlas sin generar ruido operativo.",
    result:
      "Vista centralizada para detectar anomalías y priorizar acciones de revisión.",
    client: "Proyecto personal",
    hasVideo: false,
  },
];

const technologyGrid = document.getElementById("technologyGrid");
const projectFilters = document.getElementById("projectFilters");
const projectsGrid = document.getElementById("projectsGrid");
const projectModalElement = document.getElementById("projectModal");
const projectModal = new bootstrap.Modal(projectModalElement);

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function getTechnology(id) {
  return technologies.find((technology) => technology.id === id);
}

function renderTechnologies() {
  technologyGrid.innerHTML = technologies
    .map(
      (technology) => `
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3 tech-item">
            <article
              class="tech-card"
              style="--tech-color: ${escapeHtml(technology.color)}"
            >
              <div class="tech-icon" aria-hidden="true">
                <i class="${escapeHtml(technology.icon)}"></i>
              </div>
              <div>
                <h3 class="tech-name">${escapeHtml(technology.name)}</h3>
                <span class="tech-level">${escapeHtml(technology.level)}</span>
              </div>
            </article>
          </div>
        `,
    )
    .join("");
}

function renderFilters() {
  projectFilters.innerHTML = categories
    .map(
      (category, index) => `
          <button
            class="filter-btn ${index === 0 ? "active" : ""}"
            type="button"
            data-filter="${escapeHtml(category.id)}"
            aria-pressed="${index === 0 ? "true" : "false"}"
          >
            ${escapeHtml(category.name)}
          </button>
        `,
    )
    .join("");

  projectFilters.addEventListener("click", (event) => {
    const button = event.target.closest("[data-filter]");

    if (!button) {
      return;
    }

    projectFilters.querySelectorAll(".filter-btn").forEach((filterButton) => {
      const isActive = filterButton === button;
      filterButton.classList.toggle("active", isActive);
      filterButton.setAttribute("aria-pressed", String(isActive));
    });

    renderProjects(button.dataset.filter);
  });
}

function buildTechnologyBadges(technologyIds) {
  return technologyIds
    .map(getTechnology)
    .filter(Boolean)
    .map(
      (technology) => `
          <span
            class="project-tech"
            style="--tech-color: ${escapeHtml(technology.color)}"
            title="${escapeHtml(technology.name)}"
          >
            <i class="${escapeHtml(technology.icon)}" aria-hidden="true"></i>
            <span>${escapeHtml(technology.name)}</span>
          </span>
        `,
    )
    .join("");
}

function renderProjects(filter = "all") {
  const visibleProjects =
    filter === "all"
      ? projects
      : projects.filter((project) => project.category === filter);

  if (visibleProjects.length === 0) {
    projectsGrid.innerHTML = `
          <div class="col-12">
            <div class="empty-state">
              <i class="fa-solid fa-folder-open fa-2x mb-3"></i>
              <p class="mb-0">No existen proyectos registrados en esta categoría.</p>
            </div>
          </div>
        `;
    return;
  }

  projectsGrid.innerHTML = visibleProjects
    .map((project) => {
      const privateRepoMessageId = `privateRepoMessage-${project.id}`;

      return `
            <div class="col-md-6 col-xl-4 project-grid-item">
              <article
                class="project-card"
                tabindex="0"
                role="button"
                data-project-id="${project.id}"
                aria-label="Abrir detalles de ${escapeHtml(project.title)}"
              >
                <div class="project-media">
                  <img
                    src="${escapeHtml(project.image)}"
                    alt="Vista previa de ${escapeHtml(project.title)}"
                    loading="lazy"
                  >
                  <span class="project-category">${escapeHtml(project.categoryName)}</span>

                  <button
                    class="project-github ${project.github.private ? "is-private" : ""}"
                    type="button"
                    data-github-project="${project.id}"
                    aria-label="${
                      project.github.private
                        ? `Repositorio privado de ${escapeHtml(project.title)}`
                        : `Abrir GitHub de ${escapeHtml(project.title)}`
                    }"
                    title="${project.github.private ? "Repositorio privado" : "Abrir GitHub"}"
                  >
                    <i class="fa-brands fa-github"></i>
                    ${
                      project.github.private
                        ? '<span class="visually-hidden">Repositorio privado</span>'
                        : ""
                    }
                  </button>
                </div>

                <div class="project-body">
                  <h3 class="project-title">${escapeHtml(project.title)}</h3>
                  <p class="project-description">${escapeHtml(project.excerpt)}</p>
                  <div class="project-tech-list">
                    ${buildTechnologyBadges(project.technologies)}
                  </div>

                  <div
                    class="private-repo-message"
                    id="${privateRepoMessageId}"
                    role="status"
                  >
                    <i class="fa-solid fa-lock me-1"></i>
                    Oops, este repositorio es privado. Puedes revisar la galería de fotos de este proyecto.
                  </div>
                </div>
              </article>
            </div>
          `;
    })
    .join("");

  animateProjectCards();
}

function openProjectModal(projectId) {
  const project = projects.find((item) => item.id === Number(projectId));

  if (!project) {
    return;
  }

  document.getElementById("projectModalCategory").textContent =
    `// ${project.categoryName}`;

  document.getElementById("projectModalTitle").textContent = project.title;
  document.getElementById("projectModalDescription").textContent =
    project.description;
  document.getElementById("projectModalChallenge").textContent =
    project.challenge;
  document.getElementById("projectModalResult").textContent = project.result;

  const mainImage = document.getElementById("projectModalMainImage");
  mainImage.src = project.gallery[0] ?? project.image;
  mainImage.alt = `Galería de ${project.title}`;

  const gallery = document.getElementById("projectModalGallery");
  const galleryImages = [project.image, ...project.gallery].filter(
    (image, index, list) => list.indexOf(image) === index,
  );

  gallery.innerHTML = galleryImages
    .map(
      (image, index) => `
          <button
            class="gallery-thumb ${index === 0 ? "active" : ""}"
            type="button"
            data-gallery-image="${escapeHtml(image)}"
            aria-label="Mostrar imagen ${index + 1} de ${escapeHtml(project.title)}"
          >
            <img
              src="${escapeHtml(image)}"
              alt=""
              loading="lazy"
            >
          </button>
        `,
    )
    .join("");

  document.getElementById("projectModalTechnologies").innerHTML =
    buildTechnologyBadges(project.technologies);

  const clientBlock = document.getElementById("projectModalClientBlock");
  const clientText = document.getElementById("projectModalClient");

  if (project.client) {
    clientBlock.classList.remove("d-none");
    clientText.textContent = project.client;
  } else {
    clientBlock.classList.add("d-none");
    clientText.textContent = "";
  }

  const privateMessage = document.getElementById("projectModalPrivateMessage");
  privateMessage.classList.toggle("show", project.github.private);

  const githubLink = document.getElementById("projectModalGithub");
  githubLink.classList.toggle(
    "d-none",
    project.github.private || !project.github.url,
  );
  githubLink.href = project.github.url || "#";

  const liveLink = document.getElementById("projectModalLive");
  liveLink.classList.toggle("d-none", !project.liveUrl);
  liveLink.href = project.liveUrl || "#";

  const videoPlaceholder = document.getElementById("projectVideoPlaceholder");
  videoPlaceholder.classList.toggle("d-none", !project.hasVideo);

  projectModal.show();
}

projectsGrid.addEventListener("click", (event) => {
  const githubButton = event.target.closest("[data-github-project]");

  if (githubButton) {
    event.stopPropagation();

    const project = projects.find(
      (item) => item.id === Number(githubButton.dataset.githubProject),
    );

    if (!project) {
      return;
    }

    if (project.github.private || !project.github.url) {
      const message = document.getElementById(
        `privateRepoMessage-${project.id}`,
      );

      message?.classList.toggle("show");
      return;
    }

    window.open(project.github.url, "_blank", "noopener,noreferrer");
    return;
  }

  const card = event.target.closest("[data-project-id]");

  if (card) {
    openProjectModal(card.dataset.projectId);
  }
});

projectsGrid.addEventListener("keydown", (event) => {
  const card = event.target.closest("[data-project-id]");

  if (!card || (event.key !== "Enter" && event.key !== " ")) {
    return;
  }

  event.preventDefault();
  openProjectModal(card.dataset.projectId);
});

document
  .getElementById("projectModalGallery")
  .addEventListener("click", (event) => {
    const thumbnail = event.target.closest("[data-gallery-image]");

    if (!thumbnail) {
      return;
    }

    document
      .querySelectorAll("#projectModalGallery .gallery-thumb")
      .forEach((item) => item.classList.remove("active"));

    thumbnail.classList.add("active");
    document.getElementById("projectModalMainImage").src =
      thumbnail.dataset.galleryImage;
  });

function animateProjectCards() {
  if (
    !window.gsap ||
    window.matchMedia("(prefers-reduced-motion: reduce)").matches
  ) {
    return;
  }

  gsap.fromTo(
    ".project-grid-item",
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
    },
  );
}

function initializeAnimations() {
  const reduceMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  if (reduceMotion) {
    document.querySelectorAll(".terminal-line").forEach((line) => {
      line.style.opacity = "1";
      line.style.transform = "none";
    });
    return;
  }

  if (window.gsap) {
    gsap.registerPlugin(ScrollTrigger);

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
      )
      .to(
        ".terminal-line",
        {
          autoAlpha: 1,
          x: 0,
          duration: 0.25,
          stagger: 0.22,
        },
        "-=0.25",
      );

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
      });
    });

    document.querySelectorAll("[data-counter]").forEach((counter) => {
      const target = Number(counter.dataset.counter);
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
          counter.textContent = Math.round(state.value);
        },
      });
    });
  }

  if (window.anime) {
    anime({
      targets: ".floating-node",
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
      delay: anime.stagger(260),
    });

    const glitchElement = document.querySelector(".glitch");

    window.setInterval(() => {
      glitchElement.classList.add("is-glitching");

      anime({
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
}

function initializeNavigation() {
  const navLinks = document.querySelectorAll(".nav-link[href^='#']");
  const sections = [...navLinks]
    .map((link) => document.querySelector(link.getAttribute("href")))
    .filter(Boolean);

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

  sections.forEach((section) => observer.observe(section));

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      const navbarCollapse = document.getElementById("mainNavbar");

      if (navbarCollapse.classList.contains("show")) {
        bootstrap.Collapse.getOrCreateInstance(navbarCollapse).hide();
      }
    });
  });
}

function initializeContactForm() {
  const form = document.getElementById("contactForm");
  const toastElement = document.getElementById("contactToast");
  const toast = new bootstrap.Toast(toastElement, {
    delay: 4500,
  });

  const dateInput = document.getElementById("meetingDate");
  const today = new Date();
  const timezoneOffset = today.getTimezoneOffset() * 60000;
  dateInput.min = new Date(today.getTime() - timezoneOffset)
    .toISOString()
    .split("T")[0];

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      return;
    }

    form.classList.remove("was-validated");
    toast.show();
    form.reset();
  });
}

document.getElementById("currentYear").textContent = new Date().getFullYear();

renderTechnologies();
renderFilters();
renderProjects();
initializeAnimations();
initializeNavigation();
initializeContactForm();
