/** Composición local: cambia posiciones e iconos, nunca relaciones ni registros. */
export const presentacionesTraza = {
    turno: { nombre: 'Ruta del cuidado', icono: 'ph-heartbeat', forma: 'paso', guia: 'Del hecho registrado a la actuación y su continuidad.' },
    clinico: { nombre: 'Revisión clínica', icono: 'ph-stethoscope', forma: 'ficha', guia: 'Contrasta la fuente del aviso con la revisión profesional disponible.' },
    psicologia: { nombre: 'Red de bienestar', icono: 'ph-brain', forma: 'red', guia: 'Explora observaciones, contexto y vínculos de la persona.' },
    nutricion: { nombre: 'Alimentación e hidratación', icono: 'ph-bowl-food', forma: 'balance', guia: 'Distingue alimentación, apoyo hídrico y continuidad nutricional.' },
    funcional: { nombre: 'Movilidad y autonomía', icono: 'ph-person-simple-walk', forma: 'capacidad', guia: 'Recorre capacidad funcional, autonomía y objetivos documentados.' },
    pedagogia: { nombre: 'Recorrido de participación', icono: 'ph-book-open', forma: 'actividad', guia: 'Conecta observación de la actividad, participación y continuidad.' },
    coordinacion: { nombre: 'Mesa de coordinación', icono: 'ph-clipboard-text', forma: 'paso', guia: 'Organiza el caso por responsabilidad, fuente y seguimiento.' },
    supervision: { nombre: 'Equipo y responsabilidades', icono: 'ph-users-three', forma: 'red', guia: 'Consulta quién está a cargo y qué actuaciones respaldan el caso.' },
    auditoria: { nombre: 'Origen e historial', icono: 'ph-fingerprint', forma: 'ficha', guia: 'Inspecciona procedencia, autoría y eventos conservados.' },
};

export function distribuirTraza(datos) {
    const foco = presentacionesTraza[datos.foco] ? datos.foco : 'auditoria';
    const posiciones = {};
    const nodos = datos.nodos || [];
    const grupo = nombre => nodos.filter(n => n.id !== 'alerta' && (n.grupo || 'caso') === nombre);
    const columna = (items, x, y = 140) => items.forEach((n, i) => { posiciones[n.id] = { x, y: y + i * 160 }; });
    const colocar = (id, x, y) => { if (nodos.some(n => n.id === id)) posiciones[id] = { x, y }; };
    const casos = grupo('caso'), origen = grupo('origen'), profesional = grupo('profesional'), eventos = grupo('eventos');
    colocar('alerta', 450, 100);
    switch (foco) {
    case 'turno':
        colocar('alerta', 140, 100); columna(casos, 140, 280); columna(origen, 450, 100); columna([...profesional, ...eventos], 760, 100); break;
    case 'clinico':
        columna(casos, 450, 300); columna(origen, 140, 300); columna([...profesional, ...eventos], 760, 300); break;
    case 'psicologia': {
        colocar('alerta', 610, 610);
        [...casos, ...profesional, ...origen, ...eventos].forEach((n, i) => {
            const anillo = i < 8 ? 0 : 1 + Math.floor((i - 8) / 12);
            const angulo = (i < 8 ? i / 8 : ((i - 8) % 12) / 12) * Math.PI * 2 - Math.PI / 2;
            const radio = 270 + anillo * 230;
            posiciones[n.id] = { x: 610 + Math.cos(angulo) * radio, y: 610 + Math.sin(angulo) * radio };
        }); break;
    }
    case 'nutricion': {
        columna(casos, 140, 260); columna(origen, 760, 260);
        const campos = profesional.filter(n => Number.isInteger(n.seccion));
        columna(profesional.filter(n => !Number.isInteger(n.seccion)), 450, 320);
        const base = Math.max(320 + profesional.filter(n => !Number.isInteger(n.seccion)).length * 160, 260 + Math.max(casos.length, origen.length) * 160);
        campos.forEach((n, i) => { posiciones[n.id] = { x: 140 + (i % 3) * 310, y: base + Math.floor(i / 3) * 160 }; });
        columna(eventos, 1070, 260); break;
    }
    case 'funcional':
        colocar('alerta', 140, 100); columna(casos, 140, 280); columna(origen, 450, 100);
        profesional.forEach((n, i) => { posiciones[n.id] = { x: 760 + (i % 2) * 310, y: 100 + Math.floor(i / 2) * 180 }; });
        columna(eventos, 1380, 100); break;
    case 'pedagogia':
        colocar('alerta', 450, 130); columna(casos, 120, 320); columna(origen, 1280, 320);
        profesional.forEach((n, i) => {
            const angulo = i / Math.max(1, profesional.length) * Math.PI * 2 - Math.PI / 2;
            posiciones[n.id] = { x: 640 + Math.cos(angulo) * 320, y: 650 + Math.sin(angulo) * 280 };
        }); columna(eventos, 1600, 320); break;
    case 'coordinacion':
        colocar('alerta', 140, 100); columna(casos, 140, 280); columna(origen, 450, 100);
        columna(profesional, 760, 100); columna(eventos, 1070, 100); break;
    case 'supervision':
        colocar('alerta', 520, 400); colocar('responsable', 520, 140);
        columna(casos.filter(n => n.id !== 'responsable'), 140, 300); columna(origen, 900, 300);
        columna(profesional, 520, 680); columna(eventos, 1220, 300); break;
    default: {
        colocar('alerta', 600, 100); columna(casos, 140, 300);
        const orden = ['plan-origen', 'intervencion-origen', 'origen-operativo'];
        columna([...origen].sort((a, b) => (orden.includes(a.id) ? orden.indexOf(a.id) : 9) - (orden.includes(b.id) ? orden.indexOf(b.id) : 9)), 600, 300);
        columna(profesional, 940, 300); columna(eventos, 1250, 300);
    }
    }
    // Todo nodo autorizado conserva una posición, incluso si aparece un grupo nuevo.
    nodos.filter(n => !posiciones[n.id]).forEach((n, i) => { posiciones[n.id] = { x: 1700, y: 100 + i * 160 }; });
    return posiciones;
}

export function iconoTraza(nodo, datos) {
    const perfil = presentacionesTraza[datos.foco] || presentacionesTraza.auditoria;
    const iconos = { alerta: 'ph-bell-ringing', residente: 'ph-user', responsable: 'ph-user-check', estado: 'ph-check-circle',
        'plan-origen': 'ph-clipboard-text', 'intervencion-origen': 'ph-list-checks', 'medida-origen': 'ph-hand-heart',
        'area-origen': 'ph-buildings', 'autor-profesional': 'ph-user-circle', 'atencion-profesional': 'ph-calendar-check', origen: 'ph-heartbeat' };
    if (nodo.id === 'origen-operativo') return { MEDICACION: 'ph-pill', PLAN: 'ph-list-checks', SOLICITUD_MEDICA: 'ph-stethoscope' }[datos.origenModulo] || 'ph-first-aid';
    if (nodo.grupo === 'eventos') return 'ph-clock-counter-clockwise';
    const campos = { nutricion: ['ph-bowl-food', 'ph-drop', 'ph-notebook'], funcional: ['ph-person-simple-walk', 'ph-hand-heart', 'ph-target'],
        psicologia: ['ph-smiley', 'ph-chats-circle', 'ph-notebook'], pedagogia: ['ph-book-open', 'ph-chart-line'] };
    if (Number.isInteger(nodo.seccion)) return campos[datos.foco]?.[nodo.seccion] || perfil.icono;
    return iconos[nodo.id] || perfil.icono;
}
