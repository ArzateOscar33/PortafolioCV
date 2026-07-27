"use strict";

/*
|--------------------------------------------------------------------------
| Elementos principales
|--------------------------------------------------------------------------
*/

const categoriesModule = document.querySelector("#categoriesModule");

const frmCategoria = document.querySelector("#categoryForm");

const btnNuevaCategoria = document.querySelector("#btnNewCategory");
const btnNuevaCategoriaVacio = document.querySelector("#btnEmptyNewCategory");

const btnGuardarCategoria = document.querySelector("#btnSaveCategory");

const btnActualizarCategorias = document.querySelector("#btnRefreshCategories");

const btnGenerarSlug = document.querySelector("#btnGenerateCategorySlug");

const btnConfirmarEstado = document.querySelector("#btnConfirmCategoryStatus");

const tablaCategorias = document.querySelector("#categoriesTableBody");

const contenedorTabla = document.querySelector("#categoriesTableContainer");

const estadoVacio = document.querySelector("#categoriesEmptyState");

const inputBusqueda = document.querySelector("#categorySearch");

const filtroEstado = document.querySelector("#categoryStatusFilter");

const modalCategoriaElemento = document.querySelector("#categoryModal");

const modalEstadoElemento = document.querySelector("#categoryStatusModal");

const modalCategoria = modalCategoriaElemento
  ? new bootstrap.Modal(modalCategoriaElemento)
  : null;

const modalEstado = modalEstadoElemento
  ? new bootstrap.Modal(modalEstadoElemento)
  : null;

const toastElemento = document.querySelector("#categoryNotification");

const toastCategoria = toastElemento
  ? new bootstrap.Toast(toastElemento)
  : null;

/*
|--------------------------------------------------------------------------
| Endpoints
|--------------------------------------------------------------------------
*/

const urlListar = categoriesModule?.dataset.listUrl || "";
const urlObtener = categoriesModule?.dataset.getUrl || "";
const urlGuardar = categoriesModule?.dataset.saveUrl || "";
const urlCambiarEstado = categoriesModule?.dataset.statusUrl || "";

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

let idCategoriaEstado = 0;
let nuevoEstadoCategoria = 0;
let temporizadorBusqueda = null;

/*
|--------------------------------------------------------------------------
| Inicio
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {
  if (!categoriesModule) {
    return;
  }

  listarCategorias();

  btnNuevaCategoria?.addEventListener("click", prepararNuevaCategoria);

  btnNuevaCategoriaVacio?.addEventListener("click", prepararNuevaCategoria);

  btnActualizarCategorias?.addEventListener("click", listarCategorias);

  filtroEstado?.addEventListener("change", listarCategorias);

  inputBusqueda?.addEventListener("input", function () {
    clearTimeout(temporizadorBusqueda);

    temporizadorBusqueda = setTimeout(listarCategorias, 350);
  });

  frmCategoria?.addEventListener("submit", guardarCategoria);

  btnGenerarSlug?.addEventListener("click", generarSlug);

  btnConfirmarEstado?.addEventListener("click", confirmarCambioEstado);

  configurarColor();
  configurarIcono();
  configurarContadorDescripcion();
});

/*
|--------------------------------------------------------------------------
| Listar categorías
|--------------------------------------------------------------------------
*/

function listarCategorias() {
  mostrarCargando();

  const estado = filtroEstado?.value || "all";
  const busqueda = inputBusqueda?.value.trim() || "";

  const parametros = new URLSearchParams({
    estado: estado,
    busqueda: busqueda,
  });

  const url = urlListar + "?" + parametros.toString();

  const http = new XMLHttpRequest();

  http.open("GET", url, true);

  http.setRequestHeader("X-Requested-With", "XMLHttpRequest");

  http.send();

  http.onreadystatechange = function () {
    if (this.readyState !== 4) {
      return;
    }

    if (this.status === 200) {
      try {
        const res = JSON.parse(this.responseText);

        if (res.status) {
          mostrarCategorias(res.categorias || []);

          actualizarEstadisticas(res.resumen || {});
        } else {
          mostrarCategorias([]);

          mostrarMensaje(
            res.msg || "No fue posible cargar las categorías.",
            res.icono || "error",
          );
        }
      } catch (error) {
        mostrarCategorias([]);

        mostrarMensaje("La respuesta del servidor no es válida.", "error");
      }
    } else {
      mostrarCategorias([]);

      procesarErrorHttp(this);
    }
  };
}

