
    $wire.on('confirmarGuardadoAsignacion', (event) => {
        const data = event[0] ?? event;

        Swal.fire({
            title: data.title ?? 'Guardar asignación',
            text: data.message ?? 'Se registrará el horario seleccionado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#78716C',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Revisar datos',
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('guardar');
            }
        });
    });

    $wire.on('confirmarFinalizacionAsignacion', () => {
        Swal.fire({
            title: 'Finalizar asignación',
            text: 'El horario quedará como FINALIZADO y se conservará en el historial.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#C06B4E',
            cancelButtonColor: '#78716C',
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('confirmarFinalizar');
            }
        });
    });
