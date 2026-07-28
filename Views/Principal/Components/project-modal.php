    <!-- Modal de proyecto -->
    <div
        class="modal fade"
        id="projectModal"
        tabindex="-1"
        aria-labelledby="projectModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="section-code mb-1" id="projectModalCategory"></span>
                        <h2 class="modal-title fs-4 modal-project-title" id="projectModalTitle"></h2>
                    </div>
                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <img
                                class="modal-main-image"
                                id="projectModalMainImage"
                                src=""
                                alt="">
                            <div class="gallery-strip" id="projectModalGallery"></div>

                            <div class="video-placeholder mt-3" id="projectVideoPlaceholder">
                                <div>
                                    <i class="fa-solid fa-circle-play"></i>
                                    <span>El backend podrá insertar aquí videos subidos o enlaces externos.</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="detail-block mb-3">
                                <div class="detail-label">Descripción</div>
                                <p class="detail-text" id="projectModalDescription"></p>
                            </div>

                            <div class="detail-block mb-3">
                                <div class="detail-label">Tecnologías</div>
                                <div class="modal-tech-list" id="projectModalTechnologies"></div>
                            </div>

                            <div class="detail-block mb-3">
                                <div class="detail-label">Reto técnico</div>
                                <p class="detail-text" id="projectModalChallenge"></p>
                            </div>

                            <div class="detail-block mb-3">
                                <div class="detail-label">Resultado</div>
                                <p class="detail-text" id="projectModalResult"></p>
                            </div>

                            <div class="detail-block mb-3" id="projectModalClientBlock">
                                <div class="detail-label">Cliente / propietario</div>
                                <p class="detail-text" id="projectModalClient"></p>
                            </div>

                            <div
                                class="private-repo-message"
                                id="projectModalPrivateMessage"
                                role="status">
                                <i class="fa-solid fa-lock me-1"></i>
                                Oops, este repositorio es privado. Puedes revisar la galería de fotos de este proyecto.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <a
                        class="cp-btn cp-btn-secondary"
                        id="projectModalGithub"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fa-brands fa-github"></i>
                        Ver repositorio
                    </a>

                    <a
                        class="cp-btn"
                        id="projectModalLive"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Abrir proyecto
                    </a>
                </div>
            </div>
        </div>
    </div>