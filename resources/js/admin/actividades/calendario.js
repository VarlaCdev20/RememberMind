import { Calendar } from '@fullcalendar/core';
import dayGridPlugin    from '@fullcalendar/daygrid';
import timeGridPlugin   from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin       from '@fullcalendar/list';
import esLocale         from '@fullcalendar/core/locales/es';

// ── Helpers ───────────────────────────────────────────────────────────────────

function formatHora(date) {
    if (! date) return null;
    const h = String(date.getHours()).padStart(2, '0');
    const m = String(date.getMinutes()).padStart(2, '0');
    return `${h}:${m}`;
}

function swal() {
    return window.SwalAmandita || window.Swal || { fire: () => Promise.resolve({ isConfirmed: false }) };
}

// ── Inicialización del calendario ─────────────────────────────────────────────

window.initCalendarioActividades = function (config = {}) {
    const el = document.getElementById('fullcalendar-container');
    if (! el) return;

    // Opciones inyectadas desde el blade (URLs, filtros reactivos)
    const opts = Object.assign({
        eventsUrl:        '/admin/actividades/eventos',
        reprogramarUrl:   '/admin/actividades',
        detalleUrl:       '/admin/actividades',
        csrfToken:        document.querySelector('meta[name="csrf-token"]')?.content || '',
        canEdit:          false,
        filtros:          {},
    }, config);

    // Guardar en variable global para que los eventos de filtro puedan actualizarlos
    window._calendarioFiltros = Object.assign({}, opts.filtros);

    // Configurar Axios con CSRF
    if (window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = opts.csrfToken;
    }

    // ── Crear instancia de Calendar ───────────────────────────────────────────
    const calendar = new Calendar(el, {
        plugins:     [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        locale:      esLocale,
        initialView: 'dayGridMonth',

        // Barra de herramientas
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },

        // Nombres de botones en español
        buttonText: {
            today:    'Hoy',
            month:    'Mes',
            week:     'Semana',
            day:      'Día',
            list:     'Lista',
        },

        // Comportamiento
        editable:    opts.canEdit,
        droppable:   false,
        height:      'auto',
        aspectRatio: 1.8,
        nowIndicator: true,
        dayMaxEvents: 4,
        eventDisplay: 'block',

        // Slots de tiempo (vista semana/día)
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
        slotDuration: '00:30:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

        // Fuente de eventos
        events: function (info, successCallback, failureCallback) {
            // Lee filtros actuales desde variable global (actualizados por Livewire via evento)
            const filtrosActuales = window._calendarioFiltros || opts.filtros;
            const params = new URLSearchParams({
                start:   info.startStr,
                end:     info.endStr,
                tipo:    filtrosActuales.tipo    || '',
                estado:  filtrosActuales.estado  || '',
                buscar:  filtrosActuales.buscar  || '',
            });

            fetch(`${opts.eventsUrl}?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': opts.csrfToken,
                    'Accept':       'application/json',
                },
                credentials: 'same-origin',
            })
                .then(r => r.json())
                .then(data => successCallback(data))
                .catch(() => {
                    failureCallback();
                    console.error('Error al cargar eventos del calendario.');
                });
        },

        // ── Clic en fecha vacía: abrir modal nueva actividad ──────────────────
        dateClick: function (info) {
            const hora = info.dateStr.includes('T') ? info.dateStr.split('T')[1]?.substring(0, 5) : '';
            window.dispatchEvent(new CustomEvent('calendario:date-click', {
                detail: {
                    fecha: info.dateStr.substring(0, 10),
                    hora:  hora || '',
                },
            }));
        },

        // ── Clic en evento: abrir modal de detalle ────────────────────────────
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            const id = info.event.id;

            fetch(`${opts.detalleUrl}/${id}/detalle-json`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': opts.csrfToken },
                credentials: 'same-origin',
            })
                .then(r => r.json())
                .then(data => {
                    window.dispatchEvent(new CustomEvent('calendario:event-click', {
                        detail: data,
                    }));
                })
                .catch(() => {
                    swal().fire('Error', 'No se pudo cargar el detalle de la actividad.', 'error');
                });
        },

        // ── Arrastrar evento a otra fecha/hora ────────────────────────────────
        eventDrop: function (info) {
            if (! opts.canEdit) {
                info.revert();
                return;
            }

            const event   = info.event;
            const oldDate = info.oldEvent.start;
            const newDate = event.start;

            const oldStr = oldDate.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const newStr = newDate.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const oldHora = formatHora(oldDate);
            const newHora = formatHora(newDate);

            swal().fire({
                title:             '¿Reprogramar actividad?',
                html:              `<div class="text-left text-sm">
                                        <p><b>Anterior:</b> ${oldStr} ${oldHora ? '· ' + oldHora + ' hrs.' : ''}</p>
                                        <p class="mt-1"><b>Nueva:</b> ${newStr} ${newHora ? '· ' + newHora + ' hrs.' : ''}</p>
                                    </div>`,
                icon:              'question',
                showCancelButton:  true,
                confirmButtonText: 'Sí, reprogramar',
                cancelButtonText:  'Cancelar',
            }).then(result => {
                if (result.isConfirmed) {
                    _enviarReprogramacion(info, event, newDate, event.end);
                } else {
                    info.revert();
                }
            });
        },

        // ── Cambiar duración arrastrando el borde ─────────────────────────────
        eventResize: function (info) {
            if (! opts.canEdit) {
                info.revert();
                return;
            }

            const event  = info.event;
            const newEnd = event.end;
            const horaFin = formatHora(newEnd);

            swal().fire({
                title:             '¿Actualizar duración?',
                html:              `<p class="text-sm">Nueva hora de finalización: <b>${horaFin} hrs.</b></p>`,
                icon:              'question',
                showCancelButton:  true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText:  'Cancelar',
            }).then(result => {
                if (result.isConfirmed) {
                    _enviarReprogramacion(info, event, event.start, newEnd);
                } else {
                    info.revert();
                }
            });
        },

        // ── Indicador de carga ────────────────────────────────────────────────
        loading: function (isLoading) {
            const loader = document.getElementById('calendar-loader');
            if (loader) loader.style.display = isLoading ? 'flex' : 'none';
        },

        // ── Render de evento: añadir icono de estado ──────────────────────────
        eventContent: function (arg) {
            const estado = arg.event.extendedProps.estado || '';
            const icon   = _iconoPorEstado(estado);
            return {
                html: `<div class="fc-event-inner">
                           <span class="fc-event-icon">${icon}</span>
                           <span class="fc-event-title">${arg.event.title}</span>
                       </div>`,
            };
        },
    });

    calendar.render();

    // Exponer para acceso externo (Livewire, filtros)
    window.calendarioRM = calendar;

    return calendar;
};

// ── Helper: enviar reprogramación al backend ──────────────────────────────────

function _enviarReprogramacion(info, event, start, end) {
    const baseUrl = window._calendarioOpts?.reprogramarUrl || '/admin/actividades';
    const id      = event.id;
    const fecha   = start.toISOString().substring(0, 10);
    const hora    = formatHora(start);
    const horaFin = end ? formatHora(end) : null;

    const body = { fecha, hora };
    if (horaFin) body.hora_fin = horaFin;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(`${baseUrl}/${id}/reprogramar`, {
        method:  'PATCH',
        headers: {
            'Content-Type':  'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  csrfToken,
        },
        credentials: 'same-origin',
        body:        JSON.stringify(body),
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                swal().fire({
                    icon:  'success',
                    title: 'Actividad reprogramada correctamente.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                });
                window.calendarioRM?.refetchEvents();
            } else {
                info.revert();
                swal().fire('Error', data.message || 'No se pudo reprogramar.', 'error');
            }
        })
        .catch(err => {
            info.revert();
            swal().fire('Error', 'Error de conexión al intentar reprogramar.', 'error');
            console.error('reprogramar error:', err);
        });
}

// ── Helper: icono por estado ──────────────────────────────────────────────────

function _iconoPorEstado(estado) {
    const iconos = {
        BORRADOR:    '○',
        PROGRAMADA:  '◷',
        PENDIENTE:   '◷',
        EN_CURSO:    '▶',
        REPROGRAMADA:'↺',
        REALIZADA:   '✓',
        COMPLETADA:  '✓',
        FINALIZADA:  '✓',
        EVALUADA:    '★',
        CANCELADA:   '✗',
        ANULADA:     '✗',
    };
    return iconos[estado.toUpperCase()] || '•';
}
