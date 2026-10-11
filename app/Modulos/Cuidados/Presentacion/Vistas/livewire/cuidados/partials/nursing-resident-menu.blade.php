@php
    $code = $paciente->cod_residente;
    $user = auth()->user();
    $canRegisterControl = !$modoConsulta && $user?->can('signos_vitales.crear');
    $canSeeCare = $user?->can('enfermeria.ver_ficha_paciente') && $user?->can('planes_cuidado.ver');
    $canSeeMedication = $user?->can('enfermeria.ver_ficha_paciente') && ($user?->can('prescripciones.ver') || $user?->can('administraciones_medicacion.ver'));
    $canSeeAlerts = $user?->can('alertas.ver');
    $alerts = (int) $paciente->alertas_criticas_count;
@endphp
@if($canRegisterControl || $canSeeCare || $canSeeMedication || $canSeeAlerts)
    <div class="rm-resident-compact-card__menu-heading">Acciones del residente</div>
    @if($canRegisterControl)<button type="button" wire:click="abrirControlDesdeMenu('{{ $code }}')" @click="open = false"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i></span><span>Registrar control</span></button>@endif
    @if($canSeeCare)<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $code, 'tab' => 'cuidados']) }}"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-heart" aria-hidden="true"></i></span><span>Ver cuidados</span></a>@endif
    @if($canSeeMedication)<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $code, 'tab' => 'medicacion']) }}"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-pill" aria-hidden="true"></i></span><span>Ver medicación</span></a>@endif
    @if($canSeeAlerts)<div class="rm-resident-compact-card__menu-divider" aria-hidden="true"></div><a href="{{ route('admin.enfermeria.alertas', ['adulto' => $code]) }}"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i></span><span>Ver alertas</span>@if($alerts > 0)<span class="rm-resident-compact-card__menu-count" aria-label="{{ $alerts }} prioritarias">{{ $alerts }}</span>@endif</a>@endif
@endif
