        // Gallery swiper init + thumb handlers live in product-detail.blade.php.
        // This file used to duplicate them and raced the tuned config (it won,
        // pinning speed/loop defaults and bypassing the crossfade), so the
        // duplicates were removed — one source of truth for the gallery.

        // COLOR & SIZE ACTIVE STATES
        document.querySelectorAll('.color-dot').forEach(c => {
            c.onclick = () => {
                document.querySelectorAll('.color-dot').forEach(x => x.classList.remove('active'));
                c.classList.add('active');
            }
        });
        document.querySelectorAll('.size-btn').forEach(s => {
            s.onclick = () => {
                document.querySelectorAll('.size-btn').forEach(x => x.classList.remove('active'));
                s.classList.add('active');
            }
        });