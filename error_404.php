<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullMoto - No encontrado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<?php include __DIR__ . '/nav.php'; ?>
<div class="container text-center py-5">
    <h1 class="display-1 fw-bold text-warning">404</h1>
    <p class="fs-4 text-white-50">El producto que buscas no existe o fue eliminado.</p>
    <a href="index.php" class="btn btn-primary">
        <i class="bi bi-box-seam me-2"></i>Volver al catálogo
    </a>
</div>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>