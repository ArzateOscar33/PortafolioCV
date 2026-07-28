      <!-- Contacto -->
      <section class="section" id="contacto">
          <div class="container">
              <div class="contact-panel">
                  <div class="row g-5">
                      <div class="col-lg-5 reveal-section">
                          <span class="section-code">// canal_de_contacto</span>
                          <h2 class="section-title">Iniciemos una <span>conexión</span></h2>
                          <p class="section-copy">
                              Cuéntame qué necesitas construir, mejorar o automatizar. Puedes proponer una
                              fecha para una reunión presencial o en línea.
                          </p>

                          <div class="contact-info">
                              <div class="contact-item">
                                  <i class="fa-regular fa-envelope"></i>
                                  <span>correo@ejemplo.com</span>
                              </div>
                              <div class="contact-item">
                                  <i class="fa-solid fa-location-dot"></i>
                                  <span>Tijuana, Baja California, México</span>
                              </div>
                              <div class="contact-item">
                                  <i class="fa-regular fa-clock"></i>
                                  <span>Respuesta estimada: 24–48 horas</span>
                              </div>
                          </div>

                          <div class="social-links" aria-label="Redes profesionales">
                              <a class="social-link" href="#" aria-label="GitHub">
                                  <i class="fa-brands fa-github"></i>
                              </a>
                              <a class="social-link" href="#" aria-label="LinkedIn">
                                  <i class="fa-brands fa-linkedin-in"></i>
                              </a>
                              <a class="social-link" href="#" aria-label="WhatsApp">
                                  <i class="fa-brands fa-whatsapp"></i>
                              </a>
                              <a class="social-link" href="#" aria-label="Correo electrónico">
                                  <i class="fa-regular fa-envelope"></i>
                              </a>
                          </div>
                      </div>

                      <div class="col-lg-7 reveal-section">
                          <form
                              id="contactForm"
                              data-endpoint="<?= BASE_URL ?>contacto/guardar"
                              novalidate>

                              <input
                                  id="contactFormStartedAt"
                                  name="form_started_at"
                                  type="hidden"
                                  value="">

                              <!-- Campo trampa para bots -->
                              <div
                                  class="position-absolute opacity-0 pe-none"
                                  style="left: -10000px;"
                                  aria-hidden="true">
                                  <label for="contactWebsite">
                                      Sitio web
                                  </label>
                                  <input
                                      id="contactWebsite"
                                      name="website"
                                      type="text"
                                      tabindex="-1"
                                      autocomplete="off">
                              </div>

                              <div class="row g-3">
                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="contactName">
                                          Nombre
                                      </label>

                                      <input
                                          class="form-control"
                                          id="contactName"
                                          name="nombre_completo"
                                          type="text"
                                          maxlength="150"
                                          placeholder="Tu nombre"
                                          autocomplete="name"
                                          required>

                                      <div class="invalid-feedback">
                                          Escribe tu nombre.
                                      </div>
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="contactEmail">
                                          Correo electrónico
                                      </label>

                                      <input
                                          class="form-control"
                                          id="contactEmail"
                                          name="correo"
                                          type="email"
                                          maxlength="150"
                                          placeholder="nombre@empresa.com"
                                          autocomplete="email"
                                          required>

                                      <div class="invalid-feedback">
                                          Escribe un correo válido.
                                      </div>
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="contactPhone">
                                          Teléfono
                                      </label>

                                      <input
                                          class="form-control"
                                          id="contactPhone"
                                          name="telefono"
                                          type="tel"
                                          maxlength="30"
                                          placeholder="+52 664 000 0000"
                                          autocomplete="tel">
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="contactCompany">
                                          Empresa
                                      </label>

                                      <input
                                          class="form-control"
                                          id="contactCompany"
                                          name="nombre_empresa"
                                          type="text"
                                          maxlength="150"
                                          placeholder="Opcional"
                                          autocomplete="organization">
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="contactService">
                                          Tipo de proyecto
                                      </label>

                                      <select
                                          class="form-select"
                                          id="contactService"
                                          name="servicio"
                                          required>

                                          <option
                                              value=""
                                              selected
                                              disabled>
                                              Selecciona una opción
                                          </option>

                                          <option value="sistemas-web">
                                              Sistema web
                                          </option>

                                          <option value="aplicaciones-escritorio">
                                              Aplicación de escritorio
                                          </option>

                                          <option value="aplicaciones-moviles">
                                              Aplicación móvil
                                          </option>

                                          <option value="infraestructura-redes">
                                              Infraestructura y redes
                                          </option>

                                          <option value="ciberseguridad">
                                              Ciberseguridad
                                          </option>

                                          <option value="consultoria-tecnica">
                                              Consultoría técnica
                                          </option>
                                      </select>

                                      <div class="invalid-feedback">
                                          Selecciona el tipo de proyecto.
                                      </div>
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="contactSubject">
                                          Asunto
                                      </label>

                                      <input
                                          class="form-control"
                                          id="contactSubject"
                                          name="asunto"
                                          type="text"
                                          maxlength="180"
                                          placeholder="Ejemplo: Sistema de control de inventario">
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="meetingDate">
                                          Fecha sugerida
                                      </label>

                                      <input
                                          class="form-control"
                                          id="meetingDate"
                                          name="fecha_preferida"
                                          type="date">
                                  </div>

                                  <div class="col-md-6">
                                      <label
                                          class="form-label"
                                          for="meetingMode">
                                          Modalidad
                                      </label>

                                      <select
                                          class="form-select"
                                          id="meetingMode"
                                          name="modalidad_preferida">

                                          <option value="en_linea">
                                              En línea
                                          </option>

                                          <option value="presencial">
                                              Presencial
                                          </option>

                                          <option value="cualquiera">
                                              Cualquiera
                                          </option>
                                      </select>
                                  </div>

                                  <div class="col-12">
                                      <label
                                          class="form-label"
                                          for="contactMessage">
                                          Mensaje
                                      </label>

                                      <textarea
                                          class="form-control"
                                          id="contactMessage"
                                          name="mensaje"
                                          minlength="20"
                                          maxlength="10000"
                                          placeholder="Describe brevemente tu proyecto, problema u objetivo..."
                                          required></textarea>

                                      <div class="invalid-feedback">
                                          Describe tu solicitud con al menos 20 caracteres.
                                      </div>
                                  </div>

                                  <div class="col-12">
                                      <button
                                          class="cp-btn w-100"
                                          id="btnSendContact"
                                          type="submit">
                                          <i class="fa-solid fa-paper-plane"></i>
                                          <span>Enviar solicitud</span>
                                      </button>
                                  </div>
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
          </div>
      </section>