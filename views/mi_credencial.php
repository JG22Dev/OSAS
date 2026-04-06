<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Afiliado') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Credencial - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos para que parezca una tarjeta física */
        .tarjeta-credencial {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        /* Decoración de fondo de la tarjeta */
        .tarjeta-credencial::after {
            content: ''; position: absolute; top: -50%; right: -20%;
            width: 300px; height: 300px; background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .logo-prepaga { font-size: 1.5rem; font-weight: bold; letter-spacing: 1px; }
        .numero-credencial { font-family: monospace; font-size: 1.2rem; letter-spacing: 2px; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Mi Cobertura Médica</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="tarjeta-credencial" id="tarjetaCredencial">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="logo-prepaga"><i class="bi bi-heart-pulse-fill text-warning"></i> SaludPrepaga</div>
                    <div class="badge bg-light text-primary fs-6" id="badgePlan">Cargando...</div>
                </div>
                
                <h4 class="fw-bold mb-1" id="nombreCompleto">Cargando Datos...</h4>
                <p class="mb-4 text-white-50">DNI: <span id="dniAfiliado">...</span></p>
                
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <small class="text-white-50 d-block">N° de Credencial</small>
                        <span class="numero-credencial" id="numCredencial">CRED-00000000</span>
                    </div>
                    <div class="text-end">
                        <small class="text-white-50 d-block">Prioridad</small>
                        <span class="fw-bold fs-5" id="nivelPrioridad">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-shield-check"></i> Beneficios Incluidos en tu Plan
                    </h5>
                    <ul class="list-group list-group-flush" id="listaBeneficios">
                        <li class="list-group-item text-muted">Cargando beneficios...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('../controllers/CredencialController.php?accion=obtener_mi_credencial');
            const resultado = await res.json();

            if (resultado.status === 'success') {
                const datos = resultado.datos;
                const beneficios = resultado.beneficios;

                // 1. Inyectamos los datos en la tarjeta HTML
                document.getElementById('nombreCompleto').textContent = `${datos.apellido}, ${datos.nombre}`;
                document.getElementById('dniAfiliado').textContent = datos.dni;
                document.getElementById('numCredencial').textContent = datos.numero_credencial;
                document.getElementById('badgePlan').textContent = datos.nombre_plan;
                document.getElementById('nivelPrioridad').textContent = `Nivel ${datos.nivel_prioridad}`;

                // 2. Armamos la lista de beneficios
                const ul = document.getElementById('listaBeneficios');
                ul.innerHTML = ''; // Limpiamos la lista

                if (beneficios.length > 0) {
                    beneficios.forEach(b => {
                        ul.innerHTML += `<li class="list-group-item"><i class="bi bi-check-lg text-success me-2"></i> ${b.descripcion}</li>`;
                    });
                } else {
                    ul.innerHTML = '<li class="list-group-item text-muted fst-italic">Tu plan aún no tiene beneficios cargados.</li>';
                }
            } else {
                alert(resultado.mensaje); // Si hay un error (ej: no encontró el perfil)
            }
        } catch (error) {
            console.error('Error al cargar la credencial:', error);
        }
    });
</script>

</body>
</html>