<?php
session_start();
// Barrera de seguridad: Solo Recepción y Administrador pueden entrar
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
    <title>Recepción - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Panel de Recepción <span class="badge bg-warning text-dark fs-6 ms-2">Control de Ingresos</span></h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row mb-4 bg-white p-3 rounded shadow-sm border-start border-warning border-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold text-muted small"><i class="bi bi-calendar"></i> Fecha Desde:</label>
            <input type="date" class="form-control" id="filtroInicio">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold text-muted small"><i class="bi bi-calendar"></i> Fecha Hasta:</label>
            <input type="date" class="form-control" id="filtroFin">
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100 fw-bold" onclick="cargarTablaFiltros()">
                <i class="bi bi-search"></i> Filtrar Turnos
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Día y Hora</th>
                            <th>Paciente</th>
                            <th>Credencial</th>
                            <th>Profesional</th>
                            <th>Consultorio</th>
                            <th class="text-center pe-4">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaRecepcion">
                        <tr><td colspan="6" class="text-center py-4">Cargando turnos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/RecepcionController.php';

    // Función para formatear la hora (De "09:00:00" a "09:00")
    const formatoHora = (horaFull) => horaFull.substring(0, 5);

    // Formatear fecha para mostrarla más linda (DD/MM/YYYY)
    const formatoFecha = (fechaISO) => {
        const partes = fechaISO.split('-');
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    };

    // Al cargar la página, seteamos las fechas de hoy por defecto y buscamos
    document.addEventListener('DOMContentLoaded', () => {
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('filtroInicio').value = hoy;
        document.getElementById('filtroFin').value = hoy;
        cargarTablaFiltros();
    });

    // Función principal que busca los turnos con los filtros
    async function cargarTablaFiltros() {
        const inicio = document.getElementById('filtroInicio').value;
        const fin = document.getElementById('filtroFin').value;
        const tbody = document.getElementById('tablaRecepcion');
        
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Buscando...</td></tr>';

        try {
            // Llamamos a la API pasando las fechas por URL
            const res = await fetch(`${urlController}?accion=listar_hoy&inicio=${inicio}&fin=${fin}`);
            const turnos = await res.json();
            
            tbody.innerHTML = '';

            if (turnos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-cup-hot fs-2 d-block mb-2"></i>No hay turnos registrados en este rango de fechas.</td></tr>';
                return;
            }

            turnos.forEach(t => {
                let botonAccion = '';
                let filaClase = '';

                // Lógica de botones según el estado
                if (t.id_estado == 2) { // 2 = Reservado (Aún no llegó)
                    botonAccion = `<button class="btn btn-warning text-dark btn-sm fw-bold shadow-sm" onclick="anunciarPaciente(${t.id_turno})"><i class="bi bi-bell-fill"></i> Anunciar Llegada</button>`;
                } else if (t.id_estado == 3) { // 3 = Confirmado (En Sala)
                    botonAccion = `<span class="badge bg-success"><i class="bi bi-check2-all"></i> En Sala</span>`;
                    filaClase = 'table-success';
                } else { // Atendido (4), Ausente (5), Cancelado (6)
                    let badgeColor = (t.id_estado == 6 || t.id_estado == 5) ? 'bg-danger' : 'bg-secondary';
                    botonAccion = `<span class="badge ${badgeColor}">Cerrado/Inactivo</span>`;
                    filaClase = 'opacity-50'; // Opacidad bajita porque ya no importa
                }

                tbody.innerHTML += `
                    <tr class="${filaClase}">
                        <td class="ps-4">
                            <span class="d-block fw-bold fs-5">${formatoHora(t.hora)}</span>
                            <small class="text-muted">${formatoFecha(t.fecha)}</small>
                        </td>
                        <td>
                            <strong>${t.paciente_apellido}, ${t.paciente_nombre}</strong><br>
                            <small class="text-muted">DNI: ${t.dni}</small>
                        </td>
                        <td><span class="badge border border-secondary text-secondary">${t.numero_credencial}</span></td>
                        <td>Dr/a. ${t.medico_apellido}</td>
                        <td><span class="badge bg-info text-dark">${t.especialidad}</span></td>
                        <td class="text-center pe-4">${botonAccion}</td>
                    </tr>
                `;
            });
        } catch (error) {
            Swal.fire('Error', 'No se pudieron cargar los turnos. Revisá la conexión.', 'error');
        }
    }

    // Función para cambiar el estado a "Confirmado"
    async function anunciarPaciente(id_turno) {
        const formData = new FormData();
        formData.append('id_turno', id_turno);

        try {
            const res = await fetch(urlController + '?accion=confirmar_llegada', {
                method: 'POST',
                body: formData
            });
            const resultado = await res.json();

            if (resultado.status === 'success') {
                // Notificación chiquita y no invasiva
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: '¡Paciente Anunciado!',
                    showConfirmButton: false,
                    timer: 1500
                });
                // Recargamos la tabla para que se actualice la fila a color verde
                cargarTablaFiltros(); 
            } else {
                Swal.fire('Error', resultado.mensaje, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Problema de conexión al anunciar al paciente.', 'error');
        }
    }
</script>

</body>
</html>