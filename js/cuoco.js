document.addEventListener('DOMContentLoaded', () => {

    // Auto-refresh ogni 30 secondi per nuovi ordini
    let autoRefresh = setInterval(() => {
        location.reload();
    }, 30000);

    // Pausa auto-refresh se l'utente sta interagendo
    document.addEventListener('click', () => {
        clearInterval(autoRefresh);
        autoRefresh = setInterval(() => location.reload(), 30000);
    });

    // Countdown timer per ogni ordine
    document.querySelectorAll('.kanban-time').forEach(el => {
        const timeStr = el.textContent.trim();
        const [h, m]  = timeStr.split(':').map(Number);
        const now     = new Date();
        const orderTime = new Date();
        orderTime.setHours(h, m, 0, 0);

        function aggiorna() {
            const diff = Math.floor((now - orderTime) / 60000);
            if (diff < 0) return;
            el.textContent = diff < 60
                ? `${diff} min fa`
                : `${Math.floor(diff / 60)}h ${diff % 60}m fa`;
            if (diff > 20) el.style.color = '#C0392B';
            else if (diff > 10) el.style.color = '#F39C12';
        }

        aggiorna();
        setInterval(aggiorna, 60000);
    });

    // Suona notifica per nuovi ordini (se browser lo supporta)
    const count = parseInt(document.getElementById('ordini-count')?.textContent ?? '0');
    const prevCount = parseInt(sessionStorage.getItem('ordini_count') ?? '0');

    if (count > prevCount && prevCount > 0) {
        showToast(`🆕 Nuovo ordine ricevuto!`, 'success');
    }
    sessionStorage.setItem('ordini_count', count);

    function showToast(msg, type = 'success') {
        const t = document.createElement('div');
        t.textContent = msg;
        Object.assign(t.style, {
            position: 'fixed', top: '80px', right: '24px',
            padding: '.8rem 1.4rem', borderRadius: '12px',
            background: type === 'success' ? '#047857' : '#991B1B',
            color: '#fff', fontWeight: '600', fontSize: '1rem',
            boxShadow: '0 8px 24px rgba(0,0,0,.18)', zIndex: '9999',
            transition: 'opacity .3s'
        });
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; }, 3500);
        setTimeout(() => t.remove(), 4000);
    }
});