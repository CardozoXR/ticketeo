<!-- CONTENIDO CAJA 1 -->
<div id="tabCaja1" class="tab-content" style="display: block;">
    <?php if (isset($caja1) && $caja1): ?>
        <div class="info-group">
            <p><strong>Cajero:</strong> <span><?= htmlspecialchars($caja1['cajero_nombre']) ?></span></p>
            <?php if (!empty($caja1['cajero_id'])): ?>
                <div class="btn-navCajero" style="display: flex">
                    <button class="btn-accion" onclick="cambiarNombre(<?= (int)$caja1['cajero_id'] ?>, 1)" style="font-size:11px">Cambiar Nombre</button>
                    <button class="btn-accion" onclick="resetPin(<?= (int)$caja1['cajero_id'] ?>)" style="font-size:11px">Restablecer PIN</button>
                </div>
            <?php endif; ?>
        </div>
        <div class="info-group totales">
            <p><strong>Efectivo:</strong> $<?= number_format($caja1['efectivo'], 0, ',', '.') ?></p>
            <p><strong>Tarjetas:</strong> $<?= number_format($caja1['tarjeta'], 0, ',', '.') ?></p>
            <p class="resaltado"><strong>TOTAL CAJA 1:</strong> $<?= number_format($caja1['total'], 0, ',', '.') ?></p>
            <p><strong>Tickets emitidos:</strong> <?= (int)$caja1['tickets_emitidos'] ?></p>
        </div>
        <div class="info-group">
            <p><strong>Últimas 10 Ventas:</strong></p>
            <ul class="ticket-list">
                <?php foreach ($caja1['ultimos_tickets'] as $t): ?>
                    <li>
                        Op #<?= (int)$t['numero_operacion'] ?>
                        (<?= (int)$t['cantidad_tickets'] ?> ticket<?= $t['cantidad_tickets'] > 1 ? 's' : '' ?>)
                        - $<?= number_format($t['total'], 0, ',', '.') ?>
                        <div class="reimprimirTickets" data-venta-id="<?= (int)$t['venta_id'] ?>" data-op="<?= (int)$t['numero_operacion'] ?>">
                            <i class='bx bx-printer'></i>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p>Caja 1 no configurada.</p>
    <?php endif; ?>
</div>

<!-- CONTENIDO CAJA 2 -->
<div id="tabCaja2" class="tab-content" style="display: none;">
    <?php if (isset($caja2) && $caja2): ?>
        <div class="info-group">
            <p><strong>Cajero:</strong> <span><?= htmlspecialchars($caja2['cajero_nombre']) ?></span></p>
            <?php if (!empty($caja2['cajero_id'])): ?>
                <div class="btn-navCajero" style="display: flex">
                    <button class="btn-accion" onclick="cambiarNombre(<?= (int)$caja1['cajero_id'] ?>, 1)" style="font-size:11px">Cambiar Nombre</button>
                    <button class="btn-accion" onclick="resetPin(<?= (int)$caja1['cajero_id'] ?>)" style="font-size:11px">Restablecer PIN</button>
                </div>
            <?php endif; ?>
        </div>
        <div class="info-group totales">
            <p><strong>Efectivo:</strong> $<?= number_format($caja2['efectivo'], 0, ',', '.') ?></p>
            <p><strong>Tarjetas:</strong> $<?= number_format($caja2['tarjeta'], 0, ',', '.') ?></p>
            <p class="resaltado"><strong>TOTAL CAJA 2:</strong> $<?= number_format($caja2['total'], 0, ',', '.') ?></p>
            <p><strong>Tickets emitidos:</strong> <?= (int)$caja2['tickets_emitidos'] ?></p>
        </div>
        <div class="info-group">
            <p><strong>Últimas 10 Ventas:</strong></p>
            <ul class="ticket-list">
                <?php foreach ($caja2['ultimos_tickets'] as $t): ?>
                    <li>
                        Op #<?= (int)$t['numero_operacion'] ?>
                        (<?= (int)$t['cantidad_tickets'] ?> ticket<?= $t['cantidad_tickets'] > 1 ? 's' : '' ?>)
                        - $<?= number_format($t['total'], 0, ',', '.') ?>
                        <div class="reimprimirTickets" data-venta-id="<?= (int)$t['venta_id'] ?>" data-op="<?= (int)$t['numero_operacion'] ?>">
                            <i class='bx bx-printer'></i>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p>Caja 2 no operativa/configurada.</p>
    <?php endif; ?>
</div>

<!-- CONTENIDO RESUMEN -->
<div id="tabResumen" class="tab-content" style="display: none;">
    <?php if (isset($resumen)): ?>
        <div class="info-group totales">
            <p><strong>Total Caja 1:</strong> $<?= number_format($caja1['total'] ?? 0, 0, ',', '.') ?></p>
            <p><strong>Total Caja 2:</strong> $<?= number_format($caja2['total'] ?? 0, 0, ',', '.') ?></p>
            <p class="resaltado"><strong>TOTAL GENERAL:</strong> $<?= number_format($resumen['gran_total'] ?? 0, 0, ',', '.') ?></p>
        </div>
        <div class="info-group">
            <p><strong>Producto + vendido:</strong> <?= htmlspecialchars($resumen['top_producto']) ?></p>
            <p><strong>Vasos:</strong> <?= (int)($resumen['vasos'] ?? 0) ?></p>
            <p><strong>Cortesías:</strong> <?= (int)($resumen['cortesias'] ?? 0) ?></p>
            <p><strong>Total Tickets:</strong> <?= (int)($resumen['total_tickets'] ?? 0) ?></p>
        </div>
    <?php endif; ?>
</div>