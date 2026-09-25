# Seguridad y privacidad

## Hallazgos y controles

| Área | Evidencia AS-IS | Acción propuesta / validación |
|---|---|---|
| Alta/roles HTTP | usuarios resource protegido por usuarios.ver; StoreUsuarioRequest authorize=true; UsuarioController asigna rol recibido | P0: permiso create/update y grantRole independiente; impedir concesión superior/autoelevación |
| Familiar | Seeder concede adultos.ver; show carga colecciones sin scope de vínculo | P0: Policy por residente + filtrado de campos y exportes; prueba directa de URL y payload |
| Livewire | UsuariosPanel y SaludSignosPanel sí hacen can; DecisionAdmisionModal::guardar no autoriza explícitamente | Auditar cada método mutante, no solo mount/botón; estado e ID releídos bajo autorización |
| Login | Fortify verifica password/estado y limita 5 intentos/minuto | Conservar; comprobar acceso_sistema y revocación de sesiones de cuentas desactivadas |
| Verificación email | Features::emailVerification comentado; User no implementa MustVerifyEmail | Middleware verified no demuestra verificación activa; decidir política explícita |
| 2FA | Feature confirm y confirmPassword habilitada; User usa trait | Disponibilidad no equivale a obligatoriedad; exigir a cuentas privilegiadas en piloto si se aprueba |
| Serialización | User hidden solo password/remember_token | No serializar User completo; proteger two_factor_secret/recovery_codes por hidden y Resource |
| Credenciales | Iniciales+documento en UsuarioController; correo de contraseñas en UsuariosPanel | P0: invitación/reset de un uso, TTL, sin contraseña en correo/flash |
| Archivos | PreadmisionWizard usa public para documentos y PDFs | P0: privado, autorización por propietario/versión, descarga controlada |
| Errores | Varios catch muestran getMessage al usuario | Respuesta genérica con correlación; detalles minimizados en logs privados |
| Logs | logFillable en AdultoMayor/EvaluacionGeriatrica | Filtrar salud/identidad sensible; controles de consulta, retención y exportación |
| Superadmin | Gate::before devuelve true para todo | Separar operación técnica de datos clínicos; acceso excepcional temporal si hace falta |
| Sesión | auth:sanctum + auth_session en rutas | Revisar cookies Secure/HttpOnly/SameSite/TLS y expiración en despliegue, sin afirmar configuración productiva |

## Amenaza concreta del alta de usuarios

El recurso exige lectura; store usa FormRequest cuya authorize devuelve true; el rol se valida como existente y el controlador llama assignRole($request->rol). No hay una comprobación de quién puede conceder ese rol en esa ruta. No se explotó creando cuentas reales. La corrección debe proteger la ruta HTTP aunque el panel Livewire ya compruebe usuarios.crear: arreglar solo el botón/panel deja la otra entrada.

## Protección documental

Permitir solo MIME/extensiones necesarios y tamaño máximo, nombres de almacenamiento aleatorios, conservar nombre original como metadato escapado, no aceptar rutas del cliente. Verificar firma de formato cuando corresponda; cuarentena/análisis si se habilitan formatos de mayor riesgo. Evitar SVG/HTML ejecutable salvo sanitización justificada. Descarga con Content-Disposition y no-sniff; no enlazar storage público para salud, consentimiento o identificación. La exposición externa actual depende del symlink/servidor, no se comprobó descargando documentos reales.

Autorizar también generación/envío/exportación y limitar destino a direcciones aprobadas. El usuario de esta auditoría no autorizó envíos reales; no se enviaron correos. Logs de acceso a documento registran quién/cuándo/objeto, sin copiar contenido.

## Autorización y datos

Mass assignment: fillable no sustituye una lista de campos por caso de uso. $request->validated no autoriza campos administrativos si las rules los permiten a cualquier actor. Ignorar author_id, estado final y resident_id arbitrarios; derivarlos del contexto autorizado. FK compuestas o derivación por orden para evitar pertenencias inconsistentes.

FormRequest valida formato y Policy; Action valida invariantes para llamadas desde Jobs/CLI/Livewire. Job de exportación recibe solicitante y revalida acceso al ejecutar/descargar, porque los permisos pueden haberse revocado. Caché incluye identidad/alcance y se invalida al cambiar acceso; no cachear paquetes clínicos globales.

## Datos de simulación y piloto

Separar simulación/piloto y respaldos, cifrar transporte y almacenamiento de respaldo, practicar restauración. La retención, consentimiento y facultades clínicas requieren validación institucional; esta auditoría no determina cumplimiento jurídico. No reutilizar contraseñas o datos reales en fixtures. Auditoría de acceso y correcciones es distinta de historia clínica. El orden de prioridad solicitado no permite posponer controles de seguridad críticos detrás de mejoras estéticas.
