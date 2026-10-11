<button {{ $attributes->merge(['type' => 'button', 'class' => 'rm-btn rm-btn-danger shadow-xs']) }}>
    {{ $slot }}
</button>
