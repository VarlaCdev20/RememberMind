export function crearMapaDireccion(wire, tilesUrl) {
    let mapa, marcador, leaflet, destruido = false, secuencia = 0;
    return {
        abierto: false, cargando: false, consultando: false, mensaje: '', contexto: '', punto: null, propuesta: '', referencia: '',
        contextoActual: null,
        zonaActual: null,
        zonaInicializada: false,
        cambiarZona(valor) {
            const anterior = this.zonaActual;
            this.zonaActual = valor;
            if (!this.zonaInicializada) { this.zonaInicializada = true; return; }
            if (anterior === valor) return;
            secuencia++; this.punto = null; this.propuesta = ''; this.referencia = '';
            this.cargando = this.consultando = false;
            if (this.abierto && valor && valor !== 'OTRA') return this.abrir();
        },
        cambiarContexto(valor) {
            if (this.contextoActual !== null && this.contextoActual !== valor) {
                secuencia++; this.abierto = false; this.punto = null; this.propuesta = ''; this.mensaje = '';
                this.cargando = this.consultando = false;
            }
            this.contextoActual = valor;
        },
        async abrir() {
            if (this.cargando) return;
            const solicitud = ++secuencia;
            this.cargando = true; this.mensaje = ''; this.propuesta = ''; this.punto = null;
            try {
                const centro = await wire.centrarMapaDireccion();
                if (destruido || solicitud !== secuencia || !centro) return;
                leaflet ??= await import('leaflet');
                if (destruido || solicitud !== secuencia) return;
                this.abierto = true;
                await this.$nextTick();
                if (destruido || solicitud !== secuencia) return;
                if (!mapa) {
                    mapa = leaflet.map(this.$refs.mapa, { scrollWheelZoom: false, zoomControl: true });
                    leaflet.tileLayer(tilesUrl, { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors' })
                        .on('tileerror', () => { this.mensaje = 'No se pudieron cargar algunas partes del mapa. Reintenta o utiliza los campos de dirección.'; }).addTo(mapa);
                    mapa.on('click', e => this.marcar(e.latlng));
                }
                if (marcador) { marcador.remove(); marcador = null; }
                mapa.invalidateSize(); mapa.setView([centro.lat, centro.lon], centro.zoom, { animate: false });
                this.contexto = centro.contexto;
                this.$refs.mapa.focus({ preventScroll: true });
            } catch {
                if (!destruido && solicitud === secuencia) this.mensaje = 'No se pudo abrir el mapa. Revisa los campos y vuelve a intentarlo.';
            } finally {
                if (solicitud === secuencia) this.cargando = false;
            }
        },
        marcar(latlng) {
            if (!mapa || this.consultando) return;
            this.punto = { lat: latlng.lat, lng: latlng.lng }; this.propuesta = ''; this.referencia = ''; this.mensaje = '';
            if (marcador) marcador.setLatLng(latlng);
            else marcador = leaflet.marker(latlng, { keyboard: false, icon: leaflet.divIcon({ className: 'rm-address-map__pin', html: '<span aria-hidden="true">📍</span>', iconSize: [36, 44], iconAnchor: [18, 40] }) }).addTo(mapa);
        },
        marcarCentro() { if (mapa) this.marcar(mapa.getCenter()); },
        async consultar() {
            if (!this.punto || this.consultando) return;
            const solicitud = ++secuencia;
            this.consultando = true; this.mensaje = ''; this.propuesta = '';
            try {
                const resultado = await wire.consultarDireccionMapa(this.punto.lat, this.punto.lng);
                if (destruido || solicitud !== secuencia || !resultado) return;
                this.propuesta = resultado.direccion;
                this.referencia = [resultado.zona, resultado.municipio, resultado.departamento].filter(Boolean).join(', ');
                await this.$nextTick(); this.$refs.direccion.focus({ preventScroll: true });
            } catch {
                if (!destruido && solicitud === secuencia) this.mensaje = 'No se pudo obtener la dirección. Reintenta o usa los campos manuales.';
            } finally {
                if (solicitud === secuencia) this.consultando = false;
            }
        },
        async usar() {
            if (!this.propuesta.trim() || this.consultando) return;
            this.consultando = true; this.mensaje = '';
            try { await wire.usarDireccionMapa(this.propuesta.toLocaleUpperCase('es')); }
            catch { this.mensaje = 'No se pudo aplicar la dirección. Revisa el texto y vuelve a intentarlo.'; }
            finally { this.consultando = false; }
        },
        cerrar() { secuencia++; this.abierto = false; this.cargando = this.consultando = false; this.$refs.abrir.focus(); },
        destroy() { destruido = true; secuencia++; mapa?.remove(); mapa = marcador = null; },
    };
}

if (typeof window !== 'undefined') window.rmPersonalAddressMap = crearMapaDireccion;
