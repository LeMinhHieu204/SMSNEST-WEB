function resolveBaseUrl() {
    if (window.APP_BASE_URL) {
        return window.APP_BASE_URL;
    }
    const path = window.location.pathname;
    const marker = '/public';
    const idx = path.indexOf(marker);
    if (idx >= 0) {
        return path.slice(0, idx + marker.length);
    }
    return '';
}

const baseUrl = resolveBaseUrl();
const serviceInput = document.getElementById('service-select');
const countryInput = document.getElementById('country-select');
const countryList = document.getElementById('country-list');
const resultList = document.getElementById('country-result');
const stockValue = document.getElementById('stock-value');
const priceRange = document.getElementById('price-range');
let lastPricing = [];
let allCountries = [];
const quickPurchaseBtn = document.getElementById('quick-purchase-btn');
const quickResult = document.getElementById('quick-purchase-result');
const quickQuantity = document.getElementById('quick-quantity');
const quickPricingOption = document.getElementById('quick-pricing-option');
const quickMaxPrice = document.getElementById('quick-max-price');
const pendingBody = document.getElementById('pending-body');
const serviceDropdown = document.querySelector('[data-dropdown="service"] .dropdown-display');
const countryDropdown = document.querySelector('[data-dropdown="country"] .dropdown-display');
const pendingTable = document.getElementById('pending-table');
let currentMinPrice = null;
let currentMaxPrice = null;

function parseJson(text) {
    if (!text) {
        return null;
    }
    const clean = text.replace(/^\uFEFF/, '');
    const start = clean.indexOf('{');
    const jsonText = start >= 0 ? clean.slice(start) : clean;
    return JSON.parse(jsonText);
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown.open').forEach((drop) => drop.classList.remove('open'));
}

function setupDropdown(dropdown) {
    const display = dropdown.querySelector('.dropdown-display');
    const toggle = dropdown.querySelector('.dropdown-toggle');
    const search = dropdown.querySelector('.dropdown-search');
    const list = dropdown.querySelector('.dropdown-list');
    const hidden = dropdown.querySelector('input[type="hidden"]');

    function openDropdown() {
        const isOpen = dropdown.classList.contains('open');
        closeAllDropdowns();
        dropdown.classList.toggle('open', !isOpen);
        if (!isOpen && search) {
            search.value = '';
            search.focus();
            filterList(list, '');
        }
    }

    toggle.addEventListener('click', openDropdown);
    if (display) {
        display.addEventListener('click', openDropdown);
    }
    dropdown.querySelector('.dropdown-control').addEventListener('click', (event) => {
        if (event.target === toggle || event.target === display) {
            return;
        }
        openDropdown();
    });

    if (search) {
        search.addEventListener('input', (event) => {
            filterList(list, event.target.value);
        });
    }

    list.addEventListener('click', (event) => {
        const target = event.target.closest('.dropdown-item');
        if (!target) {
            return;
        }
        const value = target.dataset.value || '';
        const label = target.textContent.trim();
        if (hidden) {
            hidden.value = value;
        }
        if (display) {
            display.value = label;
        }
        dropdown.classList.remove('open');
        dropdown.dispatchEvent(new CustomEvent('dropdown:change', { detail: { value } }));
    });
}

function filterList(list, query) {
    const term = query.trim().toLowerCase();
    const items = Array.from(list.querySelectorAll('.dropdown-item'));
    let empty = list.querySelector('.dropdown-empty');
    if (!empty) {
        empty = document.createElement('div');
        empty.className = 'dropdown-empty';
        empty.textContent = 'No matches';
        empty.style.display = 'none';
        list.appendChild(empty);
    }
    let visible = 0;
    items.forEach((item) => {
        const text = item.textContent.toLowerCase();
        const match = !term || text.includes(term);
        item.style.display = match ? 'block' : 'none';
        if (match) {
            visible += 1;
        }
    });
    empty.style.display = visible ? 'none' : 'block';
}

function renderCountryDropdown(items) {
    if (!countryList) {
        return;
    }
    countryList.innerHTML = '';
    items.forEach((item) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'dropdown-item';
        const countryId = item.country_id ?? item.id ?? '';
        button.dataset.value = String(countryId);
        button.textContent = item.country_name || item.name || '';
        countryList.appendChild(button);
    });
    if (!items.length) {
        countryList.innerHTML = '<div class="dropdown-empty">No data for this service</div>';
    }
}

function renderCountryResults(items) {
    if (!resultList) {
        return;
    }
    resultList.innerHTML = '';
    items.forEach((item) => {
        const row = document.createElement('div');
        row.className = 'country-row';
        const countryId = item.country_id ?? item.id ?? '';
        const countryName = item.country_name || item.name || '';
        row.innerHTML = `
            <div class="country-code">${item.code || ''}</div>
            <div class="country-name">${countryName}</div>
            <div class="country-stock">${item.stock ?? 0}</div>
            <div class="country-price">$${item.min_price}-${item.max_price}</div>
            <button class="btn icon country-order" type="button" data-country-id="${countryId}" data-country-name="${countryName}">?</button>
        `;
        row.dataset.countryId = String(countryId);
        row.dataset.countryName = countryName;
        resultList.appendChild(row);
    });
}

