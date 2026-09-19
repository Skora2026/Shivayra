(function() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {        
        // Initialize all features
        initGridView();
        initPriceSliders();
        initFilters();
        initSorting();
        initMobileFilters();
        initCartWishlist();
        initResetButtons();
        
        // Handle URL category and subcategory parameters on page load
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');
        const subcategoryParam = urlParams.get('subcategory');

        if (categoryParam) {
            const mainCheckboxes = document.querySelectorAll(`.main-cat-filter[value="${categoryParam}"]`);
            mainCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            const subCheckboxes = document.querySelectorAll(`.cat-filter[data-parent="${categoryParam}"]`);
            subCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        if (subcategoryParam) {
            // Check ONLY the requested subcategory. Checking the parent main
            // category too would over-match ("main OR sub" logic shows every
            // sibling product), defeating the deep link.
            const subCheckboxes = document.querySelectorAll(`.cat-filter[value="${subcategoryParam}"]`);
            subCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        }
        
        // Initial filter application
        filterProducts();
    }
    
    // ========== 1. GRID VIEW ==========
    function initGridView() {
        const btns = document.querySelectorAll('.grid-btn');
        btns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                btns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const grid = this.dataset.grid;
                let colClass = 'col-lg-4';
                if (grid === '12') colClass = 'col-lg-12';
                else if (grid === '6') colClass = 'col-lg-6';
                else if (grid === '4') colClass = 'col-lg-4';
                
                document.querySelectorAll('.product-grid').forEach(col => {
                    col.classList.remove('col-lg-12', 'col-lg-6', 'col-lg-4');
                    col.classList.add(colClass);
                });
            });
        });
    }
    
    // ========== 2. PRICE SLIDERS (SYNC BOTH) ==========
    function updateSliderBackground(slider) {
        if (!slider) return;
        const min = slider.min ? parseFloat(slider.min) : 0;
        const max = slider.max ? parseFloat(slider.max) : 100;
        const value = slider.value ? parseFloat(slider.value) : max;
        const percentage = ((value - min) / (max - min)) * 100;
        slider.style.background = `linear-gradient(to right, #C5A880 0%, #C5A880 ${percentage}%, #e5e0d8 ${percentage}%, #e5e0d8 100%)`;
    }

    function initPriceSliders() {
        const desktopSlider = document.getElementById('priceRange');
        const desktopValue = document.getElementById('priceValue');
        const mobileSlider = document.getElementById('mobilePriceRange');
        const mobileValue = document.getElementById('mobilePriceValue');
        
        if (desktopSlider) updateSliderBackground(desktopSlider);
        if (mobileSlider) updateSliderBackground(mobileSlider);
        
        if (desktopSlider && desktopValue) {
            desktopSlider.addEventListener('input', function() {
                const val = this.value;
                desktopValue.textContent = val;
                updateSliderBackground(this);
                if (mobileSlider) {
                    mobileSlider.value = val;
                    updateSliderBackground(mobileSlider);
                }
                if (mobileValue) mobileValue.textContent = val;
                filterProducts();
            });
        }
        
        if (mobileSlider && mobileValue) {
            mobileSlider.addEventListener('input', function() {
                const val = this.value;
                mobileValue.textContent = val;
                updateSliderBackground(this);
                if (desktopSlider) {
                    desktopSlider.value = val;
                    updateSliderBackground(desktopSlider);
                }
                if (desktopValue) desktopValue.textContent = val;
                filterProducts();
            });
        }
    }
    
    // ========== 3. HELPER FUNCTIONS FOR FILTERS ==========
    function getCheckedValues(selector) {
        return Array.from(document.querySelectorAll(selector + ':checked')).map(cb => cb.value);
    }
    
    function getSelectedColors() {
        return Array.from(document.querySelectorAll('.color-dot.selected')).map(dot => dot.dataset.color);
    }
    
    function getActiveFilters() {
        return {
            mainCats: getCheckedValues('.main-cat-filter'),
            cats: getCheckedValues('.cat-filter'),
            sizes: getCheckedValues('.size-filter'),
            patterns: getCheckedValues('.pattern-filter'),
            occasions: getCheckedValues('.occasion-filter'),
            fabrics: getCheckedValues('.fabric-filter'),
            necklines: getCheckedValues('.neckline-filter'),
            colors: getCheckedValues('.color-filter')
        };
    }
    
    function getMaxPrice() {
        const desktopSlider = document.getElementById('priceRange');
        const mobileSlider = document.getElementById('mobilePriceRange');
        let price = 5000;
        if (desktopSlider && desktopSlider.getAttribute('max')) {
            price = parseInt(desktopSlider.getAttribute('max'));
        } else if (mobileSlider && mobileSlider.getAttribute('max')) {
            price = parseInt(mobileSlider.getAttribute('max'));
        }
        if (desktopSlider && desktopSlider.value) price = parseInt(desktopSlider.value);
        if (mobileSlider && mobileSlider.value) price = parseInt(mobileSlider.value);
        return price;
    }
    
    // ========== 4. MAIN FILTER FUNCTION ==========
    function filterProducts() {
        const maxPrice = getMaxPrice();
        const filters = getActiveFilters();
        let visibleCount = 0;
        const products = document.querySelectorAll('.product-grid');
        
        // Fetch query from header search
        const searchInput = document.getElementById("headerSearchInput");
        const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : "";
        
        products.forEach(product => {
            const price = parseInt(product.dataset.price);
            const mainCategory = product.dataset.mainCategory || "";
            const subcategory = product.dataset.category || "";
            const size = product.dataset.size;
            const pattern = product.dataset.pattern;
            const occasion = product.dataset.occasion;
            const fabric = product.dataset.fabric;
            const color = product.dataset.color;
            const neckline = product.dataset.neckline;
            const name = product.dataset.name || "";
            const description = product.dataset.description || "";
            
            let show = true;
            
            // Apply price filter
            if (price > maxPrice) show = false;

            // Apply category filter (matches main category OR subcategory)
            const hasMainCat = filters.mainCats.length > 0;
            const hasSubCat = filters.cats.length > 0;
            if (hasMainCat || hasSubCat) {
                const matchMain = hasMainCat && filters.mainCats.includes(mainCategory);
                const matchSub = hasSubCat && filters.cats.includes(subcategory);
                if (!matchMain && !matchSub) {
                    show = false;
                }
            }

            if (filters.sizes.length && !filters.sizes.includes(size)) show = false;
            if (filters.patterns.length && !filters.patterns.includes(pattern)) show = false;
            if (filters.occasions.length && !filters.occasions.includes(occasion)) show = false;
            if (filters.fabrics.length && !filters.fabrics.includes(fabric)) show = false;
            if (filters.necklines.length && !filters.necklines.includes(neckline)) show = false;
            if (filters.colors.length && !filters.colors.includes(color)) show = false;
            
            // Apply keyword search filter
            if (searchQuery && !name.includes(searchQuery) && !description.includes(searchQuery)) {
                show = false;
            }
            
            if (show) {
                // If it was hidden, show it and fade in
                if (product.style.display === 'none') {
                    product.style.display = 'block';
                    product.style.opacity = '0';
                    product.style.transform = 'scale(0.95)';
                    // Trigger reflow
                    product.offsetHeight;
                }
                product.style.opacity = '1';
                product.style.transform = 'scale(1)';
                visibleCount++;
            } else {
                product.style.opacity = '0';
                product.style.transform = 'scale(0.95)';
                
                // Hide after transition ends
                const handleTransitionEnd = () => {
                    if (product.style.opacity === '0') {
                        product.style.display = 'none';
                    }
                    product.removeEventListener('transitionend', handleTransitionEnd);
                };
                product.addEventListener('transitionend', handleTransitionEnd);
            }
        });
        
        // Show/hide no products message
        const productRow = document.getElementById('productRow');
        let existingMsg = document.getElementById('noProductsMsg');
        
        if (visibleCount === 0) {
            if (!existingMsg) {
                const msgDiv = document.createElement('div');
                msgDiv.id = 'noProductsMsg';
                msgDiv.className = 'col-12';
                msgDiv.innerHTML = '<div class="text-center py-5"><h5>No products found</h5><p>Try changing your filters</p></div>';
                if (productRow) productRow.appendChild(msgDiv);
            }
        } else {
            if (existingMsg) existingMsg.remove();
        }
    }
    
    // ========== 5. ATTACH FILTER LISTENERS ==========
    function initFilters() {
        // Search Input listener
        const searchInput = document.getElementById("headerSearchInput");
        if (searchInput) {
            searchInput.addEventListener('input', filterProducts);
            
            // Prevent reloading on enter if we are already on products page
            const searchForm = searchInput.closest('form');
            if (searchForm && window.location.pathname.includes('/product')) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                });
            }
        }

        // Checkbox filters
        const filterSelectors = ['.main-cat-filter', '.cat-filter', '.size-filter', '.pattern-filter', 
                                '.occasion-filter', '.fabric-filter', '.neckline-filter', '.color-filter'];
        
        filterSelectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(cb => {
                cb.addEventListener('change', function() {
                    const val = this.value;
                    const checked = this.checked;

                    // Sync the state with other checkboxes of same selector and value
                    document.querySelectorAll(`${selector}[value="${val}"]`).forEach(otherCb => {
                        if (otherCb !== this) {
                            otherCb.checked = checked;
                        }
                    });

                    // If main category checkbox, sync child subcategory checkboxes
                    if (selector === '.main-cat-filter') {
                        document.querySelectorAll(`.cat-filter[data-parent="${val}"]`).forEach(subCb => {
                            subCb.checked = checked;
                        });
                    }

                    // If subcategory checkbox, sync parent main category checkbox state
                    if (selector === '.cat-filter') {
                        const parentSlug = this.dataset.parent;
                        if (parentSlug) {
                            const siblings = Array.from(document.querySelectorAll(`.cat-filter[data-parent="${parentSlug}"]`));
                            const anyChecked = siblings.some(s => s.checked);
                            document.querySelectorAll(`.main-cat-filter[value="${parentSlug}"]`).forEach(pCb => {
                                pCb.checked = anyChecked;
                            });
                        }
                    }
                    
                    filterProducts();
                });
            });
        });
    }
    
    // ========== 6. SORTING FUNCTION ==========
    function initSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const type = this.value;
                const container = document.getElementById('productRow');
                if (!container) return;
                
                const cards = Array.from(container.querySelectorAll('.product-grid'));
                
                if (type === 'low') {
                    cards.sort((a, b) => parseInt(a.dataset.price) - parseInt(b.dataset.price));
                } else if (type === 'high') {
                    cards.sort((a, b) => parseInt(b.dataset.price) - parseInt(a.dataset.price));
                }
                
                if (type === 'low' || type === 'high') {
                    cards.forEach(card => container.appendChild(card));
                }
            });
        }
    }
    
    // ========== 7. MOBILE FILTER PANELS ==========
    function initMobileFilters() {
        const chips = document.querySelectorAll('.mobile-filter-bar .filter-chip');
        const panels = document.querySelectorAll('.mobile-filter-content .filter-panel');
        
        if (chips.length) {
            chips.forEach(chip => {
                chip.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const targetPanel = document.getElementById(targetId);
                    
                    // Remove active class from all chips and panels
                    chips.forEach(c => c.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to current
                    this.classList.add('active');
                    if (targetPanel) targetPanel.classList.add('active');
                });
            });
        }
    }
    
    // Toast helpers — reuse the header toasts if present, else a floating notice.
    function notify(html) {
        const t = document.getElementById("toast");
        if (!t) return;
        t.innerHTML = html;
        t.classList.add("show");
        setTimeout(() => t.classList.remove("show"), 1800);
    }
    function notifyWish(html) {
        const t = document.getElementById("wishToast");
        if (!t) return;
        t.innerHTML = html;
        t.classList.add("show");
        setTimeout(() => t.classList.remove("show"), 1800);
    }

    // ========== 8. CART & WISHLIST ==========
    function initCartWishlist() {
        // Add to cart
        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const name = this.dataset.name || 'Product';
                const price = this.dataset.price || '0';
                notify(`<svg class="icon"><use href="#i-check"/></svg> Added to cart: ${name} — ₹${price}`);
            });
        });
        
        // Add to wishlist
        document.querySelectorAll('.add-to-wishlist').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const name = this.dataset.name || 'Product';
                notifyWish(`<svg class="icon"><use href="#i-heart-fill"/></svg> Added to wishlist: ${name}`);
            });
        });
    }
    
    // ========== 9. RESET BUTTONS ==========
    function initResetButtons() {
        // Desktop reset button
        const resetBtn = document.getElementById('resetFiltersBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', resetAllFilters);
        }
        
        // Mobile reset button
        const mobileResetBtn = document.getElementById('mobileResetBtn');
        if (mobileResetBtn) {
            mobileResetBtn.addEventListener('click', resetAllFilters);
        }
    }
    
    function resetAllFilters() {
        // Reset all checkboxes
        const checkboxes = document.querySelectorAll('.main-cat-filter, .cat-filter, .size-filter, .pattern-filter, .occasion-filter, .fabric-filter, .neckline-filter');
        checkboxes.forEach(cb => cb.checked = false);
        
        // Reset color dots
        document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('selected'));
        
        // Reset price sliders
        const desktopSlider = document.getElementById('priceRange');
        const mobileSlider = document.getElementById('mobilePriceRange');
        const desktopValue = document.getElementById('priceValue');
        const mobileValue = document.getElementById('mobilePriceValue');
        
        if (desktopSlider) {
            const maxVal = desktopSlider.getAttribute('max') || '5000';
            desktopSlider.value = maxVal;
            if (desktopValue) desktopValue.textContent = maxVal;
            updateSliderBackground(desktopSlider);
        }
        if (mobileSlider) {
            const maxVal = mobileSlider.getAttribute('max') || '5000';
            mobileSlider.value = maxVal;
            if (mobileValue) mobileValue.textContent = maxVal;
            updateSliderBackground(mobileSlider);
        }
        
        // Reset sort dropdown
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) sortSelect.value = '';
        
        // Reorder products to original (by data-id or keep as is)
        const container = document.getElementById('productRow');
        if (container) {
            const cards = Array.from(container.querySelectorAll('.product-grid'));
            cards.sort((a, b) => parseInt(a.dataset.id || a.querySelector('.add-to-cart')?.dataset.id || 0) - 
                             parseInt(b.dataset.id || b.querySelector('.add-to-cart')?.dataset.id || 0));
            cards.forEach(card => container.appendChild(card));
        }
        
        // Apply filters
        filterProducts();
    }
    
})();