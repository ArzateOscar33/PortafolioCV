(() => {
  "use strict";

  const module = document.getElementById("messagesModule");

  if (!module) {
    return;
  }

  const $ = (selector, parent = document) => parent.querySelector(selector);

  const tableBody = $("#messagesTableBody");
  const detailElement = $("#messageDetailModal");
  const stateElement = $("#messageStateModal");
  const contactElement = $("#messageContactModal");

  const detailModal = bootstrap.Modal.getOrCreateInstance(detailElement);

  const stateModal = bootstrap.Modal.getOrCreateInstance(stateElement);

  const contactModal = bootstrap.Modal.getOrCreateInstance(contactElement);

  const notification = bootstrap.Toast.getOrCreateInstance(
    $("#messageNotification"),
    {
      delay: 4500,
    },
  );

  let states = [];
  let services = [];
  let contacts = [];
  let currentMessage = null;
  let searchTimer = null;

  async function request(url, options = {}) {
    const response = await fetch(url, {
      credentials: "same-origin",
      ...options,
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(options.headers || {}),
      },
    });

    const rawResponse = await response.text();
    let data;

    try {
      data = JSON.parse(rawResponse);
    } catch (error) {
      console.error(
        "[Mensajes] Respuesta no JSON:",
        response.status,
        rawResponse,
      );

      throw new Error(
        `El servidor devolvió una respuesta no válida ` +
          `(HTTP ${response.status}). Revisa la consola y error.log.`,
      );
    }

    if (!response.ok || data.status === false) {
      const message = data.detalle
        ? `${data.msg}\n\nDetalle técnico: ${data.detalle}`
        : data.msg || "No fue posible completar la operación.";

      console.error("[Mensajes]", {
        status: response.status,
        response: data,
        url,
      });

      throw new Error(message);
    }

    return data;
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function formatDate(value, includeTime = true) {
    if (!value) {
      return "Sin fecha";
    }

    const date = new Date(String(value).replace(" ", "T"));

    if (Number.isNaN(date.getTime())) {
      return String(value);
    }

    return new Intl.DateTimeFormat(
      "es-MX",
      includeTime
        ? {
            dateStyle: "medium",
            timeStyle: "short",
          }
        : {
            dateStyle: "medium",
          },
    ).format(date);
  }

  function stateLabel(state) {
    const item = states.find((candidate) => candidate.slug === state);

    if (item) {
      return item.nombre;
    }

    return String(state || "Sin estado").replaceAll("_", " ");
  }

  function stateBadge(state) {
    const classes = {
      nueva: "text-bg-danger",
      leida: "text-bg-info",
      en_seguimiento: "text-bg-warning",
      respondida: "text-bg-success",
      cerrada: "text-bg-dark",
      archivada: "text-bg-secondary",
      spam: "text-bg-danger",
    };

    return `
            <span class="badge ${classes[state] || "text-bg-secondary"}">
                ${escapeHtml(stateLabel(state))}
            </span>
        `;
  }

  function modalityLabel(value) {
    const labels = {
      en_linea: "En línea",
      online: "En línea",
      presencial: "Presencial",
      onsite: "Presencial",
      cualquiera: "Cualquiera",
      either: "Cualquiera",
    };

    return labels[value] || value || "Sin preferencia";
  }

  function originLabel(value) {
    const labels = {
      formulario_web: "Formulario web",
      manual: "Registro manual",
      referido: "Referido",
      otro: "Otro",
    };

    return labels[value] || value || "Sin origen";
  }

  function notify(message, success = true) {
    $("#messageNotificationTitle").textContent = success
      ? "Operación completada"
      : "No fue posible completar la operación";

    $("#messageNotificationBody").textContent = message;

    const icon = $("#messageNotificationIcon");
    icon.className = success
      ? "fa-solid fa-circle-check text-success me-2"
      : "fa-solid fa-triangle-exclamation text-danger me-2";

    notification.show();
  }

  function setLoading() {
    tableBody.innerHTML = `
            <tr>
                <td class="text-center py-5" colspan="6">
                    <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                    Cargando mensajes...
                </td>
            </tr>
        `;
  }

  function truncate(value, maximum = 90) {
    const text = String(value || "").trim();

    if (text.length <= maximum) {
      return text;
    }

    return `${text.slice(0, maximum).trim()}…`;
  }

  function renderMessages(messages) {
    const emptyState = $("#messagesEmptyState");

    if (!messages.length) {
      tableBody.innerHTML = "";
      emptyState.hidden = false;
      return;
    }

    emptyState.hidden = true;

    tableBody.innerHTML = messages
      .map((message) => {
        const isNew = message.estado === "nueva";
        const linked = message.contacto_nombre
          ? `
                    <div>
                        <strong>${escapeHtml(message.contacto_nombre)}</strong>
                        <div class="small text-body-secondary">
                            ${escapeHtml(
                              message.cliente_nombre || "Cliente vinculado",
                            )}
                        </div>
                    </div>
                `
          : `
                    <span class="text-body-secondary small">
                        Sin vincular
                    </span>
                `;

        const appointment =
          Number(message.tiene_cita) === 1
            ? `
                    <span
                        class="badge text-bg-primary ms-1"
                        title="Tiene una cita vinculada">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>
                `
            : "";

        return `
                <tr class="${isNew ? "fw-semibold" : ""}">
                    <td>
                        <div class="d-flex align-items-start gap-3">
                            <span
                                class="d-inline-flex align-items-center
                                       justify-content-center fs-4">
                                <i class="fa-solid ${
                                  isNew
                                    ? "fa-envelope text-danger"
                                    : "fa-envelope-open text-body-secondary"
                                }"></i>
                            </span>

                            <div>
                                <strong>
                                    ${escapeHtml(message.nombre_completo)}
                                </strong>

                                <div class="small text-body-secondary">
                                    ${escapeHtml(message.correo)}
                                </div>

                                <div class="small text-body-secondary">
                                    ${escapeHtml(
                                      message.nombre_empresa || "Sin empresa",
                                    )}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <strong>
                            ${escapeHtml(message.asunto || "Sin asunto")}
                        </strong>

                        <div class="small text-body-secondary mt-1">
                            ${escapeHtml(
                              message.servicio_nombre || "Servicio general",
                            )}
                        </div>

                        <div class="small mt-1">
                            ${escapeHtml(truncate(message.mensaje, 100))}
                        </div>
                    </td>

                    <td>
                        ${stateBadge(message.estado)}
                        ${appointment}
                    </td>

                    <td>
                        ${linked}
                    </td>

                    <td class="small text-body-secondary">
                        ${escapeHtml(formatDate(message.creado_en))}
                    </td>

                    <td>
                        <div
                            class="d-flex justify-content-end
                                   gap-1 flex-wrap">

                            <button
                                class="btn btn-sm btn-outline-primary"
                                type="button"
                                data-action="view"
                                data-id="${Number(
                                  message.id_solicitud_contacto,
                                )}"
                                title="Abrir mensaje">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button
                                class="btn btn-sm btn-outline-secondary"
                                type="button"
                                data-action="state"
                                data-id="${Number(
                                  message.id_solicitud_contacto,
                                )}"
                                data-state="${escapeHtml(message.estado)}"
                                title="Cambiar estado">
                                <i class="fa-solid fa-list-check"></i>
                            </button>

                            <a
                                class="btn btn-sm btn-outline-success"
                                href="mailto:${encodeURIComponent(
                                  message.correo,
                                )}?subject=${encodeURIComponent(
                                  `Re: ${message.asunto || "Tu solicitud"}`,
                                )}"
                                title="Responder por correo">
                                <i class="fa-solid fa-reply"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
      })
      .join("");
  }

  function updateSidebarBadge(pending) {
    const badge = document.querySelector(
      'a[href$="admin/mensajes"] .admin-nav__badge',
    );

    if (!badge) {
      return;
    }

    badge.textContent = String(pending || 0);
    badge.hidden = Number(pending || 0) === 0;
  }

  async function loadMessages() {
    setLoading();

    const params = new URLSearchParams({
      busqueda: $("#messageSearch").value.trim(),
      estado: $("#messageStateFilter").value,
      id_servicio: $("#messageServiceFilter").value,
      origen: $("#messageOriginFilter").value,
      fecha_desde: $("#messageDateFrom").value,
      fecha_hasta: $("#messageDateTo").value,
    });

    try {
      const data = await request(
        `${module.dataset.listUrl}?${params.toString()}`,
      );

      renderMessages(data.mensajes || []);

      $("#totalMessages").textContent = data.resumen?.total ?? 0;

      $("#newMessages").textContent = data.resumen?.nuevas ?? 0;

      $("#trackingMessages").textContent = data.resumen?.seguimiento ?? 0;

      $("#answeredMessages").textContent = data.resumen?.respondidas ?? 0;

      updateSidebarBadge(data.resumen?.pendientes ?? 0);
    } catch (error) {
      tableBody.innerHTML = `
                <tr>
                    <td
                        class="text-center py-5 text-danger"
                        colspan="6">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;

      notify(error.message, false);
    }
  }

  function populateCatalogs() {
    const stateFilter = $("#messageStateFilter");
    const stateSelect = $("#messageState");
    const serviceFilter = $("#messageServiceFilter");
    const contactSelect = $("#messageContact");

    stateFilter.innerHTML = `
            <option value="all">Todos</option>
            ${states
              .map(
                (state) => `
                <option value="${escapeHtml(state.slug)}">
                    ${escapeHtml(state.nombre)}
                </option>
            `,
              )
              .join("")}
        `;

    stateSelect.innerHTML = states
      .map(
        (state) => `
            <option value="${escapeHtml(state.slug)}">
                ${escapeHtml(state.nombre)}
            </option>
        `,
      )
      .join("");

    serviceFilter.innerHTML = `
            <option value="0">Todos</option>
            ${services
              .map(
                (service) => `
                <option value="${Number(service.id_servicio)}">
                    ${escapeHtml(service.nombre)}
                </option>
            `,
              )
              .join("")}
        `;

    contactSelect.innerHTML = `
            <option value="0">Sin contacto vinculado</option>
            ${contacts
              .map(
                (contact) => `
                <option value="${Number(contact.id_contacto_cliente)}">
                    ${escapeHtml(contact.cliente_nombre)}
                    — ${escapeHtml(contact.nombre_completo)}
                    (${escapeHtml(contact.correo)})
                </option>
            `,
              )
              .join("")}
        `;
  }

  async function loadCatalogs() {
    const data = await request(module.dataset.catalogsUrl);

    states = data.estados || [];
    services = data.servicios || [];
    contacts = data.contactos || [];

    populateCatalogs();
  }

  function linkedContactText(message) {
    if (!message.contacto_nombre) {
      return "Este mensaje todavía no está vinculado a un contacto registrado.";
    }

    const pieces = [
      message.cliente_nombre,
      message.contacto_nombre,
      message.contacto_puesto,
      message.contacto_correo,
    ].filter(Boolean);

    return pieces.join(" · ");
  }

  function buildMailto(message) {
    const subject = `Re: ${
      message.asunto || "Tu solicitud desde el portafolio"
    }`;

    const body = [
      `Hola ${message.nombre_completo || ""},`,
      "",
      "Gracias por contactarme.",
      "",
      "",
      "Saludos,",
      "Oscar Arzate",
    ].join("\n");

    return `mailto:${encodeURIComponent(
      message.correo || "",
    )}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  }

  function buildWhatsapp(phone, message) {
    const digits = String(phone || "").replace(/\D/g, "");

    if (digits.length < 10) {
      return "";
    }

    const text = `Hola ${
      message.nombre_completo || ""
    }, te contacto respecto a tu solicitud: ${message.asunto || "Proyecto"}.`;

    return `https://wa.me/${digits}?text=${encodeURIComponent(text)}`;
  }

  function renderDetail(message) {
    $("#messageDetailId").value = message.id_solicitud_contacto || 0;

    $("#messageDetailTitle").textContent =
      `Mensaje #${message.id_solicitud_contacto}`;

    $("#messageDetailSubject").textContent = message.asunto || "Sin asunto";

    $("#messageDetailState").innerHTML = stateBadge(message.estado);

    $("#messageDetailBody").textContent = message.mensaje || "";

    $("#messageDetailName").textContent =
      message.nombre_completo || "Sin nombre";

    $("#messageDetailEmail").textContent = message.correo || "Sin correo";

    $("#messageDetailPhone").textContent = message.telefono || "Sin teléfono";

    $("#messageDetailCompany").textContent =
      message.nombre_empresa || "Sin empresa";

    $("#messageDetailService").textContent =
      message.servicio_nombre || "Servicio general";

    $("#messageDetailPreferredDate").textContent = message.fecha_preferida
      ? formatDate(`${message.fecha_preferida}T00:00:00`, false)
      : "Sin fecha preferida";

    $("#messageDetailMode").textContent = modalityLabel(
      message.modalidad_preferida,
    );

    $("#messageDetailLinkedContact").textContent = linkedContactText(message);

    $("#messageDetailOrigin").textContent = originLabel(message.origen);

    $("#messageDetailIp").textContent = message.direccion_ip || "No registrada";

    $("#messageDetailCreated").textContent = formatDate(message.creado_en);

    $("#messageDetailAppointment").textContent = message.id_cita
      ? `Cita #${message.id_cita} · ${formatDate(message.cita_inicia_en)}`
      : "Sin cita vinculada";

    $("#btnReplyMessage").href = buildMailto(message);

    const whatsappUrl = buildWhatsapp(message.telefono, message);

    const whatsappButton = $("#btnMessageWhatsapp");
    whatsappButton.hidden = whatsappUrl === "";
    whatsappButton.href = whatsappUrl || "#";
  }

  async function markRead(message) {
    if (message.estado !== "nueva") {
      return;
    }

    const body = new FormData();
    body.append("id_solicitud_contacto", message.id_solicitud_contacto);

    body.append("csrf_token", $('#messageStateForm [name="csrf_token"]').value);

    try {
      await request(module.dataset.readUrl, {
        method: "POST",
        body,
      });

      message.estado = "leida";
      $("#messageDetailState").innerHTML = stateBadge("leida");

      await loadMessages();
    } catch (error) {
      notify(error.message, false);
    }
  }

  async function openDetail(id) {
    try {
      const message = await request(
        `${module.dataset.getUrl}/${encodeURIComponent(id)}`,
      );

      currentMessage = message;
      renderDetail(message);
      detailModal.show();
      await markRead(message);
    } catch (error) {
      notify(error.message, false);
    }
  }

  function openStateModal(id, state) {
    $("#messageStateId").value = id;
    $("#messageState").value = state || "leida";
    stateModal.show();
  }

  async function saveState(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const button = $("#btnSaveMessageState");
    const original = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            Guardando...
        `;

    try {
      const data = await request(module.dataset.statusUrl, {
        method: "POST",
        body: new FormData(form),
      });

      const id = Number($("#messageStateId").value);

      const newState = $("#messageState").value;

      if (
        currentMessage &&
        Number(currentMessage.id_solicitud_contacto) === id
      ) {
        currentMessage.estado = newState;
        $("#messageDetailState").innerHTML = stateBadge(newState);
      }

      stateModal.hide();
      notify(data.msg);
      await loadMessages();
    } catch (error) {
      notify(error.message, false);
    } finally {
      button.disabled = false;
      button.innerHTML = original;
    }
  }

  function openContactModal() {
    if (!currentMessage) {
      return;
    }

    $("#messageContactMessageId").value = currentMessage.id_solicitud_contacto;

    $("#messageContact").value = currentMessage.id_contacto_cliente || "0";

    contactModal.show();
  }

  async function saveContact(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const button = $("#btnSaveMessageContact");
    const original = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            Guardando...
        `;

    try {
      const data = await request(module.dataset.contactUrl, {
        method: "POST",
        body: new FormData(form),
      });

      contactModal.hide();
      notify(data.msg);

      const id = $("#messageContactMessageId").value;
      const updated = await request(
        `${module.dataset.getUrl}/${encodeURIComponent(id)}`,
      );

      currentMessage = updated;
      renderDetail(updated);
      await loadMessages();
    } catch (error) {
      notify(error.message, false);
    } finally {
      button.disabled = false;
      button.innerHTML = original;
    }
  }

  async function copyMessage() {
    if (!currentMessage) {
      return;
    }

    const text = [
      currentMessage.asunto || "Sin asunto",
      "",
      currentMessage.mensaje || "",
      "",
      `Remitente: ${currentMessage.nombre_completo || ""}`,
      `Correo: ${currentMessage.correo || ""}`,
      `Teléfono: ${currentMessage.telefono || ""}`,
    ].join("\n");

    try {
      await navigator.clipboard.writeText(text);
      notify("Mensaje copiado al portapapeles.");
    } catch (error) {
      notify("El navegador no permitió copiar el mensaje.", false);
    }
  }

  function clearFilters() {
    $("#messageSearch").value = "";
    $("#messageStateFilter").value = "all";
    $("#messageServiceFilter").value = "0";
    $("#messageOriginFilter").value = "all";
    $("#messageDateFrom").value = "";
    $("#messageDateTo").value = "";
    loadMessages();
  }

  tableBody.addEventListener("click", (event) => {
    const button = event.target.closest("[data-action]");

    if (!button) {
      return;
    }

    const id = Number(button.dataset.id || 0);

    if (button.dataset.action === "view") {
      openDetail(id);
    }

    if (button.dataset.action === "state") {
      openStateModal(id, button.dataset.state || "leida");
    }
  });

  $("#messageStateForm").addEventListener("submit", saveState);

  $("#messageContactForm").addEventListener("submit", saveContact);

  $("#btnRefreshMessages").addEventListener("click", loadMessages);

  $("#btnClearMessageFilters").addEventListener("click", clearFilters);

  $("#btnChangeMessageState").addEventListener("click", () => {
    if (!currentMessage) {
      return;
    }

    openStateModal(currentMessage.id_solicitud_contacto, currentMessage.estado);
  });

  $("#btnLinkMessageContact").addEventListener("click", openContactModal);

  $("#btnCopyMessage").addEventListener("click", copyMessage);

  $("#messageSearch").addEventListener("input", () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(loadMessages, 350);
  });

  [
    "#messageStateFilter",
    "#messageServiceFilter",
    "#messageOriginFilter",
    "#messageDateFrom",
    "#messageDateTo",
  ].forEach((selector) => {
    $(selector).addEventListener("change", loadMessages);
  });

  (async () => {
    try {
      await loadCatalogs();
      await loadMessages();
    } catch (error) {
      notify(error.message, false);
    }
  })();
})();
