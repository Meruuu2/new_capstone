{{-- Province → Town/City → Barangay pickers for .ga-address. Lists come from public/data/ph-address (built from PSGC):
     provinces.json, then one <provinceCode>.json per province ({ "Town": ["Barangay", ...] }), all A–Z. --}}
<script>
(function () {
    const box = document.querySelector('.ga-address');
    if (!box) return;
    const src = box.dataset.addressSrc;
    const province = document.getElementById('address_province');
    const town = document.getElementById('address_town');
    const barangay = document.getElementById('address_barangay');
    let towns = {};

    function fill(select, names, placeholder, selected) {
        select.replaceChildren(new Option(placeholder, ''));
        names.forEach(name => select.add(new Option(name, name, false, name === selected)));
        select.disabled = !names.length;
    }
    function showBarangays(selected) {
        fill(barangay, towns[town.value] || [], town.value ? 'Choose your barangay' : 'Choose a town first', selected);
    }
    async function showTowns(selectedTown, selectedBarangay) {
        const code = province.selectedOptions[0]?.dataset.code;
        towns = {};
        fill(town, [], code ? 'Loading…' : 'Choose a province first');
        showBarangays();
        if (!code) return;
        try {
            towns = await (await fetch(src + '/' + code + '.json')).json();
        } catch (e) {
            fill(town, [], 'Could not load towns. Reload the page.');
            return;
        }
        fill(town, Object.keys(towns), 'Choose your town or city', selectedTown);
        showBarangays(selectedBarangay);
    }

    province.addEventListener('change', () => showTowns());
    town.addEventListener('change', () => showBarangays());

    fetch(src + '/provinces.json').then(r => r.json()).then(list => {
        province.replaceChildren(new Option('Choose your province', ''));
        list.forEach(p => {
            const option = new Option(p.name, p.name, false, p.name === box.dataset.oldProvince);
            option.dataset.code = p.code;
            province.add(option);
        });
        showTowns(box.dataset.oldTown, box.dataset.oldBarangay);
    }).catch(() => province.replaceChildren(new Option('Could not load provinces. Reload the page.', '')));
})();
</script>
