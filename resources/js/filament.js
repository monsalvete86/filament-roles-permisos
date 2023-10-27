// resources/js/filament.js

function openAssignDigitadoresForm(record) {
    // Esto redirige al usuario a la página de asignación con el ID del coordinador como parámetro.
    window.location.href = `/admin/assign-digitadores/${record.id}`;
}