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
    <title>Gestión Comercial - SaludPrepaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Planes Prepagos (Catálogo de 5 Niveles)</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-4" id="contenedorPlanes">
        <div class="col-12 text-center w-100"><p>Cargando los 5 planes...</p></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    const urlController = '../controllers/PlanController.php';

    // Función para formatear plata en pesos argentinos
    const formatearMoneda = (numero) => {
        return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(numero);
    };

    // 1. Cargar y dibujar los 5 planes
    async function cargarPlanes() {
        try {
            const res = await fetch(urlController + '?accion=listar_todo');
            const planes = await res.json();
            const contenedor = document.getElementById('contenedorPlanes');
            contenedor.innerHTML = '';

            // Colores para identificar los niveles (Básico = gris, Black = oscuro)
            const colores = ['secondary', 'info', 'warning', 'primary', 'dark'];

            planes.forEach((plan, index) => {
                const color = colores[index % 5];
                
                // Armamos la lista de viñetas (<li>) de los beneficios
                let listaBeneficios = '';
                if(plan.beneficios.length > 0) {
                    plan.beneficios.forEach(b => {
                        listaBeneficios += `<li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>${b.descripcion}</li>`;
                    });
                } else {
                    listaBeneficios = `<li class="text-muted fst-italic">Sin beneficios asignados aún.</li>`;
                }

                // Dibujamos la tarjeta del plan
                contenedor.innerHTML += `
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 border-top border-${color} border-4">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-center text-${color} fw-bold">${plan.nombre}</h5>
                                <h3 class="text-center mb-0 mt-3">${formatearMoneda(plan.cuota_mensual)}</h3>
                                <p class="text-center text-muted small">Prioridad de Turnos: Nivel ${plan.nivel_prioridad}</p>
                                <hr>
                                <ul class="list-unstyled flex-grow-1 small">
                                    ${listaBeneficios}
                                </ul>
                                <button class="btn btn-outline-${color} btn-sm w-100 mt-3" onclick="agregarBeneficio(${plan.id_plan}, '${plan.nombre}')">
                                    + Agregar Beneficio
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } catch (error) {
            Swal.fire('Error', 'No se pudieron cargar los planes', 'error');
        }
    }

    // 2. Función que abre un pop-up para sumar un beneficio
    async function agregarBeneficio(id_plan, nombre_plan) {
        const { value: descripcion } = await Swal.fire({
            title: `Nuevo beneficio para ${nombre_plan}`,
            input: 'text',
            inputLabel: 'Descripción del beneficio',
            inputPlaceholder: 'Ej: Kinesiología 50%...',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) { return '¡Necesitás escribir algo!' }
            }
        });

        if (descripcion) {
            // Mandamos por POST al controlador
            const formData = new FormData();
            formData.append('id_plan', id_plan);
            formData.append('descripcion', descripcion);

            try {
                const res = await fetch(urlController + '?accion=agregar_beneficio', {
                    method: 'POST',
                    body: formData
                });
                const resultado = await res.json();

                if(resultado.status === 'success') {
                    Swal.fire({
                        icon: 'success', title: '¡Guardado!', text: resultado.mensaje, timer: 1500, showConfirmButton: false
                    });
                    cargarPlanes(); // Recarga la pantalla para mostrar la viñeta nueva
                } else {
                    Swal.fire('Error', resultado.mensaje, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Problema de red', 'error');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', cargarPlanes);
</script>

</body>
</html>