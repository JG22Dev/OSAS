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
    <title>Mis Turnos - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Turnos</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 border-top border-success border-4">
                <div class="card-body p-4">
                    <h4 class="card-title text-success mb-4"><i class="bi bi-geo-alt"></i> Reservar Turno Presencial</h4>
                    <form id="formTurno">
                        <input type="hidden" id="fecha_hora_final" name="fecha_hora">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Elegí la Clínica (Sede)</label>
                            <select class="form-select" id="id_sede" name="id_sede" required>
                                <option value="" selected disabled>Cargando sedes...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Especialidad</label>
                            <select class="form-select" id="id_especialidad" required disabled>
                                <option value="" selected disabled>Primero elegí una clínica</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">3. Profesional</label>
                            <select class="form-select" id="id_profesional" name="id_profesional" required disabled>
                                <option value="" selected disabled>Primero elegí especialidad</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">4. Seleccioná el Día</label>
                            <input type="text" class="form-control bg-white" id="fecha_turno" placeholder="Esperando datos..." readonly required disabled>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">5. Horario</label>
                            <select class="form-select" id="hora_turno" name="hora_turno" required disabled>
                                <option value="" selected disabled>Primero elegí una fecha</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 fw-bold">Confirmar Reserva</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-card-list"></i> Mi Historial de Turnos</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Profesional</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMisTurnos">
                                <tr><td colspan="4" class="text-center">Cargando mis turnos...</td></tr>
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
    let calendarioInstance = null;
    const mapaDias = { 'Domingo': 0, 'Lunes': 1, 'Martes': 2, 'Miercoles': 3, 'Jueves': 4, 'Viernes': 5, 'Sabado': 6 };

    // 🚨 APRENDIZAJE: FASE 1 - Al cargar la página, solo traemos las Sedes.
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('../controllers/TurnoController.php?accion=get_sedes');
            const data = await res.json();
            const selectSede = document.getElementById('id_sede');
            selectSede.innerHTML = '<option value="" selected disabled>Seleccioná una clínica...</option>';
            data.forEach(sede => selectSede.innerHTML += `<option value="${sede.id_sede}">${sede.nombre}</option>`);
        } catch (error) { console.error("Error cargando sedes"); }
        cargarMisTurnos();
    });

    // 🚨 APRENDIZAJE: FASE 2 - Efecto Dominó de la SEDE
    // Si el usuario cambia la Sede, reseteamos todos los selects de abajo y pedimos las especialidades.
    document.getElementById('id_sede').addEventListener('change', async function() {
        const id_sede = this.value;
        const selectEsp = document.getElementById('id_especialidad');
        
        // Efecto Dominó: Apagar y resetear todo lo que está abajo
        selectEsp.innerHTML = '<option value="" selected disabled>Buscando especialidades...</option>';
        selectEsp.disabled = true;
        document.getElementById('id_profesional').innerHTML = '<option value="" selected disabled>Primero elegí especialidad</option>';
        document.getElementById('id_profesional').disabled = true;
        document.getElementById('fecha_turno').disabled = true;
        document.getElementById('hora_turno').disabled = true;

        const res = await fetch(`../controllers/TurnoController.php?accion=get_especialidades_sede&id_sede=${id_sede}`);
        const especialidades = await res.json();

        if(especialidades.length > 0) {
            selectEsp.innerHTML = '<option value="" selected disabled>Elegí una especialidad...</option>';
            especialidades.forEach(esp => selectEsp.innerHTML += `<option value="${esp.id_especialidad}">${esp.nombre}</option>`);
            selectEsp.disabled = false; // Encendemos el select
        } else {
            Swal.fire('Info', 'Esta sede aún no tiene especialidades cargadas.', 'info');
            selectEsp.innerHTML = '<option value="" selected disabled>Sin especialidades</option>';
        }
    });

    // 🚨 APRENDIZAJE: FASE 3 - Efecto Dominó de la ESPECIALIDAD
    // Para buscar al médico, ahora mandamos DOS variables a la base de datos: la Sede y la Especialidad.
    document.getElementById('id_especialidad').addEventListener('change', async function() {
        const id_esp = this.value;
        const id_sede = document.getElementById('id_sede').value; // Rescatamos la sede elegida arriba
        const selectProf = document.getElementById('id_profesional');
        
        selectProf.innerHTML = '<option value="" selected disabled>Buscando médicos...</option>';
        selectProf.disabled = true;
        document.getElementById('fecha_turno').disabled = true;

        const res = await fetch(`../controllers/TurnoController.php?accion=get_profesionales_v2&id_sede=${id_sede}&id_especialidad=${id_esp}`);
        const medicos = await res.json();

        if(medicos.length > 0) {
            selectProf.innerHTML = '<option value="" selected disabled>Elegí un médico...</option>';
            medicos.forEach(med => selectProf.innerHTML += `<option value="${med.id_profesional}">Dr/a. ${med.apellido}, ${med.nombre}</option>`);
            selectProf.disabled = false;
        } else {
            selectProf.innerHTML = '<option value="" selected disabled>Sin médicos para esta especialidad aquí</option>';
        }
    });

    // 🚨 APRENDIZAJE: FASE 4 - Armar el Calendario
    // (Esta parte ya la conocés, es el Flatpickr que armamos antes)
    document.getElementById('id_profesional').addEventListener('change', async function() {
        const id_prof = this.value;
        const inputFecha = document.getElementById('fecha_turno');
        
        const res = await fetch(`../controllers/TurnoController.php?accion=get_dias_laborales&id_profesional=${id_prof}`);
        const diasLaborales = await res.json();

        if(diasLaborales.length > 0) {
            inputFecha.disabled = false; inputFecha.placeholder = "Hacé clic para elegir un día";
            const diasHabilitados = diasLaborales.map(dia => mapaDias[dia]);
            if (calendarioInstance) calendarioInstance.destroy();

            calendarioInstance = flatpickr("#fecha_turno", {
                locale: "es", minDate: "today", dateFormat: "Y-m-d", disableMobile: "true",
                enable: [ function(date) { return diasHabilitados.includes(date.getDay()); } ],
                onChange: async function(selectedDates, dateStr) {
                    const selectHora = document.getElementById('hora_turno');
                    selectHora.innerHTML = '<option value="" selected disabled>Buscando...</option>'; selectHora.disabled = true;
                    
                    const r = await fetch(`../controllers/TurnoController.php?accion=get_horarios_libres&id_profesional=${id_prof}&fecha=${dateStr}`);
                    const horarios = await r.json();

                    if(horarios.length > 0) {
                        selectHora.innerHTML = '<option value="" selected disabled>Elegí un horario...</option>';
                        horarios.forEach(h => selectHora.innerHTML += `<option value="${h}">${h}</option>`);
                        selectHora.disabled = false;
                    } else { selectHora.innerHTML = '<option value="" selected disabled>Sin turnos hoy</option>'; }
                }
            });
        }
    });

    // 🚨 APRENDIZAJE: FASE 5 - Guardar
    document.getElementById('formTurno').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fecha = document.getElementById('fecha_turno').value;
        const hora = document.getElementById('hora_turno').value;
        document.getElementById('fecha_hora_final').value = `${fecha} ${hora}:00`;

        const res = await fetch('../controllers/TurnoController.php?accion=reservar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();

        if(resultado.status === 'success') {
            Swal.fire('¡Éxito!', resultado.mensaje, 'success');
            setTimeout(() => { window.location.reload(); }, 1500); // Recargamos para resetear el form en cascada
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    });

    // Funciones de la tabla "Mis Turnos" (Igual que antes)
    async function cargarMisTurnos() {
        const res = await fetch('../controllers/TurnoController.php?accion=listar_mis_turnos');
        const turnos = await res.json();
        const tbody = document.getElementById('tablaMisTurnos');
        tbody.innerHTML = '';

        if(turnos.length === 0) { tbody.innerHTML = '<tr><td colspan="4" class="text-center">No tenés turnos registrados.</td></tr>'; return; }

        turnos.forEach(t => {
            const fechaObj = new Date(t.fecha_hora);
            const fechaStr = fechaObj.toLocaleDateString() + ' ' + fechaObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            let boton = (t.id_estado == 2 || t.id_estado == 3) ? `<button class="btn btn-outline-danger btn-sm" onclick="cancelarTurno(${t.id_turno})"><i class="bi bi-trash"></i> Cancelar</button>` : '-';
            let badgeColor = (t.id_estado == 6 || t.id_estado == 5) ? 'bg-danger' : (t.id_estado == 4 ? 'bg-success' : 'bg-primary');

            tbody.innerHTML += `
                <tr>
                    <td class="fw-bold">${fechaStr}</td>
                    <td>Dr/a. ${t.med_apellido}<br><small class="text-muted">${t.especialidad}</small></td>
                    <td><span class="badge ${badgeColor}">${t.nombre_estado}</span></td>
                    <td class="text-center">${boton}</td>
                </tr>
            `;
        });
    }

    async function cancelarTurno(id_turno) {
        const confirmacion = await Swal.fire({ title: '¿Cancelar Turno?', text: "Recordá la política de 24 horas.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, cancelar' });
        if (confirmacion.isConfirmed) {
            const formData = new FormData(); formData.append('id_turno', id_turno);
            const res = await fetch('../controllers/TurnoController.php?accion=cancelar_turno', { method: 'POST', body: formData });
            const resultado = await res.json();
            if (resultado.status === 'success') { Swal.fire('Cancelado', resultado.mensaje, 'success'); cargarMisTurnos(); } 
            else { Swal.fire('No permitido', resultado.mensaje, 'error'); }
        }
    }
</script>
</body>
</html>