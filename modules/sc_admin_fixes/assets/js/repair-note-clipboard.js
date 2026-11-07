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
        const previews = document.createElement('ul');
        previews.className = 'list-unstyled';
        previews.setAttribute('aria-label', 'Attachment previews');
        Object.assign(previews.style, {display: 'flex', flexWrap: 'wrap', gap: '12px'});
        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'btn btn-default btn-xs tw-mb-2';
        clear.textContent = 'Clear attachments';
        clear.style.display = 'none';
        input.after(pasteArea, status, previews, clear);

        let picked = Array.from(input.files);
        let pasted = [];
        const previewUrls = [];

        function renderPreviews(files) {
            previews.replaceChildren();
            for (const url of previewUrls.splice(0)) URL.revokeObjectURL(url);

            for (const file of files) {
                const item = document.createElement('li');
                Object.assign(item.style, {width: '180px', maxWidth: '100%', overflowWrap: 'anywhere'});
                const isImage = imageTypes.has(file.type);
                const isVideo = ['video/mp4', 'video/webm', 'video/quicktime'].includes(file.type);
                if (isImage || isVideo) {
                    const media = document.createElement(isImage ? 'img' : 'video');
                    Object.assign(media.style, {display: 'block', width: '100%', height: '112px', objectFit: 'contain'});
                    if (isImage) {
                        media.alt = 'Preview of ' + file.name;
                    } else {
                        media.controls = true;
                        media.preload = 'metadata';
                        media.setAttribute('aria-label', 'Preview of ' + file.name);
                    }
                    media.addEventListener('error', () => media.remove(), {once: true});
                    const url = URL.createObjectURL(file);
                    previewUrls.push(url);
                    media.src = url;
                    item.append(media);
                }
                const caption = document.createElement('span');
                caption.textContent = file.name;
                item.append(caption);
                previews.append(item);
            }
        }

        function updateFiles() {
            const transfer = new DataTransfer();
            for (const file of [...picked, ...pasted]) transfer.items.add(file);
            input.files = transfer.files;
            renderPreviews(input.files);
            const count = input.files.length;
            clear.style.display = count ? '' : 'none';
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
