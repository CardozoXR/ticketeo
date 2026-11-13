<?php
include "conn.php";
include_once "funciones_navbar.php";

$caja1 = getDatosCaja($conn, 1);
$caja2 = getDatosCaja($conn, 2);
$resumen = getDatosResumen($conn);
?>

<!-- CONTENIDO CAJA 1 -->
<div id="tabCaja1" class="tab-content" style="display: block;">
    <?php if(isset($caja1) && $caja1): ?>
        <div class="info-group">
            <p><strong>Cajero:</strong> <span><?= htmlspecialchars($caja1['cajero_nombre']) ?></span></p>
            <button class="btn-accion" onclick="cambiarNombre(<?= $caja1['cajero_id'] ?>, 1)">Cambiar Nombre</button>
            <button class="btn-accion" onclick="resetPin(<?= $caja1['cajero_id'] ?>)">Restablecer PIN</button>
        </div>
        <div class="info-group totales">
            <p><strong>Efectivo:</strong> $<?= number_format($caja1['efectivo'], 0, ',', '.') ?></p>
            <p><strong>Tarjetas:</strong> $<?= number_format($caja1['tarjeta'], 0, ',', '.') ?></p>
            <p class="resaltado"><strong>TOTAL CAJA 1:</strong> $<?= number_format($caja1['total'], 0, ',', '.') ?></p>
            <p><strong>Tickets emitidos:</strong> <?= $caja1['tickets_emitidos'] ?></p>
        </div>
        <div class="info-group">
            <p><strong>Últimos 10 Tickets:</strong></p>
            <ul class="ticket-list">
                <?php foreach($caja1['ultimos_tickets'] as $t): ?>
                    <li>#<?= htmlspecialchars($t['numero_ticket']) ?> - $<?= number_format($t['total'], 0, ',', '.') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p>Caja 1 no configurada.</p>
    <?php endif; ?>
</div>

<!-- CONTENIDO CAJA 2 -->
<div id="tabCaja2" class="tab-content" style="display: none;">
    <?php if(isset($caja2) && $caja2): ?>
         <div class="info-group">
            <p><strong>Cajero:</strong> <span><?= htmlspecialchars($caja2['cajero_nombre']) ?></span></p>
            <button class="btn-accion" onclick="cambiarNombre(<?= $caja2['cajero_id'] ?>, 2)">Cambiar Nombre</button>
            <button class="btn-accion" onclick="resetPin(<?= $caja2['cajero_id'] ?>)">Restablecer PIN</button>
        </div>
        <div class="info-group totales">
            <p><strong>Efectivo:</strong> $<?= number_format($caja2['efectivo'], 0, ',', '.') ?></p>
            <p><strong>Tarjetas:</strong> $<?= number_format($caja2['tarjeta'], 0, ',', '.') ?></p>
            <p class="resaltado"><strong>TOTAL CAJA 2:</strong> $<?= number_format($caja2['total'], 0, ',', '.') ?></p>
            <p><strong>Tickets emitidos:</strong> <?= $caja2['tickets_emitidos'] ?></p>
        </div>
        <div class="info-group">
            <p><strong>Últimos 10 Tickets:</strong></p>
            <ul class="ticket-list">
                <?php foreach($caja2['ultimos_tickets'] as $t): ?>
                    <li>#<?= htmlspecialchars($t['numero_ticket']) ?> - $<?= number_format($t['total'], 0, ',', '.') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p>Caja 2 no operativa/configurada.</p>
    <?php endif; ?>
</div>

<!-- CONTENIDO RESUMEN -->
<div id="tabResumen" class="tab-content" style="display: none;">
    <?php if(isset($resumen)): ?>
    <div class="info-group totales">
        <p><strong>Total Caja 1:</strong> $<?= number_format($caja1['total'] ?? 0, 0, ',', '.') ?></p>
        <p><strong>Total Caja 2:</strong> $<?= number_format($caja2['total'] ?? 0, 0, ',', '.') ?></p>
        <p class="resaltado"><strong>TOTAL GENERAL:</strong> $<?= number_format($resumen['gran_total'] ?? 0, 0, ',', '.') ?></p>
    </div>
    <div class="info-group">
        <p><strong>Producto + vendido:</strong> <?= htmlspecialchars($resumen['top_producto']) ?></p>
        <p><strong>Vasos:</strong> <?= $resumen['vasos'] ?? 0 ?></p>
        <p><strong>Cortesías:</strong> <?= $resumen['cortesias'] ?? 0 ?></p>
        <p><strong>Total Tickets:</strong> <?= $resumen['total_tickets'] ?? 0 ?></p>
    </div>
    <?php endif; ?>
</div>