/*
|--------------------------------------------------------------------------
| Dibujar tabla
|--------------------------------------------------------------------------
*/

function mostrarCategorias(categorias) {
  tablaCategorias.innerHTML = "";

  if (!Array.isArray(categorias) || categorias.length === 0) {
    contenedorTabla.hidden = true;
    estadoVacio.hidden = false;
    return;
  }

  contenedorTabla.hidden = false;
  estadoVacio.hidden = true;

  categorias.forEach(function (categoria) {
    const idCategoria = Number(categoria.id_categoria);

    const activa = Number(categoria.activa) === 1;

    const nuevoEstado = activa ? 0 : 1;

    const color = categoria.color_hexadecimal || "#00F6FF";

    const icono = categoria.clase_icono || "fa-solid fa-tags";

    const descripcion = categoria.descripcion || "Sin descripción";

    const fecha = formatearFecha(categoria.actualizada_en);

    const estadoHtml = activa
      ? `
                <span class="badge text-bg-success">
                    Activa
                </span>
            `
      : `
                <span class="badge text-bg-secondary">
                    Inactiva
                </span>
            `;

    const botonEstado = activa
      ? `
                <button
                    class="btn btn-sm btn-outline-danger"
                    type="button"
                    title="Dar de baja"
                    onclick="cambiarEstadoCategoria(
                        ${idCategoria},
                        ${nuevoEstado}
                    )">

                    <i class="fa-solid fa-ban"></i>
                </button>
            `
      : `
                <button
                    class="btn btn-sm btn-outline-success"
                    type="button"
                    title="Activar"
                    onclick="cambiarEstadoCategoria(
                        ${idCategoria},
                        ${nuevoEstado}
                    )">

                    <i class="fa-solid fa-circle-check"></i>
                </button>
            `;

    const fila = document.createElement("tr");

    fila.innerHTML = `
            <td>
                ${Number(categoria.orden_visualizacion || 0)}
            </td>

            <td>
                <div class="d-flex align-items-center gap-3">

                    <span
                        class="d-inline-flex align-items-center
                               justify-content-center rounded"
                        style="
                            width: 40px;
                            height: 40px;
                            color: ${escapeHtml(color)};
                            border: 1px solid ${escapeHtml(color)};
                        ">

                        <i class="${escapeHtml(icono)}"></i>

                    </span>

                    <div>
                        <strong class="d-block">
                            ${escapeHtml(categoria.nombre)}
                        </strong>

                        <small class="text-body-secondary">
                            ${escapeHtml(descripcion)}
                        </small>
                    </div>

                </div>
            </td>

            <td>
                <code>
                    ${escapeHtml(categoria.slug)}
                </code>
            </td>

            <td>
                ${estadoHtml}
            </td>

            <td>
                ${escapeHtml(fecha)}
            </td>

            <td class="text-end">

                <div
                    class="d-inline-flex
                           align-items-center gap-2">

                    <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        title="Editar categoría"
                        onclick="editarCategoria(
                            ${idCategoria}
                        )">

                        <i class="fa-solid fa-pen-to-square"></i>

                    </button>

                    ${botonEstado}

                </div>

            </td>
        `;

    tablaCategorias.appendChild(fila);
  });
}

/*
|--------------------------------------------------------------------------
| Preparar registro
|--------------------------------------------------------------------------
*/

