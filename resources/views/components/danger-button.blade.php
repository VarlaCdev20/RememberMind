<button {{ $attributes->merge(['type' => 'button', 'class' => 'enf-btn enf-btn-danger shadow-sm']) }}>
    {{ $slot }}
</button>