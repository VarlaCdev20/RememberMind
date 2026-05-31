@if (session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info') || (isset($errors) && $errors->any()))
    <div x-data="{ 
            show: true, 
            tipo: '{{ session()->has('success') ? 'success' : (session()->has('error') || (isset($errors) && $errors->any()) ? 'error' : (session()->has('warning') ? 'warning' : 'info')) }}',
            mensaje: '{{ session('success') ?? session('error') ?? session('warning') ?? session('info') ?? ((isset($errors) && $errors->any()) ? 'Revise los campos marcados antes de continuar.' : '') }}',
            init() {
                setTimeout(() => { this.show = false; }, 5000);
            }
         }"
         x-show="show"
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="pointer-events-none fixed inset-0 z-50 flex items-end px-4 py-6 sm:items-start sm:p-6"
         style="display: none;">
         
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl shadow-modal backdrop-blur-xl ring-1 ring-borde"
                 :class="{
                    'bg-estado-exito-bg text-estado-exito-texto border border-estado-exito-borde': tipo === 'success',
                    'bg-estado-peligro-bg text-estado-peligro-texto border border-estado-peligro-borde': tipo === 'error',
                    'bg-estado-advertencia-bg text-estado-advertencia-texto border border-estado-advertencia-borde': tipo === 'warning',
                    'bg-estado-info-bg text-estado-info-texto border border-estado-info-borde': tipo === 'info'
                 }">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <template x-if="tipo === 'success'">
                                <i class="ph-fill ph-check-circle text-2xl"></i>
                            </template>
                            <template x-if="tipo === 'error'">
                                <i class="ph-fill ph-x-circle text-2xl"></i>
                            </template>
                            <template x-if="tipo === 'warning'">
                                <i class="ph-fill ph-warning-circle text-2xl"></i>
                            </template>
                            <template x-if="tipo === 'info'">
                                <i class="ph-fill ph-info text-2xl"></i>
                            </template>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-black" x-text="mensaje"></p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button @click="show = false" type="button" class="inline-flex rounded-md text-current opacity-70 hover:opacity-100 focus:outline-none">
                                <span class="sr-only">Cerrar</span>
                                <i class="ph-bold ph-x text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
