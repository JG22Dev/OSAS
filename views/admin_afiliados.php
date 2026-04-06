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
                            <select class="form-select" id="id_plan" name="id_plan" required>
                                <option value="" selected disabled>Cargando planes...</option>
                            </select>
                        </div>

                        <hr>
                        <h6 class="text-muted">Crear Cuenta de Acceso</h6>

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
                        <table class="table table-hover mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Afiliado</th>
                                    <th>DNI</th>
                                    <th>Credencial</th>
                                    <th>Plan Contratado</th>
                                    <th>Email</th>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/AfiliadoController.php';

    async function cargarPlanes() {
        try {
            const res = await fetch(urlController + '?accion=listar_planes');
            const data = await res.json();
            const select = document.getElementById('id_plan');
            select.innerHTML = '<option value="" selected disabled>Seleccioná un plan</option>';
            data.forEach(plan => {
                select.innerHTML += `<option value="${plan.id_plan}">${plan.nombre}</option>`;
            });
        } catch (error) {
            console.error(error);
        }
    }

    async function cargarTabla() {
        try {
            const res = await fetch(urlController + '?accion=listar_afiliados');
            const data = await res.json();
            const tbody = document.getElementById('tablaAfiliados');
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay afiliados registrados</td></tr>';
                return;
            }

            data.forEach(afi => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${afi.apellido}</strong>, ${afi.nombre}</td>
                        <td>${afi.dni}</td>
                        <td><span class="badge bg-secondary">${afi.numero_credencial}</span></td>
                        <td><span class="badge bg-success">${afi.plan_nombre}</span></td>
                        <td>${afi.email}</td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error(error);
        }
    }

    document.getElementById('formAfiliado').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const res = await fetch(urlController + '?accion=registrar', {
                method: 'POST',
                body: formData
            });
            const resultado = await res.json();

            if(resultado.status === 'success') {
                Swal.fire({
                    icon: 'success', title: 'Excelente', text: resultado.mensaje, timer: 2000, showConfirmButton: false
                });
                this.reset();
                cargarTabla();
            } else {
                Swal.fire('Atención', resultado.mensaje, 'warning');
            }
        } catch (error) {
            Swal.fire('Error', 'Problema de conexión al servidor', 'error');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        cargarPlanes();
        cargarTabla();
    });
</script>

</body>
</html>