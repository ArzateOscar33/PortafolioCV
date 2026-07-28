<?php
const APP_PATH = '/Portafolio/';
const HOST = 'localhost';
const USER = 'root';
const PASS = '@Osc4r4rz4t3';
const DB = 'portafolio';
const CHARSET = 'charset=utf8mb4';

//ENVIO DE CORREOS
const USER_SMTP = "arzateoscar33@gmail.com";
const PASS_SMTP = "wxat tekd xqhw ikml";
const PUERTO_SMTP = "465";
const HOST_SMTP = "smtp.gmail.com";


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
const MAIL_ADMIN_EMAIL = 'arzateoscar33@gmail.com';
const MAIL_ADMIN_NAME = 'Oscar Arzate';

const MAIL_AUTO_REPLY_ENABLED = true;