function prepararNuevaCategoria() {
  frmCategoria.reset();

  document.querySelector("#categoryId").value = 0;

  document.querySelector("#categoryModalTitle").textContent = "Nueva categoría";

  document.querySelector("#categoryModalDescription").textContent =
    "Completa la información de la nueva categoría.";

  document.querySelector("#btnSaveCategory span").textContent =
    "Guardar categoría";

  document.querySelector("#categoryActive").checked = true;

  document.querySelector("#categoryOrder").value = 0;

  document.querySelector("#categoryColor").value = "#00F6FF";

  document.querySelector("#categoryColorPicker").value = "#00F6FF";

  document.querySelector("#categoryIconClass").value = "fa-solid fa-tags";

  actualizarVistaIcono();
  actualizarContadorDescripcion();
  ocultarErrorFormulario();
}

/*
|--------------------------------------------------------------------------
| Guardar o modificar
|--------------------------------------------------------------------------
*/

function guardarCategoria(e) {
  e.preventDefault();

  if (!frmCategoria.checkValidity()) {
    frmCategoria.classList.add("was-validated");

    return;
  }

  frmCategoria.classList.remove("was-validated");

  ocultarErrorFormulario();
  bloquearBotonGuardar(true);

  const data = new FormData(frmCategoria);

  const activa = document.querySelector("#categoryActive").checked;

  data.set("activa", activa ? "1" : "0");

  const http = new XMLHttpRequest();

  http.open("POST", urlGuardar, true);

  http.setRequestHeader("X-Requested-With", "XMLHttpRequest");

  http.send(data);

  http.onreadystatechange = function () {
    if (this.readyState !== 4) {
      return;
    }

    bloquearBotonGuardar(false);

    try {
      const res = JSON.parse(this.responseText);

      if (this.status === 200 && res.status) {
        modalCategoria.hide();

        frmCategoria.reset();

        listarCategorias();

        mostrarMensaje(res.msg, res.icono);
      } else {
        mostrarErrorFormulario(
          res.msg || "No fue posible guardar la categoría.",
        );

        mostrarMensaje(
          res.msg || "No fue posible guardar la categoría.",
          res.icono || "error",
        );
      }
    } catch (error) {
      mostrarErrorFormulario("La respuesta del servidor no es válida.");

      mostrarMensaje("La respuesta del servidor no es válida.", "error");
    }
  };
}

/*
|--------------------------------------------------------------------------
| Editar categoría
|--------------------------------------------------------------------------
*/

function editarCategoria(idCategoria) {
  const url = urlObtener + "/" + idCategoria;

  const http = new XMLHttpRequest();

  http.open("GET", url, true);

  http.setRequestHeader("X-Requested-With", "XMLHttpRequest");

  http.send();

  http.onreadystatechange = function () {
    if (this.readyState !== 4) {
      return;
    }

    if (this.status === 200) {
      try {
        const res = JSON.parse(this.responseText);

        if (res.status === false) {
          mostrarMensaje(res.msg, res.icono || "error");

          return;
        }

        frmCategoria.reset();

        document.querySelector("#categoryId").value = res.id_categoria;

        document.querySelector("#categoryName").value = res.nombre || "";

        document.querySelector("#categorySlug").value = res.slug || "";

        document.querySelector("#categoryDescription").value =
          res.descripcion || "";

        document.querySelector("#categoryColor").value =
          res.color_hexadecimal || "";

        document.querySelector("#categoryColorPicker").value =
          res.color_hexadecimal || "#00F6FF";

        document.querySelector("#categoryIconClass").value =
          res.clase_icono || "fa-solid fa-tags";

        document.querySelector("#categoryOrder").value =
          res.orden_visualizacion || 0;

        document.querySelector("#categoryActive").checked =
          Number(res.activa) === 1;

        document.querySelector("#categoryModalTitle").textContent =
          "Modificar categoría";

        document.querySelector("#categoryModalDescription").textContent =
          "Actualiza la información de la categoría.";

        document.querySelector("#btnSaveCategory span").textContent =
          "Actualizar categoría";

        actualizarVistaIcono();
        actualizarContadorDescripcion();
        ocultarErrorFormulario();

        modalCategoria.show();
      } catch (error) {
        mostrarMensaje("No fue posible procesar la categoría.", "error");
      }
    } else {
      procesarErrorHttp(this);
    }
  };
}

