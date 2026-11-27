<?php
date_default_timezone_set('America/Montevideo');

require_once __DIR__ . "/../impresion_ticket.php";


class FuncionesImpresion
{
    // 1. true = vista previa en pantalla | false = imprime en impresora real
    public const MODO_PRUEBA = false;

    // 2. true = impresora común vía driver Windows | false = térmica 58mm (HTML -> imagen -> ESC/POS)
    public const IMPRESORA_COMUN = false;

    // 3. Ancho del ticket en píxeles (58mm a 203 dpi = 384 px)
    public const ANCHO_PX = 384;

    // 4. Navegador para renderizar el HTML (se prueba en orden hasta encontrar uno)
    public const NAVEGADORES = [
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    ];

    // 5. DEPURACIÓN: poné una carpeta (ej. 'C:\\temp\\tickets') y se guardará un PNG de cada ticket
    //    tal como lo renderiza el navegador, para ver qué se manda a imprimir. '' = desactivado.
    public const DEBUG_DIR_PNG = '';

    // 6. Tamaño del QR nativo de la impresora. Solo se usa si el código es demasiado largo
    //    para el generador interno (más de 213 caracteres).
    public const QR_TAMANO = 7;

    // 7. Ruta fija del logo: sale de la carpeta de este PHP, entra a "imgs" y usa logo2.png.
    //    Si la dejás vacía ('') se busca el logo en los datos de la venta.
    public const LOGO_RUTA = __DIR__ . '/../imgs/logo2.png';

    //    Medidas máximas del logo en píxeles (el ticket mide 384 de ancho).
    public const LOGO_ANCHO_MAX = 300;
    public const LOGO_ALTO_MAX  = 150;
    // true = tramado (mejor para logos con colores, degradados o fotos)
    // false = blanco/negro puro (más nítido para logos de un solo color y texto)
    public const LOGO_TRAMADO = false;

    // Tablas del generador de QR interno (byte mode, corrección M, versiones 1-10)
    private const QR_CAP = [0, 14, 26, 42, 62, 84, 106, 122, 152, 180, 213];
    // versión => [ec por bloque, bloques grupo1, datos grupo1, bloques grupo2, datos grupo2]
    private const QR_EC = [
        1  => [10, 1, 16, 0, 0],
        2  => [16, 1, 28, 0, 0],
        3  => [26, 1, 44, 0, 0],
        4  => [18, 2, 32, 0, 0],
        5  => [24, 2, 43, 0, 0],
        6  => [16, 4, 27, 0, 0],
        7  => [18, 4, 31, 0, 0],
        8  => [22, 2, 38, 2, 39],
        9  => [22, 3, 36, 2, 37],
        10 => [26, 4, 43, 1, 44],
    ];
    private const QR_ALIGN = [
        1  => [],
        2  => [6, 18],
        3  => [6, 22],
        4  => [6, 26],
        5  => [6, 30],
        6  => [6, 34],
        7  => [6, 22, 38],
        8  => [6, 24, 42],
        9  => [6, 26, 46],
        10 => [6, 28, 50],
    ];

