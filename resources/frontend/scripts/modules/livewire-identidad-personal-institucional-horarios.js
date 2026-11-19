
    $wire.on('confirmarGuardadoAsignacion', (event) => {
        const data = event[0] ?? event;
        const message = data.message ?? 'Se registrará el horario seleccionado.';
        let content = { text: message };

        if (data.fechas?.length) {
            const preview = document.createElement('div');
            const description = document.createElement('p');
            description.textContent = message;

            const dates = document.createElement('details');
            const summary = document.createElement('summary');
            summary.textContent = `Ver fechas (${data.fechas.length})`;

            const list = document.createElement('ul');
            list.className = 'max-h-40 overflow-auto text-left space-y-1';
            for (const date of data.fechas) {
                const item = document.createElement('li');
                item.textContent = date;
                list.append(item);
            }

            dates.append(summary, list);
            preview.append(description, dates);
            content = { html: preview };
        }

        Swal.fire({
            title: data.title ?? 'Guardar asignación',
            ...content,
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

    $wire.on('confirmarPreparacionTurnosRotacion', () => {
        Swal.fire({
            title: 'Preparar turnos de ocho horas',
            text: 'Se crearán los turnos que falten para mañana (07:00–15:00), tarde (15:00–23:00) y noche (23:00–07:00).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Preparar turnos',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('prepararTurnosRotacion');
            }
        });
    });
