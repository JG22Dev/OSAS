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
    <title>Especialidades - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Catálogo de Especialidades</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Nueva Especialidad</h5>
                    <form id="formAgregar">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ej: Neurología" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Guardar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Especialidad</th>
                                <th class="text-center pe-4">Acciones y Configuración</th>
                            </tr>
                        </thead>
                        <tbody id="tablaEspecialidades">
                            <tr><td colspan="3" class="text-center py-4">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Editar Especialidad</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditar">
          <div class="modal-body">
              <input type="hidden" id="edit_id" name="id_especialidad">
              <div class="mb-3">
                  <label class="form-label">Nombre</label>
                  <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSedes" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-dark">
        <h5 class="modal-title"><i class="bi bi-geo-alt"></i> Asignar Sedes: <span id="tituloEspModal" class="fw-bold"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAsignarSedes">
          <div class="modal-body">
              <p class="text-muted small">Seleccioná en qué clínicas se ofrecerá esta especialidad.</p>
              <input type="hidden" id="sede_id_especialidad" name="id_especialidad">
              
              <div id="contenedorSedes" class="mt-3"></div>
              
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-info fw-bold">Guardar Sedes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const api = '../controllers/EspecialidadController.php';
    let modalEdicion = new bootstrap.Modal(document.getElementById('modalEditar'));
    let modalSedes = new bootstrap.Modal(document.getElementById('modalSedes'));

    async function cargarTabla() {
        const res = await fetch(api + '?accion=listar');
        const data = await res.json();
        const tbody = document.getElementById('tablaEspecialidades');
        tbody.innerHTML = '';

        data.forEach(esp => {
            tbody.innerHTML += `
                <tr>
                    <td class="ps-4 text-muted">#${esp.id_especialidad}</td>
                    <td class="fw-bold text-primary">${esp.nombre}</td>
                    <td class="text-center pe-4">
                        <button class="btn btn-sm btn-info text-dark fw-bold me-2" onclick="abrirModalSedes(${esp.id_especialidad}, '${esp.nombre}')"><i class="bi bi-building"></i> Ubicaciones</button>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="abrirModal(${esp.id_especialidad}, '${esp.nombre}')"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${esp.id_especialidad})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
        });
    }

    // --- Alta, Edición y Baja (Viejas) ---
    document.getElementById('formAgregar').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(api + '?accion=agregar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') { this.reset(); cargarTabla(); } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    function abrirModal(id, nombre) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nombre').value = nombre;
        modalEdicion.show();
    }

    document.getElementById('formEditar').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(api + '?accion=editar', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') { modalEdicion.hide(); cargarTabla(); }
    });

    function eliminar(id) {
        Swal.fire({ title: '¿Eliminar?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, borrar' }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData(); formData.append('id_especialidad', id);
                const res = await fetch(api + '?accion=eliminar', { method: 'POST', body: formData });
                const resultado = await res.json();
                if(resultado.status === 'success') { cargarTabla(); } else { Swal.fire('No se puede', resultado.mensaje, 'error'); }
            }
        });
    }

    // 🔥 LOGICA NUEVA PARA SEDES 🔥
    async function abrirModalSedes(id_especialidad, nombre_especialidad) {
        document.getElementById('sede_id_especialidad').value = id_especialidad;
        document.getElementById('tituloEspModal').innerText = nombre_especialidad;
        
        const contenedor = document.getElementById('contenedorSedes');
        contenedor.innerHTML = '<p class="text-center text-muted">Cargando clínicas...</p>';
        modalSedes.show();

        // Buscamos todas las sedes y marcamos las que ya tiene
        const res = await fetch(`${api}?accion=get_configuracion_sedes&id_especialidad=${id_especialidad}`);
        const data = await res.json();
        
        contenedor.innerHTML = '';
        if (data.todas.length === 0) {
            contenedor.innerHTML = '<div class="alert alert-warning">No hay clínicas activas en el sistema.</div>';
            return;
        }

        data.todas.forEach(sede => {
            // Checkeamos si la sede está en el array de seleccionadas
            let checked = data.seleccionadas.includes(sede.id_sede) ? 'checked' : '';
            
            contenedor.innerHTML += `
                <div class="form-check form-switch mb-2 fs-5">
                  <input class="form-check-input" type="checkbox" name="sedes[]" value="${sede.id_sede}" id="sede_${sede.id_sede}" ${checked}>
                  <label class="form-check-label" for="sede_${sede.id_sede}">${sede.nombre}</label>
                </div>
            `;
        });
    }

    document.getElementById('formAsignarSedes').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await fetch(api + '?accion=guardar_sedes', { method: 'POST', body: new FormData(this) });
        const resultado = await res.json();
        if(resultado.status === 'success') {
            Swal.fire({ position: 'top-end', icon: 'success', title: 'Guardado', showConfirmButton: false, timer: 1000 });
            modalSedes.hide();
        } else { Swal.fire('Error', resultado.mensaje, 'error'); }
    });

    document.addEventListener('DOMContentLoaded', cargarTabla);
</script>
</body>
</html>