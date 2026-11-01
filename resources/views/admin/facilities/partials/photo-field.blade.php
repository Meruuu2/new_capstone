{{-- Facility photo upload. Expects $photo (current URL or null) and $hasUpload (an uploaded file exists that can be removed). --}}
<div class="facform-step">
    <span class="facform-num">{{ $num }}</span>
    <div>
        <h2>Photo</h2>
        <p>Shown on the facilities list and in <em>Key Facilities</em> on the homepage.</p>

        <div class="facform-photo" id="photo-drop">
            <div class="facform-photo-thumb">
                <img id="photo-thumb" src="{{ $photo }}" alt="" @unless ($photo) hidden @endunless>
                <svg id="photo-empty" @if ($photo) hidden @endif viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/></svg>
            </div>
            <div class="facform-photo-text">
                <label for="image" class="pk-btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M6 10l6-6 6 6M4 20h16"/></svg>
                    <span id="photo-btn-text">{{ $photo ? 'Replace photo' : 'Upload photo' }}</span>
                </label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" class="sr-only">
                <span class="facform-photo-hint" id="photo-name">JPG, PNG or WebP, up to 5 MB. Or drop a file here.</span>
                @if ($hasUpload)
                    <label class="facform-photo-remove">
                        <input type="checkbox" name="remove_image" id="remove_image" value="1" {{ old('remove_image') ? 'checked' : '' }}>
                        Remove uploaded photo
                    </label>
                @endif
            </div>
        </div>
        @error('image')<p class="pk-error u-mt-8">{{ $message }}</p>@enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('image');
    const drop = document.getElementById('photo-drop');
    const thumb = document.getElementById('photo-thumb');
    const empty = document.getElementById('photo-empty');
    const preview = document.getElementById('pv-photo');
    const remove = document.getElementById('remove_image');
    const original = thumb.getAttribute('src');

    function show(src) {
        [thumb, preview].forEach((img) => { if (!img) return; img.hidden = !src; if (src) img.src = src; });
        empty.hidden = !!src;
        document.getElementById('pv-photo-icon')?.toggleAttribute('hidden', !!src);
    }
    input.addEventListener('change', () => {
        const file = input.files[0];
        document.getElementById('photo-name').textContent = file ? file.name : 'JPG, PNG or WebP, up to 5 MB. Or drop a file here.';
        if (remove && file) remove.checked = false;
        show(file ? URL.createObjectURL(file) : original);
    });
    remove?.addEventListener('change', () => {
        if (remove.checked) { input.value = ''; show(null); } else { show(original); }
    });
    ['dragenter', 'dragover'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); drop.classList.add('is-drag'); }));
    ['dragleave', 'drop'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); drop.classList.remove('is-drag'); }));
    drop.addEventListener('drop', (e) => {
        if (!e.dataTransfer.files.length) return;
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});
</script>
