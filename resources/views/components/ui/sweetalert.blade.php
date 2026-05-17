<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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

        // Tema de Casa Amandita para alertas (botones color #BC6C25)
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
                title: "{{ session('success') }}"
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: "{{ session('warning') }}"
            });
        @endif

        @if(session('info'))
            Toast.fire({
                icon: 'info',
                title: "{{ session('info') }}"
            });
        @endif
        
        @if(session('status'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('status') }}"
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
</script>
