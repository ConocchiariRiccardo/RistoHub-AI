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

document.addEventListener('DOMContentLoaded', () => {

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

    document.addEventListener('click', e => {
        if (!e.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
        }
    });

    const ordine       = {};
    let scontoAttivo   = 0;

    const orderItemsUl = document.getElementById('order-items');
    const orderTotalEl = document.getElementById('order-total');
    const btnInvia     = document.getElementById('btn-invia');
    const btnSvuota    = document.getElementById('btn-svuota');
    const noteCucina   = document.getElementById('note-cucina');

    function fmt(n) { return n.toFixed(2).replace('.', ','); }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function aggiornaTotal() {
        const tot = Object.values(ordine).reduce((s, p) => s + p.price * p.qty, 0);
        const scontato = scontoAttivo > 0 ? tot * (1 - scontoAttivo / 100) : tot;
        if (orderTotalEl) orderTotalEl.textContent = fmt(scontato) + ' €';
        const input = document.getElementById('input-totale-finale');
        if (input) input.value = scontato.toFixed(2);
        const inputLordo = document.getElementById('input-totale-lordo');
        if (inputLordo) inputLordo.value = tot.toFixed(2);
    }

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

    async function caricaOrdineAttivo(idTavolo) {
        if (!idTavolo) return;
        try {
            const res  = await fetch(`ordine_attivo.php?tavolo=${idTavolo}`);
            const data = await res.json();
            Object.keys(ordine).forEach(k => delete ordine[k]);
            data.forEach(p => {
                ordine[p.id] = { title: p.nome, price: parseFloat(p.prezzo_unitario), qty: p.quantita };
            });
            renderOrdine();
        } catch(e) {
            console.error('Errore caricamento ordine:', e);
        }
    }

    const idTavoloInit = new URLSearchParams(location.search).get('tavolo');
    caricaOrdineAttivo(idTavoloInit);

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

    btnSvuota?.addEventListener('click', () => {
        if (!Object.keys(ordine).length) return;
        if (confirm("Svuotare l'ordine?")) {
            Object.keys(ordine).forEach(k => delete ordine[k]);
            renderOrdine();
        }
    });

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
            body: JSON.stringify({ id_tavolo: idTavolo, piatti, note: noteCucina?.value ?? '' })
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

    const btnVerifica = document.getElementById('btn-verifica-coupon');
    const feedbackEl  = document.getElementById('coupon-feedback');
    const inputCoupon = document.getElementById('codice-coupon');

    btnVerifica?.addEventListener('click', () => {
        const codice = inputCoupon?.value.trim().toUpperCase();
        if (!codice) { feedbackEl.textContent = 'Inserisci un codice.'; feedbackEl.style.color = '#8E3D2D'; return; }

        fetch(`verifica_coupon.php?codice=${encodeURIComponent(codice)}`)
            .then(r => r.json())
            .then(data => {
                if (data.valido) {
                    scontoAttivo = data.sconto;
                    feedbackEl.textContent = `✅ Coupon valido: -${data.sconto}% applicato.`;
                    feedbackEl.style.color = '#35686A';
                } else {
                    scontoAttivo = 0;
                    feedbackEl.textContent = '❌ Coupon non valido o già usato.';
                    feedbackEl.style.color = '#8E3D2D';
                }
                aggiornaTotal();
            })
            .catch(() => { feedbackEl.textContent = 'Errore di rete.'; });
    });
});