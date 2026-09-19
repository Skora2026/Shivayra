        // SWIPER INIT
        // Initialize thumbSwiper first — loop only makes sense with enough
        // slides to fill a viewport (gallery is usually 1–4 images).
        if (!window.thumbSwiper) {
            const thumbCount = document.querySelectorAll(".thumbSwiper .swiper-slide").length;
            window.thumbSwiper = new Swiper(".thumbSwiper", {
                slidesPerView: 4,
                spaceBetween: 10,
                loop: thumbCount > 4,
                navigation: thumbCount > 4 ? {
                    nextEl: ".thumbSwiper .swiper-button-next",
                    prevEl: ".thumbSwiper .swiper-button-prev"
                } : false,
                breakpoints: {
                    0: {
                        slidesPerView: 3
                    },
                    768: {
                        slidesPerView: 4
                    }
                }
            });
        }

        // Update main image on thumbnail click
        document.querySelectorAll('.thumbSwiper img').forEach(img => {
            img.onclick = () => {
                document.getElementById('mainImg').src = img.src;
                document.querySelectorAll('.thumbSwiper img').forEach(i => i.classList.remove('active'));
                img.classList.add('active');
            }
        });

        // Update main image on slide change (for desktop next/prev)
        if (window.thumbSwiper) {
            window.thumbSwiper.on('slideChange', () => {
                const activeSlide = window.thumbSwiper.slides[window.thumbSwiper.activeIndex];
                const activeImg = activeSlide ? activeSlide.querySelector('img') : null;
                if (activeImg) {
                    document.getElementById('mainImg').src = activeImg.src;

                    // Update active class
                    document.querySelectorAll('.thumbSwiper img').forEach(i => i.classList.remove('active'));
                    activeImg.classList.add('active');
                }
            });
        }

        // MOBILE MAIN SWIPER
        if (!window.mainSwiper) {
            const mainCount = document.querySelectorAll(".mainSwiper .swiper-slide").length;
            window.mainSwiper = new Swiper(".mainSwiper", {
                slidesPerView: 1,
                spaceBetween: 10,
                loop: mainCount > 1,
                navigation: mainCount > 1 ? {
                    nextEl: ".mainSwiper .swiper-button-next",
                    prevEl: ".mainSwiper .swiper-button-prev"
                } : false
            });
        }

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