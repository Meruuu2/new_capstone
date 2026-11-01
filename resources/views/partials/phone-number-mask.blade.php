<script>
(function () {
    const input = document.getElementById(@json($inputId ?? 'contact_number'));
    if (!input) return;

    function formatNumber(value) {
        const digits = value.replace(/\D/g, '').slice(0, 11);
        if (digits.length <= 4) return digits;
        if (digits.length <= 7) return digits.slice(0, 4) + '-' + digits.slice(4);
        return digits.slice(0, 4) + '-' + digits.slice(4, 7) + '-' + digits.slice(7);
    }

    input.addEventListener('input', function () {
        const formatted = formatNumber(input.value);
        if (formatted !== input.value) {
            const digitCount = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;
            input.value = formatted;
            const position = digitCount + (digitCount > 4 ? 1 : 0) + (digitCount > 7 ? 1 : 0);
            input.setSelectionRange(position, position);
        }
    });
    input.value = formatNumber(input.value);
})();
</script>
