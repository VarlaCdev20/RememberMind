
 document.addEventListener('DOMContentLoaded', function () {
 const Toast = Swal.mixin({
 toast: true,
 position: 'top-end',
 showConfirmButton: false,
 timer: 4000,
 timerProgressBar: true,
 didOpen: (toast) => {
 toast.addEventListener('mouseenter', Swal.stopTimer)
 toast.addEventListener('mouseleave', Swal.resumeTimer)
 }
 });

 // Tema de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS para alertas (botones color #BC6C25)
 const swalAmandita = Swal.mixin({
 confirmButtonColor: '#BC6C25',
 cancelButtonColor: '#6B7280',
 iconColor: '#BC6C25',
 focusConfirm: false,
 });

 window.SwalToast = Toast;
 window.SwalAmandita = swalAmandita;

 @if(session('success'))
 Toast.fire({
 icon: 'success',
 title:"{{ session('success') }}"
 });
 @endif

 @if(session('error'))
 Toast.fire({
 icon: 'error',
 title:"{{ session('error') }}"
 });
 @endif

 @if(session('warning'))
 Toast.fire({
 icon: 'warning',
 title:"{{ session('warning') }}"
 });
 @endif

 @if(session('info'))
 Toast.fire({
 icon: 'info',
 title:"{{ session('info') }}"
 });
 @endif
 
 @if(session('status'))
 Toast.fire({
 icon: 'success',
 title:"{{ session('status') }}"
 });
 @endif

 // Escuchar eventos de Livewire 3
 window.addEventListener('swal', function(event) {
 const data = event.detail[0] || event.detail; // Livewire 3 a veces pasa los datos como el primer elemento del array
 window.SwalAmandita.fire({
 icon: data.icon || 'success',
 title: data.title || 'Éxito',
 text: data.text || '',
 });
 });

 // Escuchar el evento de post-registro interactivo
 window.addEventListener('mostrar-post-registro', function(event) {
 const data = event.detail[0] || event.detail;
 
 let credentialsHtml = '';
 if (data.credenciales_enviadas) {
 credentialsHtml = `
 <div class="mt-2 text-xs rm-badge-success text-left flex items-start gap-2 p-3 rounded-xl border border-borde-suave">
 <i class="ph-bold ph-check-circle text-base mt-0.5 flex-shrink-0"></i>
 <div>
 <strong>Credenciales enviadas:</strong> Las credenciales de acceso se enviaron correctamente a <span class="font-black">${data.email}</span>.
 </div>
 </div>
 `;
 } else {
 credentialsHtml = `
 <div class="mt-2 text-xs rm-badge-danger text-left flex flex-col gap-2 p-3 rounded-xl border border-borde-suave">
 <div class="flex items-start gap-2">
 <i class="ph-bold ph-warning-circle text-base mt-0.5 flex-shrink-0"></i>
 <div>
 <strong>Error de entrega:</strong> No se pudo enviar el correo de credenciales.
 </div>
 </div>
 <div class="bg-fondo-panel border border-borde-suave rounded-lg p-2 flex items-center justify-between">
 <span class="font-bold text-titulo">Clave Temporal:</span>
 <code class="bg-fondo-input border border-borde-suave px-2 py-0.5 rounded text-xs font-bold select-all text-boton-acento">${data.password_temporal}</code>
 </div>
 </div>
 `;
 }

 const htmlContent = `
 <div class="text-left">
 <div class="bg-fondo-panel border border-borde-suave rounded-xl p-3.5 mb-3 text-left">
 <span class="text-xs text-meta font-black uppercase tracking-wide block mb-0.5">Colaborador</span>
 <strong class="text-sm text-titulo">${data.nombre}</strong>
 </div>

 ${credentialsHtml}

 <div class="mt-4 border-t border-borde pt-3">
 <span class="text-xs text-meta font-black uppercase tracking-wide block mb-2">Acciones Sugeridas Post-Registro</span>
 <div class="grid grid-cols-2 gap-2">
 <a href="/admin/usuarios/${data.usuario_id}?tab=documentacion" class="flex flex-col justify-between gap-1.5 rounded-xl border border-borde-suave bg-fondo-card p-2.5 text-left transition hover:border-borde-focus hover:bg-fondo-hover group">
 <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-titulo">
 <i class="ph-bold ph-folder-lock text-boton-acento text-sm"></i> Documentos
 </span>
 <span class="text-xs text-apoyo font-bold leading-tight">Expediente y requisitos del rol</span>
 </a>

 <a href="/admin/usuarios/${data.usuario_id}?tab=horarios" class="flex flex-col justify-between gap-1.5 rounded-xl border border-borde-suave bg-fondo-card p-2.5 text-left transition hover:border-borde-focus hover:bg-fondo-hover group">
 <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-titulo">
 <i class="ph-bold ph-calendar-check text-boton-acento text-sm"></i> Horarios
 </span>
 <span class="text-xs text-apoyo font-bold leading-tight">Planificar turnos y jornada</span>
 </a>

 <a href="/admin/usuarios/${data.usuario_id}" class="flex flex-col justify-between gap-1.5 rounded-xl border border-borde-suave bg-fondo-card p-2.5 text-left transition hover:border-borde-focus hover:bg-fondo-hover group">
 <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-titulo">
 <i class="ph-bold ph-user-focus text-boton-acento text-sm"></i> Ver Ficha
 </span>
 <span class="text-xs text-apoyo font-bold leading-tight">Perfil general e institucional</span>
 </a>

 <a href="/admin/usuarios/${data.usuario_id}/ficha/pdf" target="_blank" class="flex flex-col justify-between gap-1.5 rounded-xl border border-borde-suave bg-fondo-card p-2.5 text-left transition hover:border-borde-focus hover:bg-fondo-hover group">
 <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-titulo">
 <i class="ph-bold ph-file-pdf text-boton-acento text-sm"></i> Imprimir Ficha
 </span>
 <span class="text-xs text-apoyo font-bold leading-tight">Descargar PDF del expediente</span>
 </a>

 <button onclick="window.enviarFichaEmail('${data.usuario_id}')" class="col-span-2 flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-card p-2.5 text-center transition hover:border-borde-focus hover:bg-fondo-hover group">
 <i class="ph-bold ph-paper-plane-tilt text-boton-acento text-base group-hover:scale-110 transition-transform"></i>
 <div class="text-left">
 <span class="block text-xs font-bold uppercase tracking-wide text-titulo">Enviar Ficha por Correo</span>
 <span class="block text-xs text-apoyo font-bold leading-none">Envía expediente firmado en PDF al colaborador</span>
 </div>
 </button>
 </div>
 </div>
 </div>
 `;

 window.SwalAmandita.fire({
 title: '¡Usuario Registrado Exitosamente!',
 html: htmlContent,
 icon: 'success',
 showConfirmButton: true,
 confirmButtonText: '<i class="ph-bold ph-arrow-left mr-1"></i> Volver a Usuarios',
 confirmButtonColor: '#2F3E5C',
 customClass: {
 title: 'text-lg font-extrabold text-titulo pt-4',
 popup: 'rounded-2xl border border-borde-suave bg-fondo-panel shadow-modal'
 }
 });
 });

 // Función global para enviar ficha por correo
 window.enviarFichaEmail = function(usuarioId) {
 window.SwalToast.fire({
 icon: 'info',
 title: 'Encolando solicitud de envío...'
 });

 fetch(`/admin/usuarios/${usuarioId}/ficha/enviar-correo`, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
 }
 })
 .then(response => response.json())
 .then(data => {
 if (data.success) {
 window.SwalToast.fire({
 icon: 'success',
 title: data.message
 });
 } else {
 window.SwalToast.fire({
 icon: 'error',
 title: data.message || 'Error al procesar la solicitud'
 });
 }
 })
 .catch(error => {
 console.error('Error:', error);
 window.SwalToast.fire({
 icon: 'error',
 title: 'Error de conexión con el servidor.'
 });
 });
 };
 });

 // Función global para confirmaciones con SweetAlert2
 window.confirmarAccion = function(event, titulo, texto = 'Esta acción quedará registrada en el historial institucional.', confirmText = 'Sí, confirmar') {
 event.preventDefault();
 const form = event.target || event.currentTarget;
 
 window.SwalAmandita.fire({
 title: titulo,
 text: texto,
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: confirmText,
 cancelButtonText: 'Cancelar'
 }).then((result) => {
 if (result.isConfirmed) {
 // Evitar doble submit
 const submitBtn = form.querySelector('button[type="submit"]');
 if (submitBtn) {
 const originalContent = submitBtn.innerHTML;
 submitBtn.disabled = true;
 submitBtn.innerHTML = '<i class="ph-bold ph-spinner animate-spin mr-1"></i> Procesando...';
 submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
 }
 form.submit();
 }
 });
 };

 // Función para prevenir doble envío en formularios estándar
 window.procesarFormulario = function(event) {
 const form = event.target || event.currentTarget;
 const submitBtns = document.querySelectorAll('button[type="submit"][form="' + form.id + '"], ' + '#' + form.id + ' button[type="submit"]');
 
 submitBtns.forEach(btn => {
 btn.disabled = true;
 btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin mr-1"></i> Guardando...';
 btn.classList.add('opacity-75', 'cursor-not-allowed');
 });
 };
