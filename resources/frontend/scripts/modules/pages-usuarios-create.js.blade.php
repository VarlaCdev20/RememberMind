
 function userRegistration() {
 return {
 step: 1,
 isSubmitting: false,
 nombres: @js(old('nombres', '')),
 apPaterno: @js(old('ap_paterno', '')),
 apMaterno: @js(old('ap_materno', '')),
 genero: @js(old('genero', '')),
 fechaNac: @js(old('fecha_nacimiento', '')),
 paisDoc: @js(old('pais_documento', 'Bolivia')),
 tipoDoc: @js(old('tipo_documento', 'CI')),
 documento: @js(old('numero_documento', '')),
 expedido: @js(old('expedido', '')),
 correo: @js(old('correo', '')),
 paisTel: @js(old('pais_telefono', 'Bolivia')),
 telefono: @js(old('telefono', '')),
 rol: @js(old('rol', '')),
 fechaIng: @js(old('fecha_ingreso', now()->format('Y-m-d'))),
 especialidad: @js(old('especialidad_salud', '')),
 cargo: @js(old('cargo_administrativo', '')),
 estado: @js(old('estado', 'ACTIVO')),
 acceso: @js(old('acceso_sistema', 'HABILITADO')),
 observaciones: @js(old('observaciones', '')),
 fotoPreview: null,
 errors: {
 nombres: '', apellidos: '', genero: '',
 pais_documento: '', tipo_documento: '', numero_documento: '', expedido: '',
 correo: '', telefono: '',
 rol: '', especialidad: '', cargo_administrativo: '',
 estado: '', acceso_sistema: '', foto: ''
 },
 completionPercentage: 0,

 paisesDoc: {
 'Bolivia': ['CI'],
 'Brasil': ['CPF', 'RG', 'PASAPORTE'],
 'Argentina': ['DNI', 'PASAPORTE'],
 'Perú': ['DNI', 'PASAPORTE'],
 'Chile': ['RUN/RUT', 'PASAPORTE'],
 'Colombia': ['CÉDULA', 'PASAPORTE'],
 'México': ['CURP', 'PASAPORTE'],
 'Otro': ['DOCUMENTO NACIONAL', 'PASAPORTE', 'OTRO']
 },
 codigosTel: {
 'Bolivia': '+591', 'Brasil': '+55', 'Argentina': '+54',
 'Perú': '+51', 'Chile': '+56', 'Colombia': '+57',
 'México': '+52', 'Otro': ''
 },

 init() {
 this.calculateCompletion();
 this.$watch('cargo', () => this.calculateCompletion());

 // Watchers de Pais para evitar reseteos involuntarios y actualizar dependencias
 this.$watch('paisDoc', (val) => {
 // Solo resetear tipoDoc si el actual no es válido para el nuevo país
 if (!this.paisesDoc[val].includes(this.tipoDoc)) {
 this.tipoDoc = this.paisesDoc[val][0];
 }
 });

 this.$watch('paisTel', (val) => {
 this.codigoTel = this.codigosTel[val] || '';
 });

 // Cargar errores de backend si existen
 @if($errors->any())
 @foreach($errors->keys() as $key)
 @php $targetKey = $key ==="ap_paterno" || $key ==="ap_materno" ?"apellidos" : $key; @endphp
 this.errors['{{ $targetKey }}'] = '{{ $errors->first($key) }}';
 @endforeach
 
 // Ir al primer paso con error
 if (this.errors.nombres || this.errors.apellidos || this.errors.genero || this.errors.fecha_nacimiento || this.errors.foto) this.step = 1;
 else if (this.errors.pais_documento || this.errors.tipo_documento || this.errors.numero_documento || this.errors.expedido) this.step = 2;
 else if (this.errors.correo || this.errors.telefono) this.step = 3;
 else if (this.errors.rol || this.errors.especialidad || this.errors.cargo_administrativo) this.step = 4;
 else if (this.errors.estado || this.errors.acceso_sistema) this.step = 5;
 @endif
 },

 calculateCompletion() {
 let fields = [
 { val: this.nombres, weight: 1 },
 { val: (this.apPaterno || this.apMaterno), weight: 1 },
 { val: this.genero, weight: 1 },
 { val: this.fechaNac, weight: 1 },
 { val: this.documento, weight: 1 },
 { val: (this.correo && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.correo)), weight: 1 },
 { val: (this.telefono && this.telefono.length >= 7), weight: 1 },
 { val: this.rol, weight: 1 }
 ];

 if (['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'].includes(this.rol)) fields.push({ val: this.especialidad, weight: 1 });
 else if (['SUPERADMINISTRADOR', 'ADMINISTRADOR'].includes(this.rol)) fields.push({ val: this.cargo, weight: 1 });

 let completed = fields.filter(f => f.val).length;
 this.completionPercentage = Math.round((completed / fields.length) * 100);
 },

 initials() {
 if (!this.nombres) return 'RM';
 let parts = this.nombres.trim().split(/\s+/);
 return parts.map(p => p.charAt(0).toUpperCase()).join('').substring(0, 2);
 },

 fullName() {
 return `${this.nombres} ${this.apPaterno} ${this.apMaterno}`.trim().toUpperCase();
 },

 rolDisplay() {
 if (['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'].includes(this.rol)) return 'PERSONAL DE SALUD';
 if (['SUPERADMINISTRADOR', 'ADMINISTRADOR'].includes(this.rol)) return 'PERSONAL ADMINISTRATIVO';
 if (this.rol === 'FAMILIAR') return 'FAMILIAR / RESPONSABLE';
 if (this.rol === 'VOLUNTARIO') return 'VOLUNTARIO';
 return this.rol ? this.rol.replace('_', ' ').toUpperCase() : 'ROL NO ASIGNADO';
 },

 calculateAgeText() {
 if (!this.fechaNac) return 'Pendiente de fecha';
 const nac = new Date(this.fechaNac);
 const hoy = new Date();
 if (nac > hoy) return 'Fecha inválida (Futura)';
 
 let edad = hoy.getFullYear() - nac.getFullYear();
 const m = hoy.getMonth() - nac.getMonth();
 if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;
 
 if (edad < 0) return 'Fecha inválida';
 return edad + ' años' + (edad < 18 ? ' (Menor de edad)' : '');
 },

 documentoPlaceholder() {
 const placeholders = {
 'Bolivia': 'Ej. 10013724',
 'Brasil': 'Ej. 12345678901 (11 dígitos)',
 'Argentina': 'Ej. 12345678',
 'Perú': 'Ej. 12345678',
 'Chile': 'Ej. 12345678-K',
 'Colombia': 'Ej. 1234567890',
 'México': 'Ej. ABC123456XYZ',
 'Otro': 'Ingrese documento'
 };
 return placeholders[this.paisDoc] || 'Ingrese documento';
 },

 telefonoEjemplo() {
 const ejemplos = {
 'Bolivia': '8 dígitos (76543210)',
 'Brasil': '10-11 dígitos',
 'Argentina': '10-11 dígitos',
 'Perú': '9 dígitos',
 'Chile': '9 dígitos',
 'Colombia': '10 dígitos',
 'México': '10 dígitos',
 'Otro': '6-15 dígitos'
 };
 return ejemplos[this.paisTel] || 'Formato local';
 },

 passwordPreview() {
 if (!this.nombres || (!this.apPaterno && !this.apMaterno) || !this.documento) return '---';
 let partes = this.nombres.trim().split(/\s+/);
 if (this.apPaterno) partes.push(this.apPaterno.trim());
 if (this.apMaterno) partes.push(this.apMaterno.trim());
 let iniciales = partes.filter(p => p.length > 0).map(p => p.charAt(0).toUpperCase()).join('');
 let docClean = this.documento.toUpperCase().replace(/[^A-Z0-9]/g, '');
 return iniciales + docClean;
 },

 handleFotoChange(event) {
 const file = event.target.files[0];
 this.errors.foto = '';
 if (!file) return;

 if (!file.type.match('image.*')) {
 this.errors.foto = 'La foto debe ser JPG, PNG o WEBP.';
 event.target.value = '';
 this.fotoPreview = null;
 return;
 }

 if (file.size > 2 * 1024 * 1024) {
 this.errors.foto = 'La foto no debe superar los 2 MB.';
 event.target.value = '';
 this.fotoPreview = null;
 return;
 }

 this.fotoPreview = URL.createObjectURL(file);
 },

 clearError(field) {
 this.errors[field] = '';
 },

 nextStep() {
 if (this.validateStep()) {
 this.step++;
 window.scrollTo({ top: 0, behavior: 'smooth' });
 } else {
 Swal.fire({
 title: 'Campos Incompletos',
 text: 'Por favor, revise los errores marcados en rojo antes de continuar.',
 icon: 'warning',
 confirmButtonColor: '#2F3E5C'
 });
 }
 },

 prevStep() {
 this.step--;
 window.scrollTo({ top: 0, behavior: 'smooth' });
 },

 validateStep() {
 let stepErrors = {};
 
 if (this.step === 1) {
 if (!this.nombres.trim()) stepErrors.nombres = 'Debe ingresar los nombres.';
 else if (this.nombres.trim().length < 2) stepErrors.nombres = 'El nombre debe tener al menos 2 caracteres.';
 
 if (!this.apPaterno.trim() && !this.apMaterno.trim()) {
 stepErrors.apellidos = 'Debe ingresar al menos un apellido: paterno o materno.';
 }
 
 if (!this.genero) stepErrors.genero = 'Seleccione el sexo.';

 if (!this.fechaNac) {
 stepErrors.fecha_nacimiento = 'La fecha de nacimiento es obligatoria.';
 } else {
 const nac = new Date(this.fechaNac);
 const hoy = new Date();
 let edad = hoy.getFullYear() - nac.getFullYear();
 const m = hoy.getMonth() - nac.getMonth();
 if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;

 if (edad < 18) stepErrors.fecha_nacimiento = 'El usuario debe tener al menos 18 años.';
 if (edad > 100) stepErrors.fecha_nacimiento = 'La fecha de nacimiento no puede superar los 100 años.';
 if (nac > hoy) stepErrors.fecha_nacimiento = 'La fecha de nacimiento no puede ser futura.';
 }
 }
 
 if (this.step === 2) {
 if (!this.documento.trim()) stepErrors.numero_documento = 'Debe ingresar el número de documento.';
 
 if (this.paisDoc === 'Brasil' && this.tipoDoc === 'CPF') {
 let cpf = this.documento.replace(/\D/g, '');
 if (cpf.length !== 11) stepErrors.numero_documento = 'El CPF de Brasil debe tener exactamente 11 dígitos.';
 }

 if (this.paisDoc === 'Argentina' && this.tipoDoc === 'DNI') {
 let dni = this.documento.replace(/\D/g, '');
 if (dni.length < 7 || dni.length > 9) stepErrors.numero_documento = 'El DNI de Argentina debe tener entre 7 y 9 dígitos.';
 }

 if (this.paisDoc === 'Perú' && this.tipoDoc === 'DNI') {
 let dni = this.documento.replace(/\D/g, '');
 if (dni.length !== 8) stepErrors.numero_documento = 'El DNI de Perú debe tener exactamente 8 dígitos.';
 }

 if (this.paisDoc === 'Colombia' && this.tipoDoc === 'CÉDULA') {
 let cedula = this.documento.replace(/\D/g, '');
 if (cedula.length < 6 || cedula.length > 10) stepErrors.numero_documento = 'La Cédula de Colombia debe tener entre 6 y 10 dígitos.';
 }

 if (this.paisDoc === 'México' && this.tipoDoc === 'CURP') {
 if (this.documento.length !== 18) stepErrors.numero_documento = 'El CURP de México debe tener exactamente 18 caracteres.';
 }

 if (this.paisDoc === 'Otro') {
 if (this.documento.length < 5 || this.documento.length > 30) stepErrors.numero_documento = 'El documento debe tener entre 5 y 30 caracteres.';
 }
 }
 
 if (this.step === 3) {
 const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
 if (!this.correo.trim()) stepErrors.correo = 'Debe ingresar un correo válido.';
 else if (!emailRegex.test(this.correo)) stepErrors.correo = 'El formato de correo no es válido.';
 
 if (!this.telefono.trim()) {
 stepErrors.telefono = 'Debe ingresar el número de celular.';
 } else {
 let telLimpio = this.telefono.replace(/\D/g, '');
 if (this.paisTel === 'Bolivia' && telLimpio.length !== 8) {
 stepErrors.telefono = 'En Bolivia el celular debe tener 8 dígitos.';
 } else if (this.paisTel === 'Brasil' && (telLimpio.length < 10 || telLimpio.length > 11)) {
 stepErrors.telefono = 'En Brasil el celular debe tener 10 u 11 dígitos.';
 } else if (this.paisTel === 'Argentina' && (telLimpio.length < 10 || telLimpio.length > 11)) {
 stepErrors.telefono = 'En Argentina el celular debe tener 10 u 11 dígitos.';
 } else if (this.paisTel === 'Perú' && telLimpio.length !== 9) {
 stepErrors.telefono = 'En Perú el celular debe tener 9 dígitos.';
 } else if (this.paisTel === 'Chile' && telLimpio.length !== 9) {
 stepErrors.telefono = 'En Chile el celular debe tener 9 dígitos.';
 } else if (this.paisTel === 'Colombia' && telLimpio.length !== 10) {
 stepErrors.telefono = 'En Colombia el celular debe tener 10 dígitos.';
 } else if (this.paisTel === 'México' && telLimpio.length !== 10) {
 stepErrors.telefono = 'En México el celular debe tener 10 dígitos.';
 } else if (telLimpio.length < 6) {
 stepErrors.telefono = 'El celular debe tener al menos 6 dígitos.';
 }
 }
 }
 
 if (this.step === 4) {
 if (!this.rol) stepErrors.rol = 'Debe asignar un rol al usuario.';
 if (['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'].includes(this.rol) && !this.especialidad) {
 stepErrors.especialidad = 'Debe seleccionar una especialidad para el personal de salud.';
 }
 if (['SUPERADMINISTRADOR', 'ADMINISTRADOR'].includes(this.rol) && !this.cargo) {
 stepErrors.cargo_administrativo = 'Debe seleccionar un cargo administrativo.';
 }
 }

 this.errors = { ...this.errors, ...stepErrors };
 return Object.keys(stepErrors).length === 0;
 },

 confirmSubmit() {
 Swal.fire({
 title: '¿Confirmar Registro?',
 text:"Se registrará al usuario con las credenciales autogeneradas.",
 icon: 'question',
 showCancelButton: true,
 confirmButtonColor: '#2F3E5C',
 cancelButtonColor: '#E27D60',
 confirmButtonText: 'Sí, Registrar',
 cancelButtonText: 'Revisar'
 }).then((result) => {
 if (result.isConfirmed) {
 this.isSubmitting = true;
 document.getElementById('registrationForm').submit();
 }
 });
 }
 }
 }
 