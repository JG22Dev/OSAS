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
    <title>Gestión de Afiliados - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Afiliados</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-success border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Registrar Nuevo Paciente</h5>
                    <form id="formAfiliado">
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">DNI</label>
                                <input type="text" class="form-control" name="dni" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nacimiento" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Plan a Contratar</label>
                            <select class="form-select select-planes" name="id_plan" required>
                                <option value="" selected disabled>Cargando planes...</option>
                            </select>
                        </div>

                        <hr>
                        <h6 class="text-muted">Cuenta de Acceso</h6>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">Dar de Alta</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Padrón de Afiliados</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Afiliado</th>
                                    <th>DNI</th>
                                    <th>Credencial</th>
                                    <th>Plan / Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAfiliados">
                                <tr><td colspan="5" class="text-center">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarAfiliado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Paciente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEditarAfiliado">
          <div class="modal-body">
              <input type="hidden" id="edit_id_afiliado" name="id_afiliado">
              
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

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label">DNI</label>
                      <input type="text" class="form-control" id="edit_dni" name="dni" required>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Nacimiento</label>
                      <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento" required>
                  </div>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">Cambiar Plan</label>
                  <select class="form-select select-planes" id="edit_id_plan" name="id_plan" required>
                  </select>
              </div>
              
              <div class="alert alert-warning small mt-3">
                  <i class="bi bi-exclamation-triangle"></i> Si se suspende al afiliado por falta de pago, usar el botón rojo de la tabla.
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success fw-bold">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/AfiliadoController.php';
    let modalEditar = new bootstrap.Modal(document.getElementById('modalEditarAfiliado'));

    async function cargarPlanes() {
        try {
            const res = await fetch(urlController + '?accion=listar_planes');
            const data = await res.json();
            const selects = document.querySelectorAll('.select-planes');
            
            selects.forEach(select => {
                select.innerHTML = '<option value="" selected disabled>Seleccioná un plan</option>';
                data.forEach(plan => select.innerHTML += `<option value="${plan.id_plan}">${plan.nombre}</option>`);
            });
        } catch (error) { console.error(error); }
    }

    async function cargarTabla() {
        try {
            const res = await fetch(urlController + '?accion=listar_afiliados');
            const data = await res.json();
            const tbody = document.getElementById('tablaAfiliados');
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay afiliados</td></tr>'; return;
            }

            data.forEach(afi => {
                let esActivo = afi.estado === 'activo';
                let trClass = esActivo ? '' : 'table-secondary opacity-75';
                let badgeEstado = esActivo ? `<span class="badge bg-success ms-1">Al día</span>` : `<span class="badge bg-danger ms-1">Suspendido</span>`;
                
                let btnEditar = `<button class="btn btn-sm btn-outline-primary me-1" title="Editar" onclick="abrirModal(${afi.id_afiliado}, '${afi.nombre}', '${afi.apellido}', '${afi.dni}', '${afi.fecha_nacimiento}', ${afi.id_plan})"><i class="bi bi-pencil"></i></button>`;
                
                let btnEstado = esActivo 
                    ? `<button class="btn btn-sm btn-outline-danger" title="Suspender (Falta de pago)" onclick="cambiarEstado(${afi.id_usuario}, 'inactivo')"><i class="bi bi-person-x"></i></button>`
                    : `<button class="btn btn-sm btn-outline-success" title="Reactivar" onclick="cambiarEstado(${afi.id_usuario}, 'activo')"><i class="bi bi-person-check"></i></button>`;

                tbody.innerHTML += `
                    <tr class="${trClass}">
                        <td><strong>${afi.apellido}</strong>, ${afi.nombre}<br><small class="text-muted">${afi.email}</small></td>
                        <td>${afi.dni}</td>
                        <td><span class="badge bg-secondary">${afi.numero_credencial}</span></td>
                        <td><span class="badge border border-success text-success">${afi.plan_nombre}</span> <br> ${badgeEstado}</td>
                        <td class="text-center">${btnEditar} ${btnEstado}</td>
                    </tr>
                `;
            });
        } catch (error) { console.error(error); }
    }

    document.getElementById('formAfiliado').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=registrar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Excelente', text: resultado.mensaje, timer: 1500, showConfirmButton: false });
            this.reset(); cargarTabla();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    function abrirModal(id_afiliado, nombre, apellido, dni, fecha_nac, id_plan) {
        document.getElementById('edit_id_afiliado').value = id_afiliado;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_apellido').value = apellido;
        document.getElementById('edit_dni').value = dni;
        document.getElementById('edit_fecha_nacimiento').value = fecha_nac;
        document.getElementById('edit_id_plan').value = id_plan;
        modalEditar.show();
    }

    document.getElementById('formEditarAfiliado').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=editar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ position: 'top-end', icon: 'success', title: resultado.mensaje, showConfirmButton: false, timer: 1500 });
            modalEditar.hide(); cargarTabla();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    function cambiarEstado(id_usuario, nuevo_estado) {
        let titulo = nuevo_estado === 'inactivo' ? '¿Suspender afiliado?' : '¿Reactivar afiliado?';
        let texto = nuevo_estado === 'inactivo' ? 'No podrá iniciar sesión ni sacar turnos.' : 'Su cuenta volverá a estar operativa.';

        Swal.fire({ title: titulo, text: texto, icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, aplicar' })
        .then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData(); formData.append('id_usuario', id_usuario); formData.append('estado', nuevo_estado);
                const res = await fetch(urlController + '?accion=cambiar_estado', { method: 'POST', body: formData });
                const resultado = await res.json();
                if(resultado.status === 'success') { cargarTabla(); } 
                else { Swal.fire('Error', resultado.mensaje, 'error'); }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => { cargarPlanes(); cargarTabla(); });
</script>
</body>
</html>