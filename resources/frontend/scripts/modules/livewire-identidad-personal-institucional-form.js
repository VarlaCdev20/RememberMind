
    $wire.on('confirmarRegistroFinal', (e) => {
        const data = e[0];
        let html = '';
        if (data.faltan_documentos) {
            html = `<div class="p-3 bg-estado-peligroBg text-estado-peligro text-sm rounded-xl mb-4 border border-estado-peligro/30 text-left"><i class="ph-bold ph-warning"></i> <b>Advertencia:</b> Faltan documentos obligatorios. No se permite finalizar como ACTIVO. El registro se completará pero el acceso quedará en estado <b>DOCUMENTACION_PENDIENTE</b>.</div>`;
        }
        html += '<p class="text-sm text-apoyo">¿Desea confirmar y procesar el alta final en el sistema?</p>';

        Swal.fire({
            title: 'Confirmar Registro Final',
            html: html,
            icon: data.faltan_documentos ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="ph-bold ph-check"></i> Sí, registrar personal',
            cancelButtonText: 'Revisar datos'
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.guardar();
            }
        });
    });

    $wire.on('credencialesGeneradas', (e) => {
        const data = e[0];
        Swal.fire({
            title: '¡Registro Exitoso!',
            html: data.html + "<div class='mt-4 p-3 bg-estado-advertenciaBg text-estado-advertencia text-xs rounded-lg border border-estado-advertenciaBorde'><i class='ph-bold ph-warning-circle'></i> <b>IMPORTANTE:</b> Copie esta contraseña temporal. Por seguridad, no volverá a mostrarse en el sistema.</div><div class='mt-3 p-3 bg-boton-acento/10 text-boton-acento text-xs rounded-lg border border-boton-acento/30'><i class='ph-bold ph-calendar-plus'></i> El siguiente paso recomendado es asignar el horario institucional.</div>",
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: '<i class="ph-bold ph-calendar-plus"></i> Continuar a horarios',
            cancelButtonText: 'Volver al listado',
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#78716C',
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.dispatch('personalRegistradoParaHorario', { usuarioId: data.usuarioId });
            } else {
                $wire.dispatch('cerrarModalGestion');
            }
            $wire.dispatch('actualizarTablaPersonal');
        });
    });

    $wire.on('confirmarCambioRoles', (e) => {
        Swal.fire({
            title: '¿Cambiar Rol del Sistema?',
            html: `Está a punto de modificar los permisos de acceso de este usuario.<br><br>Nuevos roles: <b>${e[0].roles}</b><br><br>¿Desea continuar y actualizar el personal?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar roles y actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('guardar');
            }
        });
    });

    $wire.on('mostrarAlerta', (e) => {
        const d = e[0];
        const iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        Swal.fire({
            title: d.title ?? '',
            text: d.message ?? '',
            icon: iconMap[d.type] ?? 'info',
            confirmButtonColor: '#3F7D5A',
            timer: d.type === 'success' ? 3000 : undefined,
            timerProgressBar: d.type === 'success',
        });
    });


