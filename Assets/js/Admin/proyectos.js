(() => {
  "use strict";

  const module = document.querySelector("#projectsModule");

  if (!module) {
    return;
  }

  const $ = (selector) => module.querySelector(selector);
  const tableBody = $("#projectsTableBody");
  const form = $("#projectForm");

  const projectLinksList = $("#projectLinksList");
  const projectLinksEmptyState = $("#projectLinksEmptyState");
  const projectLinkTemplate = $("#projectLinkTemplate");
  const btnAddProjectLink = $("#btnAddProjectLink");

  const csrfToken = form.querySelector('[name="csrf_token"]').value;

  const MAX_PROJECT_LINKS = 20;
  const modal = new bootstrap.Modal($("#projectModal"));
  const statusModal = new bootstrap.Modal($("#projectStatusModal"));
  const toast = new bootstrap.Toast($("#projectNotification"));

  let catalogs = {
    estados: [],
    categorias: [],
    tecnologias: [],
    clientes: [],
    tipos_enlace: [],
  };
  let pendingStatus = null;
  let searchTimer = null;

  function escapeHtml(value) {
    return String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function slugify(value) {
    return String(value ?? "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }
  function safeIconClass(value) {
    const classes = String(value ?? "")
      .split(/\s+/)
      .map((item) => item.trim())
      .filter((item) => /^[a-zA-Z0-9_-]+$/.test(item));

    return classes.length ? classes.join(" ") : "fa-solid fa-link";
  }
  async function request(url, options = {}) {
    const response = await fetch(url, {
      ...options,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        ...(options.headers || {}),
      },
    });

    const text = await response.text();
    let data = {};

    try {
      data = text ? JSON.parse(text) : {};
    } catch (error) {
      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    if (!response.ok || data.status === false) {
      throw new Error(data.msg || "No fue posible completar la operación.");
    }

    return data;
  }

  function notify(message, type = "success") {
    const icon = $("#projectNotificationIcon");
    $("#projectNotificationMessage").textContent = message;
    icon.className =
      type === "error"
        ? "fa-solid fa-circle-exclamation text-danger me-2"
        : "fa-solid fa-circle-check text-success me-2";
    toast.show();
  }

  function badges(values, className = "text-bg-secondary") {
    if (!values) {
      return '<span class="text-body-secondary">Sin asignar</span>';
    }

    return values
      .split("||")
      .map(
        (value) =>
          `<span class="badge ${className} me-1 mb-1">${escapeHtml(value)}</span>`,
      )
      .join("");
  }

  function technologyBadges(values) {
    if (!values) {
      return '<span class="text-body-secondary">Sin asignar</span>';
    }

    return values
      .split("||")
      .map((item) => {
        const [name, color = "#A8AEC5"] = item.split("::");
        const safeColor = /^#[0-9A-Fa-f]{6}$/.test(color) ? color : "#A8AEC5";

        return `
                <span class="badge me-1 mb-1"
                      style="color:${safeColor};background:${safeColor}18;border:1px solid ${safeColor}66">
                    ${escapeHtml(name)}
                </span>`;
      })
      .join("");
  }

  function renderRows(projects) {
    const emptyState = $("#projectsEmptyState");
    emptyState.hidden = projects.length > 0;

    if (!projects.length) {
      tableBody.innerHTML = "";
      return;
    }

    tableBody.innerHTML = projects
      .map((project) => {
        const available = !project.eliminado_en;
        const featured = Number(project.destacado) === 1;
        const stateColor = /^#[0-9A-Fa-f]{6}$/.test(project.estado_color || "")
          ? project.estado_color
          : "#A8AEC5";

        return `
                <tr class="${available ? "" : "opacity-75"}">
                    <td style="min-width:250px">
                        <div class="d-flex align-items-start gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded flex-shrink-0"
                                  style="width:42px;height:42px;color:${stateColor};background:${stateColor}18;border:1px solid ${stateColor}66">
                                <i class="fa-solid fa-diagram-project"></i>
                            </span>
                            <div>
                                <strong>${escapeHtml(project.titulo)}</strong>
                                <code class="d-block mt-1">${escapeHtml(project.slug)}</code>
                                <small class="text-body-secondary d-block mt-1">
                                    ${escapeHtml(project.resumen_corto)}
                                </small>
                                ${
                                  project.cliente_nombre
                                    ? `<small class="d-block mt-1"><i class="fa-solid fa-building me-1"></i>${escapeHtml(project.cliente_nombre)}</small>`
                                    : ""
                                }
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge"
                              style="color:${stateColor};background:${stateColor}18;border:1px solid ${stateColor}66">
                            ${escapeHtml(project.estado_nombre)}
                        </span>
                        ${
                          available
                            ? ""
                            : '<span class="badge text-bg-danger d-block mt-2">Baja lógica</span>'
                        }
                    </td>
                    <td style="min-width:160px">${badges(project.categorias, "text-bg-secondary")}</td>
                    <td style="min-width:190px">${technologyBadges(project.tecnologias)}</td>
                    <td class="text-center">
                        <i class="fa-solid ${featured ? "fa-star text-warning" : "fa-minus text-body-secondary"}"></i>
                    </td>
                    <td class="text-nowrap">${escapeHtml(project.actualizado_en || "")}</td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-outline-info"
                                type="button"
                                data-action="edit"
                                data-id="${project.id_proyecto}"
                                title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm ${available ? "btn-outline-danger" : "btn-outline-success"}"
                                type="button"
                                data-action="status"
                                data-id="${project.id_proyecto}"
                                data-name="${escapeHtml(project.titulo)}"
                                data-available="${available ? 1 : 0}"
                                title="${available ? "Dar de baja" : "Restaurar"}">
                            <i class="fa-solid ${available ? "fa-box-archive" : "fa-rotate-left"}"></i>
                        </button>
                    </td>
                </tr>`;
      })
      .join("");
  }

  function updateSummary(summary = {}) {
    $("#totalProjects").textContent = Number(summary.total) || 0;
    $("#activeProjects").textContent = Number(summary.activos) || 0;
    $("#publishedProjects").textContent = Number(summary.publicados) || 0;
    $("#deletedProjects").textContent = Number(summary.bajas) || 0;
  }

  function option(value, label, selected = false) {
    return `<option value="${value}" ${selected ? "selected" : ""}>${escapeHtml(label)}</option>`;
  }

  function renderCatalogs() {
    const stateFilter = $("#projectStateFilter");
    const projectState = $("#projectState");
    const projectClient = $("#projectClient");
    const categories = $("#projectCategories");
    const technologies = $("#projectTechnologies");

    stateFilter.innerHTML =
      '<option value="0">Todos</option>' +
      catalogs.estados
        .map((state) => option(state.id_estado_proyecto, state.nombre))
        .join("");

    projectState.innerHTML = catalogs.estados
      .map((state) =>
        option(
          state.id_estado_proyecto,
          state.nombre,
          state.slug === "borrador",
        ),
      )
      .join("");

    projectClient.innerHTML =
      '<option value="0">Proyecto personal / sin cliente</option>' +
      catalogs.clientes
        .map((client) => option(client.id_cliente, client.nombre_mostrar))
        .join("");

    categories.innerHTML = catalogs.categorias
      .map((category) =>
        option(
          category.id_categoria,
          `${category.nombre}${Number(category.activa) === 1 ? "" : " (Inactiva)"}`,
        ),
      )
      .join("");

    technologies.innerHTML = catalogs.tecnologias
      .map((technology) =>
        option(
          technology.id_tecnologia,
          `${technology.nombre}${Number(technology.activa) === 1 ? "" : " (Inactiva)"}`,
        ),
      )
      .join("");
  }

  async function loadCatalogs() {
    const data = await request(module.dataset.catalogUrl);

    catalogs = {
      estados: Array.isArray(data.estados) ? data.estados : [],
      categorias: Array.isArray(data.categorias) ? data.categorias : [],
      tecnologias: Array.isArray(data.tecnologias) ? data.tecnologias : [],
      clientes: Array.isArray(data.clientes) ? data.clientes : [],
      tipos_enlace: Array.isArray(data.tipos_enlace) ? data.tipos_enlace : [],
    };

    renderCatalogs();
  }

  async function loadProjects() {
    const params = new URLSearchParams({
      busqueda: $("#projectSearch").value.trim(),
      estado: $("#projectStateFilter").value,
      registro: $("#projectRecordFilter").value,
    });

    tableBody.innerHTML = `
            <tr><td class="text-center py-5" colspan="7">
                <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                Cargando proyectos...
            </td></tr>`;

    try {
      const data = await request(`${module.dataset.listUrl}?${params}`);
      renderRows(data.proyectos || []);
      updateSummary(data.resumen);
    } catch (error) {
      tableBody.innerHTML = `
                <tr><td class="text-center py-5 text-danger" colspan="7">
                    ${escapeHtml(error.message)}
                </td></tr>`;
    }
  }

  function selectValues(select, values = []) {
    const selected = values.map(Number);

    Array.from(select.options).forEach((item) => {
      item.selected = selected.includes(Number(item.value));
    });
  }

  function updateSummaryCounter() {
    $("#projectSummaryCounter").textContent =
      `${$("#projectSummary").value.length} / 350`;
  }

  function validateDates() {
    const start = $("#projectStartDate");
    const end = $("#projectEndDate");
    end.setCustomValidity(
      start.value && end.value && end.value < start.value
        ? "La fecha final no puede ser anterior al inicio."
        : "",
    );
  }
  function getProjectLinkItems() {
    return Array.from(projectLinksList.querySelectorAll("[data-project-link]"));
  }

  function getLinkType(idType) {
    const id = Number(idType);

    return (
      catalogs.tipos_enlace.find(
        (type) => Number(type.id_tipo_enlace) === id,
      ) || null
    );
  }

  function populateLinkTypeSelect(select, selectedValue = "") {
    select.innerHTML = "";
    select.append(new Option("Selecciona un tipo", ""));

    catalogs.tipos_enlace.forEach((type) => {
      select.append(
        new Option(type.nombre || "Enlace", String(type.id_tipo_enlace || "")),
      );
    });

    select.value = selectedValue ? String(selectedValue) : "";
  }

  function validateProjectLink(item) {
    const typeField = item.querySelector('[data-link-field="type"]');
    const urlField = item.querySelector('[data-link-field="url"]');
    const privateField = item.querySelector('[data-link-field="private"]');

    const type = getLinkType(typeField.value);
    const url = urlField.value.trim();
    const isPrivate = privateField.checked;

    urlField.required = !isPrivate;
    urlField.setCustomValidity("");

    if (!url) {
      if (!isPrivate) {
        urlField.setCustomValidity("Captura la URL del enlace.");
      }

      return urlField.checkValidity();
    }

    let parsedUrl;

    try {
      parsedUrl = new URL(url);
    } catch (error) {
      urlField.setCustomValidity("Captura una URL completa y válida.");
      return false;
    }

    if (!["http:", "https:"].includes(parsedUrl.protocol.toLowerCase())) {
      urlField.setCustomValidity("La URL debe utilizar HTTP o HTTPS.");
      return false;
    }

    if (type?.slug === "github") {
      const hostname = parsedUrl.hostname.toLowerCase();

      if (!["github.com", "www.github.com"].includes(hostname)) {
        urlField.setCustomValidity(
          "El enlace de GitHub debe pertenecer a github.com.",
        );
        return false;
      }
    }

    return urlField.checkValidity();
  }

  function updateProjectLinkPresentation(item, autofillLabel = false) {
    const typeField = item.querySelector('[data-link-field="type"]');
    const labelField = item.querySelector('[data-link-field="label"]');
    const urlField = item.querySelector('[data-link-field="url"]');
    const privateField = item.querySelector('[data-link-field="private"]');

    const title = item.querySelector("[data-link-title]");
    const description = item.querySelector("[data-link-description]");
    const icon = item.querySelector("[data-link-icon]");
    const help = item.querySelector("[data-link-help]");

    const type = getLinkType(typeField.value);
    const previousAutoLabel = labelField.dataset.autoLabel || "";

    if (
      autofillLabel &&
      type &&
      (!labelField.value.trim() ||
        labelField.value.trim() === previousAutoLabel)
    ) {
      labelField.value = type.nombre || "Enlace";
      labelField.dataset.autoLabel = labelField.value;
    }

    title.textContent =
      labelField.value.trim() || type?.nombre || "Nuevo enlace";

    icon.className = safeIconClass(type?.clase_icono);

    if (!type) {
      description.textContent = "Configura el destino y el texto del botón.";

      urlField.placeholder = "https://ejemplo.com";

      help.textContent =
        "Utiliza una dirección completa que comience con https:// o http://.";
    } else if (type.slug === "github") {
      description.textContent = privateField.checked
        ? "Repositorio privado; la URL no se mostrará públicamente."
        : "Repositorio público del código fuente.";

      urlField.placeholder = "https://github.com/usuario/repositorio";

      help.textContent = "Para GitHub se acepta únicamente github.com.";
    } else {
      description.textContent = privateField.checked
        ? `${type.nombre}: enlace privado, oculto en la vista pública.`
        : `${type.nombre}: enlace visible en la vista pública.`;

      urlField.placeholder = "https://ejemplo.com/recurso";

      help.textContent =
        "Utiliza una dirección completa que comience con https:// o http://.";
    }

    validateProjectLink(item);
  }

  function syncProjectLinksState() {
    const items = getProjectLinkItems();
    const count = items.length;
    const hasLinkTypes = catalogs.tipos_enlace.length > 0;

    projectLinksEmptyState.hidden = count > 0;
    projectLinksList.hidden = count === 0;

    btnAddProjectLink.disabled = !hasLinkTypes || count >= MAX_PROJECT_LINKS;

    if (!hasLinkTypes) {
      btnAddProjectLink.title = "No existen tipos de enlace disponibles.";
    } else if (count >= MAX_PROJECT_LINKS) {
      btnAddProjectLink.title = `El límite es de ${MAX_PROJECT_LINKS} enlaces.`;
    } else {
      btnAddProjectLink.title = "Agregar enlace";
    }
  }

  function reindexProjectLinks() {
    const fieldNames = {
      type: "id_tipo_enlace",
      label: "etiqueta",
      order: "orden_visualizacion",
      url: "url",
      private: "es_privado",
    };

    getProjectLinkItems().forEach((item, index) => {
      item.dataset.linkIndex = String(index);

      item.querySelector("[data-link-number]").textContent = String(index + 1);

      Object.entries(fieldNames).forEach(([fieldKey, fieldName]) => {
        const field = item.querySelector(`[data-link-field="${fieldKey}"]`);

        const label = item.querySelector(`[data-link-label="${fieldKey}"]`);

        if (!field) {
          return;
        }

        const formattedField =
          fieldKey.charAt(0).toUpperCase() + fieldKey.slice(1);

        const fieldId = `projectLink${index}${formattedField}`;

        field.name = `enlaces[${index}][${fieldName}]`;

        field.id = fieldId;

        if (label) {
          label.htmlFor = fieldId;
        }
      });
    });

    syncProjectLinksState();
  }

  function createProjectLink(link = {}, focus = false) {
    if (getProjectLinkItems().length >= MAX_PROJECT_LINKS) {
      notify(
        `Un proyecto no puede tener más de ${MAX_PROJECT_LINKS} enlaces.`,
        "error",
      );

      return null;
    }

    if (!catalogs.tipos_enlace.length) {
      notify("No existen tipos de enlace disponibles.", "error");

      return null;
    }

    const fragment = projectLinkTemplate.content.cloneNode(true);

    const item = fragment.querySelector("[data-project-link]");

    const typeField = item.querySelector('[data-link-field="type"]');

    const labelField = item.querySelector('[data-link-field="label"]');

    const orderField = item.querySelector('[data-link-field="order"]');

    const urlField = item.querySelector('[data-link-field="url"]');

    const privateField = item.querySelector('[data-link-field="private"]');

    populateLinkTypeSelect(typeField, link.id_tipo_enlace ?? "");

    labelField.value = link.etiqueta ?? "";

    orderField.value =
      link.orden_visualizacion === null ||
      link.orden_visualizacion === undefined
        ? ""
        : String(link.orden_visualizacion);

    urlField.value = link.url ?? "";

    privateField.checked = Number(link.es_privado) === 1;

    projectLinksList.append(item);

    reindexProjectLinks();
    updateProjectLinkPresentation(item, false);

    if (focus) {
      typeField.focus();
    }

    return item;
  }

  function clearProjectLinks() {
    projectLinksList.innerHTML = "";
    reindexProjectLinks();
  }

  function renderProjectLinks(links = []) {
    clearProjectLinks();

    if (!Array.isArray(links)) {
      return;
    }

    links.slice(0, MAX_PROJECT_LINKS).forEach((link) => {
      createProjectLink(link);
    });

    reindexProjectLinks();
  }

  function validateProjectLinks() {
    return getProjectLinkItems().every((item) => validateProjectLink(item));
  }

  function serializeProjectLinks() {
    return getProjectLinkItems().map((item, index) => {
      const typeField = item.querySelector('[data-link-field="type"]');

      const labelField = item.querySelector('[data-link-field="label"]');

      const orderField = item.querySelector('[data-link-field="order"]');

      const urlField = item.querySelector('[data-link-field="url"]');

      const privateField = item.querySelector('[data-link-field="private"]');

      const orderValue = orderField.value.trim();

      return {
        id_tipo_enlace: Number(typeField.value) || 0,

        etiqueta: labelField.value.trim(),

        url: urlField.value.trim(),

        es_privado: privateField.checked ? 1 : 0,

        orden_visualizacion:
          orderValue === "" ? index + 1 : Math.max(0, Number(orderValue) || 0),
      };
    });
  }

  function replaceNestedLinksWithJson(formData) {
    const keys = Array.from(formData.keys());

    keys.forEach((key) => {
      if (key === "enlaces" || key.startsWith("enlaces[")) {
        formData.delete(key);
      }
    });

    formData.set("enlaces", JSON.stringify(serializeProjectLinks()));
  }
  function resetForm() {
    form.reset();
    form.classList.remove("was-validated");

    $("#projectId").value = "0";
    $("#projectModalTitle").textContent = "Nuevo proyecto";

    $("#projectFormFeedback").textContent = "";
    $("#projectFormFeedback").classList.add("d-none");

    $("#projectOrder").value = "";
    $("#projectFeatured").checked = false;

    /*
     * Campos heredados. Ya no administran los enlaces,
     * pero se mantienen vacíos mientras existan en la vista.
     */
    $("#projectGithub").value = "";
    $("#projectGithubPrivate").value = "0";

    selectValues($("#projectCategories"));
    selectValues($("#projectTechnologies"));

    clearProjectLinks();

    const draft = catalogs.estados.find((state) => state.slug === "borrador");

    if (draft) {
      $("#projectState").value = draft.id_estado_proyecto;
    }

    updateSummaryCounter();
    validateDates();
  }

  async function editProject(id) {
    try {
      const project = await request(`${module.dataset.getUrl}/${id}`);

      resetForm();

      $("#projectId").value = project.id_proyecto;

      $("#projectTitle").value = project.titulo || "";

      $("#projectSlug").value = project.slug || "";

      $("#projectSummary").value = project.resumen_corto || "";

      $("#projectDescription").value = project.descripcion || "";

      $("#projectChallenge").value = project.reto_tecnico || "";

      $("#projectResult").value = project.resultado || "";

      $("#projectState").value = project.id_estado_proyecto || "";

      $("#projectClient").value = project.id_cliente || "0";

      $("#projectStartDate").value = project.fecha_inicio || "";

      $("#projectEndDate").value = project.fecha_fin || "";

      $("#projectOrder").value = project.orden_visualizacion ?? "";

      $("#projectFeatured").checked = Number(project.destacado) === 1;

      selectValues($("#projectCategories"), project.categorias || []);

      selectValues($("#projectTechnologies"), project.tecnologias || []);

      let projectLinks = Array.isArray(project.enlaces) ? project.enlaces : [];

      /*
       * Compatibilidad temporal por si el backend anterior
       * solamente devuelve url_github.
       */
      if (
        projectLinks.length === 0 &&
        (project.url_github || Number(project.github_privado) === 1)
      ) {
        const githubType = catalogs.tipos_enlace.find(
          (type) => type.slug === "github",
        );

        if (githubType) {
          projectLinks = [
            {
              id_tipo_enlace: githubType.id_tipo_enlace,

              etiqueta: "GitHub",

              url: project.url_github || "",

              es_privado: Number(project.github_privado) === 1 ? 1 : 0,

              orden_visualizacion: 1,
            },
          ];
        }
      }

      renderProjectLinks(projectLinks);

      $("#projectModalTitle").textContent = "Editar proyecto";

      updateSummaryCounter();
      validateDates();
      modal.show();
    } catch (error) {
      notify(error.message, "error");
    }
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    validateDates();
    validateProjectLinks();

    form.classList.add("was-validated");

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const button = $("#btnSaveProject");
    const original = button.innerHTML;
    const feedback = $("#projectFormFeedback");
    const data = new FormData(form);

    data.set("destacado", $("#projectFeatured").checked ? "1" : "0");

    /*
     * Elimina los nombres enlaces[0][...] agregados
     * automáticamente por FormData y envía una sola
     * colección JSON.
     *
     * Esto también permite enviar [] cuando el usuario
     * elimina todos los enlaces.
     */
    replaceNestedLinksWithJson(data);

    /*
     * Neutraliza los campos antiguos para que no
     * interfieran con enlaces.
     */
    data.set("url_github", "");
    data.set("github_privado", "0");

    feedback.textContent = "";
    feedback.classList.add("d-none");

    button.disabled = true;
    button.innerHTML =
      '<i class="fa-solid fa-circle-notch fa-spin"></i> Guardando...';

    try {
      const result = await request(module.dataset.saveUrl, {
        method: "POST",
        body: data,
      });

      modal.hide();

      notify(result.msg || "Proyecto guardado correctamente.");

      await loadProjects();
    } catch (error) {
      feedback.textContent = error.message;
      feedback.classList.remove("d-none");
    } finally {
      button.disabled = false;
      button.innerHTML = original;
    }
  });

  tableBody.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-action]");

    if (!button) {
      return;
    }

    if (button.dataset.action === "edit") {
      editProject(button.dataset.id);
      return;
    }

    pendingStatus = {
      id: button.dataset.id,
      name: button.dataset.name,
      available: button.dataset.available === "1",
    };

    $("#projectStatusMessage").textContent = pendingStatus.available
      ? `¿Deseas dar de baja el proyecto ${pendingStatus.name}?`
      : `¿Deseas restaurar el proyecto ${pendingStatus.name}?`;
    statusModal.show();
  });

  $("#btnConfirmProjectStatus").addEventListener("click", async () => {
    if (!pendingStatus) {
      return;
    }

    const data = new FormData();
    data.set("csrf_token", csrfToken);
    data.set("id_proyecto", pendingStatus.id);
    data.set("activo", pendingStatus.available ? "0" : "1");

    try {
      const result = await request(module.dataset.statusUrl, {
        method: "POST",
        body: data,
      });
      statusModal.hide();
      notify(result.msg);
      pendingStatus = null;
      await loadProjects();
    } catch (error) {
      notify(error.message, "error");
    }
  });
  btnAddProjectLink.addEventListener("click", () => {
    createProjectLink({}, true);
  });

  projectLinksList.addEventListener("click", (event) => {
    const removeButton = event.target.closest("[data-remove-project-link]");

    if (!removeButton) {
      return;
    }

    const item = removeButton.closest("[data-project-link]");

    if (item) {
      item.remove();
      reindexProjectLinks();
    }
  });

  projectLinksList.addEventListener("change", (event) => {
    const field = event.target.closest("[data-link-field]");

    if (!field) {
      return;
    }

    const item = field.closest("[data-project-link]");

    if (!item) {
      return;
    }

    const isTypeChange = field.dataset.linkField === "type";

    updateProjectLinkPresentation(item, isTypeChange);
  });

  projectLinksList.addEventListener("input", (event) => {
    const field = event.target.closest("[data-link-field]");

    if (!field) {
      return;
    }

    const item = field.closest("[data-project-link]");

    if (!item) {
      return;
    }

    if (field.dataset.linkField === "label") {
      const autoLabel = field.dataset.autoLabel || "";

      if (field.value.trim() !== autoLabel) {
        delete field.dataset.autoLabel;
      }
    }

    if (["label", "url"].includes(field.dataset.linkField)) {
      updateProjectLinkPresentation(item, false);
    }
  });
  $("#btnNewProject").addEventListener("click", () => {
    resetForm();
    modal.show();
  });
  $("#btnGenerateProjectSlug").addEventListener("click", () => {
    $("#projectSlug").value = slugify($("#projectTitle").value);
  });
  $("#projectSummary").addEventListener("input", updateSummaryCounter);
  $("#projectStartDate").addEventListener("change", validateDates);
  $("#projectEndDate").addEventListener("change", validateDates);
  $("#btnRefreshProjects").addEventListener("click", loadProjects);
  $("#projectStateFilter").addEventListener("change", loadProjects);
  $("#projectRecordFilter").addEventListener("change", loadProjects);
  $("#projectSearch").addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadProjects, 300);
  });

  (async () => {
    try {
      await loadCatalogs();
      resetForm();
      await loadProjects();
    } catch (error) {
      tableBody.innerHTML = `
                <tr><td class="text-center py-5 text-danger" colspan="7">
                    ${escapeHtml(error.message)}
                </td></tr>`;
    }
  })();
})();
