
        $wire.on('swal', (data) => {
            const payload = data[0] || data;
            window.rmAvisar({
                    title: payload.title,
                    text: payload.text,
                    icon: payload.icon,
                    confirmText: 'Entendido',
                    tone: payload.icon === 'error' ? 'danger' : 'neutral'
                });
        });

