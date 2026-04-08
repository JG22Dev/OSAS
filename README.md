# OSAS
🏥 Sistema Integral de Gestión Médica (V2.0) en PHP y MySQL (MVC). Gestiona clínicas multi-sede con 4 roles: Afiliado, Médico, Recepción y Admin. Incluye reservas con filtros en cascada, agenda inteligente (anti-teletransportación), historia clínica digital y base de datos relacional segura mediante bajas lógicas.

## 🚀 Características Principales

El sistema está dividido en 4 paneles con control de acceso por roles:

### 👤 1. Portal del Afiliado (Paciente)
* **Reserva Inteligente (Flujo en Cascada):** Sistema de turnos guiado. El paciente elige la Clínica -> Especialidad disponible en esa clínica -> Médicos que trabajan allí.
* **Calendario Dinámico:** Integración con `Flatpickr` que solo habilita los días y horarios en los que el médico seleccionado tiene disponibilidad real.
* **Gestión de Turnos:** Visualización del historial y cancelación de turnos condicionada por reglas de negocio (política de 24 horas de anticipación).

### 👨‍⚕️ 2. Portal del Profesional (Médico)
* **Agenda Segura (Anti-Teletransportación):** Algoritmo matemático en el backend que impide cruces de horarios y exige un mínimo de 60 minutos de "tiempo de viaje" si el médico se agenda en dos sedes físicas distintas el mismo día.
* **Consultorio Virtual:** Vista de pacientes en sala de espera en tiempo real.
* **Historia Clínica Digital:** Panel para registrar evolución, diagnóstico, tratamiento y recetas. Acceso inmediato al historial médico previo del paciente.

### 🧑‍💼 3. Portal de Recepción
* **Control de Tráfico:** Anuncio de llegada de pacientes (cambia el estado del turno para que el médico lo vea en su consultorio).
* **Gestión Manual:** Asignación de turnos presenciales o telefónicos utilizando el mismo motor de validación que el afiliado.

### 👨‍💻 4. Panel de Administración
* **Gestión Geográfica:** CRUD de Sedes (clínicas físicas).
* **Matriz de Especialidades:** Asignación granular de qué especialidades se atienden en qué sedes.
* **Gestión de Usuarios y Planes:** Alta, edición y "Baja Lógica" de médicos, pacientes y planes comerciales. Control total sobre la base de datos sin afectar la integridad referencial.

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8+ (Programación Orientada a Objetos, PDO para consultas seguras preparadas).
* **Base de Datos:** MySQL (Diseño Relacional, Claves Foráneas, Restricciones de Integridad).
* **Frontend:** HTML5, CSS3, Bootstrap 5.
* **Interacciones Asíncronas:** JavaScript Vanilla (Fetch API) para recarga de datos sin refrescar la página.
* **Librerías de UI:** SweetAlert2 (alertas interactivas), Flatpickr (calendarios inteligentes).

## 💡 Lógica de Negocios Destacada
* **Baja Lógica:** Los registros nunca se borran físicamente (`DELETE`), se desactivan cambiando su `estado` a inactivo para mantener el historial clínico y financiero intacto.
* **Validación de Relaciones (`Error 1451`):** El sistema captura excepciones de la base de datos para impedir que un administrador elimine una especialidad o un plan si existen pacientes o médicos utilizándolos.
