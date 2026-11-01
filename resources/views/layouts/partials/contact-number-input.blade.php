{{-- Contact number field used on every "add user" form (Admin, College, Org).
     Digits only, max 11, shown as 0909-340-9211 while typing. $value: current value (optional). --}}
<div class="pk-field">
    <label for="contact_number" class="pk-label">Contact number</label>
    <input type="tel" name="contact_number" id="contact_number" class="pk-input js-contact-number"
           inputmode="numeric" maxlength="13" autocomplete="off"
           pattern="\d{4}-?\d{3}-?\d{4}" title="11 digits in the format 09xx-xxx-xxxx"
           placeholder="09xx-xxx-xxxx" aria-describedby="contact_number_help"
           value="{{ $value ?? '' }}">
    <p class="pk-help" id="contact_number_help">11 digits, numbers only. Dashes are added for you.</p>
    @error('contact_number')<p class="pk-error">{{ $message }}</p>@enderror
</div>

@once
<script>
// Keep only digits (max 11) and show them as ####-###-####
document.querySelectorAll('.js-contact-number').forEach(function (input) {
    const format = function (raw) {
        const d = String(raw).replace(/\D/g, '').slice(0, 11);
        return d.length > 7 ? d.slice(0, 4) + '-' + d.slice(4, 7) + '-' + d.slice(7)
             : d.length > 4 ? d.slice(0, 4) + '-' + d.slice(4)
             : d;
    };
    // Block letters and symbols before they appear (paste and autofill are cleaned on input)
    input.addEventListener('beforeinput', function (e) {
        if (e.data && /\D/.test(e.data) && e.inputType === 'insertText') e.preventDefault();
    });
    input.addEventListener('input', function () {
        const caretFromEnd = input.value.length - (input.selectionStart ?? input.value.length);
        input.value = format(input.value);
        const pos = Math.max(0, input.value.length - caretFromEnd);
        input.setSelectionRange(pos, pos);
    });
    input.value = format(input.value);
});
</script>
@endonce