    public static function imprimirTicketsVenta(?string $nombreImpresora, array $datosVenta): array
    {
        date_default_timezone_set('America/Montevideo');

        if (empty($datosVenta['tickets'])) {
            return ["ok" => false, "error" => "No hay tickets para imprimir."];
        }

        if (self::MODO_PRUEBA) {
            return ["ok" => true, "modo_prueba" => true];
        }

        if (empty($nombreImpresora) || !preg_match('/^[a-zA-Z0-9_\-\\\\. ]+$/', $nombreImpresora)) {
            return ["ok" => false, "error" => "Nombre de impresora inválido o no asignado."];
        }

        $fechaHora    = date('d/m/Y H:i');
        $eventoNombre = self::limpiarTexto(strtoupper($datosVenta['evento_nombre'] ?? 'Evento'));
        $rawTotal     = "";

        if (self::IMPRESORA_COMUN) {
            foreach ($datosVenta['tickets'] as $ticket) {
                $esCortesia = ((float)$ticket['precio'] === 0.0);
                $precioTxt  = $esCortesia
                    ? "CORTESIA"
                    : "$ " . number_format((float)$ticket['precio'], 2, '.', '');

                $etiqueta = self::limpiarTexto(strtoupper($ticket['etiqueta']));
                $cajaTxt  = self::limpiarTexto($datosVenta['caja_nombre']);

                $rawTotal .= "================================\r\n";
                $rawTotal .= "  CODIGO: " . $ticket['codigo_qr'] . "\r\n";
                $rawTotal .= "  EVENTO: " . $eventoNombre . "\r\n";
                $rawTotal .= "--------------------------------\r\n";
                $rawTotal .= "  1x " . $etiqueta . "\r\n";
                $rawTotal .= "  " . $precioTxt . "\r\n";
                $rawTotal .= "  TICKET: " . $ticket['numero_ticket'] . "\r\n";
                $rawTotal .= "--------------------------------\r\n";
                $rawTotal .= "  Op #" . $datosVenta['numero_operacion'] . " - " . $cajaTxt . "\r\n";
                $rawTotal .= "  Cajero: " . $cajaTxt . " | " . $fechaHora . "\r\n";
                $rawTotal .= "================================\r\n\r\n";
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'tck_') . '.txt';
            file_put_contents($tmpFile, $rawTotal);

            $nombreBuscado   = ltrim(str_replace('\\\\localhost\\', '', $nombreImpresora), '\\');
            $nombreEscapado  = str_replace("'", "''", $nombreBuscado);
            $archivoEscapado = str_replace("'", "''", $tmpFile);

            $psScript = "\$p = Get-Printer | Where-Object { \$_.ShareName -eq '{$nombreEscapado}' -or \$_.Name -eq '{$nombreEscapado}' } | Select-Object -First 1; "
                      . "if (-not \$p) { Write-Error 'No se encontro impresora {$nombreEscapado}'; exit 1 }; "
                      . "Get-Content -Path '{$archivoEscapado}' -Encoding UTF8 | Out-Printer -Name \$p.Name";

            $cmd = 'powershell -NoProfile -NonInteractive -Command "' . $psScript . '" 2>&1';

            $salida = [];
            $codigo = 0;
            exec($cmd, $salida, $codigo);
            @unlink($tmpFile);

            if ($codigo !== 0) {
                $detalleError = trim(implode(" ", $salida));
                return [
                    "ok"    => false,
                    "error" => "Error driver Windows ({$nombreBuscado}): " . ($detalleError !== '' ? $detalleError : "código {$codigo}")
                ];
            }

            return ["ok" => true, "modo_prueba" => false];
        }

        // ---------------------------------------------------------------
        // Impresión térmica 58mm: una llamada a imprimirTicket() por ticket
        // ---------------------------------------------------------------
        $rutaImpresora = str_starts_with($nombreImpresora, '\\\\')
            ? $nombreImpresora
            : '\\\\localhost\\' . $nombreImpresora;

        if (!function_exists('imprimirTicket')) {
            return ["ok" => false, "error" => "No se encontró la función imprimirTicket() (falta el require del archivo)."];
        }

        $logoPath  = $datosVenta['logo_path'] ?? (dirname(__DIR__) . "/imgs/logo2.png");
        $fechaEvt  = $datosVenta['evento_fecha'] ?? date('10.12');
        $errores   = [];

        foreach ($datosVenta['tickets'] as $ticket) {
            $resultado = \imprimirTicket(
                self::limpiarTexto(strtoupper($ticket['etiqueta'])),   // nombre / tipo de entrada
                (float)$ticket['precio'],                              // precio
                (string)$ticket['codigo_qr'],                          // código del QR
                $eventoNombre,                                         // evento
                $fechaEvt,                                             // fecha
                $rutaImpresora,                                        // impresora
                $logoPath                                              // logo
            );

            if (($resultado['status'] ?? '') !== 'ok') {
                $errores[] = "Ticket " . ($ticket['numero_ticket'] ?? '?') . ": " . ($resultado['message'] ?? 'error desconocido');
            }
        }

        if (!empty($errores)) {
            return ["ok" => false, "error" => implode(" | ", $errores)];
        }

        return ["ok" => true, "modo_prueba" => false];
    }

    public static function imprimirResumen(?string $nombreImpresora, array $resumen): array
{
    if (empty($nombreImpresora)) {
        $nombreImpresora = "caja1";
    }

    if (self::MODO_PRUEBA) {
        return ["ok" => true, "modo_prueba" => true];
    }

    require_once dirname(__DIR__) . '/impresion_ticket.php';

    $logoPath = $resumen['logo_path'] ?? (dirname(__DIR__) . "/imgs/logo2.png");
    $r = \imprimirResumen($nombreImpresora, $resumen, $logoPath);

    return ($r['status'] ?? '') === 'ok'
        ? ["ok" => true, "modo_prueba" => false]
        : ["ok" => false, "error" => $r['message'] ?? 'Error desconocido'];
}

