<?php
session_start();
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
    <title>Gestión de Profesionales - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Plantel Médico</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Registrar Nuevo Médico</h5>
                    <form id="formAltaProfesional">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Especialidad</label>
                            <select class="form-select select-especialidades" name="id_especialidad" required>
                                <option value="" selected disabled>Cargando...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">N° Matrícula</label>
                            <input type="text" class="form-control" name="matricula" placeholder="Ej: MN-12345" required>
                        </div>

                        <hr>
                        <h6 class="text-muted">Datos de Acceso al Sistema</h6>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Contraseña Temporal</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold">Dar de Alta</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Listado de Profesionales</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Profesional</th>
                                    <th>Especialidad</th>
                                    <th>Matrícula</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProfesionales">
                                <tr><td colspan="5" class="text-center">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Profesional</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEditarProfesional">
          <div class="modal-body">
              <input type="hidden" id="edit_id_profesional" name="id_profesional">
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Nombre</label>
                      <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Apellido</label>
                      <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                  </div>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">Especialidad</label>
                  <select class="form-select select-especialidades" id="edit_id_especialidad" name="id_especialidad" required>
                      </select>
              </div>

              <div class="mb-3">
                  <label class="form-label">N° Matrícula</label>
                  <input type="text" class="form-control" id="edit_matricula" name="matricula" required>
              </div>
              
              <div class="alert alert-info small mt-3">
                  <i class="bi bi-info-circle"></i> El correo y contraseña solo pueden ser modificados por el usuario desde su perfil.
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/ProfesionalController.php';
    let modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));

    // 1. Cargar las especialidades en los dos selects (Alta y Edición)
    async function cargarEspecialidades() {
        try {
            const res = await fetch(urlController + '?accion=listar_especialidades');
            const data = await res.json();
            const selects = document.querySelectorAll('.select-especialidades');
            
            selects.forEach(select => {
                select.innerHTML = '<option value="" selected disabled>Seleccioná una especialidad</option>';
                data.forEach(esp => {
                    select.innerHTML += `<option value="${esp.id_especialidad}">${esp.nombre}</option>`;
                });
            });
        } catch (error) { console.error(error); }
    }

    // 2. Cargar la tabla con botones
    async function cargarTabla() {
        try {
            const res = await fetch(urlController + '?accion=listar_profesionales');
            const data = await res.json();
            const tbody = document.getElementById('tablaProfesionales');
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay profesionales</td></tr>'; return;
            }

            data.forEach(prof => {
                // Lógica de diseño según estado
                let esActivo = prof.estado === 'activo';
                let trClass = esActivo ? '' : 'table-secondary opacity-75';
                let badgeEstado = esActivo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                
                // Botones (La M de Modificar y la B de Baja)
                let btnEditar = `<button class="btn btn-sm btn-outline-primary me-1" title="Editar" onclick="abrirModal(${prof.id_profesional}, '${prof.nombre}', '${prof.apellido}', '${prof.matricula}', ${prof.id_especialidad})"><i class="bi bi-pencil"></i></button>`;
                
                let btnEstado = esActivo 
                    ? `<button class="btn btn-sm btn-outline-danger" title="Dar de Baja" onclick="cambiarEstado(${prof.id_usuario}, 'inactivo')"><i class="bi bi-person-x"></i></button>`
                    : `<button class="btn btn-sm btn-outline-success" title="Reactivar" onclick="cambiarEstado(${prof.id_usuario}, 'activo')"><i class="bi bi-person-check"></i></button>`;

                tbody.innerHTML += `
                    <tr class="${trClass}">
                        <td><strong>${prof.apellido}</strong>, ${prof.nombre}<br><small class="text-muted">${prof.email}</small></td>
                        <td><span class="badge bg-info text-dark">${prof.especialidad}</span></td>
                        <td>${prof.matricula}</td>
                        <td>${badgeEstado}</td>
                        <td class="text-center">${btnEditar} ${btnEstado}</td>
                    </tr>
                `;
            });
        } catch (error) { console.error(error); }
    }

    // 3. Alta
    document.getElementById('formAltaProfesional').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=registrar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Excelente', text: resultado.mensaje, timer: 1500, showConfirmButton: false });
            this.reset();
            cargarTabla();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    // 4. Abrir Modal de Edición con datos cargados
    function abrirModal(id_profesional, nombre, apellido, matricula, id_especialidad) {
        document.getElementById('edit_id_profesional').value = id_profesional;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_apellido').value = apellido;
        document.getElementById('edit_matricula').value = matricula;
        document.getElementById('edit_id_especialidad').value = id_especialidad;
        modalEditar.show();
    }

    // 5. Guardar Edición
    document.getElementById('formEditarProfesional').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=editar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ position: 'top-end', icon: 'success', title: resultado.mensaje, showConfirmButton: false, timer: 1500 });
            modalEditar.hide();
            cargarTabla();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    // 6. Baja Lógica / Reactivación
    function cambiarEstado(id_usuario, nuevo_estado) {
        let titulo = nuevo_estado === 'inactivo' ? '¿Suspender médico?' : '¿Reactivar médico?';
        let texto = nuevo_estado === 'inactivo' ? 'El médico no podrá iniciar sesión ni recibirá nuevos turnos.' : 'El médico volverá a operar normalmente.';

        Swal.fire({
            title: titulo, text: texto, icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id_usuario', id_usuario);
                formData.append('estado', nuevo_estado);

                const res = await fetch(urlController + '?accion=cambiar_estado', { method: 'POST', body: formData });
                const resultado = await res.json();
                
                if(resultado.status === 'success') { cargarTabla(); } 
                else { Swal.fire('Error', resultado.mensaje, 'error'); }
            }
        });
    }

    // Iniciar
    document.addEventListener('DOMContentLoaded', () => { cargarEspecialidades(); cargarTabla(); });
</script>
</body>
</html>