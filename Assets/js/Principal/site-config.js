(() => {
    'use strict';

    const script = document.currentScript;

    if (!script) {
        return;
    }

    const scriptUrl = new URL(script.src, window.location.href);
    const marker = 'Assets/js/Principal/site-config.js';
    const basePath = scriptUrl.pathname.includes(marker)
        ? scriptUrl.pathname.split(marker)[0]
        : '/';

    const endpoint = script.dataset.configUrl
        || `${scriptUrl.origin}${basePath}sitio/configuracion`;

    function resolvePath(path) {
        if (!path) {
            return '';
        }

        if (/^(https?:|mailto:|tel:|#)/i.test(path)) {
            return path;
        }

        if (path.startsWith('/')) {
            return path;
        }

        return `${scriptUrl.origin}${basePath}${path.replace(/^\/+/, '')}`;
    }

    function setText(selector, value) {
        const element = document.querySelector(selector);

        if (element && value !== null && value !== undefined && value !== '') {
            element.textContent = value;
        }
    }

    function setHighlightedTitle(selector, value) {
        const element = document.querySelector(selector);

        if (!element || !value) {
            return;
        }

        const words = String(value).trim().split(/\s+/);
        const highlighted = words.pop();
        element.replaceChildren();

        if (words.length) {
            element.appendChild(document.createTextNode(`${words.join(' ')} `));
        }

        const span = document.createElement('span');
        span.textContent = highlighted;
        element.appendChild(span);
    }

    function setMeta(name, content, attribute = 'name') {
        if (!content) {
            return;
        }

        let meta = document.head.querySelector(
            `meta[${attribute}="${CSS.escape(name)}"]`
        );

        if (!meta) {
            meta = document.createElement('meta');
            meta.setAttribute(attribute, name);
            document.head.appendChild(meta);
        }

        meta.setAttribute('content', content);
    }

    function setLink(rel, href) {
        if (!href) {
            return;
        }

        let link = document.head.querySelector(`link[rel="${rel}"]`);

        if (!link) {
            link = document.createElement('link');
            link.rel = rel;
            document.head.appendChild(link);
        }

        link.href = resolvePath(href);
    }

    function replaceButtonText(button, text) {
        if (!button || !text) {
            return;
        }

        [...button.childNodes]
            .filter((node) => node.nodeType === Node.TEXT_NODE)
            .forEach((node) => node.remove());

        button.appendChild(document.createTextNode(` ${text}`));
    }

    function applyConfiguration(config, socials) {
        const root = document.documentElement;

        if (config.color_primario) {
            root.style.setProperty('--cp-cyan', config.color_primario);
        }

        if (config.color_secundario) {
            root.style.setProperty('--cp-pink', config.color_secundario);
        }

        if (config.color_acento) {
            root.style.setProperty('--cp-yellow', config.color_acento);
        }

        document.title = config.seo_titulo
            || config.nombre_sitio
            || document.title;

        setMeta('description', config.seo_descripcion || config.descripcion_sitio);
        setMeta('keywords', config.seo_palabras_clave);
        setMeta('og:title', config.seo_titulo || config.nombre_sitio, 'property');
        setMeta('og:description', config.seo_descripcion || config.descripcion_sitio, 'property');
        setMeta('og:image', resolvePath(config.seo_imagen_ruta), 'property');
        setLink('icon', config.favicon_ruta);

        const brand = document.querySelector('.navbar-brand');

        if (brand && config.nombre_sitio) {
            [...brand.childNodes]
                .filter((node) => node.nodeType === Node.TEXT_NODE)
                .forEach((node) => node.remove());
            brand.appendChild(document.createTextNode(` ${config.nombre_sitio}`));
        }

        setText('.hero-kicker', config.hero_kicker);

        const glitch = document.querySelector('.hero-title .glitch');

        if (glitch && config.hero_titulo_principal) {
            glitch.textContent = config.hero_titulo_principal;
            glitch.dataset.text = config.hero_titulo_principal;
        }

        setText('.hero-title .outline', config.hero_titulo_destacado);
        setText('.hero-copy', config.hero_descripcion);

        const heroButtons = document.querySelectorAll('.hero .hero-actions a');

        if (heroButtons[0]) {
            replaceButtonText(heroButtons[0], config.hero_cta_principal_texto);
            heroButtons[0].href = resolvePath(config.hero_cta_principal_url || '#proyectos');
        }

        if (heroButtons[1]) {
            replaceButtonText(heroButtons[1], config.hero_cta_secundario_texto);
            heroButtons[1].href = resolvePath(config.hero_cta_secundario_url || '#contacto');
        }

        setText('.profile-label strong', config.nombre_completo);
        setText('.profile-label span', config.titulo_profesional);

        const profileFrame = document.querySelector('.profile-frame');

        if (profileFrame && config.foto_perfil_ruta) {
            profileFrame.style.backgroundImage = `url("${resolvePath(config.foto_perfil_ruta)}")`;
            profileFrame.style.backgroundSize = 'cover';
            profileFrame.style.backgroundPosition = 'center';
        }

        setHighlightedTitle('#acerca .section-title', config.acerca_titulo);

        const aboutParagraphs = document.querySelectorAll('#acerca .about-panel .section-copy');

        if (aboutParagraphs[0] && config.acerca_parrafo_1) {
            aboutParagraphs[0].textContent = config.acerca_parrafo_1;
        }

        if (aboutParagraphs[1] && config.acerca_parrafo_2) {
            aboutParagraphs[1].textContent = config.acerca_parrafo_2;
        }

        const aboutList = document.querySelector('#acerca .about-list');

        if (aboutList && Array.isArray(config.acerca_habilidades)) {
            aboutList.replaceChildren();

            config.acerca_habilidades.forEach((ability) => {
                const item = document.createElement('li');
                const icon = document.createElement('i');
                const text = document.createElement('span');
                icon.className = 'fa-solid fa-check';
                text.textContent = ability;
                item.append(icon, text);
                aboutList.appendChild(item);
            });
        }

        const resumeLink = document.querySelector('a[aria-label="Descargar currículum"]');

        if (resumeLink) {
            if (config.cv_ruta) {
                resumeLink.href = resolvePath(config.cv_ruta);
                resumeLink.hidden = false;
                resumeLink.setAttribute('download', '');
            } else {
                resumeLink.hidden = true;
            }
        }

        const contactItems = document.querySelectorAll('#contacto .contact-item');

        if (contactItems[0]) {
            const span = contactItems[0].querySelector('span');
            if (span && config.correo_publico) span.textContent = config.correo_publico;
            contactItems[0].hidden = config.mostrar_correo === false;
        }

        if (contactItems[1]) {
            const span = contactItems[1].querySelector('span');
            if (span && config.ubicacion) span.textContent = config.ubicacion;
        }

        if (contactItems[2]) {
            const span = contactItems[2].querySelector('span');
            if (span && config.tiempo_respuesta) {
                span.textContent = `Respuesta estimada: ${config.tiempo_respuesta}`;
            }
        }

        const contactInfo = document.querySelector('#contacto .contact-info');
        let phoneItem = document.getElementById('dynamicPublicPhone');

        if (contactInfo && config.telefono_publico && config.mostrar_telefono !== false) {
            if (!phoneItem) {
                phoneItem = document.createElement('div');
                phoneItem.className = 'contact-item';
                phoneItem.id = 'dynamicPublicPhone';
                phoneItem.innerHTML = '<i class="fa-solid fa-phone"></i><span></span>';
                contactInfo.appendChild(phoneItem);
            }

            phoneItem.querySelector('span').textContent = config.telefono_publico;
            phoneItem.hidden = false;
        } else if (phoneItem) {
            phoneItem.hidden = true;
        }

        const statusPrompts = [...document.querySelectorAll('.terminal-prompt')];
        const statusPrompt = statusPrompts.find((element) =>
            element.textContent.trim().toLowerCase().startsWith('status')
        );

        if (statusPrompt && config.disponibilidad_texto) {
            const line = statusPrompt.closest('.terminal-line');

            if (line) {
                line.replaceChildren(statusPrompt, document.createTextNode(` ${config.disponibilidad_texto}`));
            }
        }

        const socialContainer = document.querySelector('#contacto .social-links');

        if (socialContainer && Array.isArray(socials)) {
            socialContainer.replaceChildren();

            socials.forEach((social) => {
                const link = document.createElement('a');
                const icon = document.createElement('i');
                link.className = 'social-link';
                link.href = social.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.setAttribute('aria-label', social.etiqueta || social.plataforma);

                if (social.color_hexadecimal) {
                    link.style.setProperty('--social-color', social.color_hexadecimal);
                    link.style.color = social.color_hexadecimal;
                }

                icon.className = social.clase_icono || 'fa-solid fa-link';
                link.appendChild(icon);
                socialContainer.appendChild(link);
            });
        }

        const footerText = document.querySelector('.cp-footer .container span:last-child');

        if (footerText && config.footer_texto) {
            footerText.textContent = `© ${new Date().getFullYear()} ${config.footer_texto}`;
        }
    }

    fetch(endpoint, {
        headers: {
            Accept: 'application/json',
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('No fue posible cargar la configuración pública.');
            }

            return response.json();
        })
        .then((data) => {
            if (data.status !== true) {
                return;
            }

            applyConfiguration(
                data.configuracion || {},
                data.redes || []
            );
        })
        .catch((error) => {
            console.warn('[site-config]', error.message);
        });
})();
