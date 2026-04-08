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
    <title>Planes Comerciales - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión Comercial de Planes <i class="bi bi-cash-coin text-success"></i></h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-success border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Crear Nuevo Plan</h5>
                    <form id="formPlan">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Plan</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ej: Plan Black 300" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Cuota Mensual ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" name="cuota_mensual" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Guardar Plan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-4">Código</th>
                                    <th>Nombre del Plan</th>
                                    <th>Cuota Mensual</th>
                                    <th class="text-center pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPlanes">
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
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Plan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditar">
          <div class="modal-body">
              <input type="hidden" id="edit_id_plan" name="id_plan">
              <div class="mb-3">
                  <label class="form-label fw-bold">Nombre</label>
                  <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Cuota Mensual ($)</label>
                  <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" class="form-control" id="edit_cuota" name="cuota_mensual" required>
                  </div>
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
    const urlController = '../controllers/PlanController.php';
    let modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));

    // Formatear plata
    const formatearDinero = (monto) => {
        return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto);
    };

    async function cargarTabla() {
        const res = await fetch(urlController + '?accion=listar');
        const data = await res.json();
        const tbody = document.getElementById('tablaPlanes');
        tbody.innerHTML = '';

        if(data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No hay planes registrados.</td></tr>'; return;
        }

        data.forEach(plan => {
            tbody.innerHTML += `
                <tr>
                    <td class="ps-4 text-muted small">#PLAN-${plan.id_plan}</td>
                    <td class="fw-bold text-success">${plan.nombre}</td>
                    <td class="fw-bold">${formatearDinero(plan.cuota_mensual)}</td>
                    <td class="text-center pe-4">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="abrirModal(${plan.id_plan}, '${plan.nombre}', ${plan.cuota_mensual})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${plan.id_plan})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
        });
    }

    document.getElementById('formPlan').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=agregar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ position: 'top-end', icon: 'success', title: 'Plan creado', showConfirmButton: false, timer: 1000 });
            this.reset(); cargarTabla();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    function abrirModal(id, nombre, cuota) {
        document.getElementById('edit_id_plan').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_cuota').value = cuota;
        modalEditar.show();
    }

    document.getElementById('formEditar').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(urlController + '?accion=editar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') { modalEditar.hide(); cargarTabla(); }
    });

    function eliminar(id) {
        Swal.fire({ title: '¿Eliminar Plan?', text: 'Cuidado: Si hay pacientes usándolo, no podrás borrarlo.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, borrar' }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData(); formData.append('id_plan', id);
                const res = await fetch(urlController + '?accion=eliminar', { method: 'POST', body: formData });
                const resultado = await res.json();
                if(resultado.status === 'success') { cargarTabla(); } 
                else { Swal.fire('Bloqueo de seguridad', resultado.mensaje, 'error'); }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', cargarTabla);
</script>
</body>
</html>