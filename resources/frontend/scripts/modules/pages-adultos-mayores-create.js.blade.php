
 function adultoMayorFormCreate() {
 return {
 paso: 1,
 total: 6,
 errors: {},
 touched: {},
 edad: null,
 fotoPreview: null,
 tiene_celular: @js(old('tiene_celular', true)),
 fecha_nac: @js(old('fecha_nac', '')),
 alergias: @js(old('alergias', '')),
 observaciones: @js(old('observaciones', '')),
 direccion_emergencia: @js(old('contacto_emergencia_direccion', '')),

 init() {
 @foreach($errors->keys() as $key)
 this.errors['{{ $key }}'] = @js($errors->first($key));
 @endforeach
 this.$watch('fecha_nac', () => this.calcularEdad());
 if (this.fecha_nac) this.calcularEdad();
 },

 calcularEdad() {
 if (!this.fecha_nac) { this.edad = null; return; }
 const hoy = new Date(); const nac = new Date(this.fecha_nac);
 let e = hoy.getFullYear() - nac.getFullYear();
 const m = hoy.getMonth() - nac.getMonth();
 if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) e--;
 this.edad = e >= 0 ? e : 0;
 },

 previewImage(event) {
 const file = event.target.files[0];
 if (!file) { this.fotoPreview = null; return; }
 if (file.size > 2 * 1024 * 1024) alert('La imagen supera los 2MB.');
 this.fotoPreview = URL.createObjectURL(file);
 },

 setError(f, m) { this.errors = { ...this.errors, [f]: m }; },
 clearError(f) { if (this.errors[f]) { const e = {...this.errors}; delete e[f]; this.errors = e; } },
 hasError(f) { return Boolean(this.errors[f]); },
 touch(f) { this.touched = { ...this.touched, [f]: true }; },

 fieldClass(f) {
 if (this.hasError(f)) return 'border-borde-focus bg-estado-peligroBg ring-4 ring-[#E27D60]/10';
 if (this.touched[f]) return 'border-borde bg-fondo-panel';
 return 'border-borde-suave bg-fondo-panel';
 },

 isEmpty(v) { return v === null || v === undefined || String(v).trim() === ''; },
 onlyLetters(v) { return /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'\-]+$/.test(String(v).trim()); },
 isCI(v) { return /^[0-9]{5,9}$/.test(String(v).trim()); },
 isPhone(v) { return /^[67][0-9]{7}$/.test(String(v).trim()); },
 isFixedPhone(v) { if (this.isEmpty(v)) return true; return /^[2-4][0-9]{6,7}$/.test(String(v).trim()); },
 isBeforeToday(v) { if (this.isEmpty(v)) return false; const d=new Date(v),t=new Date(); d.setHours(0,0,0,0); t.setHours(0,0,0,0); return d < t; },
 isMaxToday(v) { if (this.isEmpty(v)) return false; const d=new Date(v),t=new Date(); d.setHours(0,0,0,0); t.setHours(0,0,0,0); return d <= t; },

 getValue(f) {
 const el = document.querySelector('[name="' + f + '"]');
 if (!el) return '';
 return el.type === 'checkbox' ? (el.checked ? '1' : '') : el.value;
 },

 focusFirstError() {
 const f = Object.keys(this.errors)[0];
 if (!f) return;
 const el = document.querySelector('[name="' + f + '"]');
 if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(() => el.focus(), 250); }
 },

 validarPasoIdentidad() {
 const n = this.getValue('nombres'), ap = this.getValue('ap_paterno'),
 am = this.getValue('ap_materno'), ci = this.getValue('ci'),
 exp = this.getValue('expedicion_ci'), ec = this.getValue('estado_civil'),
 comp = this.getValue('complemento_ci');
 if (this.isEmpty(n)) this.setError('nombres', 'Ingrese los nombres del adulto mayor.');
 else if (n.trim().length < 2) this.setError('nombres', 'El nombre debe tener al menos 2 caracteres.');
 else if (!this.onlyLetters(n)) this.setError('nombres', 'Los nombres solo deben contener letras.');
 if (this.isEmpty(ap)) this.setError('ap_paterno', 'Ingrese el apellido paterno.');
 else if (ap.trim().length < 2) this.setError('ap_paterno', 'El apellido debe tener al menos 2 caracteres.');
 else if (!this.onlyLetters(ap)) this.setError('ap_paterno', 'El apellido solo debe contener letras.');
 if (!this.isEmpty(am) && (am.trim().length < 2 || !this.onlyLetters(am)))
 this.setError('ap_materno', 'El apellido materno debe tener al menos 2 letras.');
 if (this.isEmpty(ci)) this.setError('ci', 'Ingrese el número de CI.');
 else if (!this.isCI(ci)) this.setError('ci', 'El CI debe tener entre 5 y 9 dígitos numéricos.');
 if (!this.isEmpty(comp) && comp.trim().length > 2)
 this.setError('complemento_ci', 'El complemento tiene máximo 2 caracteres.');
 if (this.isEmpty(exp)) this.setError('expedicion_ci', 'Seleccione la expedición del CI.');
 if (this.isEmpty(ec)) this.setError('estado_civil', 'Seleccione el estado civil.');
 },

 validarPasoMedico() {
 const fn = this.getValue('fecha_nac') || this.fecha_nac,
 g = this.getValue('genero'), gs = this.getValue('grupo_sanguineo'),
 ss = this.getValue('seguro_salud'), ne = this.getValue('nivel_educat');
 if (this.isEmpty(fn)) this.setError('fecha_nac', 'Ingrese la fecha de nacimiento.');
 else if (!this.isBeforeToday(fn)) this.setError('fecha_nac', 'La fecha de nacimiento debe ser anterior a hoy.');
 if (this.isEmpty(g)) this.setError('genero', 'Seleccione el género.');
 if (this.isEmpty(gs)) this.setError('grupo_sanguineo', 'Seleccione el grupo sanguíneo.');
 if (this.isEmpty(this.alergias)) this.setError('alergias', 'Especifique alergias o escriba"Ninguna".');
 if (this.isEmpty(ss)) this.setError('seguro_salud', 'Seleccione el seguro de salud.');
 if (this.isEmpty(ne)) this.setError('nivel_educat', 'Seleccione el nivel educativo.');
 },

 validarPasoUbicacion() {
 const cel = this.getValue('celular'), fijo = this.getValue('telefono_fijo'),
 dep = this.getValue('departamento_residencia'),
 ciu = this.getValue('ciudad_municipio'),
 zon = this.getValue('zona'), cal = this.getValue('calle');
 
 if (this.tiene_celular) {
 if (this.isEmpty(cel)) this.setError('celular', 'Ingrese un celular válido de 8 dígitos que empiece con 6 o 7.');
 else if (!this.isPhone(cel)) this.setError('celular', 'Ingrese un celular válido de 8 dígitos que empiece con 6 o 7.');
 }
 
 if (!this.isFixedPhone(fijo)) this.setError('telefono_fijo', 'Ingrese un teléfono fijo válido (ej. 2223344).');
 if (this.isEmpty(dep)) this.setError('departamento_residencia', 'Seleccione el departamento de residencia.');
 if (this.isEmpty(ciu)) this.setError('ciudad_municipio', 'Ingrese la ciudad o municipio.');
 if (this.isEmpty(zon)) this.setError('zona', 'Ingrese la zona o barrio.');
 if (this.isEmpty(cal)) this.setError('calle', 'Ingrese la calle, avenida o referencia.');
 },

 validarPasoContacto() {
 const nom = this.getValue('contacto_emergencia_nombre'),
 par = this.getValue('contacto_emergencia_parentesco'),
 celC = this.getValue('contacto_emergencia_celular'),
 celA = this.getValue('celular');
 if (this.isEmpty(nom)) this.setError('contacto_emergencia_nombre', 'Ingrese el nombre del responsable o contacto de emergencia.');
 else if (nom.trim().length < 3) this.setError('contacto_emergencia_nombre', 'El nombre debe tener al menos 3 caracteres.');
 else if (!this.onlyLetters(nom)) this.setError('contacto_emergencia_nombre', 'Solo se permiten letras en el nombre del contacto.');
 if (this.isEmpty(par)) this.setError('contacto_emergencia_parentesco', 'Seleccione el parentesco.');
 if (this.isEmpty(celC)) this.setError('contacto_emergencia_celular', 'Ingrese un celular válido del responsable.');
 else if (!this.isPhone(celC)) this.setError('contacto_emergencia_celular', 'Ingrese un celular válido del responsable.');
 else if (!this.isEmpty(celA) && celC === celA)
 this.setError('contacto_emergencia_celular', 'El celular del responsable no puede ser igual al del adulto mayor.');
 },

 validarPasoIngreso() {
 const fi = this.getValue('fecha_ing'), ti = this.getValue('tipo_ing'),
 pe = this.getValue('permanencia'), es = this.getValue('cod_est_adul');
 if (this.isEmpty(fi)) this.setError('fecha_ing', 'Ingrese la fecha de ingreso institucional.');
 else if (!this.isMaxToday(fi)) this.setError('fecha_ing', 'La fecha de ingreso no puede ser posterior a hoy.');
 if (this.isEmpty(ti)) this.setError('tipo_ing', 'Seleccione el tipo de ingreso.');
 if (this.isEmpty(pe)) this.setError('permanencia', 'Seleccione la permanencia.');
 if (this.isEmpty(es)) this.setError('cod_est_adul', 'Seleccione el estado institucional.');
 },

 validarPasoCierre() {
 if (this.getValue('consentimiento_datos') !== '1')
 this.setError('consentimiento_datos', 'Debe aceptar el consentimiento para registrar y tratar los datos.');
 },

 validarPaso() {
 this.errors = {};
 if (this.paso === 1) this.validarPasoIdentidad();
 if (this.paso === 2) this.validarPasoMedico();
 if (this.paso === 3) this.validarPasoUbicacion();
 if (this.paso === 4) this.validarPasoContacto();
 if (this.paso === 5) this.validarPasoIngreso();
 if (this.paso === 6) this.validarPasoCierre();
 if (Object.keys(this.errors).length > 0) { this.focusFirstError(); return false; }
 return true;
 },

 siguiente() {
 if (!this.validarPaso()) return;
 if (this.paso < this.total) { this.paso++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
 },

 anterior() {
 if (this.paso > 1) { this.errors = {}; this.paso--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
 }
 };
 }
 