<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? null;
$rolActivo = $usuario['rolActivo'] ?? null;
$rolesUsuario = $usuario['roles'] ?? [];
?>
<div class="text-center py-2 text-secondary medium text-uppercase fw-semibold" style="background-color:#141414; border-bottom:1px solid #333333;">
    Repuestos y servicios personalizados para tu moto
</div>
<nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color:#1b1b1b; border-bottom:1px solid #333333;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="IMG/fullmoto_sin_fondo.png" alt="FullMoto" style="height:90px;" class="me-1">
            <span class="fw-bold">FullMoto</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php" style="text-shadow: 0 0 8px #28a745, 0 0 8px #28a745;">
    <i class="bi bi-box-seam me-1"></i>Catálogo
</a>
                </li>
                <?php if ($rolActivo === 'Vendedor' || $rolActivo === 'Administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_comercializacion.php"><i class="bi bi-person-lines-fill me-1"></i>Gestión Comercial</a>
                    </li>
                <?php endif; ?>
                <?php if ($rolActivo === 'Agente Logistico' || $rolActivo === 'Administrador'): ?>
    <li class="nav-item">
        <a class="nav-link" href="logistica_distribucion.php"><i class="bi bi-truck me-1"></i>Logística</a>
    </li>
<?php endif; ?>
                <?php if ($rolActivo === 'Vendedor'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="registrar_venta.php"><i class="bi bi-cart-check me-1"></i>Registrar Venta</a>
                    </li>
                <?php endif; ?>

                <?php if ($rolActivo === 'Cliente'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">
                            <i class="bi bi-cart3 me-1"></i>Carrito
                            <?php if (!empty($_SESSION['carrito'])): ?>
                                <span class="badge bg-success ms-1"><?php echo array_sum($_SESSION['carrito']); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($usuario === null): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Acceso</a>
                    </li>
                <?php else: ?>
                    <?php if (count($rolesUsuario) > 1): ?>
                        <li class="nav-item">
                            <form action="CONTROLADORES/cambiar_rol_controller.php" method="POST" class="d-flex align-items-center">
                                <select name="rol" class="form-select form-select-sm bg-dark text-white border-secondary me-2" onchange="this.form.submit()">
                                    <?php foreach ($rolesUsuario as $rol): ?>
                                        <option value="<?php echo htmlspecialchars($rol); ?>" <?php echo ($rol === $rolActivo) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rol); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <span class="nav-link text-white-50"><?php echo htmlspecialchars($rolActivo); ?></span>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <span class="nav-link"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="CONTROLADORES/logout_controller.php"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
