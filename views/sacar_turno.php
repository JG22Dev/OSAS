<?php
session_start();
// Solo permitimos entrar si está logueado y es Afiliado
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
    <title>Sacar Turno - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-top border-success border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title text-success mb-0">Reservar Nuevo Turno</h4>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Volver al panel</a>
                    </div>
                    
                    <form id="formTurno">
                        <div class="mb-3">
                            <label for="especialidad" class="form-label fw-bold">1. Seleccioná la Especialidad</label>
                            <select class="form-select" id="especialidad" required>
                                <option value="" selected disabled>Cargando especialidades...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_profesional" class="form-label fw-bold">2. Elegí al Profesional</label>
                            <select class="form-select" id="id_profesional" name="id_profesional" required disabled>
                                <option value="" selected disabled>Primero elegí una especialidad</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_turno" class="form-label fw-bold">3. Seleccioná el Día</label>
                            <input type="date" class="form-control" id="fecha_turno" name="fecha_turno" required disabled>
                        </div>

                        <div class="mb-3">
                            <label for="hora_turno" class="form-label fw-bold">Horarios Disponibles</label>
                            <select class="form-select" id="hora_turno" name="hora_turno" required disabled>
                                <option value="" selected disabled>Primero elegí una fecha</option>
                            </select>
                        </div>

                        <input type="hidden" id="fecha_hora_final" name="fecha_hora">
                        <div class="mb-4">
                            <label for="motivo" class="form-label fw-bold">4. Motivo de la consulta (Opcional)</label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="2" placeholder="Ej: Control anual, dolor de cabeza..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">Confirmar Reserva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    // Al cargar la página, traemos las especialidades
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            // Usamos el controlador viejo de Especialidades! Reutilización de código a tope.
            const res = await fetch('../controllers/EspecialidadController.php?accion=listar');
            const especialidades = await res.json();
            
            const selectEsp = document.getElementById('especialidad');
            selectEsp.innerHTML = '<option value="" selected disabled>Elegí una especialidad...</option>';
            
            especialidades.forEach(esp => {
                selectEsp.innerHTML += `<option value="${esp.id_especialidad}">${esp.nombre}</option>`;
            });
        } catch (error) {
            Swal.fire('Error', 'No se pudieron cargar las especialidades', 'error');
        }
    });

    // Cuando cambian la especialidad, traemos a los médicos
    document.getElementById('especialidad').addEventListener('change', async function() {
        const id_esp = this.value;
        const selectProf = document.getElementById('id_profesional');
        
        selectProf.innerHTML = '<option value="" selected disabled>Buscando médicos...</option>';
        selectProf.disabled = true;

        try {
            const res = await fetch(`../controllers/TurnoController.php?accion=get_profesionales&id_especialidad=${id_esp}`);
            const medicos = await res.json();

            if(medicos.length > 0) {
                selectProf.innerHTML = '<option value="" selected disabled>Elegí un médico...</option>';
                medicos.forEach(med => {
                    selectProf.innerHTML += `<option value="${med.id_profesional}">Dr/a. ${med.apellido}, ${med.nombre}</option>`;
                });
                selectProf.disabled = false;
            } else {
                selectProf.innerHTML = '<option value="" selected disabled>No hay médicos para esta especialidad</option>';
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudieron cargar los médicos', 'error');
        }
    });
    
    // 1. Bloquear fechas anteriores a hoy en el calendario
    const fechaInput = document.getElementById('fecha_turno');
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.setAttribute('min', hoy);

    // 2. Desbloquear la fecha cuando eligen al médico
    document.getElementById('id_profesional').addEventListener('change', function() {
        document.getElementById('fecha_turno').disabled = false;
        document.getElementById('hora_turno').innerHTML = '<option value="" selected disabled>Ahora elegí una fecha</option>';
        document.getElementById('hora_turno').disabled = true;
    });

    // 3. Cuando cambian el DÍA, vamos a buscar los turnos de 30 mins
    document.getElementById('fecha_turno').addEventListener('change', async function() {
        const id_profesional = document.getElementById('id_profesional').value;
        const fecha = this.value;
        const selectHora = document.getElementById('hora_turno');

        if(!id_profesional) return;

        selectHora.innerHTML = '<option value="" selected disabled>Buscando horarios...</option>';
        selectHora.disabled = true;

        try {
            const res = await fetch(`../controllers/TurnoController.php?accion=get_horarios_libres&id_profesional=${id_profesional}&fecha=${fecha}`);
            const horarios = await res.json();

            if(horarios.length > 0) {
                selectHora.innerHTML = '<option value="" selected disabled>Elegí un horario...</option>';
                horarios.forEach(hora => {
                    selectHora.innerHTML += `<option value="${hora}">${hora}</option>`;
                });
                selectHora.disabled = false;
            } else {
                selectHora.innerHTML = '<option value="" selected disabled>El médico no atiende o no hay lugar</option>';
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudieron consultar los horarios', 'error');
        }
    });

    // 4. Antes de mandar el formulario, unimos la fecha y la hora elegida en un solo campo
    document.getElementById('formTurno').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Unimos YYYY-MM-DD + HH:MM para mandarlo al controlador de reservar
        const fechaElegida = document.getElementById('fecha_turno').value;
        const horaElegida = document.getElementById('hora_turno').value;
        document.getElementById('fecha_hora_final').value = `${fechaElegida} ${horaElegida}:00`;

        const formData = new FormData(this);

        try {
            const res = await fetch('../controllers/TurnoController.php?accion=reservar', {
                method: 'POST',
                body: formData
            });
            const resultado = await res.json();

            if(resultado.status === 'success') {
                Swal.fire({
                    icon: 'success', title: '¡Turno Confirmado!', text: resultado.mensaje,
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        } catch (error) {
            Swal.fire('Error de conexión', 'Hubo un problema al guardar el turno', 'error');
        }
    });


    // Enviar el formulario para reservar
    document.getElementById('formTurno').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

        try {
            const res = await fetch('../controllers/TurnoController.php?accion=reservar', {
                method: 'POST',
                body: formData
            });
            const resultado = await res.json();

            if(resultado.status === 'success') {
                Swal.fire({
                    icon: 'success', title: '¡Turno Confirmado!', text: resultado.mensaje,
                }).then(() => {
                    window.location.href = 'dashboard.php'; // Lo devolvemos al panel
                });
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        } catch (error) {
            Swal.fire('Error de conexión', 'Hubo un problema al guardar el turno', 'error');
        }
    });
</script>

</body>
</html>