function setCountrySelection(countryId, countryName) {
    if (countryInput) {
        countryInput.value = countryId ? String(countryId) : '';
    }
    if (countryDropdown) {
        countryDropdown.value = countryName || 'Select a country';
    }
}

function findCountryNameById(countryId, fallback) {
    if (!countryId) {
        return fallback || '';
    }
    const match = allCountries.find((country) => String(country.id) === String(countryId));
    return match ? match.country_name : (fallback || '');
}

function fetchAllCountries() {
    if (!countryList) {
        return;
    }
    fetch(`${baseUrl}/api/countries`)
        .then((res) => {
            if (res.status === 401) {
                window.location.href = `${baseUrl}/login`;
                return null;
            }
            return res.ok ? res.text() : null;
        })
        .then((text) => (text ? parseJson(text) : null))
        .then((payload) => {
            if (!payload) {
                return;
            }
            allCountries = payload.data || [];
            renderCountryDropdown(allCountries);
        })
        .catch(() => {
            countryList.innerHTML = '<div class="dropdown-empty">Error loading countries</div>';
        });
}

function fetchServiceCountries(serviceId) {
    if (!serviceId) {
        lastPricing = [];
        if (priceRange) {
            priceRange.value = '$0.00 - $0.00';
        }
        currentMinPrice = null;
        currentMaxPrice = null;
        if (stockValue) {
            stockValue.value = 0;
        }
        if (resultList) {
            resultList.innerHTML = '';
        }
        return;
    }

    fetch(`${baseUrl}/api/service-countries?service_id=${serviceId}`)
        .then((res) => {
            if (res.status === 401) {
                window.location.href = `${baseUrl}/login`;
                return null;
            }
            if (!res.ok) {
                throw new Error(`Request failed (${res.status})`);
            }
            return res.text();
        })
        .then((text) => {
            if (!text) {
                return null;
            }
            return parseJson(text);
        })
        .then((payload) => {
            if (!payload) {
                return;
            }
            const items = payload.data || [];
            lastPricing = items;
            renderCountryDropdown(items);
            renderCountryResults(items);
            const selectedId = countryInput ? countryInput.value : '';
            const match = items.find((item) => String(item.country_id) === String(selectedId));
            if (match) {
                setCountrySelection(match.country_id, findCountryNameById(match.country_id, match.country_name));
                currentMinPrice = Number(match.min_price || 0);
                currentMaxPrice = Number(match.max_price || 0);
                updatePriceRangeForQuantity();
                refreshStock(match.country_id, match.stock);
                return;
            }
            if (items.length) {
                setCountrySelection(items[0].country_id, findCountryNameById(items[0].country_id, items[0].country_name));
                currentMinPrice = Number(items[0].min_price || 0);
                currentMaxPrice = Number(items[0].max_price || 0);
                updatePriceRangeForQuantity();
                refreshStock(items[0].country_id, items[0].stock);
            } else {
                setCountrySelection('', 'Select a country');
                currentMinPrice = null;
                currentMaxPrice = null;
                updatePriceRangeForQuantity();
            }
        })
        .catch(() => {
            if (countryList) {
                countryList.innerHTML = '<div class="dropdown-empty">Error loading countries</div>';
            }
        });
}

function syncPricing(serviceId) {
    if (!serviceId) {
        return Promise.resolve();
    }
    return fetch(`${baseUrl}/api/smspool-pricing?service_id=${serviceId}`)
        .then((res) => {
            if (res.status === 401) {
                window.location.href = `${baseUrl}/login`;
                return null;
            }
            return res.ok ? res.text() : null;
        })
        .catch(() => null);
}

function loadPricing(serviceId) {
    if (!serviceId) {
        fetchServiceCountries(serviceId);
        return;
    }
    syncPricing(serviceId).finally(() => {
        fetchServiceCountries(serviceId);
    });
}

function refreshStock(countryId, fallbackStock) {
    if (!serviceInput || !serviceInput.value) {
        return;
    }
    const serviceId = serviceInput.value;
    fetch(`${baseUrl}/api/smspool-stock?service_id=${serviceId}&country_id=${countryId}`)
        .then((res) => {
            if (res.status === 401) {
                window.location.href = `${baseUrl}/login`;
                return null;
            }
            return res.ok ? res.text() : null;
        })
        .then((text) => {
            if (!text) {
                return null;
            }
            return parseJson(text);
        })
        .then((payload) => {
            if (!payload) {
                return;
            }
            if (payload.error) {
                if (stockValue) {
                    stockValue.value = fallbackStock ?? 0;
                }
                return;
            }
            const rawAmount = payload.raw && payload.raw.amount !== undefined ? payload.raw.amount : null;
            const nextStock = payload.stock !== null && payload.stock !== undefined ? payload.stock : rawAmount;
            if (stockValue && nextStock !== null && nextStock !== undefined) {
                stockValue.value = nextStock;
            } else if (stockValue) {
                stockValue.value = fallbackStock ?? 0;
            }
        })
        .catch(() => {
            if (stockValue) {
                stockValue.value = fallbackStock ?? 0;
            }
        });
}

