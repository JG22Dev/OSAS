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
    <title>Gestión de Sedes - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Sucursales (Clínicas) <i class="bi bi-building text-primary"></i></h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Agregar Nueva Sede</h5>
                    <form id="formSede">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Sede</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ej: Centro Médico Belgrano" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Dirección Completa</label>
                            <input type="text" class="form-control" name="direccion" placeholder="Ej: Av. Cabildo 1234, CABA" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Guardar Sucursal</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-4">Sede / Clínica</th>
                                    <th>Dirección Física</th>
                                    <th>Estado</th>
                                    <th class="text-center pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaSedes">
                                <tr><td colspan="4" class="text-center py-4">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Sede</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditar">
          <div class="modal-body">
              <input type="hidden" id="edit_id_sede" name="id_sede">
              <div class="mb-3">
                  <label class="form-label">Nombre</label>
                  <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Dirección</label>
                  <input type="text" class="form-control" id="edit_direccion" name="direccion" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/SedeController.php';
    let modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));

    // Cargar la tabla
    async function cargarTabla() {
        const res = await fetch(urlController + '?accion=listar');
        const data = await res.json();
        const tbody = document.getElementById('tablaSedes');
        tbody.innerHTML = '';

        if(data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No hay sedes registradas.</td></tr>'; return;
        }

        data.forEach(sede => {
            let esActivo = sede.estado === 'activo';
            let trClass = esActivo ? '' : 'table-secondary opacity-75';
            let badgeEstado = esActivo ? '<span class="badge bg-success">Operativa</span>' : '<span class="badge bg-danger">En Reparación / Cerrada</span>';
            
            let btnEditar = `<button class="btn btn-sm btn-outline-primary me-1" title="Editar" onclick="abrirModal(${sede.id_sede}, '${sede.nombre}', '${sede.direccion}')"><i class="bi bi-pencil"></i></button>`;
            let btnEstado = esActivo 
                ? `<button class="btn btn-sm btn-outline-danger" title="Cerrar Sede" onclick="cambiarEstado(${sede.id_sede}, 'inactivo')"><i class="bi bi-door-closed"></i></button>`
                : `<button class="btn btn-sm btn-outline-success" title="Reabrir Sede" onclick="cambiarEstado(${sede.id_sede}, 'activo')"><i class="bi bi-door-open"></i></button>`;

            tbody.innerHTML += `
                <tr class="${trClass}">
                    <td class="ps-4 fw-bold">${sede.nombre}</td>
                    <td><i class="bi bi-geo-alt-fill text-danger"></i> ${sede.direccion}</td>
                    <td>${badgeEstado}</td>
                    <td class="text-center pe-4">${btnEditar} ${btnEstado}</td>
                </tr>
            `;
        });
    }

    // Agregar
    document.getElementById('formSede').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=agregar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Agregada', text: resultado.mensaje, timer: 1500, showConfirmButton: false });
            this.reset(); cargarTabla();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    // Abrir Modal
    function abrirModal(id, nombre, direccion) {
        document.getElementById('edit_id_sede').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_direccion').value = direccion;
        modalEditar.show();
    }

    // Guardar Edición
    document.getElementById('formEditar').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=editar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            modalEditar.hide(); cargarTabla();
        }
    });

    // Baja Lógica
    function cambiarEstado(id_sede, nuevo_estado) {
        let texto = nuevo_estado === 'inactivo' ? 'Los pacientes no podrán elegir esta sede temporalmente.' : 'La sede volverá a estar disponible para turnos.';
        Swal.fire({
            title: '¿Confirmar acción?', text: texto, icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, aplicar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData(); formData.append('id_sede', id_sede); formData.append('estado', nuevo_estado);
                const res = await fetch(urlController + '?accion=cambiar_estado', { method: 'POST', body: formData });
                await res.json();
                cargarTabla();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', cargarTabla);
</script>
</body>
</html>