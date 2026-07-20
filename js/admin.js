document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar navigation ────────────────────────────────────────────────────
    const currentTab = new URLSearchParams(location.search).get('tab') || 'statistiche';

    document.querySelectorAll('.admin-sidebar a').forEach(link => {
        const tab = link.getAttribute('data-tab');
        if (tab === currentTab) link.classList.add('active');

        link.addEventListener('click', e => {
            e.preventDefault();
            const target = link.getAttribute('data-tab');
            history.pushState({}, '', `?tab=${target}`);
            showSection(target);
            document.querySelectorAll('.admin-sidebar a').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    function showSection(tab) {
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
        const el = document.getElementById(tab);
        if (el) el.classList.add('active');
    }

    showSection(currentTab);

    // ── Toggle form visibilità ────────────────────────────────────────────────
    window.toggleForm = function(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('visible');
        if (el.classList.contains('visible')) {
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    // Apri form automaticamente se siamo in modifica
    ['form-piatto', 'form-tavolo', 'form-utente', 'form-ingrediente'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const hidden = el.querySelector('input[type="hidden"]');
        if (hidden && hidden.value) {
            el.classList.add('visible');
            setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
        }
    });

    // ── Cambia password utente ────────────────────────────────────────────────
    window.mostraPassword = function() {
        const el = document.getElementById('pwd');
        if (el) el.style.display = 'block';
    };

    // ── Modifica prenotazione cameriere ───────────────────────────────────────
    window.modificaPrenotazione = function(id) {
        window.location.href = `cameriere.php?modifica_id=${id}&tab=sala`;
    };

});