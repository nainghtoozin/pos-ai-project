const searchInput = document.getElementById('product-search');
const tableBody = document.getElementById('products-table');
const paginationContainer = document.getElementById('products-pagination');

if (searchInput && tableBody && paginationContainer) {
    const searchUrl = searchInput.dataset.searchUrl;
    const baseUrl = searchInput.dataset.productsBase;
    const canEdit = searchInput.dataset.canEdit === '1';
    const canDelete = searchInput.dataset.canDelete === '1';

    const debounce = (fn, delay = 400) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(null, args), delay);
        };
    };

    const encodePayload = (payload) => {
        const json = JSON.stringify(payload);
        const bytes = new TextEncoder().encode(json);
        let binary = '';
        bytes.forEach((byte) => {
            binary += String.fromCharCode(byte);
        });
        return window.btoa(binary);
    };

    const decodePayload = (value) => {
        if (!value) return null;
        try {
            const binary = window.atob(value);
            const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
            const json = new TextDecoder().decode(bytes);
            return JSON.parse(json);
        } catch (error) {
            console.error('Unable to decode product payload', error);
            return null;
        }
    };

    const renderRows = (products) => {
        if (!products.length) {
            tableBody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-gray-500">No products found.</td></tr>';
            return;
        }

        tableBody.innerHTML = products
            .map((product) => {
                const payload = encodePayload({
                    name: product.name,
                    sku: product.sku,
                    barcode: product.barcode,
                    category: product.category?.name ?? null,
                    brand: product.brand?.name ?? null,
                    unit: product.unit?.name ?? null,
                    stock: Number(product.stock?.quantity ?? 0).toFixed(2),
                    image_url: product.image_url,
                    description: product.description,
                    status: product.is_active ? 'Active' : 'Inactive',
                });

                const imageCell = product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" class="h-full w-full object-cover">`
                    : '<div class="flex h-full w-full items-center justify-center text-xs text-gray-400">No Image</div>';

                const editButton = canEdit
                    ? `<a href="${baseUrl}/${product.id}/edit" class="rounded-full border border-gray-200 p-2 text-gray-600 hover:border-indigo-200 hover:text-indigo-600">
                            <span class="sr-only">Edit</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232a2.5 2.5 0 1 1 3.536 3.536L8.5 19.036l-4 1 1-4 9.732-10.804z" />
                            </svg>
                       </a>`
                    : '';

                const deleteButton = canDelete
                    ? `<button type="button" class="rounded-full border border-gray-200 p-2 text-gray-600 hover:border-rose-200 hover:text-rose-600" data-action="delete" data-delete-url="${baseUrl}/${product.id}" data-delete-name="${product.name}">
                            <span class="sr-only">Delete</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 6h-15m3 0V4.5A1.5 1.5 0 0 1 9 3h6a1.5 1.5 0 0 1 1.5 1.5V6m1.5 0v12.75A1.25 1.25 0 0 1 16.75 20H7.25A1.25 1.25 0 0 1 6 18.75V6" />
                            </svg>
                       </button>`
                    : '';

                return `
                    <tr>
                        <td class="px-6 py-4">
                            <div class="h-14 w-14 overflow-hidden rounded-lg bg-gray-100">${imageCell}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">${product.name}</div>
                            <div class="text-xs text-gray-400">#${product.id}</div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs uppercase text-gray-600">${product.sku}</td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-600">${product.barcode}</td>
                        <td class="px-6 py-4">${product.category?.name ?? '--'}</td>
                        <td class="px-6 py-4">${product.brand?.name ?? '--'}</td>
                        <td class="px-6 py-4 font-semibold">${Number(product.stock?.quantity ?? 0).toFixed(2)}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${product.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}">${product.is_active ? 'Active' : 'Inactive'}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" class="rounded-full border border-gray-200 p-2 text-gray-600 hover:border-indigo-200 hover:text-indigo-600" data-action="view" data-product="${payload}">
                                    <span class="sr-only">View</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12 18 19.5 12 19.5 2.25 12 2.25 12z" />
                                        <circle cx="12" cy="12" r="2.25" />
                                    </svg>
                                </button>
                                ${editButton}
                                ${deleteButton}
                            </div>
                        </td>
                    </tr>
                `;
            })
            .join('');
    };

    const renderPagination = (links) => {
        if (!Array.isArray(links)) {
            paginationContainer.innerHTML = '';
            return;
        }

        paginationContainer.innerHTML = `
            <div class="flex flex-wrap gap-2">
                ${links
                    .map((link) => {
                        const disabled = !link.url;
                        const active = link.active;
                        const classes = [
                            'rounded-xl px-4 py-2 text-sm font-semibold transition',
                            active ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                            disabled ? 'opacity-50 cursor-not-allowed' : '',
                        ].join(' ');

                        return `<a href="${link.url ?? '#'}" class="${classes}" data-pagination-link ${disabled ? 'aria-disabled="true"' : ''}>${link.label}</a>`;
                    })
                    .join('')}
            </div>
        `;
    };

    const fetchProducts = async (url) => {
        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Unable to fetch products');
            }

            const data = await response.json();
            renderRows(data.data ?? []);
            renderPagination(data.links ?? data.meta?.links ?? []);
        } catch (error) {
            console.error(error);
        }
    };

    const buildUrl = (query) => {
        if (!query) {
            return searchUrl;
        }

        const params = new URLSearchParams({ search: query });
        return `${searchUrl}?${params.toString()}`;
    };

    searchInput.addEventListener(
        'input',
        debounce((event) => {
            const value = event.target.value.trim();
            fetchProducts(buildUrl(value));
        }),
    );

    paginationContainer.addEventListener('click', (event) => {
        const link = event.target.closest('[data-pagination-link]');
        if (!link || link.getAttribute('aria-disabled')) {
            return;
        }

        event.preventDefault();
        const url = link.getAttribute('href');
        if (url) {
            fetchProducts(url);
        }
    });

    document.addEventListener('click', (event) => {
        const actionBtn = event.target.closest('[data-action]');
        if (!actionBtn) {
            return;
        }

        const action = actionBtn.dataset.action;

        if (action === 'view') {
            const payload = decodePayload(actionBtn.dataset.product);
            if (payload) {
                window.dispatchEvent(new CustomEvent('product-view', { detail: payload }));
            }
        }

        if (action === 'delete') {
            const detail = {
                url: actionBtn.dataset.deleteUrl,
                name: actionBtn.dataset.deleteName,
            };
            window.dispatchEvent(new CustomEvent('product-delete', { detail }));
        }
    });
}
