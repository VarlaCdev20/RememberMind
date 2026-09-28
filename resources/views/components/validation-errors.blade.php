@if ($errors->any())
 <div
  {{ $attributes->class(['rm-alert rm-alert-danger']) }}
  role="alert"
  aria-live="assertive"
  tabindex="-1"
  x-data
  x-init="$nextTick(() => $el.focus())">
  <i class="ph-bold ph-warning-octagon rm-alert-icon" aria-hidden="true"></i>
  <div class="rm-alert-content">
   <p class="rm-alert-title">Revisa la información ingresada</p>
   <p class="rm-alert-desc">Encontramos {{ $errors->count() }} {{ $errors->count() === 1 ? 'campo que necesita corrección' : 'campos que necesitan corrección' }}.</p>
   <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
    @foreach ($errors->all() as $error)
     <li>{{ $error }}</li>
    @endforeach
   </ul>
  </div>
 </div>
@endif