    // ===================================================================
    // HTML del ticket (editá el CSS/HTML a gusto: es lo que se imprime)
    // $parte: 'completo' | 'arriba' | 'abajo' (las dos últimas solo en el modo QR nativo)
    // ===================================================================
    private static function htmlTicket(array $ticket, string $eventoNombre, string $qrSvg, string $parte = 'completo', string $logoUri = ''): string
    {
        $ancho      = self::ANCHO_PX;
        $esCortesia = ((float)$ticket['precio'] === 0.0);
        $precioTxt  = $esCortesia
            ? 'CORTESIA'
            : '$ ' . number_format((float)$ticket['precio'], 2, '.', '');

        $etiqueta = strtoupper((string)$ticket['etiqueta']);
        $qrData   = (string)$ticket['codigo_qr'];

        $e = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $bloqueQr = ($qrSvg !== '') ? '<div id="qr">' . $qrSvg . '</div>' : '';

        $logoHtml = ($logoUri !== '') ? '<div class="logo"><img src="' . $logoUri . '" alt=""></div>' : '';

        $arriba = '<div class="codigo">' . $e($qrData) . '</div>'
                . $logoHtml
                . '<div class="evento">' . $e($eventoNombre) . ' 10.12</div>'
                . '<div class="sep"></div>'
                . '<div class="etiqueta">1x ' . $e($etiqueta) . '</div>'
                . '<div class="precio">' . $e($precioTxt) . '</div>'
                . $bloqueQr;

        $abajo  = '<div class="codigo-txt">' . $e($qrData) . '</div>'
                . '<div class="sep" style="margin: bottom 0 !important;"></div>';

        $cuerpo = ($parte === 'arriba') ? $arriba : (($parte === 'abajo') ? $abajo : $arriba . $abajo);

        return '<!DOCTYPE html><html><head><meta charset="UTF-8">'
            . '<style>'
            . '*{box-sizing:border-box;margin:0;padding:0;}'
            . 'html,body{background:#fff;color:#000;}'
            . "body{width:{$ancho}px;font-family:Arial,Helvetica,sans-serif;}"
            . '.ticket{text-align:center;}'
            . '.logo{text-align:center;margin:20px 0;line-height:0;}'
            . '.logo img{display:inline-block;image-rendering:pixelated;}'
            . '.codigo{background:#000;color:#fff;font-size:22px;padding:0px 5px;letter-spacing:3px;}'
            . '.evento{font-weight:900;font-size:28px;margin-top:10px;line-height:1.15;}'
            . '.sep{border-top:5px dashed #000;margin:10px 0;}'
            . '.etiqueta{font-weight:900;font-size:30px;line-height:1.1;word-break:break-word;}'
            . '.precio{font-weight:900;font-size:36px;margin-top:4px;}'
            . '#qr{margin:12px auto 6px;text-align:center;line-height:0;}'
            . '#qr svg{display:inline-block;}'
            . '.codigo-txt{font-weight:900;font-size:22px;margin-top:4px;}'
            . '</style></head><body><div class="ticket">'
            . $cuerpo
            . '</div></body></html>';
    }

    // ===================================================================
    // Generador de QR en PHP puro (byte mode, corrección M, versiones 1-10)
    // Devuelve la matriz de módulos (0/1) o null si el texto es muy largo.
    // ===================================================================
    private static function qrGfMul(int $a, int $b): int
    {
        $r = 0;
        for ($i = 7; $i >= 0; $i--) {
            $r = ($r << 1) ^ (($r >> 7) * 0x11D);
            $r ^= (($b >> $i) & 1) * $a;
        }
        return $r & 0xFF;
    }

    private static function qrRsDiv(int $deg): array
    {
        $res = array_fill(0, $deg, 0);
        $res[$deg - 1] = 1;
        $root = 1;
        for ($i = 0; $i < $deg; $i++) {
            for ($j = 0; $j < $deg; $j++) {
                $res[$j] = self::qrGfMul($res[$j], $root);
                if ($j + 1 < $deg) {
                    $res[$j] ^= $res[$j + 1];
                }
            }
            $root = self::qrGfMul($root, 2);
        }
        return $res;
    }

    private static function qrRsRem(array $data, array $div): array
    {
        $len = count($div);
        $res = array_fill(0, $len, 0);
        foreach ($data as $b) {
            $f = $b ^ $res[0];
            array_shift($res);
            $res[] = 0;
            for ($i = 0; $i < $len; $i++) {
                $res[$i] ^= self::qrGfMul($div[$i], $f);
            }
        }
        return $res;
    }

