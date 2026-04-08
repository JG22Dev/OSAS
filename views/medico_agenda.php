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
    <title>Mi Agenda - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Consultorio Virtual <i class="bi bi-heart-pulse text-danger"></i></h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="card shadow-sm border-0 border-top border-danger border-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Pacientes de Hoy (<span id="fechaHoy"></span>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Hora / Sede</th>
                            <th>Paciente</th>
                            <th>Estado en Recepción</th>
                            <th class="text-center pe-4">Acciones Médicas</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAgenda">
                        <tr><td colspan="4" class="text-center py-5">Cargando tu agenda...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtencion" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-journal-medical"></i> Registrar Atención Médica</h5>
      </div>
      <form id="formEvolucion">
          <div class="modal-body bg-light">
              <input type="hidden" id="atencion_id_turno" name="id_turno">
              
              <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white border rounded shadow-sm">
                  <div>
                      <h5 class="mb-0 fw-bold" id="lblNombrePaciente"></h5>
                      <small class="text-muted" id="lblDniPaciente"></small>
                  </div>
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="abrirHistorial()"><i class="bi bi-clock-history"></i> Ver Historial Previo</button>
              </div>

              <div class="mb-3">
                  <label class="form-label fw-bold text-danger">Diagnóstico *</label>
                  <textarea class="form-control" name="diagnostico" rows="3" placeholder="Redactá los síntomas y el diagnóstico de la visita..." required></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Tratamiento Indicado</label>
                  <textarea class="form-control" name="tratamiento" rows="2" placeholder="Estudios, derivaciones, reposo..."></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Receta de Medicamentos</label>
                  <textarea class="form-control" name="receta" rows="2" placeholder="Fármacos y dosis..."></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarAtencion()">Cancelar</button>
            <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle"></i> Guardar y Cerrar Turno</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalHistorial" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-archive"></i> Historial Médico</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenidoHistorial">
          </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const api = '../controllers/HistoriaClinicaController.php';
    let modalAtencion = new bootstrap.Modal(document.getElementById('modalAtencion'));
    let modalHistorial = new bootstrap.Modal(document.getElementById('modalHistorial'));
    let idAfiliadoActual = null; // Para saber de quién buscar el historial

    // Poner la fecha linda en el título
    document.getElementById('fechaHoy').innerText = new Date().toLocaleDateString('es-AR');

    // 1. Cargar la Agenda
    async function cargarAgenda() {
        const res = await fetch(api + '?accion=listar_agenda_hoy');
        const data = await res.json();
        const tbody = document.getElementById('tablaAgenda');
        tbody.innerHTML = '';

        if(data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-cup-hot fs-1 d-block mb-2"></i>No tenés pacientes agendados para hoy.</td></tr>'; return;
        }

        data.forEach(t => {
            const horaStr = t.hora.substring(0, 5);
            let badgeEstado = '';
            let botones = '';
            let claseFila = '';

            // Lógica de Semáforo
            if (t.id_estado == 2) { 
                badgeEstado = '<span class="badge bg-secondary"><i class="bi bi-clock"></i> Aún no llega</span>';
                botones = `<button class="btn btn-sm btn-outline-danger" onclick="marcarAusente(${t.id_turno})">Marcar Ausente</button>`;
            } else if (t.id_estado == 3) {
                // 🚨 APRENDIZAJE: Si el paciente está en sala (Estado 3), habilitamos el botón verde de Atender.
                badgeEstado = '<span class="badge bg-warning text-dark border border-warning shadow-sm"><i class="bi bi-bell-fill"></i> ¡Esperando en Sala!</span>';
                botones = `<button class="btn btn-sm btn-success fw-bold px-3 shadow-sm" onclick="abrirAtencion(${t.id_turno}, ${t.id_afiliado}, '${t.pac_apellido}, ${t.pac_nombre}', '${t.dni}')"><i class="bi bi-stethoscope"></i> Atender</button>`;
                claseFila = 'table-warning';
            } else if (t.id_estado == 4) {
                badgeEstado = '<span class="badge bg-success">Atendido</span>';
                botones = '<span class="text-muted small">Consulta Cerrada</span>';
                claseFila = 'opacity-50';
            } else {
                badgeEstado = '<span class="badge bg-danger">Ausente / Cancelado</span>';
                botones = '-';
                claseFila = 'opacity-50';
            }

            tbody.innerHTML += `
                <tr class="${claseFila}">
                    <td class="ps-4"><span class="fs-5 fw-bold">${horaStr}</span><br><small class="text-muted">${t.sede_nombre}</small></td>
                    <td><strong>${t.pac_apellido}, ${t.pac_nombre}</strong><br><small>DNI: ${t.dni} | Cred: ${t.numero_credencial}</small></td>
                    <td>${badgeEstado}</td>
                    <td class="text-center pe-4">${botones}</td>
                </tr>
            `;
        });
    }

    // 2. Abrir Modal para Escribir Diagnóstico
    function abrirAtencion(id_turno, id_afiliado, nombre, dni) {
        document.getElementById('atencion_id_turno').value = id_turno;
        idAfiliadoActual = id_afiliado; // Lo guardamos por si toca "Ver Historial"
        document.getElementById('lblNombrePaciente').innerText = nombre;
        document.getElementById('lblDniPaciente').innerText = "DNI: " + dni;
        
        document.getElementById('formEvolucion').reset();
        modalAtencion.show();
    }

    function cerrarAtencion() {
        Swal.fire({title: '¿Cancelar atención?', text: "El diagnóstico no se guardará", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, salir'})
        .then((result) => { if (result.isConfirmed) modalAtencion.hide(); });
    }

    // 3. Guardar Evolución
    document.getElementById('formEvolucion').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(api + '?accion=guardar_evolucion', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ position: 'center', icon: 'success', title: 'Atención Finalizada', text: 'El paciente fue dado de alta del turno.', showConfirmButton: false, timer: 2000 });
            modalAtencion.hide();
            cargarAgenda(); // Refrescamos para que se ponga en gris (Atendido)
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    // 4. Ver Historial Previo
    async function abrirHistorial() {
        const div = document.getElementById('contenidoHistorial');
        div.innerHTML = '<p class="text-center">Buscando antecedentes...</p>';
        modalHistorial.show();

        const res = await fetch(`${api}?accion=ver_historial&id_afiliado=${idAfiliadoActual}`);
        const historial = await res.json();

        div.innerHTML = '';
        if(historial.length === 0) {
            div.innerHTML = '<div class="alert alert-info">El paciente no tiene registros médicos previos en el sistema.</div>'; return;
        }

        historial.forEach(h => {
            const fechaL = new Date(h.fecha_registro).toLocaleDateString('es-AR') + ' ' + new Date(h.fecha_registro).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            div.innerHTML += `
                <div class="card mb-3 border-info">
                    <div class="card-header bg-info bg-opacity-10 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-dark"><i class="bi bi-calendar-check"></i> ${fechaL}</span>
                            <span class="badge bg-primary">${h.especialidad}</span>
                        </div>
                        <small class="text-muted">Atendido por: Dr/a. ${h.med_apellido}, ${h.med_nombre}</small>
                    </div>
                    <div class="card-body py-2">
                        <p class="mb-1"><strong>Diagnóstico:</strong> ${h.diagnostico}</p>
                        ${h.tratamiento ? `<p class="mb-1 text-muted small"><strong>Tratamiento:</strong> ${h.tratamiento}</p>` : ''}
                        ${h.receta ? `<p class="mb-0 text-muted small"><strong>Receta:</strong> ${h.receta}</p>` : ''}
                    </div>
                </div>
            `;
        });
    }

    // 5. Marcar Ausente
    function marcarAusente(id_turno) {
        Swal.fire({title: '¿Marcar como ausente?', text: "El paciente perderá su turno definitivamente.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, está ausente'})
        .then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData(); formData.append('id_turno', id_turno);
                await fetch(api + '?accion=marcar_ausente', { method: 'POST', body: formData });
                cargarAgenda();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', cargarAgenda);
</script>
</body>
</html>