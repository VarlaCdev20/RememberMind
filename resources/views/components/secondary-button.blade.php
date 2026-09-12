<button {{ $attributes->merge(['type' => 'button', 'class' => 'enf-btn enf-btn-secondary shadow-sm']) }}>
    {{ $slot }}
</button>