<?php
session_start();
// Solo permitimos entrar si está logueado y es Admin
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Alta de Equipo Médico</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Registrar Nuevo Médico</h5>
                    <form id="formProfesional">
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
                            <select class="form-select" id="id_especialidad" name="id_especialidad" required>
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
                    <h5 class="card-title">Plantel Médico Actual</h5>
                    <div class="table-responsive">
                        <table class="table table-hover mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Profesional</th>
                                    <th>Especialidad</th>
                                    <th>Matrícula</th>
                                    <th>Email (Usuario)</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProfesionales">
                                <tr><td colspan="4" class="text-center">Cargando datos...</td></tr>
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
    const urlController = '../controllers/ProfesionalController.php';

    // 1. Cargar las especialidades en el <select>
    async function cargarEspecialidades() {
        try {
            const res = await fetch(urlController + '?accion=listar_especialidades');
            const data = await res.json();
            const select = document.getElementById('id_especialidad');
            select.innerHTML = '<option value="" selected disabled>Seleccioná una especialidad</option>';
            data.forEach(esp => {
                select.innerHTML += `<option value="${esp.id_especialidad}">${esp.nombre}</option>`;
            });
        } catch (error) {
            console.error(error);
        }
    }

    // 2. Cargar la tabla de médicos
    async function cargarTabla() {
        try {
            const res = await fetch(urlController + '?accion=listar_profesionales');
            const data = await res.json();
            const tbody = document.getElementById('tablaProfesionales');
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay profesionales registrados</td></tr>';
                return;
            }

            data.forEach(prof => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${prof.apellido}</strong>, ${prof.nombre}</td>
                        <td><span class="badge bg-info text-dark">${prof.especialidad}</span></td>
                        <td>${prof.matricula}</td>
                        <td>${prof.email}</td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error(error);
        }
    }

    // 3. Enviar el formulario
    document.getElementById('formProfesional').addEventListener('submit', async function(e) {
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
                this.reset(); // Limpia el formulario
                cargarTabla(); // Recarga la tabla para mostrar al nuevo médico
            } else {
                Swal.fire('Atención', resultado.mensaje, 'warning');
            }
        } catch (error) {
            Swal.fire('Error', 'Problema de conexión al servidor', 'error');
        }
    });

    // Iniciar funciones al cargar la pantalla
    document.addEventListener('DOMContentLoaded', () => {
        cargarEspecialidades();
        cargarTabla();
    });
</script>

</body>
</html>