// ── Funzioni globali ──────────────────────────────────────────────────────────

window.vaiAlTavolo = function(sel) {
    if (sel.value) window.location.href = `cameriere.php?tavolo=${sel.value}&tab=ordini`;
};

window.toggleDropdown = function(id) {
    document.querySelectorAll('.dropdown-menu').forEach(m => {
        if (m.id !== id) m.style.display = 'none';
    });
    const el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'block' ? 'none' : 'block';
};

window.preparaChiusura = function(event) {
    const testo = document.getElementById('order-total')?.innerText ?? '0';
    const num   = parseFloat(testo.replace('€', '').replace('.', '').replace(',', '.').trim());
    document.getElementById('input-totale-finale').value = isNaN(num) ? 0 : num.toFixed(2);
    if (isNaN(num) || num <= 0) {
        if (!confirm("Il totale è 0,00 €. Chiudere comunque l'ordine?")) {
            event.preventDefault();
        }
    }
};

window.modificaPrenotazione = function(id) {
    window.location.href = `cameriere.php?modifica_id=${id}&tab=sala`;
};

// ── Logica principale ─────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar navigation ────────────────────────────────────────────────────
    const currentTab = new URLSearchParams(location.search).get('tab') || 'ordini';

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

    // ── Chiudi dropdown cliccando fuori ───────────────────────────────────────
    document.addEventListener('click', e => {
        if (!e.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
        }
    });

    // ── Stato ordine in memoria ───────────────────────────────────────────────
    const ordine = {};

    const orderItemsUl = document.getElementById('order-items');
    const orderTotalEl = document.getElementById('order-total');
    const btnInvia     = document.getElementById('btn-invia');
    const btnSvuota    = document.getElementById('btn-svuota');
    const noteCucina   = document.getElementById('note-cucina');

    // Inizializza da piatti già presenti (ordine attivo dal PHP)
    document.querySelectorAll('#order-items .order-item').forEach(li => {
        const id    = li.dataset.id;
        const price = parseFloat(li.dataset.price);
        const qty   = parseInt(li.querySelector('.qty')?.textContent ?? '1', 10);
        const title = li.querySelector('.order-item-name')?.textContent?.trim() ?? '';
        if (id && price >= 0) ordine[id] = { title, price, qty };
    });
    aggiornaTotal();

    // ── Aggiunta piatti dal menu ──────────────────────────────────────────────
    document.querySelectorAll('.dish-add').forEach(btn => {
        btn.addEventListener('click', () => {
            const li    = btn.closest('.dish');
            const id    = li.dataset.id;
            const price = parseFloat(li.dataset.price);
            const title = li.dataset.title;
            if (ordine[id]) ordine[id].qty++;
            else ordine[id] = { title, price, qty: 1 };
            renderOrdine();
        });
    });

    // ── Controlli quantità (delega) ───────────────────────────────────────────
    orderItemsUl?.addEventListener('click', e => {
        const item = e.target.closest('.order-item');
        if (!item) return;
        const id = item.dataset.id;
        if (e.target.classList.contains('qty-plus')) {
            ordine[id].qty++;
        } else if (e.target.classList.contains('qty-minus')) {
            ordine[id].qty--;
            if (ordine[id].qty <= 0) delete ordine[id];
        }
        renderOrdine();
    });

    // ── Svuota ────────────────────────────────────────────────────────────────
    btnSvuota?.addEventListener('click', () => {
        if (!Object.keys(ordine).length) return;
        if (confirm("Svuotare l'ordine?")) {
            Object.keys(ordine).forEach(k => delete ordine[k]);
            renderOrdine();
        }
    });

    // ── Invia in cucina ───────────────────────────────────────────────────────
    btnInvia?.addEventListener('click', () => {
        const idTavolo = new URLSearchParams(location.search).get('tavolo');
        if (!idTavolo) { alert('Seleziona prima un tavolo.'); return; }

        const piatti = Object.entries(ordine).map(([id, p]) => ({ id, qty: p.qty }));
        if (!piatti.length) { alert('Aggiungi almeno un piatto.'); return; }

        btnInvia.disabled    = true;
        btnInvia.textContent = 'Invio…';

        fetch('ordine_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_tavolo: idTavolo,
                piatti,
                note: noteCucina?.value ?? ''
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Ordine inviato in cucina!', 'success');
                Object.keys(ordine).forEach(k => delete ordine[k]);
                renderOrdine();
                if (noteCucina) noteCucina.value = '';
            } else {
                showToast('⚠️ ' + (data.message ?? "Errore nell'invio."), 'error');
            }
        })
        .catch(() => showToast('⚠️ Errore di rete.', 'error'))
        .finally(() => {
            btnInvia.disabled    = false;
            btnInvia.textContent = 'Invia in cucina';
        });
    });

    // ── Render ordine ─────────────────────────────────────────────────────────
    function renderOrdine() {
        if (!orderItemsUl) return;
        orderItemsUl.innerHTML = '';
        Object.entries(ordine).forEach(([id, p]) => {
            const li = document.createElement('li');
            li.className     = 'order-item';
            li.dataset.id    = id;
            li.dataset.price = p.price;
            li.innerHTML = `
                <span class="order-item-name">${escHtml(p.title)}</span>
                <div class="qty-controls">
                    <button type="button" class="qty-minus">−</button>
                    <span class="qty">${p.qty}</span>
                    <button type="button" class="qty-plus">+</button>
                </div>
                <span class="order-item-price">€ ${fmt(p.price * p.qty)}</span>
            `;
            orderItemsUl.appendChild(li);
        });
        aggiornaTotal();
    }

    function aggiornaTotal() {
        const tot = Object.values(ordine).reduce((s, p) => s + p.price * p.qty, 0);
        if (orderTotalEl) orderTotalEl.textContent = fmt(tot) + ' €';
        const input = document.getElementById('input-totale-finale');
        if (input) input.value = tot.toFixed(2);
    }

    function fmt(n) { return n.toFixed(2).replace('.', ','); }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function showToast(msg, type = 'success') {
        const t = document.createElement('div');
        t.textContent = msg;
        Object.assign(t.style, {
            position: 'fixed', bottom: '24px', right: '24px',
            padding: '.8rem 1.4rem', borderRadius: '12px',
            background: type === 'success' ? '#047857' : '#991B1B',
            color: '#fff', fontWeight: '600', fontSize: '.9rem',
            boxShadow: '0 8px 24px rgba(0,0,0,.18)', zIndex: '9999',
            transition: 'opacity .3s'
        });
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; }, 2800);
        setTimeout(() => t.remove(), 3200);
    }
});