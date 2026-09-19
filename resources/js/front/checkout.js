document.addEventListener('DOMContentLoaded', function() {
    // Only execute if we are on the checkout page
    let checkoutForm = document.getElementById("checkoutForm");
    if (!checkoutForm) return;

    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    
    if (cart.length === 0) {
        // In-page empty state instead of a dead-end alert() dialog.
        const wrap = document.querySelector("main .container") ?? document.querySelector("main");
        if (wrap)
            wrap.innerHTML = `
            <div class="text-center py-5 my-5">
                <img src="/images/empty-state.png" alt="" width="240" class="mb-4">
                <h2 class="main-heading mb-2">Your cart is empty</h2>
                <p class="text-muted mb-4">Add something beautiful before checking out.</p>
                <a href="/products" class="btn btn-gold px-4 py-2">Continue Shopping</a>
            </div>`;
        return;
    }
    
    let orderItems = document.getElementById("orderItems");
    let subtotalElement = document.getElementById("subtotal");
    let shippingElement = document.getElementById("shipping");
    let taxElement = document.getElementById("tax");
    let totalElement = document.getElementById("total");
    
    if (!orderItems || !subtotalElement || !shippingElement || !taxElement || !totalElement) {
        console.error("Required elements not found on page");
        return;
    }
    
    let subtotal = 0;
    orderItems.innerHTML = '';
    
    cart.forEach(item => {
        subtotal += item.price * item.qty;
        let variantHtml = '';
        if (item.variantValues) {
            variantHtml = `<small class="text-muted d-block" style="font-size:0.78rem;"><i class="bi bi-tag-fill me-1"></i>${escapeHtml(item.variantValues)}</small>`;
        }
        orderItems.innerHTML += `
            <div class="mb-2 pb-2 border-bottom border-light-subtle">
                <div class="d-flex justify-content-between align-items-center">
                    <span><strong>${escapeHtml(item.name)}</strong> x${item.qty}</span>
                    <span>₹${(item.price * item.qty).toFixed(2)}</span>
                </div>
                ${variantHtml}
            </div>
        `;
    });
    
    let taxPercent = typeof window.taxPercent !== 'undefined' ? window.taxPercent : 5.0;
    let deliveryFee = typeof window.deliveryFee !== 'undefined' ? window.deliveryFee : 60.0;
    let minOrderFreeDelivery = typeof window.minOrderFreeDelivery !== 'undefined' ? window.minOrderFreeDelivery : 1000.0;

    let shipping = (minOrderFreeDelivery > 0 && subtotal >= minOrderFreeDelivery) ? 0 : deliveryFee;
    let tax = subtotal * (taxPercent / 100.0);
    let total = subtotal + shipping + tax;
    
    subtotalElement.innerText = subtotal.toFixed(2);
    shippingElement.innerText = shipping.toFixed(2);
    taxElement.innerText = tax.toFixed(2);
    totalElement.innerText = total.toFixed(2);
    
    // ========== SELECT PAYMENT METHOD ==========
    window.selectPayment = function(box) {
        let input = box.querySelector("input");
        if (input && input.disabled) {
            return;
        }
        document.querySelectorAll(".payment-box").forEach(b => {
            b.classList.remove("active");
            b.style.borderColor = "#e5e7eb";
            b.style.background = "transparent";
        });
        box.classList.add("active");
        box.style.borderColor = "#0A9051";
        box.style.background = "#f0fdf4";
        if (input) input.checked = true;
    };
    
    // Initialize payment box styling
    const checkedRadio = document.querySelector('input[name="payment_method"]:checked:not(:disabled)');
    if (checkedRadio) {
        const parentBox = checkedRadio.closest('.payment-box');
        if (parentBox) selectPayment(parentBox);
    }
    
    let sameCheckbox = document.getElementById("sameAddress");
    
    if (sameCheckbox) {
        sameCheckbox.addEventListener("change", copyBillingToShipping);
    }
    
    function copyBillingToShipping() {
        let shippingFields = document.querySelectorAll(".shipping-field");
        
        if (sameCheckbox && sameCheckbox.checked) {
            let billName = document.getElementById("billName");
            let billEmail = document.getElementById("billEmail");
            let billPhone = document.getElementById("billPhone");
            let billAddress = document.getElementById("billAddress");
            let billCity = document.getElementById("billCity");
            let billState = document.getElementById("billState");
            let billPin = document.getElementById("billPin");
            
            if (shippingFields[0] && billName) shippingFields[0].value = billName.value;
            if (shippingFields[1] && billEmail) shippingFields[1].value = billEmail.value;
            if (shippingFields[2] && billPhone) shippingFields[2].value = billPhone.value;
            if (shippingFields[3] && billAddress) shippingFields[3].value = billAddress.value;
            if (shippingFields[4] && billCity) shippingFields[4].value = billCity.value;
            if (shippingFields[5] && billState) shippingFields[5].value = billState.value;
            if (shippingFields[6] && billPin) shippingFields[6].value = billPin.value;
            
            shippingFields.forEach(field => {
                if (field) field.setAttribute("readonly", true);
            });
        } else {
            shippingFields.forEach(field => {
                if (field) {
                    field.removeAttribute("readonly");
                }
            });
        }
    }
    
    // Run copy on page load
    if (sameCheckbox) {
        copyBillingToShipping();
    }
    
    // Copy real-time changes
    let billingFields = ["billName", "billEmail", "billPhone", "billAddress", "billCity", "billState", "billPin"];
    billingFields.forEach(fieldId => {
        let field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener("input", function() {
                if (sameCheckbox && sameCheckbox.checked) {
                    copyBillingToShipping();
                }
            });
            field.addEventListener("change", function() {
                if (sameCheckbox && sameCheckbox.checked) {
                    copyBillingToShipping();
                }
            });
        }
    });

    // Populate hidden input and copy fields on form submit
    checkoutForm.addEventListener("submit", function(e) {
        if (sameCheckbox && sameCheckbox.checked) {
            copyBillingToShipping();
        }
        let cartDataInput = document.getElementById("cartDataInput");
        if (cartDataInput) {
            cartDataInput.value = JSON.stringify(cart);
        }
    });
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
});
