{{-- Drag & drop for every admin image input.
     Included once from admin/layouts/app.blade.php, so any upload field on any
     admin page is a drop target without per-page wiring.

     Targets, either of:
       .image-upload-zone      the big dashed upload boxes
       [data-dropzone]         a bare <input type="file"> marked in place

     After a drop it dispatches a bubbling `change` on the input, exactly as a
     real file pick would. That means whatever the page already wired — an
     inline onchange="previewImage(this)", a listener, nothing at all — runs with
     no coupling to a particular page's functions.

     Optional extras, both driven from the same change event a real pick raises:
       <small data-dropzone-hint>   counts the selected files
       data-preview="#some-img"     shows the newly chosen image in that <img> --}}

<style>
    /* Reached while a file is held over the zone. */
    .image-upload-zone.is-dragover,
    [data-dropzone].is-dragover {
        border-color: #2d6aad;
        background: #e6f1fc;
        box-shadow: 0 0 0 4px rgba(45, 106, 173, 0.12);
    }

    /* Dropped something the input would not have accepted. */
    .image-upload-zone.is-rejected,
    [data-dropzone].is-rejected {
        border-color: #dc3545;
        background: #fdf2f3;
    }

    [data-dropzone-hint] {
        color: #2d6aad;
        font-size: 0.75rem;
    }
</style>

<script>
    (function () {
        'use strict';

        const ZONE = '.image-upload-zone, [data-dropzone]';

        function zoneFor(target) {
            return target && target.closest ? target.closest(ZONE) : null;
        }

        function inputFor(zone) {
            return zone.matches('input[type="file"]') ? zone : zone.querySelector('input[type="file"]');
        }

        function hintFor(input) {
            const host = input.parentElement;
            return host ? host.querySelector('[data-dropzone-hint]') : null;
        }

        // Inputs with no preview of their own (bare file fields) can't show the
        // browser's "No file chosen" text after a programmatic drop, so report
        // the count ourselves.
        function updateHint(input) {
            const hint = hintFor(input);
            if (!hint) return;

            const count = input.files ? input.files.length : 0;
            hint.textContent = count
                ? count + (count === 1 ? ' image ready to upload' : ' images ready to upload')
                : '';
            hint.style.display = count ? 'block' : 'none';
        }

        // Optional thumbnail: an input can name an <img> to fill with the file
        // that was just chosen, whether by picker or by drop.
        function updatePreview(input) {
            const selector = input.getAttribute('data-preview');
            if (!selector) return;

            const file = input.files && input.files[0];
            const img = document.querySelector(selector);
            if (!img || !file) return;

            // Release the previous URL so repeated picks don't leak.
            if (img.dataset.objectUrl) URL.revokeObjectURL(img.dataset.objectUrl);

            const url = URL.createObjectURL(file);
            img.dataset.objectUrl = url;
            img.src = url;

            const wrap = img.closest('[data-preview-wrap]');
            if (wrap) {
                wrap.style.display = '';
            } else {
                img.style.display = '';
            }
        }

        // A drop landing outside a zone would make the browser navigate to the
        // file and throw the half-filled form away.
        ['dragover', 'drop'].forEach(function (type) {
            window.addEventListener(type, function (e) {
                if (!zoneFor(e.target)) e.preventDefault();
            });
        });

        // dragenter/dragleave also fire while crossing child elements, so count
        // enter/leave pairs instead of toggling the highlight on every event.
        const depth = new WeakMap();

        function bump(zone, delta) {
            const next = Math.max(0, (depth.get(zone) || 0) + delta);
            depth.set(zone, next);
            zone.classList.toggle('is-dragover', next > 0);
        }

        document.addEventListener('dragenter', function (e) {
            const zone = zoneFor(e.target);
            if (!zone) return;
            e.preventDefault();
            bump(zone, 1);
        });

        document.addEventListener('dragover', function (e) {
            const zone = zoneFor(e.target);
            if (!zone) return;
            e.preventDefault();
            if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
        });

        document.addEventListener('dragleave', function (e) {
            const zone = zoneFor(e.target);
            if (zone) bump(zone, -1);
        });

        document.addEventListener('drop', function (e) {
            const zone = zoneFor(e.target);
            if (!zone) return;
            e.preventDefault();

            depth.set(zone, 0);
            zone.classList.remove('is-dragover');

            const input = inputFor(zone);
            if (!input) return;

            const dropped = Array.from((e.dataTransfer && e.dataTransfer.files) || []);

            // accept="image/*" doesn't apply to a programmatically built
            // FileList, so filter here to match what the picker would allow.
            const images = dropped.filter(function (file) {
                return file.type.indexOf('image/') === 0;
            });

            if (!images.length) {
                if (dropped.length) {
                    zone.classList.add('is-rejected');
                    setTimeout(function () { zone.classList.remove('is-rejected'); }, 1200);
                }
                return;
            }

            const chosen = input.multiple ? images : images.slice(0, 1);
            const dt = new DataTransfer();
            chosen.forEach(function (file) { dt.items.add(file); });
            input.files = dt.files;

            // Behave like a real selection, so the page's own preview or gallery
            // handler runs without this script needing to know about it.
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // One place keeps hints and thumbnails in step, for drops and for real
        // picks alike.
        document.addEventListener('change', function (e) {
            const input = e.target;
            if (!input || !input.matches || !input.matches('input[type="file"]')) return;

            updateHint(input);
            updatePreview(input);
        });

        // Pages that rewrite input.files themselves — a preview grid dropping one
        // pending file, say — can ask for the feedback to be recalculated. Those
        // edits raise no change event of their own, so the count would go stale.
        // Safe to call with any file input: a no-op when there is nothing to show.
        window.refreshUploadFeedback = function (input) {
            if (!input || !input.matches || !input.matches('input[type="file"]')) return;

            updateHint(input);
            updatePreview(input);
        };
    })();
</script>
