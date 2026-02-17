import 'trix/dist/trix.css';
import 'trix';

document.addEventListener('trix-file-accept', (event) => {
    event.preventDefault();
});

document.addEventListener('DOMContentLoaded', () => {
    const generateButton = document.getElementById('generate-sku');
    const skuField = document.getElementById('sku');
    const nameField = document.getElementById('name');

    if (generateButton && skuField) {
        generateButton.addEventListener('click', () => {
            const source = (nameField?.value || 'SKU').replace(/[^a-zA-Z0-9]/g, '').slice(0, 6).toUpperCase();
            const suffix = Math.floor(Math.random() * 9999)
                .toString()
                .padStart(4, '0');
            skuField.value = `${source}${suffix}`;
            skuField.dispatchEvent(new Event('input'));
        });
    }
});
