document.addEventListener('DOMContentLoaded', () => {

    const fieldData   = document.getElementById('data');
    const fieldOra    = document.getElementById('ora');
    const fieldPers   = document.getElementById('persone');
    const fieldNome   = document.getElementById('nome');
    const fieldTel    = document.getElementById('telefono');
    const panel       = document.getElementById('riepilogo-panel');

    const rData  = document.getElementById('r-data');
    const rOra   = document.getElementById('r-ora');
    const rPers  = document.getElementById('r-persone');
    const rNome  = document.getElementById('r-nome');
    const rTel   = document.getElementById('r-telefono');

    const giorni = ['Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato'];
    const mesi   = ['gennaio','febbraio','marzo','aprile','maggio','giugno',
        'luglio','agosto','settembre','ottobre','novembre','dicembre'];

    function formatData(val) {
        if (!val) return '—';
        const d = new Date(val + 'T00:00:00');
        return `${giorni[d.getDay()]} ${d.getDate()} ${mesi[d.getMonth()]} ${d.getFullYear()}`;
    }

    function aggiorna() {
        const hasData = fieldData.value && fieldOra.value && fieldPers.value && parseInt(fieldPers.value) > 0;
        if (!hasData) { panel.style.display = 'none'; return; }

        panel.style.display = 'block';
        rData.textContent  = formatData(fieldData.value);
        rOra.textContent   = fieldOra.value || '—';
        rPers.textContent  = fieldPers.value ? `${fieldPers.value} ${parseInt(fieldPers.value) === 1 ? 'persona' : 'persone'}` : '—';
        rNome.textContent  = fieldNome.value.trim() || '—';
        rTel.textContent   = fieldTel.value.trim()  || '—';
    }

    [fieldData, fieldOra, fieldPers, fieldNome, fieldTel].forEach(el => {
        el?.addEventListener('input', aggiorna);
        el?.addEventListener('change', aggiorna);
    });

    const today = new Date().toISOString().split('T')[0];
    if (fieldData) fieldData.min = today;
});