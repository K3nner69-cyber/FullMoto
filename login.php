<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - Acceso al Sistema</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
<?php include __DIR__ . '/nav.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <img src="IMG/fullmoto_sin_fondo.png" alt="FullMoto Logo" style="width: 300px; max-width: 150%;" class="img-fluid mb-2">
                    <h2 class="fw-bold text-white">FullMoto</h2>
                    <p class="text-white">Iniciar Sesión</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">Correo o contraseña incorrectos.</div>
                <?php endif; ?>

                <form action="CONTROLADORES/login_controller.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-white">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="Ingrese su correo" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al Sistema
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include __DIR__ . '/footer.php'; ?>
</html>
