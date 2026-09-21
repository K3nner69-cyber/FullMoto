<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$mensajes = [
    'campos'            => 'Completa todos los campos con datos válidos.',
    'clave_corta'       => 'La contraseña debe tener al menos 6 caracteres.',
    'clave_no_coincide' => 'Las contraseñas no coinciden.',
    'email_existe'      => 'Ya existe una cuenta con ese correo.',
    'bd'                => 'No se pudo crear la cuenta. Inténtalo de nuevo.',
];
$error = $mensajes[$_GET['error'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Crear cuenta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
<?php include __DIR__ . '/nav.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-white">Crear cuenta</h2>
                    <p class="text-white-50 mb-0">Regístrate como cliente de FullMoto</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="CONTROLADORES/registro_controller.php" method="POST">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-white">Nombre</label>
                            <input type="text" name="nombre" class="form-control" maxlength="60" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-white">Apellido</label>
                            <input type="text" name="apellido" class="form-control" maxlength="60" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="password" aria-label="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Confirmar contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirmar" id="confirmar" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="confirmar" aria-label="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2">
                            <i class="bi bi-person-plus me-2"></i>Crear cuenta
                        </button>
                    </div>
                </form>

                <p class="text-center text-white-50 mt-3 mb-0">
                    ¿Ya tienes cuenta? <a href="login.php" class="text-warning">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.toggle-pass').forEach(function (boton) {
    boton.addEventListener('click', function () {
        const campo = document.getElementById(boton.dataset.target);
        const icono = boton.querySelector('i');
        const mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        icono.className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
</script>
</body>
</html>