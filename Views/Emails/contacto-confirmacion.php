<?php
$e = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$nombre = $e($datos['nombre_completo'] ?? '');
$asunto = $e($datos['asunto'] ?? '');
$mensaje = nl2br($e($datos['mensaje'] ?? ''));
$servicioNombre = $e($servicio['nombre'] ?? 'No especificado');
$fecha = $e($datos['fecha_preferida'] ?? 'No especificada');
$modalidad = $e($datos['modalidad_preferida'] ?? 'No especificada');
$sitioUrlSeguro = $e($sitioUrl ?? '#');
$correoSeguro = $e($correoRespuesta ?? '');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Solicitud recibida</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { width:100% !important; }
            .email-padding { padding:24px 18px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#06060c;color:#f5f7ff;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#06060c;">
    <tr>
        <td align="center" style="padding:28px 12px;">
            <table class="email-shell" role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:640px;background:#10101d;border:1px solid #20334a;border-radius:18px;overflow:hidden;">
                <tr>
                    <td align="center" style="padding:34px 28px 24px;background:#0a0a14;border-bottom:1px solid #20334a;">
                        <img src="cid:fa-circle-check" width="62" height="62" alt="Solicitud recibida" style="display:block;border:0;margin:0 auto 16px;">
                        <div style="font-size:12px;letter-spacing:2px;color:#69ff97;font-weight:700;">TRANSMISSION_RECEIVED</div>
                        <h1 style="margin:9px 0 0;color:#ffffff;font-size:27px;line-height:1.3;">Recibí tu solicitud</h1>
                        <p style="margin:10px 0 0;color:#9da4bd;font-size:14px;line-height:1.6;">Referencia #<?= (int) $idSolicitud ?></p>
                    </td>
                </tr>
                <tr>
                    <td class="email-padding" style="padding:32px 30px;">
                        <p style="margin:0;color:#ffffff;font-size:18px;line-height:1.6;font-weight:700;">Hola, <?= $nombre ?>.</p>
                        <p style="margin:12px 0 0;color:#b8bfd2;font-size:15px;line-height:1.75;">
                            Gracias por escribirme. La información quedó registrada correctamente y revisaré los detalles para responderte al correo que proporcionaste.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;background:#0b1420;border:1px solid #20334a;border-radius:12px;">
                            <tr>
                                <td width="56" align="center" valign="top" style="padding:20px 0 20px 18px;">
                                    <img src="cid:fa-calendar-days" width="28" height="32" alt="" style="display:block;border:0;">
                                </td>
                                <td style="padding:18px 20px;">
                                    <div style="font-size:12px;color:#9da4bd;line-height:1.8;"><strong style="color:#ffffff;">Servicio:</strong> <?= $servicioNombre ?></div>
                                    <div style="font-size:12px;color:#9da4bd;line-height:1.8;"><strong style="color:#ffffff;">Fecha sugerida:</strong> <?= $fecha ?></div>
                                    <div style="font-size:12px;color:#9da4bd;line-height:1.8;"><strong style="color:#ffffff;">Modalidad:</strong> <?= $modalidad ?></div>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:24px;font-size:11px;letter-spacing:1px;color:#00f6ff;font-weight:700;">RESUMEN DE TU MENSAJE</div>
                        <div style="margin-top:8px;color:#ffffff;font-size:17px;font-weight:700;line-height:1.45;"><?= $asunto ?></div>
                        <div style="margin-top:14px;padding:18px;background:#090914;border:1px solid #25263c;border-radius:12px;color:#cbd1df;font-size:14px;line-height:1.75;">
                            <?= $mensaje ?>
                        </div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:25px;">
                            <tr>
                                <td width="44" valign="middle">
                                    <img src="cid:fa-paper-plane" width="28" height="25" alt="" style="display:block;border:0;">
                                </td>
                                <td style="color:#9da4bd;font-size:13px;line-height:1.6;">
                                    Puedes responder directamente a este correo o escribir a
                                    <a href="mailto:<?= $correoSeguro ?>" style="color:#00f6ff;text-decoration:none;"><?= $correoSeguro ?></a>.
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
                            <tr>
                                <td style="border-radius:9px;background:#00f6ff;">
                                    <a href="<?= $sitioUrlSeguro ?>" style="display:inline-block;padding:13px 20px;color:#05050a;text-decoration:none;font-size:13px;font-weight:700;">
                                        Volver al portafolio
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 30px;background:#0a0a14;border-top:1px solid #20334a;color:#707990;font-size:11px;line-height:1.7;">
                        Este es un acuse automático; no significa que la solicitud ya fue evaluada o aceptada.<br>
                        Iconos: Font Awesome Free 7.3.1, licencia CC BY 4.0.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
