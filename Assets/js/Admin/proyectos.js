(() => {
  "use strict";

  const module = document.querySelector("#projectsModule");

  if (!module) {
    return;
  }

  const $ = (selector) => module.querySelector(selector);
  const tableBody = $("#projectsTableBody");
  const form = $("#projectForm");
  const csrfToken = form.querySelector('[name="csrf_token"]').value;
  const modal = new bootstrap.Modal($("#projectModal"));
  const statusModal = new bootstrap.Modal($("#projectStatusModal"));
  const toast = new bootstrap.Toast($("#projectNotification"));

  let catalogs = {
    estados: [],
    categorias: [],
    tecnologias: [],
    clientes: [],
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
      estados: data.estados || [],
      categorias: data.categorias || [],
      tecnologias: data.tecnologias || [],
      clientes: data.clientes || [],
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

  function resetForm() {
    form.reset();
    form.classList.remove("was-validated");
    $("#projectId").value = "0";
    $("#projectModalTitle").textContent = "Nuevo proyecto";
    $("#projectFormFeedback").classList.add("d-none");
    $("#projectOrder").value = "";
    $("#projectFeatured").checked = false;
    $("#projectGithub").value = "";
    $("#projectGithubPrivate").checked = false;
    selectValues($("#projectCategories"));
    selectValues($("#projectTechnologies"));

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
      $("#projectGithub").value = project.url_github || "";
      $("#projectGithubPrivate").checked = Number(project.github_privado) === 1;
      $("#projectState").value = project.id_estado_proyecto || "";
      $("#projectClient").value = project.id_cliente || "0";
      $("#projectStartDate").value = project.fecha_inicio || "";
      $("#projectEndDate").value = project.fecha_fin || "";
      $("#projectOrder").value = project.orden_visualizacion ?? "";
      $("#projectFeatured").checked = Number(project.destacado) === 1;
      selectValues($("#projectCategories"), project.categorias || []);
      selectValues($("#projectTechnologies"), project.tecnologias || []);
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
    form.classList.add("was-validated");

    if (!form.checkValidity()) {
      return;
    }

    const button = $("#btnSaveProject");
    const original = button.innerHTML;
    const data = new FormData(form);
    data.set("destacado", $("#projectFeatured").checked ? "1" : "0");
    data.set("github_privado", $("#projectGithubPrivate").checked ? "1" : "0");
    button.disabled = true;
    button.innerHTML =
      '<i class="fa-solid fa-circle-notch fa-spin"></i> Guardando...';

    try {
      const result = await request(module.dataset.saveUrl, {
        method: "POST",
        body: data,
      });
      modal.hide();
      notify(result.msg);
      await loadProjects();
    } catch (error) {
      const feedback = $("#projectFormFeedback");
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
      await loadProjects();
    } catch (error) {
      tableBody.innerHTML = `
                <tr><td class="text-center py-5 text-danger" colspan="7">
                    ${escapeHtml(error.message)}
                </td></tr>`;
    }
  })();
})();
