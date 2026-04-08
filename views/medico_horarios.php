<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Profesional Medico') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Horarios - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Configuración de Agenda Semanal</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-info border-4">
                <div class="card-body">
                    <h5 class="card-title text-info mb-3"><i class="bi bi-clock-fill"></i> Agregar Turno Laboral</h5>
                    <form id="formHorario">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Clínica / Sede</label>
                            <select class="form-select" id="id_sede" name="id_sede" required>
                                <option value="" selected disabled>Cargando sedes...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Día de la Semana</label>
                            <select class="form-select" name="dia_semana" required>
                                <option value="" selected disabled>Seleccioná un día...</option>
                                <option value="Lunes">Lunes</option><option value="Martes">Martes</option>
                                <option value="Miercoles">Miércoles</option><option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option><option value="Sabado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Hora Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" required>
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label fw-bold">Hora Fin</label>
                                <input type="time" class="form-control" name="hora_fin" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info text-white w-100 fw-bold">Guardar Horario</button>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-warning mt-3 small shadow-sm">
                <i class="bi bi-shield-check"></i> <strong>Seguridad Activa:</strong> El sistema impide que te agendes en dos sedes distintas con menos de 1 hora de diferencia para garantizar tu tiempo de traslado.
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Mi Semana Planificada</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Día</th>
                                    <th>Sede</th>
                                    <th>Apertura</th>
                                    <th>Cierre</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHorarios">
                                <tr><td colspan="4" class="text-center">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/HorarioController.php';
    const formatoHora = (horaFull) => horaFull.substring(0, 5);

    // 1. Cargar sedes activas
    async function cargarSedes() {
        try {
            const res = await fetch(urlController + '?accion=listar_sedes');
            const sedes = await res.json();
            const select = document.getElementById('id_sede');
            select.innerHTML = '<option value="" selected disabled>Seleccioná dónde vas a atender</option>';
            sedes.forEach(s => select.innerHTML += `<option value="${s.id_sede}">${s.nombre}</option>`);
        } catch (error) { console.error(error); }
    }

    // 2. Cargar tabla
    async function cargarTabla() {
        try {
            const res = await fetch(urlController + '?accion=listar');
            const data = await res.json();
            const tbody = document.getElementById('tablaHorarios');
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Aún no cargaste tus horarios.</td></tr>';
                return;
            }

            data.forEach(h => {
                let badgeColor = (h.dia_semana === 'Sabado' || h.dia_semana === 'Domingo') ? 'bg-warning text-dark' : 'bg-primary';
                tbody.innerHTML += `
                    <tr>
                        <td><span class="badge ${badgeColor} fs-6">${h.dia_semana}</span></td>
                        <td><i class="bi bi-building text-secondary"></i> <strong>${h.sede_nombre}</strong></td>
                        <td class="fw-bold text-success"><i class="bi bi-box-arrow-in-right"></i> ${formatoHora(h.hora_inicio)}</td>
                        <td class="fw-bold text-danger"><i class="bi bi-box-arrow-left"></i> ${formatoHora(h.hora_fin)}</td>
                    </tr>
                `;
            });
        } catch (error) { console.error(error); }
    }

    // 3. Guardar Horario
    document.getElementById('formHorario').addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            const res = await fetch(urlController + '?accion=registrar', { method: 'POST', body: new FormData(this) });
            const resultado = await res.json();

            if(resultado.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Guardado', text: resultado.mensaje, timer: 1500, showConfirmButton: false });
                this.reset();
                cargarTabla(); 
            } else {
                Swal.fire('Bloqueo de Sistema', resultado.mensaje, 'error'); // Acá salta si te querés teletransportar
            }
        } catch (error) { Swal.fire('Error', 'Problema de conexión', 'error'); }
    });

    document.addEventListener('DOMContentLoaded', () => { cargarSedes(); cargarTabla(); });
</script>

</body>
</html>