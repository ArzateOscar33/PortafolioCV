<?php
const APP_PATH = '/Portafolio/';
const HOST = 'localhost';
const USER = 'root';
const PASS = '@Osc4r4rz4t3';
const DB = 'portafolio';
const CHARSET = 'charset=utf8mb4';

const USER_SMTP = 'sistemas@pacificnort.com';
const PASS_SMTP = 'Pacific2025.';
const PUERTO_SMTP = 465;
const HOST_SMTP = 'mailc75.carrierzone.com';


/*
 * smtps    = cifrado implícito, normalmente puerto 465
 * starttls = STARTTLS, normalmente puerto 587
 */
const SMTP_ENCRYPTION = 'smtps';
const SMTP_TIMEOUT = 20;
const SMTP_DEBUG = false;

const MAIL_FROM_EMAIL = USER_SMTP;
const MAIL_FROM_NAME = 'Oscar Arzate | Portafolio';

/* Bandeja que recibirá cada nueva solicitud. */
const MAIL_ADMIN_EMAIL = 'tu-correo@tudominio.com';
const MAIL_ADMIN_NAME = 'Oscar Arzate';

const MAIL_AUTO_REPLY_ENABLED = true;
