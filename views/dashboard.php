<?php
session_start();

// 1. BARRERA DE SEGURIDAD
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

// 2. Rescatamos los datos del usuario logueado
$id_usuario = $_SESSION['id_usuario'];
$email = $_SESSION['email'];
$nombre_rol = $_SESSION['nombre_rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-heart-pulse-fill"></i> SaludPrepaga</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-block">
                Conectado como: <strong><?php echo htmlspecialchars($email); ?></strong> (<?php echo htmlspecialchars($nombre_rol); ?>)
            </span>
            <a href="../controllers/AuthController.php?accion=logout" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">Bienvenido a tu Panel</h2>
            <p class="text-muted">Seleccioná el módulo al que deseás ingresar.</p>
        </div>
    </div>

    <div class="row g-4">
        
        <?php 
        // ==========================================
        // VISTA PARA ADMINISTRADOR
        // ==========================================
        if ($nombre_rol === 'Administrador'): 
        ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-person-lines-fill"></i> Gestión de Médicos</h5>
                        <p class="card-text text-muted">Altas, bajas y modificaciones del personal médico.</p>
                        <a href="admin_profesionales.php" class="btn btn-primary btn-sm">Ir a Médicos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-building"></i> Gestión de Sedes</h5>
                        <p class="card-text text-muted">Administrar las clínicas y centros médicos físicos.</p>
                        <a href="admin_sedes.php" class="btn btn-primary btn-sm">Ir a Sedes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-people-fill"></i> Gestión de Afiliados</h5>
                        <p class="card-text text-muted">Dar de alta pacientes y asignarles un plan médico.</p>
                        <a href="admin_afiliados.php" class="btn btn-primary btn-sm">Ir a Afiliados</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-shield-lock"></i> Auditoría de Turnos</h5>
                        <p class="card-text text-muted">Control global de todos los turnos del sistema.</p>
                        <a href="admin_turnos.php" class="btn btn-primary btn-sm">Ver Todos los Turnos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-card-list"></i> Gestión Comercial</h5>
                        <p class="card-text text-muted">Configurar Planes, Beneficios y cuotas mensuales.</p>
                        <a href="admin_planes.php" class="btn btn-primary btn-sm">Ir a Planes</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-heart-pulse"></i> Especialidades</h5>
                        <p class="card-text text-muted">Administrar las ramas médicas disponibles en la clínica.</p>
                        <a href="admin_especialidades.php" class="btn btn-primary btn-sm">Ir a Especialidades</a>
                    </div>
                </div>
            </div>

        <?php 
        // ==========================================
        // VISTA PARA AFILIADO (CLIENTE)
        // ==========================================
        elseif ($nombre_rol === 'Afiliado'): 
        ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-calendar-plus"></i> Sacar Turno</h5>
                        <p class="card-text text-muted">Buscá profesionales y reservá tu próxima consulta.</p>
                        <a href="sacar_turno.php" class="btn btn-success btn-sm">Gestionar Turnos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-card-heading"></i> Mi Credencial</h5>
                        <p class="card-text text-muted">Ver tu credencial digital y los beneficios de tu plan actual.</p>
                        <a href="mi_credencial.php" class="btn btn-success btn-sm">Ver Credencial</a>
                    </div>
                </div>
            </div>

        <?php 
        // ==========================================
        // VISTA PARA PROFESIONAL MÉDICO
        // ==========================================
        elseif ($nombre_rol === 'Profesional Medico'): 
        ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 border-start border-info border-4">
                    <div class="card-body">
                        <h5 class="card-title text-info"><i class="bi bi-calendar-check"></i> Mi Agenda de Hoy</h5>
                        <p class="card-text text-muted">Revisá los turnos asignados y gestioná la asistencia de pacientes.</p>
                        <a href="medico_agenda.php" class="btn btn-info text-white btn-sm">Ver Agenda</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 border-start border-info border-4">
                    <div class="card-body">
                        <h5 class="card-title text-info"><i class="bi bi-clock-history"></i> Mis Horarios</h5>
                        <p class="card-text text-muted">Configurá tus días y horarios de atención en consultorio.</p>
                        <a href="medico_horarios.php" class="btn btn-info text-white btn-sm">Configurar Horarios</a>
                    </div>
                </div>
            </div>

        <?php 
        // ==========================================
        // VISTA PARA RECEPCIÓN
        // ==========================================
        elseif ($nombre_rol === 'Recepcion'): 
        ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 border-start border-warning border-4">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="bi bi-front"></i> Control de Ingresos</h5>
                        <p class="card-text text-muted">Validar llegada de afiliados y derivar a sala de espera.</p>
                        <a href="recepcion_pacientes.php" class="btn btn-warning text-dark btn-sm">Ir a Recepción</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0 border-start border-warning border-4">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="bi bi-telephone"></i> Asignación Manual</h5>
                        <p class="card-text text-muted">Asignar turnos manualmente para afiliados presenciales o por teléfono.</p>
                        <a href="recepcion_turnos.php" class="btn btn-warning text-dark btn-sm">Asignar Turno</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>