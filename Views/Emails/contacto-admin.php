<?php
$e = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$nombre = $e($datos['nombre_completo'] ?? '');
$correo = $e($datos['correo'] ?? '');
$telefono = $e($datos['telefono'] ?? 'No proporcionado');
$empresa = $e($datos['nombre_empresa'] ?? 'No proporcionada');
$asunto = $e($datos['asunto'] ?? '');
$mensaje = nl2br($e($datos['mensaje'] ?? ''));
$servicioNombre = $e($servicio['nombre'] ?? 'No especificado');
$fecha = $e($datos['fecha_preferida'] ?? 'No especificada');
$modalidad = $e($datos['modalidad_preferida'] ?? 'No especificada');
$panelUrlSeguro = $e($panelUrl ?? '#');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nueva solicitud</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { width: 100% !important; }
            .email-padding { padding: 24px 18px !important; }
            .detail-column { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#06060c;color:#f5f7ff;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#06060c;">
    <tr>
        <td align="center" style="padding:28px 12px;">
            <table class="email-shell" role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:640px;background:#10101d;border:1px solid #20334a;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:22px 28px;background:#0a0a14;border-bottom:1px solid #20334a;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td width="46" valign="middle">
                                    <img src="cid:fa-bolt" width="28" height="32" alt="" style="display:block;border:0;">
                                </td>
                                <td valign="middle">
                                    <div style="font-size:12px;letter-spacing:2px;color:#00f6ff;font-weight:700;">CONTACT_INBOX</div>
                                    <div style="margin-top:4px;font-size:21px;line-height:1.3;color:#ffffff;font-weight:700;">Nueva solicitud #<?= (int) $idSolicitud ?></div>
                                </td>
                                <td width="42" align="right" valign="middle">
                                    <img src="cid:fa-envelope" width="28" height="28" alt="" style="display:block;border:0;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="email-padding" style="padding:30px 28px;">
                        <p style="margin:0 0 22px;color:#aeb6ca;font-size:15px;line-height:1.65;">
                            Se registró una nueva solicitud desde el formulario público del portafolio. Responde usando el botón del panel o directamente a este correo; el <strong style="color:#ffffff;">Reply-To</strong> apunta al visitante.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate;border-spacing:0 10px;">
                            <tr>
                                <td class="detail-column" width="50%" style="padding:14px;background:#151526;border:1px solid #25263c;border-radius:10px;vertical-align:top;">
                                    <div style="font-size:11px;letter-spacing:1px;color:#00f6ff;font-weight:700;">NOMBRE</div>
                                    <div style="margin-top:7px;color:#ffffff;font-size:15px;line-height:1.5;"><?= $nombre ?></div>
                                </td>
                                <td width="10"></td>
                                <td class="detail-column" width="50%" style="padding:14px;background:#151526;border:1px solid #25263c;border-radius:10px;vertical-align:top;">
                                    <div style="font-size:11px;letter-spacing:1px;color:#ff2bd6;font-weight:700;">CORREO</div>
                                    <div style="margin-top:7px;color:#ffffff;font-size:15px;line-height:1.5;"><?= $correo ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-column" width="50%" style="padding:14px;background:#151526;border:1px solid #25263c;border-radius:10px;vertical-align:top;">
                                    <div style="font-size:11px;letter-spacing:1px;color:#00f6ff;font-weight:700;">TELÉFONO</div>
                                    <div style="margin-top:7px;color:#ffffff;font-size:15px;line-height:1.5;"><?= $telefono ?></div>
                                </td>
                                <td width="10"></td>
                                <td class="detail-column" width="50%" style="padding:14px;background:#151526;border:1px solid #25263c;border-radius:10px;vertical-align:top;">
                                    <div style="font-size:11px;letter-spacing:1px;color:#ff2bd6;font-weight:700;">EMPRESA</div>
                                    <div style="margin-top:7px;color:#ffffff;font-size:15px;line-height:1.5;"><?= $empresa ?></div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:16px;background:#0b1420;border-left:4px solid #00f6ff;border-radius:8px;">
                            <tr>
                                <td width="52" align="center" valign="top" style="padding:18px 0 18px 16px;">
                                    <img src="cid:fa-calendar-days" width="26" height="30" alt="" style="display:block;border:0;">
                                </td>
                                <td style="padding:16px 18px;">
                                    <div style="font-size:12px;color:#9da4bd;line-height:1.7;"><strong style="color:#ffffff;">Servicio:</strong> <?= $servicioNombre ?></div>
                                    <div style="font-size:12px;color:#9da4bd;line-height:1.7;"><strong style="color:#ffffff;">Fecha sugerida:</strong> <?= $fecha ?></div>
                                    <div style="font-size:12px;color:#9da4bd;line-height:1.7;"><strong style="color:#ffffff;">Modalidad:</strong> <?= $modalidad ?></div>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:22px;font-size:11px;letter-spacing:1px;color:#f8f32b;font-weight:700;">ASUNTO</div>
                        <div style="margin-top:8px;color:#ffffff;font-size:18px;font-weight:700;line-height:1.45;"><?= $asunto ?></div>

                        <div style="margin-top:20px;padding:20px;background:#090914;border:1px solid #25263c;border-radius:12px;color:#d9deeb;font-size:14px;line-height:1.75;">
                            <?= $mensaje ?>
                        </div>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:26px;">
                            <tr>
                                <td style="border-radius:9px;background:#00f6ff;">
                                    <a href="<?= $panelUrlSeguro ?>" style="display:inline-block;padding:13px 20px;color:#05050a;text-decoration:none;font-size:13px;font-weight:700;">
                                        Abrir bandeja de mensajes
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 28px;background:#0a0a14;border-top:1px solid #20334a;color:#707990;font-size:11px;line-height:1.6;">
                        Mensaje generado automáticamente por Cyberpunk Portfolio.<br>
                        Iconos: Font Awesome Free 7.3.1, licencia CC BY 4.0.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
