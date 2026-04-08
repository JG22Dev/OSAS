<?php
session_start();
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['nombre_rol'], ['Recepcion', 'Administrador'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación Manual - SaludPrepaga</title>
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
        <h2>Asignación Manual de Turnos <i class="bi bi-telephone text-warning"></i></h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 border-top border-warning border-4">
                <div class="card-body">
                    <h5 class="card-title text-warning mb-3"><i class="bi bi-search"></i> 1. Identificar Paciente</h5>
                    <div class="input-group mb-3">
                        <input type="text" id="buscadorDni" class="form-control form-control-lg" placeholder="DNI del paciente...">
                        <button class="btn btn-warning fw-bold" id="btnBuscarDni">Buscar</button>
                    </div>
                    <div id="infoPaciente" class="alert alert-success d-none mb-0">
                        <h6 class="fw-bold mb-1" id="lblNombrePaciente"></h6>
                        <small class="d-block mb-1">Plan: <strong id="lblPlanPaciente"></strong></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0" id="tarjetaTurno" style="opacity: 0.5; pointer-events: none;">
                <div class="card-body p-4">
                    <h5 class="card-title text-secondary mb-4"><i class="bi bi-calendar2-plus"></i> 2. Detalle del Turno</h5>
                    
                    <form id="formTurnoManual">
                        <input type="hidden" id="id_afiliado" name="id_afiliado">
                        <input type="hidden" id="fecha_hora_final" name="fecha_hora">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Especialidad</label>
                                <select class="form-select" id="especialidad" required>
                                    <option value="" selected disabled>Cargando...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Profesional</label>
                                <select class="form-select" id="id_profesional" name="id_profesional" required disabled>
                                    <option value="" selected disabled>Elegí una especialidad</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Día</label>
                                <input type="text" class="form-control bg-white" id="fecha_turno" placeholder="Primero elegí médico..." readonly required disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Horario</label>
                                <select class="form-select" id="hora_turno" required disabled>
                                    <option value="" selected disabled>Elegí una fecha</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Motivo (Opcional)</label>
                            <input type="text" class="form-control" name="motivo" placeholder="Ej: Control de presión">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Confirmar Asignación</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    let calendarioInstance = null;
    const mapaDias = { 'Domingo': 0, 'Lunes': 1, 'Martes': 2, 'Miercoles': 3, 'Jueves': 4, 'Viernes': 5, 'Sabado': 6 };

    // 1. Cargar Especialidades
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('../controllers/EspecialidadController.php?accion=listar');
            const data = await res.json();
            const selectEsp = document.getElementById('especialidad');
            selectEsp.innerHTML = '<option value="" selected disabled>Seleccioná una especialidad</option>';
            data.forEach(esp => { selectEsp.innerHTML += `<option value="${esp.id_especialidad}">${esp.nombre}</option>`; });
        } catch (error) { console.error(error); }
    });

    // 2. Buscar Paciente
    document.getElementById('btnBuscarDni').addEventListener('click', async () => {
        const dni = document.getElementById('buscadorDni').value;
        if(!dni) return;
        try {
            const res = await fetch(`../controllers/RecepcionController.php?accion=buscar_paciente&dni=${dni}`);
            const resultado = await res.json();

            if (resultado.status === 'success') {
                document.getElementById('infoPaciente').classList.remove('d-none');
                document.getElementById('lblNombrePaciente').innerText = `${resultado.datos.apellido}, ${resultado.datos.nombre}`;
                document.getElementById('lblPlanPaciente').innerText = resultado.datos.plan_nombre;
                document.getElementById('id_afiliado').value = resultado.datos.id_afiliado;

                const tarjetaTurno = document.getElementById('tarjetaTurno');
                tarjetaTurno.style.opacity = '1'; tarjetaTurno.style.pointerEvents = 'auto';
            } else { Swal.fire('Atención', resultado.mensaje, 'warning'); }
        } catch (error) { Swal.fire('Error', 'Problema al buscar', 'error'); }
    });

    // 3. Buscar Médicos (Solo Activos)
    document.getElementById('especialidad').addEventListener('change', async function() {
        const selectProf = document.getElementById('id_profesional');
        selectProf.innerHTML = '<option value="" selected disabled>Buscando...</option>'; selectProf.disabled = true;

        const res = await fetch(`../controllers/TurnoController.php?accion=get_profesionales&id_especialidad=${this.value}`);
        const medicos = await res.json();

        if(medicos.length > 0) {
            selectProf.innerHTML = '<option value="" selected disabled>Elegí un médico...</option>';
            medicos.forEach(med => selectProf.innerHTML += `<option value="${med.id_profesional}">Dr/a. ${med.apellido}, ${med.nombre}</option>`);
            selectProf.disabled = false;
        } else { selectProf.innerHTML = '<option value="" selected disabled>No hay médicos activos</option>'; }
    });

    // 4. Configurar Calendario Inteligente
    document.getElementById('id_profesional').addEventListener('change', async function() {
        const inputFecha = document.getElementById('fecha_turno');
        const res = await fetch(`../controllers/TurnoController.php?accion=get_dias_laborales&id_profesional=${this.value}`);
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
                    
                    const r = await fetch(`../controllers/TurnoController.php?accion=get_horarios_libres&id_profesional=${document.getElementById('id_profesional').value}&fecha=${dateStr}`);
                    const horarios = await r.json();

                    if(horarios.length > 0) {
                        selectHora.innerHTML = '<option value="" selected disabled>Elegí un horario...</option>';
                        horarios.forEach(h => selectHora.innerHTML += `<option value="${h}">${h}</option>`);
                        selectHora.disabled = false;
                    } else { selectHora.innerHTML = '<option value="" selected disabled>Sin turnos hoy</option>'; }
                }
            });
        } else {
            Swal.fire('Atención', 'Médico sin horarios cargados.', 'warning');
            inputFecha.disabled = true; inputFecha.placeholder = "Elegí otro médico...";
        }
    });

    // 5. Guardar Turno
    document.getElementById('formTurnoManual').addEventListener('submit', async function(e) {
        e.preventDefault();
        document.getElementById('fecha_hora_final').value = `${document.getElementById('fecha_turno').value} ${document.getElementById('hora_turno').value}:00`;

        const res = await fetch('../controllers/RecepcionController.php?accion=reservar_turno', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();

        if (resultado.status === 'success') {
            Swal.fire({ icon: 'success', title: '¡Turno Asignado!', text: resultado.mensaje }).then(() => { window.location.reload(); });
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });
</script>
</body>
</html>