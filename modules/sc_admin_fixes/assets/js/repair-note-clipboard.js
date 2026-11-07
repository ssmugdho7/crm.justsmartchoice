(() => {
    'use strict';

    if (typeof DataTransfer !== 'function') return;

    const imageTypes = new Set(['image/png', 'image/jpeg', 'image/webp', 'image/gif']);
    for (const input of document.querySelectorAll('input[type="file"][name="attachments[]"][multiple]')) {
        const form = input.form;
        if (!form || !/\/development\/note\/\d+\/?$/.test(new URL(form.action).pathname)) continue;

        const pasteArea = document.createElement('div');
        pasteArea.className = 'form-control tw-mt-2';
        pasteArea.tabIndex = 0;
        pasteArea.textContent = 'Click here and paste an image (Ctrl+V or ⌘V).';
        const status = document.createElement('p');
        status.className = 'text-muted tw-mt-2';
        status.setAttribute('role', 'status');
        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'btn btn-default btn-xs tw-mb-2';
        clear.textContent = 'Clear attachments';
        clear.hidden = true;
        input.after(pasteArea, status, clear);

        let picked = Array.from(input.files);
        let pasted = [];

        function updateFiles() {
            const transfer = new DataTransfer();
            for (const file of [...picked, ...pasted]) transfer.items.add(file);
            input.files = transfer.files;
            const count = input.files.length;
            clear.hidden = count === 0;
            status.textContent = count
                ? `${count} attachment${count === 1 ? '' : 's'} ready. Add a work note or next step, then save the update.`
                : '';
        }

        input.addEventListener('change', () => {
            picked = Array.from(input.files);
            updateFiles();
        });
        clear.addEventListener('click', () => {
            picked = [];
            pasted = [];
            updateFiles();
            pasteArea.focus();
        });

        form.addEventListener('paste', (event) => {
            const files = Array.from(event.clipboardData?.files || []);
            const images = files.filter(file => imageTypes.has(file.type) && file.size > 0);
            if (!images.length) {
                if (files.some(file => file.type.startsWith('image/'))) {
                    status.textContent = 'Paste a PNG, JPEG, WebP, or GIF image.';
                }
                return;
            }
            pasted.push(...images);
            updateFiles();
            event.preventDefault();
        });
    }
})();