/*
|--------------------------------------------------------------------------
| Cambio de estado
|--------------------------------------------------------------------------
*/

function cambiarEstadoCategoria(idCategoria, nuevoEstado) {
  idCategoriaEstado = idCategoria;
  nuevoEstadoCategoria = nuevoEstado;

  const mensaje = document.querySelector("#categoryStatusMessage");

  if (nuevoEstado === 1) {
    mensaje.textContent = "¿Deseas activar esta categoría?";
  } else {
    mensaje.textContent = "¿Deseas dar de baja esta categoría?";
  }

  modalEstado.show();
}

function confirmarCambioEstado() {
  if (idCategoriaEstado <= 0) {
    return;
  }

  const data = new FormData();

  data.append("id_categoria", idCategoriaEstado);

  data.append("activa", nuevoEstadoCategoria);

  data.append("csrf_token", document.querySelector("#categoryCsrfToken").value);

  const http = new XMLHttpRequest();

  http.open("POST", urlCambiarEstado, true);

  http.setRequestHeader("X-Requested-With", "XMLHttpRequest");

  http.send(data);

  http.onreadystatechange = function () {
    if (this.readyState !== 4) {
      return;
    }

    try {
      const res = JSON.parse(this.responseText);

      if (this.status === 200 && res.status) {
        modalEstado.hide();

        listarCategorias();

        mostrarMensaje(res.msg, res.icono);

        idCategoriaEstado = 0;
        nuevoEstadoCategoria = 0;
      } else {
        mostrarMensaje(
          res.msg || "No fue posible cambiar el estado.",
          res.icono || "error",
        );
      }
    } catch (error) {
      mostrarMensaje("La respuesta del servidor no es válida.", "error");
    }
  };
}

/*
|--------------------------------------------------------------------------
| Slug
|--------------------------------------------------------------------------
*/

function generarSlug() {
  const nombre = document.querySelector("#categoryName").value;

  document.querySelector("#categorySlug").value = crearSlug(nombre);
}

