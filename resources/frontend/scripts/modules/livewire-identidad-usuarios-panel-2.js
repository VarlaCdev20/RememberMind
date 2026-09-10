
 function enviarPaqueteCorreo(codUsu, correo) {
 if(!correo) {
 Swal.fire({
 icon: 'warning',
 title: 'Sin correo',
 text: 'Este usuario no tiene un correo electrónico registrado.',
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 return;
 }

 Swal.fire({
 title: '¿Enviar Paquete Documental?',
 text:"Se enviará el documento adjunto al correo:" + correo,
 icon: 'question',
 showCancelButton: true,
 confirmButtonColor: '#8DA280',
 cancelButtonColor: '#E27D60',
 confirmButtonText: 'Sí, enviar',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) {
 Swal.fire({
 title: 'Enviando...',
 text: 'Por favor espere un momento',
 allowOutsideClick: false,
 didOpen: () => {
 Swal.showLoading();
 },
 customClass: { popup: 'rounded-[1.5rem]' }
 });

 fetch(`/admin/usuarios/${codUsu}/documentos/enviar`, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
 }
 })
 .then(response => response.json())
 .then(data => {
 if (data.success) {
 Swal.fire({
 icon: 'success',
 title: '¡Enviado!',
 text: data.message,
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 } else {
 Swal.fire({
 icon: 'error',
 title: 'Error',
 text: data.message,
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 }
 })
 .catch(error => {
 Swal.fire({
 icon: 'error',
 title: 'Error de red',
 text: 'No se pudo completar la solicitud',
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 });
 }
 });
 }
 