
        $wire.on('swal', (data) => {
            const payload = data[0] || data;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: payload.title,
                    text: payload.text,
                    icon: payload.icon,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: 'var(--boton-acento)',
                    background: 'var(--fondo-card)',
                    color: 'var(--texto-principal)'
                });
            } else {
                alert(payload.title + '\n' + payload.text);
            }
        });
    