function crearSlug(texto) {
  return texto
    .toString()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

/*
|--------------------------------------------------------------------------
| Color
|--------------------------------------------------------------------------
*/

function configurarColor() {
  const selectorColor = document.querySelector("#categoryColorPicker");

  const inputColor = document.querySelector("#categoryColor");

  selectorColor?.addEventListener("input", function () {
    inputColor.value = this.value.toUpperCase();
  });

  inputColor?.addEventListener("input", function () {
    const valor = this.value.trim();

    if (/^#[0-9A-Fa-f]{6}$/.test(valor)) {
      selectorColor.value = valor;
    }
  });
}

/*
|--------------------------------------------------------------------------
| Icono
|--------------------------------------------------------------------------
*/

function configurarIcono() {
  document
    .querySelector("#categoryIconClass")
    ?.addEventListener("input", actualizarVistaIcono);
}

function actualizarVistaIcono() {
  const claseIcono = document.querySelector("#categoryIconClass").value.trim();

  const vistaPrevia = document.querySelector("#categoryIconPreview");

  vistaPrevia.innerHTML = "";

  const icono = document.createElement("i");

  icono.className = claseIcono || "fa-solid fa-tags";

  vistaPrevia.appendChild(icono);
}

/*
|--------------------------------------------------------------------------
| Contador
|--------------------------------------------------------------------------
*/

function configurarContadorDescripcion() {
  document
    .querySelector("#categoryDescription")
    ?.addEventListener("input", actualizarContadorDescripcion);

  actualizarContadorDescripcion();
}

function actualizarContadorDescripcion() {
  const descripcion = document.querySelector("#categoryDescription");

  const contador = document.querySelector("#categoryDescriptionCounter");

  contador.textContent = descripcion.value.length + " / 500";
}

/*
|--------------------------------------------------------------------------
| Estadísticas
|--------------------------------------------------------------------------
*/

function actualizarEstadisticas(resumen) {
  document.querySelector("#totalCategories").textContent = resumen.total || 0;

  document.querySelector("#activeCategories").textContent =
    resumen.activas || 0;

  document.querySelector("#inactiveCategories").textContent =
    resumen.inactivas || 0;
}

/*
|--------------------------------------------------------------------------
| Mensajes
|--------------------------------------------------------------------------
*/

function mostrarMensaje(mensaje, icono) {
  const titulo = document.querySelector("#categoryNotificationTitle");

  const cuerpo = document.querySelector("#categoryNotificationMessage");

  const iconoToast = document.querySelector(
    "#categoryNotification .toast-header i",
  );

  cuerpo.textContent = mensaje || "";

  if (icono === "success") {
    titulo.textContent = "Operación completada";

    iconoToast.className = "fa-solid fa-circle-check text-success me-2";
  } else if (icono === "warning") {
    titulo.textContent = "Aviso";

    iconoToast.className = "fa-solid fa-triangle-exclamation text-warning me-2";
  } else {
    titulo.textContent = "Error";

    iconoToast.className = "fa-solid fa-circle-xmark text-danger me-2";
  }

  toastCategoria.show();
}

function mostrarErrorFormulario(mensaje) {
  const alerta = document.querySelector("#categoryFormFeedback");

  alerta.textContent = mensaje;
  alerta.classList.remove("d-none");
}

function ocultarErrorFormulario() {
  const alerta = document.querySelector("#categoryFormFeedback");

  alerta.textContent = "";
  alerta.classList.add("d-none");

  frmCategoria.classList.remove("was-validated");
}

/*
|--------------------------------------------------------------------------
| Utilidades
|--------------------------------------------------------------------------
*/

function mostrarCargando() {
  contenedorTabla.hidden = false;
  estadoVacio.hidden = true;

  tablaCategorias.innerHTML = `
        <tr>
            <td
                colspan="6"
                class="text-center py-5">

                <i class="fa-solid fa-circle-notch fa-spin"></i>

                <span class="ms-2">
                    Cargando categorías...
                </span>

            </td>
        </tr>
    `;
}

function bloquearBotonGuardar(bloquear) {
  btnGuardarCategoria.disabled = bloquear;

  const texto = btnGuardarCategoria.querySelector("span");

  const icono = btnGuardarCategoria.querySelector("i");

  if (bloquear) {
    texto.textContent = "Guardando...";
    icono.className = "fa-solid fa-circle-notch fa-spin";
  } else {
    const idCategoria = Number(document.querySelector("#categoryId").value);

    texto.textContent =
      idCategoria > 0 ? "Actualizar categoría" : "Guardar categoría";

    icono.className = "fa-solid fa-floppy-disk";
  }
}

function procesarErrorHttp(http) {
  try {
    const res = JSON.parse(http.responseText);

    mostrarMensaje(
      res.msg || "Ocurrió un error en la solicitud.",
      res.icono || "error",
    );
  } catch (error) {
    mostrarMensaje("No fue posible comunicarse con el servidor.", "error");
  }
}

function formatearFecha(fecha) {
  if (!fecha) {
    return "Sin información";
  }

  const fechaNormalizada = fecha.replace(" ", "T");

  const fechaObjeto = new Date(fechaNormalizada);

  if (Number.isNaN(fechaObjeto.getTime())) {
    return fecha;
  }

  return new Intl.DateTimeFormat("es-MX", {
    year: "numeric",
    month: "short",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  }).format(fechaObjeto);
}

function escapeHtml(valor) {
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}
