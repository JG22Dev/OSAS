<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prepaga Médica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; width: 100%; border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">SaludPrepaga</h3>
        <p class="text-muted">Ingresá a tu cuenta</p>
    </div>

    <form id="formLogin">
        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="******" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold">Iniciar Sesión</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    document.getElementById('formLogin').addEventListener('submit', async function(e) {
        e.preventDefault(); 
        
        const formData = new FormData(this);

        try {
            const respuesta = await fetch('controllers/AuthController.php?accion=login', {
                method: 'POST',
                body: formData
            });
            const resultado = await respuesta.json();

            if(resultado.status === 'success') {
                Swal.fire({
                    icon: 'success', title: '¡Bienvenido!', text: 'Redirigiendo al panel...',
                    timer: 1500, showConfirmButton: false
                }).then(() => {
                    // Redirigimos al panel general
                    window.location.href = 'views/dashboard.php';
                });
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error de red', 'No se pudo conectar con el servidor', 'error');
        }
    });
</script>

</body>
</html>