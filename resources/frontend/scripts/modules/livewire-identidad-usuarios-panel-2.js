function enviarPaqueteCorreo(codUsuario, correo) {
    if (!correo) {
        (window.SwalAmandita || Swal).fire({ icon: 'warning', title: 'Sin correo', text: 'Registre un correo para este usuario antes de solicitar el envío.' });
        return;
    }
    (window.SwalAmandita || Swal).fire({
        title: 'Enviar paquete documental', text: 'Se enviará al correo registrado: ' + correo,
        icon: 'question', showCancelButton: true, confirmButtonText: 'Solicitar envío', cancelButtonText: 'Cancelar',
    }).then(result => {
        if (result.isConfirmed) window.RMCorreo.enviar(`/admin/usuarios/${encodeURIComponent(codUsuario)}/documentos/enviar`);
    });
}
