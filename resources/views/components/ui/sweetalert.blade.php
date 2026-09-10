<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
{!! view()->file(resource_path('frontend/scripts/modules/components-ui-sweetalert.js.blade.php'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render() !!}
</script>
