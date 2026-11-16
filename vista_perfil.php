<?php
$cajaNum = (int)($_SESSION['caja'] ?? 1);
$cajeroId = (int)($perfilCaja['cajero_id'] ?? 0);
$cajeroNombre = $perfilCaja['cajero_nombre'] ?? ($_SESSION['nombre'] ?? 'Sin asignar');
$totalCaja = $perfilCaja['total'] ?? 0;
$ticketsCaja = $perfilCaja['tickets_emitidos'] ?? 0;
?>

<div class="perfil-header">
    <div class="perfil-avatar">
        <i class='bx bx-user'></i>
    </div>
    <div class="perfil-datos">
        <p class="perfil-nombre"><?= htmlspecialchars($cajeroNombre) ?></p>
        <span class="perfil-badge">Caja <?= $cajaNum ?> · Operativa</span>
    </div>
</div>

<div class="perfil-stats">
    <div class="perfil-stat-item">
        <span>Total Caja <?= $cajaNum ?></span>
        <strong>$<?= number_format($totalCaja, 0, ',', '.') ?></strong>
    </div>
    <div class="perfil-stat-item">
        <span>Tickets</span>
        <strong><?= (int)$ticketsCaja ?></strong>
    </div>
</div>

<div class="perfil-acciones">
    <?php if ($cajeroId > 0): ?>
        <button type="button" class="btn-perfil" onclick="cambiarNombre(<?= $cajeroId ?>, <?= $cajaNum ?>)">
            <i class='bx bx-edit-alt'></i> Cambiar nombre
        </button>
        <button type="button" class="btn-perfil" onclick="resetPin(<?= $cajeroId ?>)">
            <i class='bx bx-key'></i> Restablecer PIN
        </button>
    <?php endif; ?>

    <button type="button" class="btn-perfil" onclick="abrirTabEnNavbar(<?= $cajaNum ?>)">
        <i class='bx bx-bar-chart-alt-2'></i> Ver detalle en panel
    </button>

    <a href="logout.php?caja=<?= $cajaNum ?>" class="btn-perfil btn-perfil-salir">
        <i class='bx bx-log-out'></i> Cerrar turno
    </a>
</div>