function updatePriceRangeForQuantity() {
    if (!priceRange) {
        return;
    }
    const qty = quickQuantity ? Number(quickQuantity.value || 1) : 1;
    if (currentMinPrice === null || currentMaxPrice === null) {
        priceRange.value = '$0.00 - $0.00';
        return;
    }
    const minTotal = currentMinPrice * (Number.isFinite(qty) && qty > 0 ? qty : 1);
    const maxTotal = currentMaxPrice * (Number.isFinite(qty) && qty > 0 ? qty : 1);
    priceRange.value = `$${minTotal.toFixed(2)} - $${maxTotal.toFixed(2)}`;
}

function ensureToastContainer() {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    return container;
}

function showToast(message, isError) {
    if (!message) {
        return;
    }
    const container = ensureToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast${isError ? ' toast-error' : ' toast-success'}`;
    toast.textContent = message;
    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

function setQuickResult(message, isError) {
    showToast(message, isError);
}

function postForm(url, payload) {
    const body = new URLSearchParams();
    Object.entries(payload).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            body.append(key, value);
        }
    });
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body,
    }).then((res) => {
        if (res.status === 401) {
            window.location.href = `${baseUrl}/login`;
            return null;
        }
        return res.text();
    });
}

function startOtpPolling(orderId, phoneNumber) {
    let attempts = 0;
    const maxAttempts = 18;

    function tick() {
        attempts += 1;
        postForm(`${baseUrl}/api/smspool-check-sms`, { order_id: orderId })
            .then((text) => (text ? parseJson(text) : null))
            .then((payload) => {
                if (!payload) {
                    return;
                }
                if (payload.code) {
                    setQuickResult(`Phone: ${phoneNumber} | OTP: ${payload.code}`, false);
                    updatePendingRow(orderId, payload.code);
                    return;
                }
                if (attempts < maxAttempts) {
                    setQuickResult(`Phone: ${phoneNumber} | Waiting for OTP...`, false);
                    setTimeout(tick, 8000);
                } else {
                    setQuickResult(`Phone: ${phoneNumber} | OTP not received yet.`, true);
                }
            })
            .catch(() => {
                if (attempts < maxAttempts) {
                    setTimeout(tick, 8000);
                }
            });
    }

    tick();
}

function startPendingRowPolling(row) {
    if (!row) {
        return;
    }
    const providerOrderId = row.dataset.providerOrderId;
    if (!providerOrderId) {
        return;
    }
    let attempts = 0;
    const maxAttempts = 18;

    function tick() {
        attempts += 1;
        postForm(`${baseUrl}/api/smspool-check-sms`, { order_id: providerOrderId })
            .then((text) => (text ? parseJson(text) : null))
            .then((payload) => {
                if (!payload) {
                    return;
                }
                if (payload.code) {
                    updatePendingRow(providerOrderId, payload.code);
                    return;
                }
                if (attempts < maxAttempts) {
                    setTimeout(tick, 8000);
                }
            })
            .catch(() => {
                if (attempts < maxAttempts) {
                    setTimeout(tick, 8000);
                }
            });
    }

    tick();
}

function pollPendingRowsOnce() {
    if (!pendingBody) {
        return;
    }
    pendingBody.querySelectorAll('.table-row').forEach((row) => {
        if (row.classList.contains('pending-empty')) {
            return;
        }
        if (row.dataset.status && row.dataset.status !== 'pending') {
            return;
        }
        const codeCell = row.querySelector('.pending-code');
        if (codeCell && codeCell.textContent.includes('OTP:')) {
            return;
        }
        const providerOrderId = row.dataset.providerOrderId;
        if (!providerOrderId) {
            return;
        }
        const attempts = Number(row.dataset.pollAttempts || 0);
        if (attempts >= 18) {
            return;
        }
        row.dataset.pollAttempts = String(attempts + 1);
        postForm(`${baseUrl}/api/smspool-check-sms`, { order_id: providerOrderId })
            .then((text) => (text ? parseJson(text) : null))
            .then((payload) => {
                if (!payload) {
                    return;
                }
                if (payload.code) {
                    updatePendingRow(providerOrderId, payload.code);
                }
            })
            .catch(() => {});
    });
}

function updatePendingRow(providerOrderId, code) {
    if (!pendingBody || !providerOrderId) {
        return;
    }
    const row = pendingBody.querySelector(`[data-provider-order-id="${providerOrderId}"]`);
    if (!row) {
        return;
    }
    const codeCell = row.querySelector('.pending-code');
    if (codeCell && code) {
        codeCell.textContent = `OTP: ${code}`;
    }
    const statusCell = row.querySelector('.pending-status');
    if (statusCell && code) {
        statusCell.textContent = 'completed';
        statusCell.classList.remove('warning');
        statusCell.classList.add('success');
        row.dataset.status = 'completed';
    }
}

function ensureEmptyRow() {
    if (!pendingBody) {
        return;
    }
    if (pendingBody.children.length > 0) {
        return;
    }
    const empty = document.createElement('div');
    empty.className = 'table-row table-compact pending-empty';
    empty.innerHTML = `
        <div>No pending SMS</div>
        <div>-</div>
        <div>-</div>
        <div>-</div>
        <div>-</div>
        <div>-</div>
        <div>-</div>
    `;
    pendingBody.appendChild(empty);
}

function setRefundAvailable(row) {
    if (!row) {
        return;
    }
    if (!row.dataset.providerOrderId) {
        return;
    }
    const refundBtn = row.querySelector('.pending-refund');
    if (refundBtn) {
        refundBtn.style.display = 'inline-flex';
        refundBtn.dataset.refundLocked = '0';
    }
}

function startRefundCountdown(row, totalSeconds, refundAtSeconds, createdAtEpochSeconds, remainingSeconds, loadedAtEpochSeconds) {
    if (!row || !totalSeconds) {
        return;
    }
    const statusCell = row.querySelector('.pending-status');
    const nowSeconds = Math.floor(Date.now() / 1000);
    let startSeconds = Number.isFinite(createdAtEpochSeconds) ? createdAtEpochSeconds : nowSeconds;
    if (startSeconds > nowSeconds) {
        startSeconds = nowSeconds;
    }
    let remaining = totalSeconds - Math.floor(nowSeconds - startSeconds);
    remaining = Math.max(0, Math.min(totalSeconds, remaining));
    if (Number.isFinite(remainingSeconds) && Number.isFinite(loadedAtEpochSeconds)) {
        const elapsedSinceLoad = Math.max(0, nowSeconds - loadedAtEpochSeconds);
        const cappedRemaining = Math.min(totalSeconds, Math.max(0, remainingSeconds));
        remaining = Math.max(0, Math.min(totalSeconds, cappedRemaining - elapsedSinceLoad));
    }
    if (statusCell) {
        statusCell.textContent = `Waiting SMS (${remaining}s)`;
    }
    if (refundAtSeconds !== undefined && remaining <= refundAtSeconds) {
        setRefundAvailable(row);
    } else {
        const refundBtn = row.querySelector('.pending-refund');
        if (refundBtn) {
            refundBtn.style.display = 'inline-flex';
            refundBtn.dataset.refundLocked = '1';
        }
    }
    const timer = setInterval(() => {
        const tickNow = Math.floor(Date.now() / 1000);
        if (Number.isFinite(remainingSeconds) && Number.isFinite(loadedAtEpochSeconds)) {
            const elapsed = Math.max(0, tickNow - loadedAtEpochSeconds);
            const cappedRemaining = Math.min(totalSeconds, Math.max(0, remainingSeconds));
            remaining = Math.max(0, Math.min(totalSeconds, cappedRemaining - elapsed));
        } else {
            remaining = totalSeconds - Math.floor(tickNow - startSeconds);
            remaining = Math.max(0, Math.min(totalSeconds, remaining));
        }
        if (statusCell && remaining > 0) {
            statusCell.textContent = `Waiting SMS (${remaining}s)`;
        }
        if (refundAtSeconds !== undefined && remaining <= refundAtSeconds) {
            setRefundAvailable(row);
        }
        if (remaining <= 0) {
            clearInterval(timer);
            if (statusCell) {
                statusCell.textContent = 'Waiting SMS (timeout)';
            }
            setRefundAvailable(row);
        }
    }, 1000);
}

function getRemainingSeconds(row, totalSeconds) {
    const createdAtRaw = row ? row.dataset.createdAtEpoch : null;
    const createdAtSeconds = createdAtRaw ? Number(createdAtRaw) : NaN;
    const nowSeconds = Math.floor(Date.now() / 1000);
    if (Number.isFinite(createdAtSeconds)) {
        const elapsed = Math.max(0, nowSeconds - createdAtSeconds);
        return Math.max(0, Math.min(totalSeconds, totalSeconds - elapsed));
    }
    const remainingRaw = row ? row.dataset.remaining : null;
    const remainingSeconds = remainingRaw ? Number(remainingRaw) : NaN;
    if (Number.isFinite(remainingSeconds)) {
        return Math.max(0, Math.min(totalSeconds, remainingSeconds));
    }
    return totalSeconds;
}

function appendPendingRow(order, serviceName, countryName) {
    if (!pendingBody || !order) {
        return;
    }
    const emptyRow = pendingBody.querySelector('.pending-empty');
    if (emptyRow) {
        emptyRow.remove();
    }
    const row = document.createElement('div');
    row.className = 'table-row table-compact table-pending';
    if (order.provider_order_id) {
        row.dataset.providerOrderId = order.provider_order_id;
    }
    if (order.id) {
        row.dataset.orderId = order.id;
    }
    row.dataset.createdAtEpoch = String(Math.floor(Date.now() / 1000));
    row.dataset.remaining = String(1200);
    row.dataset.loadedAtEpoch = row.dataset.createdAtEpoch;
    row.dataset.status = 'pending';
    let quantityValue = order.quantity ? Number(order.quantity) : 1;
    if (Number.isFinite(quantityValue) && quantityValue > 1) {
        quantityValue = 1;
    }
    row.innerHTML = `
        <div>${order.phone_number || '-'}</div>
        <div class="pending-code">Waiting for OTP...</div>
        <div>${serviceName || '-'}</div>
        <div>${countryName || '-'}</div>
        <div>${Number.isFinite(quantityValue) ? quantityValue : 1}</div>
        <div class="pill warning pending-status">pending</div>
        <div>$${Number(order.price || 0).toFixed(2)}</div>
        <div class="pending-actions">
            <button class="btn icon pending-refund" type="button" style="display:inline-flex;" data-refund-locked="1">Refund</button>
        </div>
    `;
    pendingBody.prepend(row);
    startRefundCountdown(row, 1200, 900, Math.floor(Date.now() / 1000), 1200, Math.floor(Date.now() / 1000));
}

function handleQuickPurchase() {
    if (!serviceInput || !countryInput) {
        return;
    }
    const serviceId = serviceInput.value;
    const countryId = countryInput.value;
    if (!serviceId || !countryId) {
        setQuickResult('Please select service and country first.', true);
        return;
    }

    quickPurchaseBtn.disabled = true;
    setQuickResult('Purchasing number...', false);

    const payload = {
        service_id: serviceId,
        country_id: countryId,
        pricing_option: quickPricingOption ? quickPricingOption.value : 1,
        max_price: quickMaxPrice ? quickMaxPrice.value : '',
        quantity: quickQuantity ? quickQuantity.value : 1,
    };

    postForm(`${baseUrl}/api/smspool-order-sms`, payload)
        .then((text) => (text ? parseJson(text) : null))
        .then((payloadData) => {
            if (!payloadData) {
                setQuickResult('Purchase failed. Please try again.', true);
                return;
            }
            if (payloadData.error) {
                if (Array.isArray(payloadData.pools) && payloadData.pools.length) {
                    payloadData.pools.forEach((pool) => {
                        const title = `Pool ${pool.pool || ''}`.trim();
                        const detail = pool.message || payloadData.error;
                        showToast(`${title}: ${detail}`, true);
                    });
                } else {
                    setQuickResult(payloadData.error, true);
                }
                return;
            }
            const orders = Array.isArray(payloadData.data) ? payloadData.data : [];
            if (!orders.length) {
                setQuickResult('No number returned.', true);
                return;
            }
            const serviceName = serviceDropdown ? serviceDropdown.value : '';
            const countryName = countryDropdown ? countryDropdown.value : '';
            orders.forEach((order) => {
                appendPendingRow(order, serviceName, countryName);
                if (order.provider_order_id) {
                    startOtpPolling(order.provider_order_id, order.phone_number);
                }
            });
            const firstPhone = orders[0].phone_number || '';
            setQuickResult(`Phone: ${firstPhone} | Waiting for OTP...`, false);
        })
        .catch(() => {
            setQuickResult('Purchase failed. Please try again.', true);
        })
        .finally(() => {
            quickPurchaseBtn.disabled = false;
        });
}

if (resultList) {
    resultList.addEventListener('click', (event) => {
        const orderBtn = event.target.closest('.country-order');
        if (!orderBtn) {
            return;
        }
        const countryId = orderBtn.dataset.countryId || '';
        const countryName = orderBtn.dataset.countryName || '';
        if (countryId) {
            setCountrySelection(countryId, findCountryNameById(countryId, countryName));
        }
        handleQuickPurchase();
    });
}

document.querySelectorAll('.dropdown').forEach((dropdown) => {
    setupDropdown(dropdown);
    dropdown.addEventListener('dropdown:change', (event) => {
        if (dropdown.dataset.dropdown === 'service') {
            loadPricing(event.detail.value);
        }
        if (dropdown.dataset.dropdown === 'country') {
            const selectedId = event.detail.value;
            const match = lastPricing.find((item) => String(item.country_id) === String(selectedId));
            if (match) {
                currentMinPrice = Number(match.min_price || 0);
                currentMaxPrice = Number(match.max_price || 0);
                updatePriceRangeForQuantity();
            }
            if (match) {
                refreshStock(match.country_id, match.stock);
            } else {
                currentMinPrice = null;
                currentMaxPrice = null;
                updatePriceRangeForQuantity();
                if (stockValue) {
                    stockValue.value = 0;
                }
                if (selectedId) {
                    refreshStock(selectedId, 0);
                }
            }
        }
    });
});

window.addEventListener('click', (event) => {
    if (!event.target.closest('.dropdown')) {
        closeAllDropdowns();
    }
});

if (serviceInput && serviceInput.value) {
    loadPricing(serviceInput.value);
}

if (countryList) {
    fetchAllCountries();
}

if (quickPurchaseBtn) {
    quickPurchaseBtn.addEventListener('click', handleQuickPurchase);
}

if (quickQuantity) {
    quickQuantity.addEventListener('input', updatePriceRangeForQuantity);
}

if (pendingTable) {
    pendingTable.addEventListener('click', (event) => {
        const target = event.target.closest('.pending-refund');
        if (!target) {
            return;
        }
        const row = target.closest('.table-row');
        if (!row) {
            return;
        }
        if (target.dataset.refundLocked === '1') {
            const remaining = getRemainingSeconds(row, 1200);
            if (remaining > 900) {
                setQuickResult(`Refund available in ${remaining - 900}s.`, true);
                return;
            }
            target.dataset.refundLocked = '0';
        }
        const orderId = row.dataset.providerOrderId;
        const localId = row.dataset.orderId;
        target.disabled = true;
        const endpoint = orderId ? `${baseUrl}/api/smspool-cancel-sms` : `${baseUrl}/api/smspool-cancel-local`;
        const payload = orderId ? { order_id: orderId, local_id: localId } : { local_id: localId };
        postForm(endpoint, payload)
            .then((text) => (text ? parseJson(text) : null))
            .then((payload) => {
                if (!payload) {
                    setQuickResult('Refund failed. Please try again.', true);
                    return;
                }
                if (payload.error) {
                    setQuickResult(payload.error, true);
                    return;
                }
                if (!payload.data) {
                    setQuickResult('Refund failed. Please try again.', true);
                    return;
                }
                if (payload.data.success) {
                    row.remove();
                    ensureEmptyRow();
                    setQuickResult(payload.data.message || 'Refunded.', false);
                } else {
                    setQuickResult(payload.data.message || 'Refund failed.', true);
                }
            })
            .catch(() => {
                setQuickResult('Refund failed. Please try again.', true);
            })
            .finally(() => {
                target.disabled = false;
            });
    });
}

if (pendingBody) {
    pendingBody.querySelectorAll('.table-row').forEach((row) => {
        if (row.classList.contains('pending-empty')) {
            return;
        }
        if (row.dataset.status && row.dataset.status !== 'pending') {
            return;
        }
        if (row.querySelector('.pending-code') && row.querySelector('.pending-code').textContent.includes('OTP:')) {
            return;
        }
        const createdAtRaw = row.dataset.createdAtEpoch;
        const createdAtSeconds = createdAtRaw ? Number(createdAtRaw) : Math.floor(Date.now() / 1000);
        const remainingRaw = row.dataset.remaining;
        const remainingSeconds = remainingRaw ? Number(remainingRaw) : NaN;
        const loadedAtRaw = row.dataset.loadedAtEpoch;
        const loadedAtSeconds = loadedAtRaw ? Number(loadedAtRaw) : NaN;
        startRefundCountdown(row, 1200, 900, createdAtSeconds, remainingSeconds, loadedAtSeconds);
        startPendingRowPolling(row);
    });
    pollPendingRowsOnce();
    setInterval(pollPendingRowsOnce, 8000);
}

document.querySelectorAll('[data-copy-target]').forEach((button) => {
    button.addEventListener('click', () => {
        const targetId = button.dataset.copyTarget;
        const target = targetId ? document.getElementById(targetId) : null;
        if (!target) {
            return;
        }
        const value = target.value || target.textContent || '';
        if (!value) {
            return;
        }
        navigator.clipboard.writeText(value).then(() => {
            button.textContent = 'Copied';
            setTimeout(() => {
                button.textContent = 'Copy';
            }, 1200);
        }).catch(() => {});
    });
});

function showAlertToasts() {
    const alerts = Array.from(document.querySelectorAll('.alert'));
    if (!alerts.length) {
        return;
    }
    const container = ensureToastContainer();
    alerts.forEach((alert) => {
        if (alert.closest('.toast-container')) {
            return;
        }
        alert.classList.add('toast');
        container.appendChild(alert);
        requestAnimationFrame(() => {
            alert.classList.add('show');
        });
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showAlertToasts);
} else {
    showAlertToasts();
}

document.querySelectorAll('.guide-delete-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const confirmed = window.confirm('Delete this guide? This cannot be undone.');
        if (!confirmed) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('.editor').forEach((editor) => {
    const body = editor.querySelector('[data-editor]');
    const input = editor.querySelector('.editor-input');
    const toolbar = editor.querySelector('[data-editor-toolbar]');
    const form = editor.closest('form');
    const upload = form ? form.querySelector('[data-editor-upload]') : null;
    const previews = form ? form.querySelector('[data-editor-previews]') : null;
    const names = form ? form.querySelector('[data-editor-names]') : null;
    const sizeSelect = toolbar ? toolbar.querySelector('[data-img-size]') : null;
    if (!body || !input || !toolbar) {
        return;
    }
    const syncInput = () => {
        input.value = body.innerHTML.trim();
    };
    syncInput();
    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cmd]');
        if (!button) {
            return;
        }
        const cmd = button.dataset.cmd;
        const value = button.value || null;
        document.execCommand(cmd, false, value);
        body.focus();
        syncInput();
    });
    toolbar.querySelectorAll('select[data-cmd]').forEach((select) => {
        select.addEventListener('change', () => {
            const cmd = select.dataset.cmd;
            const value = select.value;
            document.execCommand(cmd, false, value);
            body.focus();
            syncInput();
        });
    });
    body.addEventListener('input', syncInput);
    body.addEventListener('click', (event) => {
        const img = event.target.closest('img');
        if (img && body.contains(img)) {
            body.querySelectorAll('img').forEach((node) => node.classList.remove('editor-image-selected'));
            img.classList.add('editor-image-selected');
        }
    });
    if (sizeSelect) {
        sizeSelect.addEventListener('change', () => {
            const selected = body.querySelector('img.editor-image-selected');
            if (!selected) {
                return;
            }
            const value = sizeSelect.value;
            if (!value) {
                return;
            }
            selected.style.width = `${value}%`;
            selected.style.height = 'auto';
            syncInput();
        });
    }
    const removeBtn = toolbar ? toolbar.querySelector('[data-img-remove]') : null;
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            const selected = body.querySelector('img.editor-image-selected');
            if (!selected) {
                return;
            }
            selected.remove();
            syncInput();
        });
    }
    if (form) {
        form.addEventListener('submit', syncInput);
    }

    if (upload && previews) {
        upload.addEventListener('change', () => {
            const dataTransfer = upload._dataTransfer || new DataTransfer();
            const incoming = Array.from(upload.files || []);
            incoming.forEach((file) => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                dataTransfer.items.add(file);
            });
            upload._dataTransfer = dataTransfer;
            upload.files = dataTransfer.files;
            previews.innerHTML = '';
            if (names) {
                names.innerHTML = '';
            }
            body.querySelectorAll('img[data-upload-index]').forEach((img) => img.remove());
            const files = Array.from(dataTransfer.files || []);
            files.forEach((file, index) => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                if (names) {
                    const nameItem = document.createElement('div');
                    nameItem.className = 'image-name';
                    nameItem.textContent = file.name;
                    names.appendChild(nameItem);
                }
                const objectUrl = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = objectUrl;
                previews.appendChild(img);
                const html = `<img src="${objectUrl}" data-upload-index="${index}" alt="Guide image">`;
                body.focus();
                if (document.queryCommandSupported && document.queryCommandSupported('insertHTML')) {
                    document.execCommand('insertHTML', false, html);
                } else {
                    body.insertAdjacentHTML('beforeend', html);
                }
                syncInput();
            });
        });
    }
});

const previewModal = document.getElementById('guide-preview-modal');
if (previewModal) {
    const previewFrame = previewModal.querySelector('.preview-frame');
    document.addEventListener('click', (event) => {
        const guideToggle = event.target.closest('[data-guide-toggle]');
        if (guideToggle) {
            const item = guideToggle.closest('.guide-item');
            if (!item) {
                return;
            }
            const editPanel = item.querySelector('.guide-edit');
            if (!editPanel) {
                return;
            }
            editPanel.hidden = !editPanel.hidden;
            return;
        }
        const previewBtn = event.target.closest('[data-preview-url]');
        if (previewBtn && previewFrame) {
            previewFrame.src = previewBtn.dataset.previewUrl || '';
            previewModal.classList.add('show');
            previewModal.setAttribute('aria-hidden', 'false');
            return;
        }
        if (event.target.closest('[data-preview-close]')) {
            previewModal.classList.remove('show');
            previewModal.setAttribute('aria-hidden', 'true');
            if (previewFrame) {
                previewFrame.src = '';
            }
        }
    });
}

const tosModal = document.getElementById('tos-modal');
if (tosModal) {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-tos-close]')) {
            return;
        }
        tosModal.classList.remove('show');
        tosModal.setAttribute('aria-hidden', 'true');
    });
}

const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
if (sidebarToggle) {
    const root = document.body;
    const storageKey = 'sidebarCollapsed';
    const applyState = (collapsed) => {
        root.classList.toggle('sidebar-collapsed', collapsed);
    };
    const saved = localStorage.getItem(storageKey);
    applyState(saved === '1');
    sidebarToggle.addEventListener('click', () => {
        const isCollapsed = root.classList.toggle('sidebar-collapsed');
        localStorage.setItem(storageKey, isCollapsed ? '1' : '0');
    });
}

const mobileSidebarToggle = document.querySelector('[data-mobile-sidebar]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
const closeMobileSidebar = () => {
    document.body.classList.remove('sidebar-open');
};

if (mobileSidebarToggle) {
    mobileSidebarToggle.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-open');
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeMobileSidebar);
}

document.querySelectorAll('.sidebar .nav-item').forEach((link) => {
    link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 900px)').matches) {
            closeMobileSidebar();
        }
    });
});

const particleCanvas = document.getElementById('landing-particles');
if (particleCanvas) {
    const ctx = particleCanvas.getContext('2d');
    const colors = ['rgba(76, 130, 255, 0.85)', 'rgba(255, 126, 86, 0.85)', 'rgba(140, 92, 255, 0.8)'];
    let particles = [];
    const density = 240;

    const resize = () => {
        particleCanvas.width = window.innerWidth;
        particleCanvas.height = window.innerHeight;
        particles = Array.from({ length: density }, () => ({
            x: Math.random() * particleCanvas.width,
            y: Math.random() * particleCanvas.height,
            r: Math.random() * 3.4 + 1.2,
            vx: (Math.random() - 0.5) * 0.3,
            vy: (Math.random() - 0.5) * 0.3,
            color: colors[Math.floor(Math.random() * colors.length)],
        }));
    };

    const step = () => {
        ctx.clearRect(0, 0, particleCanvas.width, particleCanvas.height);
        particles.forEach((p) => {
            p.x += p.vx;
            p.y += p.vy;
            if (p.x < -10) p.x = particleCanvas.width + 10;
            if (p.x > particleCanvas.width + 10) p.x = -10;
            if (p.y < -10) p.y = particleCanvas.height + 10;
            if (p.y > particleCanvas.height + 10) p.y = -10;
            ctx.beginPath();
            ctx.fillStyle = p.color;
            ctx.shadowColor = p.color;
            ctx.shadowBlur = 8;
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        });
        ctx.shadowBlur = 0;
        requestAnimationFrame(step);
    };

    resize();
    window.addEventListener('resize', resize);
    requestAnimationFrame(step);
}

const revealTargets = document.querySelectorAll('.landing-section, .feature-card, .usecase-card, .flow-card, .pricing-card, .review-card, .faq-card, .landing-cta');
if (revealTargets.length) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    revealTargets.forEach((target) => {
        target.classList.add('reveal');
        observer.observe(target);
    });
}

const contentArea = document.querySelector('.content');
if (contentArea) {
    const spaEnabled = false;
    if (spaEnabled) {
        const baseUrl = (window.APP_BASE_URL || '').replace(/\/$/, '');
        const normalizePath = (path) => {
            const trimmed = path.replace(/\/$/, '');
            return trimmed === '' ? '/' : trimmed;
        };
        const stripBase = (path) => {
            if (baseUrl && path.startsWith(baseUrl)) {
                return path.slice(baseUrl.length) || '/';
            }
            return path;
        };
        const setActiveLink = (path) => {
            const current = normalizePath(stripBase(path));
            document.querySelectorAll('.sidebar .nav-item').forEach((link) => {
                const href = link.getAttribute('href') || '';
                if (!href) return;
                const linkUrl = new URL(href, window.location.origin);
                const linkPath = normalizePath(stripBase(linkUrl.pathname));
                link.classList.toggle('active', linkPath === current);
            });
        };
        const fetchAndSwap = async (url, pushState = true) => {
            document.body.classList.add('page-loading');
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
                if (!response.ok) {
                    window.location.href = url;
                    return;
                }
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextContent = doc.querySelector('.content');
                if (!nextContent) {
                    window.location.href = url;
                    return;
                }
                contentArea.innerHTML = nextContent.innerHTML;
                if (doc.title) {
                    document.title = doc.title;
                }
                if (pushState) {
                    history.pushState(null, '', url);
                }
                setActiveLink(window.location.pathname);
            } catch (error) {
                window.location.href = url;
            } finally {
                requestAnimationFrame(() => {
                    document.body.classList.remove('page-loading');
                });
            }
        };

        document.addEventListener('click', (event) => {
            const link = event.target.closest('.sidebar .nav-item');
            if (!link) return;
            if (event.defaultPrevented || event.button !== 0) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const href = link.getAttribute('href');
            if (!href) return;
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            event.preventDefault();
            fetchAndSwap(url.href, true);
        });

        window.addEventListener('popstate', () => {
            fetchAndSwap(window.location.href, false);
        });
    }
}