    private static function qrMatriz(string $data): ?array
    {
        $n = strlen($data);
        if ($n === 0) {
            return null;
        }

        $ver = 0;
        for ($v = 1; $v <= 10; $v++) {
            if ($n <= self::QR_CAP[$v]) {
                $ver = $v;
                break;
            }
        }
        if ($ver === 0) {
            return null;
        }

        [$ec, $b1, $d1, $b2, $d2] = self::QR_EC[$ver];
        $totalData = $b1 * $d1 + $b2 * $d2;

        // --- Bits de datos ---
        $bits = [];
        $app = function (int $val, int $cnt) use (&$bits): void {
            for ($i = $cnt - 1; $i >= 0; $i--) {
                $bits[] = ($val >> $i) & 1;
            }
        };
        $app(4, 4);                                  // modo byte
        $app($n, $ver >= 10 ? 16 : 8);               // longitud
        for ($i = 0; $i < $n; $i++) {
            $app(ord($data[$i]), 8);
        }
        $cap = $totalData * 8;
        $app(0, min(4, $cap - count($bits)));        // terminador
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $cw = [];
        $nb = count($bits);
        for ($i = 0; $i < $nb; $i += 8) {
            $v = 0;
            for ($k = 0; $k < 8; $k++) {
                $v = ($v << 1) | $bits[$i + $k];
            }
            $cw[] = $v;
        }
        $pad = 0xEC;
        while (count($cw) < $totalData) {
            $cw[] = $pad;
            $pad = ($pad === 0xEC) ? 0x11 : 0xEC;
        }

        // --- Bloques + corrección de errores ---
        $div    = self::qrRsDiv($ec);
        $blocks = [];
        $ecs    = [];
        $pos    = 0;
        for ($bi = 0; $bi < $b1 + $b2; $bi++) {
            $L   = ($bi < $b1) ? $d1 : $d2;
            $blk = array_slice($cw, $pos, $L);
            $pos += $L;
            $blocks[] = $blk;
            $ecs[]    = self::qrRsRem($blk, $div);
        }

        $final = [];
        $maxd  = max($d1, $d2);
        for ($i = 0; $i < $maxd; $i++) {
            foreach ($blocks as $blk) {
                if ($i < count($blk)) {
                    $final[] = $blk[$i];
                }
            }
        }
        for ($i = 0; $i < $ec; $i++) {
            foreach ($ecs as $e) {
                $final[] = $e[$i];
            }
        }

        // --- Matriz ---
        $size = $ver * 4 + 17;
        $mod  = array_fill(0, $size, array_fill(0, $size, 0));
        $fn   = array_fill(0, $size, array_fill(0, $size, 0));

        $setf = function (int $x, int $y, bool $v) use (&$mod, &$fn): void {
            $mod[$y][$x] = $v ? 1 : 0;
            $fn[$y][$x]  = 1;
        };

        // Líneas de temporización
        for ($i = 0; $i < $size; $i++) {
            $setf(6, $i, $i % 2 === 0);
            $setf($i, 6, $i % 2 === 0);
        }

        // Patrones de búsqueda (3 esquinas)
        foreach ([[3, 3], [$size - 4, 3], [3, $size - 4]] as [$cx, $cy]) {
            for ($dy = -4; $dy <= 4; $dy++) {
                for ($dx = -4; $dx <= 4; $dx++) {
                    $x = $cx + $dx;
                    $y = $cy + $dy;
                    if ($x >= 0 && $x < $size && $y >= 0 && $y < $size) {
                        $d = max(abs($dx), abs($dy));
                        $setf($x, $y, $d !== 2 && $d !== 4);
                    }
                }
            }
        }

        // Patrones de alineación
        $al = self::QR_ALIGN[$ver];
        $na = count($al);
        for ($i = 0; $i < $na; $i++) {
            for ($j = 0; $j < $na; $j++) {
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $na - 1) || ($i === $na - 1 && $j === 0)) {
                    continue;
                }
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $setf($al[$i] + $dx, $al[$j] + $dy, max(abs($dx), abs($dy)) !== 1);
                    }
                }
            }
        }

        // Información de formato (nivel M = 00)
        $drawFormat = function (int $mask) use ($setf, $size): void {
            $rem = $mask;
            for ($i = 0; $i < 10; $i++) {
                $rem = ($rem << 1) ^ (($rem >> 9) * 0x537);
            }
            $bs  = (($mask << 10) | $rem) ^ 0x5412;
            $bit = fn(int $i): bool => ((($bs >> $i) & 1) === 1);

            for ($i = 0; $i < 6; $i++) {
                $setf(8, $i, $bit($i));
            }
            $setf(8, 7, $bit(6));
            $setf(8, 8, $bit(7));
            $setf(7, 8, $bit(8));
            for ($i = 9; $i < 15; $i++) {
                $setf(14 - $i, 8, $bit($i));
            }
            for ($i = 0; $i < 8; $i++) {
                $setf($size - 1 - $i, 8, $bit($i));
            }
            for ($i = 8; $i < 15; $i++) {
                $setf(8, $size - 15 + $i, $bit($i));
            }
            $setf(8, $size - 8, true);
        };
        $drawFormat(0);

        // Información de versión (v7 en adelante)
        if ($ver >= 7) {
            $rem = $ver;
            for ($i = 0; $i < 12; $i++) {
                $rem = ($rem << 1) ^ (($rem >> 11) * 0x1F25);
            }
            $bs = ($ver << 12) | $rem;
            for ($i = 0; $i < 18; $i++) {
                $b = ((($bs >> $i) & 1) === 1);
                $a = $size - 11 + ($i % 3);
                $c = intdiv($i, 3);
                $setf($a, $c, $b);
                $setf($c, $a, $b);
            }
        }

        // Colocar los datos en zigzag
        $idx   = 0;
        $total = count($final) * 8;
        $right = $size - 1;
        while ($right >= 1) {
            if ($right === 6) {
                $right = 5;
            }
            for ($vert = 0; $vert < $size; $vert++) {
                for ($j = 0; $j < 2; $j++) {
                    $x      = $right - $j;
                    $upward = ((($right + 1) & 2) === 0);
                    $y      = $upward ? ($size - 1 - $vert) : $vert;
                    if ($fn[$y][$x] === 0 && $idx < $total) {
                        $mod[$y][$x] = ($final[$idx >> 3] >> (7 - ($idx & 7))) & 1;
                        $idx++;
                    }
                }
            }
            $right -= 2;
        }

        // Máscaras
        $applyMask = function (int $m) use (&$mod, &$fn, $size): void {
            for ($y = 0; $y < $size; $y++) {
                for ($x = 0; $x < $size; $x++) {
                    if ($fn[$y][$x] === 1) {
                        continue;
                    }
                    switch ($m) {
                        case 0:  $inv = (($x + $y) % 2) === 0; break;
                        case 1:  $inv = ($y % 2) === 0; break;
                        case 2:  $inv = ($x % 3) === 0; break;
                        case 3:  $inv = (($x + $y) % 3) === 0; break;
                        case 4:  $inv = ((intdiv($x, 3) + intdiv($y, 2)) % 2) === 0; break;
                        case 5:  $inv = ((($x * $y) % 2) + (($x * $y) % 3)) === 0; break;
                        case 6:  $inv = (((($x * $y) % 2) + (($x * $y) % 3)) % 2) === 0; break;
                        default: $inv = (((($x + $y) % 2) + (($x * $y) % 3)) % 2) === 0; break;
                    }
                    if ($inv) {
                        $mod[$y][$x] ^= 1;
                    }
                }
            }
        };

        $penalty = function () use (&$mod, $size): int {
            $p = 0;
            $patA = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];
            $patB = [0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1];

            for ($dir = 0; $dir < 2; $dir++) {
                for ($i = 0; $i < $size; $i++) {
                    $row = [];
                    for ($k = 0; $k < $size; $k++) {
                        $row[] = ($dir === 0) ? $mod[$i][$k] : $mod[$k][$i];
                    }
                    $run = 1;
                    for ($k = 1; $k < $size; $k++) {
                        if ($row[$k] === $row[$k - 1]) {
                            $run++;
                        } else {
                            if ($run >= 5) {
                                $p += 3 + $run - 5;
                            }
                            $run = 1;
                        }
                    }
                    if ($run >= 5) {
                        $p += 3 + $run - 5;
                    }
                    for ($k = 0; $k <= $size - 11; $k++) {
                        $s = array_slice($row, $k, 11);
                        if ($s === $patA || $s === $patB) {
                            $p += 40;
                        }
                    }
                }
            }

            for ($y = 0; $y < $size - 1; $y++) {
                for ($x = 0; $x < $size - 1; $x++) {
                    $v = $mod[$y][$x];
                    if ($v === $mod[$y][$x + 1] && $v === $mod[$y + 1][$x] && $v === $mod[$y + 1][$x + 1]) {
                        $p += 3;
                    }
                }
            }

            $dark = 0;
            for ($y = 0; $y < $size; $y++) {
                $dark += array_sum($mod[$y]);
            }
            $k = intdiv(abs($dark * 20 - $size * $size * 10), $size * $size);
            $p += $k * 10;

            return $p;
        };

        $best  = 0;
        $bestP = null;
        for ($m = 0; $m < 8; $m++) {
            $applyMask($m);
            $drawFormat($m);
            $pp = $penalty();
            if ($bestP === null || $pp < $bestP) {
                $bestP = $pp;
                $best  = $m;
            }
            $applyMask($m); // deshacer
        }
        $applyMask($best);
        $drawFormat($best);

        return $mod;
    }

    /** Matriz de módulos -> SVG nítido (escala entera, ~250 px). */
    private static function qrSvg(array $m, int $quiet = 2, int $objetivoPx = 250): string
    {
        $n      = count($m);
        $total  = $n + 2 * $quiet;
        $escala = max(1, intdiv($objetivoPx, $total));
        $px     = $escala * $total;

        $path = '';
        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($m[$y][$x] === 1) {
                    $path .= 'M' . ($x + $quiet) . ',' . ($y + $quiet) . 'h1v1h-1z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $px . '" height="' . $px . '"'
             . ' viewBox="0 0 ' . $total . ' ' . $total . '" shape-rendering="crispEdges">'
             . '<rect width="' . $total . '" height="' . $total . '" fill="#fff"/>'
             . '<path d="' . $path . '" fill="#000"/></svg>';
    }

    // ===================================================================
    // Utilidades de renderizado
    // ===================================================================

    /**
     * Prepara el logo del evento para la térmica.
     * Busca en $datosVenta las claves 'evento_logo', 'logo_evento' o 'logo'. Acepta:
     *   - ruta de archivo (absoluta, relativa a esta carpeta o a la raíz web: '/uploads/logo.png')
     *   - URL http(s)
     *   - data URI base64 ('data:image/png;base64,...')
     * Devuelve un data URI PNG en blanco y negro, o '' si no hay logo o no se pudo leer.
     */
    private static function logoDataUri(array $datosVenta): string
    {
        $fuente = (self::LOGO_RUTA !== '') ? self::LOGO_RUTA : self::buscarLogo($datosVenta);
        if ($fuente === '') {
            self::logDebug('No se encontró ninguna clave con "logo" en datosVenta. Claves recibidas: ' . implode(', ', array_keys($datosVenta)));
            return '';
        }
        if (!function_exists('imagecreatefromstring')) {
            self::logDebug('Falta la extensión GD.');
            return '';
        }

        $bin = null;
        if (str_starts_with($fuente, 'data:')) {
            $pos = strpos($fuente, ',');
            if ($pos !== false) {
                $dec = base64_decode(substr($fuente, $pos + 1), true);
                $bin = ($dec !== false) ? $dec : null;
            }
        } elseif (preg_match('#^https?://#i', $fuente)) {
            $ctx = stream_context_create(['http' => ['timeout' => 5]]);
            $r   = @file_get_contents($fuente, false, $ctx);
            $bin = ($r !== false) ? $r : null;
        } else {
            $rel = ltrim($fuente, '/\\');
            $candidatos = [
                $fuente,
                __DIR__ . DIRECTORY_SEPARATOR . $rel,
                rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . DIRECTORY_SEPARATOR . $rel,
            ];
            foreach ($candidatos as $c) {
                if (is_file($c)) {
                    $r   = file_get_contents($c);
                    $bin = ($r !== false) ? $r : null;
                    break;
                }
            }
        }

        if ($bin === null || $bin === '') {
            self::logDebug('No se pudo leer el logo. Valor recibido: ' . substr($fuente, 0, 150)
                . ' | DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? '(vacío)') . ' | carpeta del PHP: ' . __DIR__);
            return '';
        }

        $src = @imagecreatefromstring($bin);
        if (!$src) {
            self::logDebug('Se leyó el archivo (' . strlen($bin) . ' bytes) pero GD no pudo interpretarlo como imagen (¿formato no soportado, p. ej. SVG o WebP?).');
            return '';
        }

        // Redimensionar (sin agrandar más de 2x), sobre fondo blanco (aplana transparencias)
        $sw = imagesx($src);
        $sh = imagesy($src);
        $escala = min(self::LOGO_ANCHO_MAX / $sw, self::LOGO_ALTO_MAX / $sh, 2.0);
        $nw = max(1, (int)round($sw * $escala));
        $nh = max(1, (int)round($sh * $escala));

        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagealphablending($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
        imagedestroy($src);

        // Escala de grises
        $gris = [];
        for ($y = 0; $y < $nh; $y++) {
            for ($x = 0; $x < $nw; $x++) {
                $rgb = imagecolorat($dst, $x, $y);
                $gris[$y][$x] = 0.299 * (($rgb >> 16) & 0xFF) + 0.587 * (($rgb >> 8) & 0xFF) + 0.114 * ($rgb & 0xFF);
            }
        }
        imagedestroy($dst);

        // Blanco y negro: tramado Floyd-Steinberg o umbral simple
        $out    = imagecreatetruecolor($nw, $nh);
        $blanco = imagecolorallocate($out, 255, 255, 255);
        $negro  = imagecolorallocate($out, 0, 0, 0);
        imagefill($out, 0, 0, $blanco);

        for ($y = 0; $y < $nh; $y++) {
            for ($x = 0; $x < $nw; $x++) {
                $viejo = $gris[$y][$x];
                $nuevo = ($viejo < 128) ? 0 : 255;
                if ($nuevo === 0) {
                    imagesetpixel($out, $x, $y, $negro);
                }
                if (self::LOGO_TRAMADO) {
                    $err = $viejo - $nuevo;
                    if ($x + 1 < $nw)                  { $gris[$y][$x + 1]     += $err * 7 / 16; }
                    if ($y + 1 < $nh && $x > 0)        { $gris[$y + 1][$x - 1] += $err * 3 / 16; }
                    if ($y + 1 < $nh)                  { $gris[$y + 1][$x]     += $err * 5 / 16; }
                    if ($y + 1 < $nh && $x + 1 < $nw)  { $gris[$y + 1][$x + 1] += $err * 1 / 16; }
                }
            }
        }

        ob_start();
        imagepng($out);
        $png = (string)ob_get_clean();
        imagedestroy($out);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /** Busca el primer valor de texto cuya clave contenga "logo" (también dentro de arrays anidados). */
    private static function buscarLogo($datos, int $nivel = 0): string
    {
        if (!is_array($datos) || $nivel > 3) {
            return '';
        }
        foreach ($datos as $k => $v) {
            if (is_string($v) && trim($v) !== '' && preg_match('/logo/i', (string)$k)) {
                return trim($v);
            }
        }
        foreach ($datos as $k => $v) {
            if (is_array($v) && $k !== 'tickets') {
                $r = self::buscarLogo($v, $nivel + 1);
                if ($r !== '') {
                    return $r;
                }
            }
        }
        return '';
    }

    /** Escribe un motivo en logo_debug.txt dentro de DEBUG_DIR_PNG (solo si está configurada). */
    private static function logDebug(string $msg): void
    {
        if (self::DEBUG_DIR_PNG === '' || !is_dir(self::DEBUG_DIR_PNG)) {
            return;
        }
        @file_put_contents(
            rtrim(self::DEBUG_DIR_PNG, '\\/') . DIRECTORY_SEPARATOR . 'logo_debug.txt',
            date('d/m/Y H:i:s') . ' - ' . $msg . "\r\n",
            FILE_APPEND
        );
    }

    /** HTML -> PNG -> comandos raster. Devuelve null si algo falla. */
    private static function rasterDesdeHtml(string $html, string $navegador): ?string
    {
        $png = self::htmlAPng($html, $navegador);
        if ($png === null) {
            return null;
        }

        if (self::DEBUG_DIR_PNG !== '' && is_dir(self::DEBUG_DIR_PNG)) {
            @copy($png, rtrim(self::DEBUG_DIR_PNG, '\\/') . DIRECTORY_SEPARATOR . 'ticket_' . date('His') . '_' . uniqid() . '.png');
        }

        $raster = self::pngAEscPos($png);
        @unlink($png);
        return $raster;
    }

    /** QR nativo ESC/POS (GS ( k): solo para códigos muy largos. Se centra si antes se envió ESC a 1. */
    private static function comandoQrNativo(string $data): string
    {
        $GS     = "\x1D";
        $tamano = chr(max(1, min(16, self::QR_TAMANO)));
        $lenBin = pack('v', strlen($data) + 3);

        return $GS . "(k\x04\x00\x31\x41\x32\x00"
             . $GS . "(k\x03\x00\x31\x43" . $tamano
             . $GS . "(k\x03\x00\x31\x45\x31"
             . $GS . "(k" . $lenBin . "\x31\x50\x30" . $data
             . $GS . "(k\x03\x00\x31\x51\x30";
    }

    private static function buscarNavegador(): ?string
    {
        foreach (self::NAVEGADORES as $ruta) {
            if (is_file($ruta)) {
                return $ruta;
            }
        }
        return null;
    }

    private static function htmlAPng(string $html, string $navegador): ?string
    {
        $base     = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tck_' . uniqid();
        $htmlFile = $base . '.html';
        $pngFile  = $base . '.png';
        $perfil   = $base . '_perfil';

        file_put_contents($htmlFile, $html);

        $url = 'file:///' . str_replace('\\', '/', $htmlFile);

        $cmd = '"' . $navegador . '"'
             . ' --headless=new --disable-gpu --hide-scrollbars --no-first-run'
             . ' --user-data-dir="' . $perfil . '"'
             . ' --force-device-scale-factor=1'
             . ' --window-size=' . self::ANCHO_PX . ',1600'
             . ' --virtual-time-budget=3000'
             . ' --screenshot="' . $pngFile . '"'
             . ' "' . $url . '" 2>&1';

        $salida = [];
        $codigo = 0;
        exec('cmd /c "' . $cmd . '"', $salida, $codigo);

        // Esperar a que aparezca el archivo (hasta ~5 s)
        for ($i = 0; $i < 25 && !(is_file($pngFile) && filesize($pngFile) > 0); $i++) {
            usleep(200000);
        }

        @unlink($htmlFile);
        self::borrarCarpeta($perfil);

        return (is_file($pngFile) && filesize($pngFile) > 0) ? $pngFile : null;
    }

    /** Convierte un PNG a comandos raster ESC/POS (GS v 0), en bloques de 255 filas. */
    private static function pngAEscPos(string $png): ?string
    {
        $im = @imagecreatefrompng($png);
        if (!$im) {
            return null;
        }

        $w = imagesx($im);
        $h = imagesy($im);

        // Recortar el blanco sobrante abajo (deja 24 px de margen)
        $ultima = 0;
        for ($y = $h - 1; $y >= 0 && $ultima === 0; $y--) {
            for ($x = 0; $x < $w; $x++) {
                if ((imagecolorat($im, $x, $y) & 0xFF) < 200) {
                    $ultima = $y;
                    break;
                }
            }
        }
        $h = min($h, $ultima + 24);

        $wb  = intdiv($w + 7, 8);
        $out = '';

        for ($inicio = 0; $inicio < $h; $inicio += 255) {
            $filas = min(255, $h - $inicio);
            $data  = '';

            for ($y = $inicio; $y < $inicio + $filas; $y++) {
                for ($xb = 0; $xb < $wb; $xb++) {
                    $byte = 0;
                    for ($bit = 0; $bit < 8; $bit++) {
                        $x = $xb * 8 + $bit;
                        if ($x < $w && (imagecolorat($im, $x, $y) & 0xFF) < 128) {
                            $byte |= (0x80 >> $bit);
                        }
                    }
                    $data .= chr($byte);
                }
            }

            $out .= "\x1D" . "v0" . "\x00" . pack('vv', $wb, $filas) . $data;
        }

        imagedestroy($im);
        return $out;
    }

    /** Envía bytes crudos a la impresora compartida y devuelve el motivo real si falla. */
    private static function enviarRaw(string $contenido, string $ruta, string $mensajeError): array
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'raw_');
        file_put_contents($tmpFile, $contenido);

        $enviado = @copy($tmpFile, $ruta);
        $detalle = '';

        if (!$enviado) {
            $ultimo  = error_get_last();
            $detalle = $ultimo['message'] ?? '';

            // Segundo intento con copy /b de Windows
            $salida = [];
            $codigo = 0;
            exec('cmd /c "copy /b "' . $tmpFile . '" "' . $ruta . '" 2>&1"', $salida, $codigo);

            if ($codigo === 0) {
                $enviado = true;
            } else {
                $detalle = trim(implode(' ', $salida)) ?: $detalle;
            }
        }

        @unlink($tmpFile);

        if (!$enviado) {
            return [
                "ok"    => false,
                "error" => "{$mensajeError} ({$ruta})" . ($detalle !== '' ? ': ' . $detalle : '.')
            ];
        }

        return ["ok" => true, "modo_prueba" => false];
    }

    private static function borrarCarpeta(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }

    private static function limpiarTexto(string $texto): string
    {
        return strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            '·' => '-'
        ]);
    }
}
?>
