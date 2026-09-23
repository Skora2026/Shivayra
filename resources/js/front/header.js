// product cards slider js
document.querySelectorAll(".productSwiper").forEach((slider) => {
    const section = slider.closest("section");
    const slideCount = slider.querySelectorAll(".swiper-slide").length;
    // Loop mode needs enough slides to fill one viewport per breakpoint;
    // otherwise Swiper logs a warning and silently disables looping.
    const canLoop = slideCount > 4;

    new Swiper(slider, {
        loop: canLoop,
        slidesPerView: 4,
        spaceBetween: 25,
        grabCursor: true,
        autoHeight: true,

        navigation: canLoop
            ? {
                  nextEl: section.querySelector(".pp-next"),
                  prevEl: section.querySelector(".pp-prev"),
              }
            : false,

        breakpoints: {
            0: {
                slidesPerView: 1,
            },
            576: {
                slidesPerView: 2,
            },
            768: {
                slidesPerView: 3,
            },
            1200: {
                slidesPerView: 4,
            },
        },
    });
});

// ============================================
// ============================================

// ========== COMPLETE Add to Cart==========

document.addEventListener("DOMContentLoaded", function () {
    // ---------- CART LOGIC ----------
    // Source of truth: localStorage for guests; the server cart_items table
    // for logged-in customers (follows the user across devices, survives
    // cache clears). On login the local list is merged into the server rows.
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const IS_AUTH = document.body.dataset.auth === "1";
    let cartBusy = false; // serialize server syncs
    // Authenticated sessions must hydrate the server cart before pushing
    // mutations, or a stale local list could wipe rows added on another
    // device. Guest sessions sync nothing, so they are always "ready".
    let hydrationDone = !IS_AUTH;

    function saveCart() {
        localStorage.setItem("cart", JSON.stringify(cart));
    }

    function cartPayload() {
        return cart.map((p) => ({ id: p.id, qty: p.qty, variantId: p.variantId ?? null }));
    }

    async function serverSync() {
        if (!IS_AUTH || !hydrationDone || cartBusy) return;
        cartBusy = true;
        try {
            await fetch("/cart/sync", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                    "Accept": "application/json",
                },
                body: JSON.stringify({ items: cartPayload() }),
            });
        } catch (err) {
            /* offline: local cart remains the truth, retried on next change */
        } finally {
            cartBusy = false;
        }
    }

    // Login/registration merge: push the pre-login guest items with ADD
    // semantics (server sums quantities, never shrinks or deletes other-device
    // rows), then adopt the merged server cart. This is the hydration step
    // itself, so it must not go through serverSync()'s hydrationDone gate —
    // that gate exists to stop post-hydration pushes from stale lists.
    async function mergeOnLogin() {
        if (!IS_AUTH) return;
        const local = JSON.parse(localStorage.getItem("cart")) || [];
        if (local.length) {
            try {
                await fetch("/cart/sync", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        items: local.map((p) => ({ id: p.id, qty: p.qty, variantId: p.variantId ?? null })),
                        merge: true,
                    }),
                });
            } catch (err) {
                /* offline: adoption below keeps whatever the server has */
            }
        }
        try {
            const res = await fetch("/cart", { headers: { "Accept": "application/json" } });
            if (res.ok) {
                const data = await res.json();
                if (Array.isArray(data.items)) {
                    cart = data.items.map((row) => ({
                        id: row.id,
                        name: row.name,
                        variantId: row.variantId,
                        variantValues: row.variantValues || null,
                        price: parseFloat(row.price) || 0,
                        img: row.img,
                        qty: parseInt(row.qty, 10) || 1,
                    }));
                    saveCart();
                }
            }
        } catch (err) {
            /* offline: keep whatever we have locally */
        }
    }

    // Run once per page load — on authenticated pages this hydrates the
    // server cart (and merges any pre-login local additions), then unlocks
    // mutation syncing. A body flag exposes readiness to tests/scripts.
    if (IS_AUTH) {
        mergeOnLogin().then(() => {
            hydrationDone = true;
            document.body.dataset.cartHydrated = "1";
            updateCart();

            // Fresh device + direct /checkout visit: checkout.js rendered its
            // empty-state before the server cart arrived. Once hydration has
            // actually delivered items, reload so the page re-reads them.
            if (
                cart.length > 0 &&
                window.location.pathname === "/checkout" &&
                !document.getElementById("checkoutForm")
            ) {
                window.location.reload();
            }
        });
    }

    function updateCart() {
        let html = "";
        let total = 0;

        cart.forEach((p, i) => {
            let sub = p.price * p.qty;
            total += sub;
            html += `<div class="cart-item"> 
                        <img src="${p.img}">
                        <div>
                            <strong>${p.name}</strong>
                            ${p.variantValues ? `<div class="text-muted" style="font-size:0.75rem; line-height: 1.2;"><i class="bi bi-tag-fill me-1"></i>${p.variantValues}</div>` : ''}
                            <div style="font-size:0.85rem; margin-top:2px;">₹${p.price} x ${p.qty} = ₹${sub.toFixed(2)}</div>
                            <div class="qty"> 
                                <button onclick="window.qty(${i},-1)">-</button>
                                ${p.qty}
                                <button onclick="window.qty(${i},1)">+</button>
                                <button onclick="window.removeItem(${i})"><i class="bi bi-trash3-fill"></i></button>
                            </div>
                        </div>
                    </div>`;
        });

        let cartContainer = document.getElementById("cartItems");
        if (cartContainer)
            cartContainer.innerHTML =
                html ||
                `<div class='text-center py-4'>
                    <img src='/images/empty-state.png' alt='' width='150' class='mb-2'>
                    <p class='mb-2'>Your cart is empty</p>
                    <a href='/products' class='btn btn-gold btn-sm px-3'>Shop Now</a>
                </div>`;

        let totalSpan = document.getElementById("cartTotal");
        if (totalSpan) totalSpan.innerText = total.toFixed(2);

        let totalQty = cart.reduce((a, b) => a + b.qty, 0);
        let lcart = document.getElementById("lcartCount");
        let mcart = document.getElementById("mcartCount");
        if (lcart) lcart.innerText = totalQty;
        if (mcart) mcart.innerText = totalQty;

        // Hide the badge while the count is zero (no visual noise)
        for (const el of [lcart, mcart]) {
            if (el) el.style.visibility = totalQty > 0 ? "visible" : "hidden";
        }
    }

    // Global functions for cart (so HTML onclick works)
    window.qty = function (i, v) {
        if (!cart[i]) return;
        cart[i].qty += v;
        if (cart[i].qty <= 0) cart.splice(i, 1);
        saveCart();
        updateCart();
        serverSync();
    };

    window.removeItem = function (i) {
        cart.splice(i, 1);
        saveCart();
        updateCart();
        serverSync();
    };

    function toast() {
        let t = document.getElementById("toast");
        if (t) {
            t.classList.add("show");
            setTimeout(() => t.classList.remove("show"), 1000);
        }
    }

    // Cart & wishlist clicks are delegated ONCE at document level. Per-node
    // listeners were silently lost on Swiper-managed slides (cloning/reordering
    // replaces nodes), which left slider card buttons dead.
    document.addEventListener("click", function (e) {
        const cartBtn = e.target.closest(".add-to-cart");
        if (cartBtn) {
            cartClickHandler.call(cartBtn, e);
            return;
        }
        const wishBtn = e.target.closest(".add-to-wishlist");
        if (wishBtn) wishlistClickHandler.call(wishBtn, e);
    });

    function cartClickHandler(e) {
        let btn = this;
        let id = btn.dataset.id;
        let variantId = btn.dataset.variantId || "";
        // Product detail pages provide a quantity selector; cards add one at a time.
        let qtyInput = document.getElementById("qtySelector");
        let addQty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
        let found = cart.find((p) => p.id == id && (p.variantId || "") == variantId);

        if (found) {
            found.qty += addQty;
        } else {
            cart.push({
                id: id,
                name: btn.dataset.name,
                variantId: variantId || null,
                variantValues: btn.dataset.variantValues || null,
                price: parseFloat(btn.dataset.price),
                img: btn.dataset.img,
                qty: addQty,
            });
        }

        btn.innerHTML = '<svg class="icon"><use href="#i-check"/></svg>';
        setTimeout(
            () => (btn.innerHTML = '<i class="bi bi-bag-plus"></i>'),
            700,
        );

        toast();
        saveCart();
        updateCart();
        serverSync();
    }

    // ---------- WISHLIST LOGIC ----------
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

    function saveWishlist() {
        localStorage.setItem("wishlist", JSON.stringify(wishlist));
    }

    function updateWishlistCount() {
        let freshWishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
        let lcounter = document.getElementById("lwishlistCount");
        let mcounter = document.getElementById("mwishlistCount");
        if (lcounter) lcounter.innerText = freshWishlist.length;
        if (mcounter) mcounter.innerText = freshWishlist.length;

        for (const el of [lcounter, mcounter]) {
            if (el) el.style.visibility = freshWishlist.length > 0 ? "visible" : "hidden";
        }
    }

    function updateWishlistIcons() {
        let freshWishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
        document.querySelectorAll(".add-to-wishlist").forEach((btn) => {
            let id = btn.dataset.id;
            let icon = btn.querySelector("i");
            if (freshWishlist.find((p) => p.id == id)) {
                icon.classList.remove("bi-heart");
                icon.classList.add("bi-heart-fill");
            } else {
                icon.classList.remove("bi-heart-fill");
                icon.classList.add("bi-heart");
            }
        });
    }

    function wishToast() {
        let t = document.getElementById("wishToast");
        if (t) {
            t.classList.add("show");
            setTimeout(() => t.classList.remove("show"), 1000);
        }
    }

    function renderWishlist() {
        let wishlistData = JSON.parse(localStorage.getItem("wishlist")) || [];
        let container = document.getElementById("wishlistItems");

        if (!container) return;

        let html = "";
        if (wishlistData.length === 0) {
            html =
                `<div class='col-12 text-center py-5'>
                    <img src='/images/empty-state.png' alt='' width='220' class='mb-3'>
                    <h5 class='mb-2'>Your wishlist is empty</h5>
                    <p class='text-muted mb-3'>Click the heart icon on any product to save it here.</p>
                    <a href='/products' class='btn btn-gold px-4 py-2'>Explore Products</a>
                </div>`;
        } else {
            wishlistData.forEach((p, i) => {
                // Show the original price only when it is a genuine discount,
                // and link quick-view to the real product page.
                const wasPrice = p.was ? parseInt(p.was) : 0;
                const hasDiscount = wasPrice > parseInt(p.price);
                const detailUrl = p.slug ? `/product-detail/${p.slug}` : "#";
                // The badge and image sit inside the link, matching the Blade cards,
                // so clicking the photo opens the product here too.
                html += `<div class="col-md-3 col-6 mb-4">
                            <div class="product-card">
                                <a href="${detailUrl}">
                                    <span class="product-badge hot"><svg class="icon"><use href="#i-heart-fill"/></svg> Wishlist</span>
                                    <div class="product-img">
                                        <img src="${p.img}">
                                    </div>
                                </a>
                                <div class="product-body">
                                    <h6>${p.name}</h6>
                                    <div class="pp-price">
                                        <strong>₹${p.price}</strong>
                                        ${hasDiscount ? `<del>₹${wasPrice}</del>` : ""}
                                    </div>
                                </div>
                                <div class="pp-hover">
                                    <a href="${detailUrl}" class="pp-hover-btn"><i class="bi bi-eye"></i></a>
                                    <button type="button" class="pp-hover-btn" onclick="window.removeWish(${i})">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                    <button type="button" class="pp-hover-btn add-to-cart-wish" 
                                        data-id="${p.id}" data-name="${p.name}" 
                                        data-price="${p.price}" data-img="${p.img}">
                                        <i class="bi bi-bag-plus"></i>
                                    </button>
                                </div>
                              
                            </div>
                        </div>`;
            });
        }
        container.innerHTML = html;

        // Attach events to wishlist cart buttons
        document.querySelectorAll(".add-to-cart-wish").forEach((btn) => {
            btn.addEventListener("click", function (e) {
                let id = this.dataset.id,
                    name = this.dataset.name,
                    price = parseFloat(this.dataset.price),
                    img = this.dataset.img;
                let existing = cart.find((p) => p.id == id);
                if (existing) existing.qty++;
                else cart.push({ id, name, price, img, qty: 1 });
                saveCart();
                updateCart();
                toast();
                this.innerHTML = '<svg class="icon"><use href="#i-check"/></svg>';
                setTimeout(
                    () => (this.innerHTML = '<i class="bi bi-bag-plus"></i>'),
                    700,
                );
            });
        });
    }

    window.removeWish = function (i) {
        let wishlistData = JSON.parse(localStorage.getItem("wishlist")) || [];
        wishlistData.splice(i, 1);
        localStorage.setItem("wishlist", JSON.stringify(wishlistData));
        wishlist = wishlistData;
        renderWishlist();
        updateWishlistCount();
        updateWishlistIcons();
    };

    function wishlistClickHandler(e) {
        let btn = this;
        let id = btn.dataset.id;
        let index = wishlist.findIndex((p) => p.id == id);

        if (index > -1) {
            wishlist.splice(index, 1);
            btn.querySelector("i").classList.remove("bi-heart-fill");
            btn.querySelector("i").classList.add("bi-heart");
        } else {
            wishlist.push({
                id: id,
                name: btn.dataset.name,
                price: btn.dataset.price,
                was: btn.dataset.was || null,
                slug: btn.dataset.slug || null,
                img: btn.dataset.img,
            });
            btn.querySelector("i").classList.remove("bi-heart");
            btn.querySelector("i").classList.add("bi-heart-fill");
        }

        saveWishlist();
        updateWishlistCount();
        renderWishlist();
        wishToast();
    }

    // ---------- CART PANEL TOGGLE ----------
    document.querySelectorAll(".cart-fab").forEach((icon) => {
        icon.addEventListener("click", function () {
            document.getElementById("cartPanel").classList.toggle("open");
        });
    });

    window.closeCart = function () {
        document.getElementById("cartPanel").classList.remove("open");
    };

    // ---------- INITIALIZE EVERYTHING ----------
    function init() {
        // Load fresh data from localStorage
        cart = JSON.parse(localStorage.getItem("cart")) || [];
        wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

        updateCart();
        updateWishlistCount();
        renderWishlist();
        updateWishlistIcons();
        attachImageFallbacks();

        // Authenticated: the fetch in mergeOnLogin() (fired above) resolves
        // and re-renders with the server cart; nothing else needed here.
    }

    // Any product/remote image that fails to load falls back to the local
    // placeholder instead of showing a broken-image icon.
    function attachImageFallbacks() {
        document.querySelectorAll(".product-img img, .cart-item img").forEach((img) => {
            img.addEventListener("error", function () {
                if (this.dataset.fallbackApplied) return;
                this.dataset.fallbackApplied = "1";
                this.src = "/images/placeholder.svg";
            });
        });
    }

    init();

    // Keep heart-icon fill state in sync on Swiper slides and on dynamically
    // rendered nodes (wishlist re-renders). Clicks need no rebinding — they
    // are delegated at document level above.
    const observer = new MutationObserver(function () {
        updateWishlistIcons();
        attachImageFallbacks();
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
// ============================================
// ============================================

// ========== DESKTOP MEGA MENU (Hover + Click — Smooth & Reliable) ==========
(function () {
    const megaDropdown = document.querySelector('.mega-dropdown');
    if (!megaDropdown) return;

    const megaMenu    = megaDropdown.querySelector('.mega-menu');
    const navLink     = megaDropdown.querySelector('a.nav-link');
    if (!megaMenu || !navLink) return;

    let closeTimer  = null;   // timer for hover-close delay
    let clickLocked = false;  // true = menu pinned open by click

    /* ---- Open ---- */
    function openMenu() {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        megaMenu.classList.add('mega-open');
        megaDropdown.classList.add('menu-active');
    }

    /* ---- Close (immediate) ---- */
    function closeMenu() {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        clickLocked = false;
        megaMenu.classList.remove('mega-open');
        megaDropdown.classList.remove('menu-active');
    }

    /* ---- Schedule hover-close with delay ---- */
    function scheduleClose() {
        if (clickLocked) return;          // don't hover-close if click-locked
        closeTimer = setTimeout(function () {
            if (!clickLocked) {
                megaMenu.classList.remove('mega-open');
                megaDropdown.classList.remove('menu-active');
            }
        }, 150);
    }

    /* ===== HOVER ===== */
    megaDropdown.addEventListener('mouseenter', openMenu);
    megaDropdown.addEventListener('mouseleave', scheduleClose);
    megaMenu.addEventListener('mouseenter', openMenu);
    megaMenu.addEventListener('mouseleave', scheduleClose);

    /* ===== CLICK on "Categories" link ===== */
    navLink.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (clickLocked) {
            // second click → close
            closeMenu();
        } else {
            clickLocked = true;
            openMenu();
        }
    });

    /* ===== Click anywhere OUTSIDE → close ===== */
    document.addEventListener('click', function (e) {
        if (!megaDropdown.contains(e.target)) {
            closeMenu();
        }
    });

    /* ===== Escape key → close ===== */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();



// ============================================
// ============================================

// ========== MOBILE MENU WITH DROPDOWN ==========
const mobileMenuToggle = document.getElementById("mobileMenuToggle");
const offcanvasMenu = document.getElementById("mobileOffcanvas");
const overlay = document.getElementById("offcanvasOverlay");
const closeBtn = document.getElementById("closeOffcanvasBtn");

function openMobileMenu() {
    offcanvasMenu.classList.add("open");
    overlay.classList.add("show");
    document.body.style.overflow = "hidden";
}
function closeMobileMenu() {
    offcanvasMenu.classList.remove("open");
    overlay.classList.remove("show");
    document.body.style.overflow = "";
}

if (mobileMenuToggle)
    mobileMenuToggle.addEventListener("click", openMobileMenu);
if (closeBtn) closeBtn.addEventListener("click", closeMobileMenu);
if (overlay) overlay.addEventListener("click", closeMobileMenu);

// Navigating from the menu should dismiss it instead of leaving it open
// when the browser restores scroll state on the next page.
document.querySelectorAll("#mobileOffcanvas a").forEach((link) => {
    link.addEventListener("click", () => closeMobileMenu());
});

// DROPDOWN TOGGLE FOR CATEGORY MENU
const dropdownHeader = document.getElementById("categoryDropdownHeader");
const submenu = document.getElementById("categorySubmenu");
if (dropdownHeader && submenu) {
    // dropdownHeader.addEventListener("click", function (e) {
    //     e.stopPropagation();
    //     this.classList.toggle("open");
    //     submenu.classList.toggle("show");
    // });

    dropdownHeader.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        this.classList.toggle("open");
        submenu.classList.toggle("show");
    });
}
//=========================
// LEVEL 1
// MEN WOMEN KIDS ACCESSORIES
//=========================

document.querySelectorAll(".mobile-parent-head").forEach(function (item) {

    item.addEventListener("click", function (e) {

        // The category name is a link to its category page — let it navigate
        // instead of toggling the submenu. The +/- icon (and the rest of the
        // row) still expands/collapses.
        if (e.target.closest("a")) return;

        e.stopPropagation();

        let parent = this.parentElement;

        parent.classList.toggle("active");

    });

});

//=========================
// LEVEL 2
// TOPWEAR BOTTOMWEAR
//=========================

document.querySelectorAll(".mobile-sub-head").forEach(function (item) {

    item.addEventListener("click", function (e) {

        e.stopPropagation();

        let parent = this.parentElement;

        parent.classList.toggle("active");

    });

});