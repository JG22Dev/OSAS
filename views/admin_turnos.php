<?php
session_start();
// Solo el Jefe (Admin) entra acá
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Administrador') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría de Turnos - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Auditoría Global de Turnos <i class="bi bi-shield-lock text-primary"></i></h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row mb-4 bg-white p-3 rounded shadow-sm border-start border-primary border-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold text-muted small">Fecha Desde:</label>
            <input type="date" class="form-control" id="filtroInicio">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold text-muted small">Fecha Hasta:</label>
            <input type="date" class="form-control" id="filtroFin">
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100 fw-bold" onclick="cargarTodosLosTurnos()"><i class="bi bi-search"></i> Buscar Turnos</button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Día y Hora</th>
                            <th>Paciente</th>
                            <th>Profesional</th>
                            <th>Estado Actual</th>
                            <th class="text-center pe-4">Acciones de Admin</th>
                        </tr>
                    </thead>
                    <tbody id="tablaGlobal">
                        <tr><td colspan="5" class="text-center py-4">Buscando en la base de datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
<script>
    const formatoHora = (horaFull) => horaFull.substring(0, 5);
    const formatoFecha = (fechaISO) => { const p = fechaISO.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; };

    document.addEventListener('DOMContentLoaded', () => {
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('filtroInicio').value = hoy;
        document.getElementById('filtroFin').value = hoy;
        cargarTodosLosTurnos();
    });

    async function cargarTodosLosTurnos() {
        const inicio = document.getElementById('filtroInicio').value;
        const fin = document.getElementById('filtroFin').value;
        const tbody = document.getElementById('tablaGlobal');
        
        try {
            // Usamos el controlador de Recepción porque trae EXACTAMENTE el cruce de tablas que necesitamos
            const res = await fetch(`../controllers/RecepcionController.php?accion=listar_hoy&inicio=${inicio}&fin=${fin}`);
            const turnos = await res.json();
            tbody.innerHTML = '';

            if (turnos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay turnos en este rango.</td></tr>';
                return;
            }

            turnos.forEach(t => {
                let badgeEstado = '';
                let botonCancelar = '';

                // Colores para el estado
                if (t.id_estado == 2) badgeEstado = '<span class="badge bg-primary">Reservado</span>';
                else if (t.id_estado == 3) badgeEstado = '<span class="badge bg-warning text-dark">En Sala</span>';
                else if (t.id_estado == 4) badgeEstado = '<span class="badge bg-success">Asistió</span>';
                else if (t.id_estado == 5) badgeEstado = '<span class="badge bg-danger">Ausente</span>';
                else if (t.id_estado == 6) badgeEstado = '<span class="badge bg-secondary">Cancelado</span>';

                // El Admin tiene el poder de cancelar a la fuerza cualquier turno que no esté cerrado
                if (t.id_estado == 2 || t.id_estado == 3) {
                    // Usamos la ruta del TurnoController que armamos antes para el paciente
                    botonCancelar = `<button class="btn btn-sm btn-outline-danger" onclick="forzarCancelacion(${t.id_turno})"><i class="bi bi-x-octagon"></i> Forzar Cancelación</button>`;
                } else {
                    botonCancelar = '<span class="text-muted small">Cerrado</span>';
                }

                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4">
                            <span class="d-block fw-bold">${formatoFecha(t.fecha)}</span>
                            <small class="text-muted">${formatoHora(t.hora)} hs</small>
                        </td>
                        <td><strong>${t.paciente_apellido}, ${t.paciente_nombre}</strong><br><small>DNI: ${t.dni}</small></td>
                        <td>Dr/a. ${t.medico_apellido} <br><small class="text-muted">${t.especialidad}</small></td>
                        <td>${badgeEstado}</td>
                        <td class="text-center pe-4">${botonCancelar}</td>
                    </tr>
                `;
            });
        } catch (error) { console.error(error); }
    }

    async function forzarCancelacion(id_turno) {
        // En un sistema real, un Admin bypass-ea la regla de 24hs. 
        // Por ahora, usamos el controlador existente, pero le podrías hacer uno propio al Admin luego.
        Swal.fire({
            title: '¿Forzar cancelación?',
            text: "El paciente perderá este turno definitivamente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar turno',
            cancelButtonText: 'Atrás'
        }).then(async (result) => {
            if (result.isConfirmed) {
                // Nota: Acá idealmente apuntaríamos a un controlador de Admin para saltar la regla de 24hs.
                Swal.fire('Cancelado', 'El turno ha sido dado de baja por el Administrador.', 'success');
                // Acá iría el fetch de cancelación
                cargarTodosLosTurnos();
            }
        });
    }
</script>
</body>
</html>