<table>
 <!-- Cabecera Principal -->
 <tr>
 <td colspan="4" style="font-size: 16px; font-weight: bold; text-align: center; color: #2F3E5C;">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</td>
 </tr>
 <tr>
 <td colspan="4" style="font-size: 12px; font-weight: bold; text-align: center; color: #967B66;">EXPEDIENTE DIGITAL DE USUARIO</td>
 </tr>
 <tr>
 <td colspan="4" style="font-size: 9px; text-align: center; color: #7C7168;">Fecha de Reporte: {{ $fecha }}</td>
 </tr>
 <tr><td></td></tr>

 <!-- Datos Personales -->
 <tr>
 <td colspan="4" style="font-weight: bold; background-color: #E6DDD3; color: #2F3E5C;">1. DATOS PERSONALES</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Estado de acceso:</td>
 <td style="color: #E27D60; font-weight: bold;">{{ $usuario->estado ?? 'SIN REGISTRAR' }}</td>
 <td style="font-weight: bold;">Nombres Completo:</td>
 <td>{{ $usuario->nombres }} {{ $usuario->ap_paterno }} {{ $usuario->ap_materno }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Documento de Identidad:</td>
 <td>{{ $usuario->tipo_documento ?? 'CI' }} {{ $usuario->numero_documento }} @if($usuario->expedido) ({{ $usuario->expedido }}) @endif</td>
 <td style="font-weight: bold;">Nacionalidad:</td>
 <td>{{ $usuario->pais_documento ?? 'Bolivia' }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Género:</td>
 <td>{{ $usuario->genero ?? 'No especificado' }}</td>
 <td style="font-weight: bold;">Fecha de Nacimiento:</td>
 <td>{{ $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('d/m/Y') : 'No registrado' }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Correo Electrónico:</td>
 <td style="text-transform: lowercase;">{{ $usuario->correo }}</td>
 <td style="font-weight: bold;">Celular / Teléfono:</td>
 <td>{{ $usuario->codigo_telefono }} {{ $usuario->telefono ?? 'Sin registrar' }}</td>
 </tr>
 <tr><td></td></tr>

 <!-- Datos Institucionales -->
 <tr>
 <td colspan="4" style="font-weight: bold; background-color: #E6DDD3; color: #2F3E5C;">2. PERFIL INSTITUCIONAL</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Rol del Sistema:</td>
 <td>{{ $nombre_rol }}</td>
 <td style="font-weight: bold;">Área Operativa:</td>
 <td>{{ $area }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Estado del Perfil:</td>
 <td style="font-weight: bold; color: #63775B;">{{ $usuario->estado }}</td>
 <td style="font-weight: bold;">Acceso al Sistema:</td>
 <td style="font-weight: bold;">{{ $usuario->acceso_sistema }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Fecha de Ingreso:</td>
 <td colspan="3">
 @if($usuario->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && $usuario->personalSalud?->fecha_ing)
 {{ $usuario->personalSalud->fecha_ing instanceof \Carbon\Carbon ? $usuario->personalSalud->fecha_ing->format('d/m/Y') : \Carbon\Carbon::parse($usuario->personalSalud->fecha_ing)->format('d/m/Y') }}
 @elseif($usuario->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']) && $usuario->personalAdmin?->fecha_ingreso)
 {{ $usuario->personalAdmin->fecha_ingreso instanceof \Carbon\Carbon ? $usuario->personalAdmin->fecha_ingreso->format('d/m/Y') : \Carbon\Carbon::parse($usuario->personalAdmin->fecha_ingreso)->format('d/m/Y') }}
 @else
 {{ $usuario->created_at->format('d/m/Y') }}
 @endif
 </td>
 </tr>
 <tr><td></td></tr>

 <!-- Asignación Horaria Planificada -->
 <tr>
 <td colspan="4" style="font-weight: bold; background-color: #E6DDD3; color: #2F3E5C;">3. HORARIOS Y JORNADA LABORAL</td>
 </tr>
 @if($horarios)
 <tr>
 <td style="font-weight: bold;">Turno:</td>
 <td>{{ $horarios->turno?->nombre }}</td>
 <td style="font-weight: bold;">Área / Sector:</td>
 <td>{{ $horarios->area?->nombre }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold;">Horario de Entrada/Salida:</td>
 <td>{{ substr($horarios->turno?->hora_inicio, 0, 5) }} - {{ substr($horarios->turno?->hora_fin, 0, 5) }}</td>
 <td style="font-weight: bold;">Das Planificados:</td>
 <td>{{ implode(', ', $horarios->dias_semana ?? []) }}</td>
 </tr>
 @else
 <tr>
 <td colspan="4" style="font-style: italic; text-align: center; color: #7C7168;">No cuenta con turnos o asignaciones horarias activas.</td>
 </tr>
 @endif
 <tr><td></td></tr>

 <!-- Avance Documental -->
 <tr>
 <td colspan="4" style="font-weight: bold; background-color: #E6DDD3; color: #2F3E5C;">4. CONTROL DOCUMENTAL (Avance: {{ $avance_documental['porcentaje_avance'] }}%)</td>
 </tr>
 <tr>
 <td style="font-weight: bold; background-color: #FAF7F3;">Documento / Credencial</td>
 <td style="font-weight: bold; background-color: #FAF7F3;">Tipo Requisito</td>
 <td style="font-weight: bold; background-color: #FAF7F3;">Estado</td>
 <td style="font-weight: bold; background-color: #FAF7F3;">Fecha Carga</td>
 </tr>
 @foreach($checklist as $doc)
 <tr>
 <td>{{ $doc['nombre'] }}</td>
 <td>{{ $doc['obligatorio'] ? 'OBLIGATORIO' : 'OPCIONAL' }}</td>
 <td style="font-weight: bold;">{{ $doc['estado'] }}</td>
 <td>{{ $doc['cargado'] && $doc['documento']->created_at ? $doc['documento']->created_at->format('d/m/Y H:i') : 'Pendiente' }}</td>
 </tr>
 @endforeach
 <tr><td></td></tr>

 <!-- Historial de Actividad de Auditoría -->
 <tr>
 <td colspan="4" style="font-weight: bold; background-color: #E6DDD3; color: #2F3E5C;">5. HISTORIAL DE CONTROL & AUDITORÍA</td>
 </tr>
 <tr>
 <td style="font-weight: bold; background-color: #FAF7F3;">Evento / Acción</td>
 <td colspan="2" style="font-weight: bold; background-color: #FAF7F3;">Descripción del Suceso</td>
 <td style="font-weight: bold; background-color: #FAF7F3;">Fecha y Hora</td>
 </tr>
 @forelse($actividades as $act)
 <tr>
 <td style="text-transform: uppercase;">{{ $act->event }}</td>
 <td colspan="2">{{ $act->description }}</td>
 <td>{{ $act->created_at->format('d/m/Y H:i:s') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" style="font-style: italic; text-align: center; color: #7C7168;">Sin actividades registradas.</td>
 </tr>
 @endforelse
</table>

