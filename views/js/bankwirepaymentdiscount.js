/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 */

document.addEventListener('DOMContentLoaded', function() {
    let discountApplied = false;

    function isBankWireOption(input) {
        if (!input) {
            return false;
        }

        const candidates = [
            (input.id || '').toLowerCase(),
            (input.value || '').toLowerCase(),
            (input.dataset.moduleName || '').toLowerCase(),
            (input.getAttribute('data-module-name') || '').toLowerCase(),
            (input.getAttribute('data-payment-option') || '').toLowerCase(),
        ];

        return candidates.some(function(value) {
            return value.includes('bankwire') || value.includes('wirepayment');
        });
    }

    // Function to inject discount information into bank wire payment option
    function enhanceBankWireOption() {
        // Find all payment options
        const paymentOptions = document.querySelectorAll('.payment-option');

        paymentOptions.forEach(function(option) {
            const input = option.querySelector('input[type="radio"]');

            if (isBankWireOption(input)) {
                // This is the bank wire payment option
                // Add a class for easier styling
                option.classList.add('bankwire-payment-option');

                // Check if discount info already added
                if (!option.querySelector('.bankwire-discount-inline')) {
                    // Create discount badge element
                    const discountBadge = document.createElement('div');
                    discountBadge.className = 'bankwire-discount-inline';
                    discountBadge.innerHTML = getBankWireDiscountHTML();

                    // Find the label or info section
                    const label = option.querySelector('label');
                    if (label) {
                        // Insert after the label
                        label.parentNode.insertBefore(discountBadge, label.nextSibling);
                    } else {
                        // Just append to the option
                        option.appendChild(discountBadge);
                    }
                }
            }
        });
    }

    // Function to get discount HTML
    function getBankWireDiscountHTML() {
        // Get discount info from data attributes if available
        const discountContainer = document.querySelector('[data-bankwire-discount]');
        let discountPercent = 5;
        let discountAmount = '';

        if (discountContainer) {
            discountPercent = discountContainer.dataset.discountPercent || 5;
            discountAmount = discountContainer.dataset.discountAmount || '';
        }

        return `
            <div class="bankwire-discount-info">
                <div class="discount-badge">
                    <span class="discount-icon">💰</span>
                    <span class="discount-text">
                        <strong>Save ${discountPercent}%!</strong>
                    </span>
                </div>
                <div class="discount-details">
                    Get ${discountAmount || discountPercent + '% discount'} when you pay by bank transfer
                </div>
            </div>
        `;
    }

    // Function to handle payment option selection
    function handlePaymentSelection() {
        const paymentInputs = document.querySelectorAll('input[name="payment-option"]');

        paymentInputs.forEach(function(input) {
            if (input.dataset.bankwireDiscountBound === 'true') {
                return;
            }

            input.dataset.bankwireDiscountBound = 'true';

            input.addEventListener('change', function() {
                // Check if bank wire is selected
                if (isBankWireOption(input)) {
                    applyBankWireDiscount();
                }
            });
        });

        applyDiscountIfBankWireSelected();
    }

    function applyDiscountIfBankWireSelected() {
        const selectedPaymentOption = document.querySelector('input[name="payment-option"]:checked');

        if (isBankWireOption(selectedPaymentOption)) {
            applyBankWireDiscount();
        }
    }

    // Function to apply bank wire discount via AJAX
    function applyBankWireDiscount() {
        if (discountApplied) {
            return;
        }

        const discountContainer = document.querySelector('[data-bankwire-discount]');

        if (!discountContainer || !discountContainer.dataset.applyDiscountUrl) {
            return;
        }

        const requestOptions = {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: ''
        };

        fetch(discountContainer.dataset.applyDiscountUrl, requestOptions)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    discountApplied = true;

                    const event = new CustomEvent('bankwire-discount-applied', {
                        detail: data
                    });

                    document.dispatchEvent(event);

                    if (window.prestashop && typeof prestashop.emit === 'function') {
                        prestashop.emit('updateCart', {
                            reason: 'bankwire-discount-applied',
                        });
                    }
                } else if (window.console && data.message) {
                    console.warn('Bank Wire Discount:', data.message);
                }
            })
            .catch(function(error) {
                if (window.console) {
                    console.error('Bank Wire Discount:', error);
                }
            });
    }

    // Initialize the enhancements
    enhanceBankWireOption();
    handlePaymentSelection();

    // Re-run when payment options are dynamically loaded
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                enhanceBankWireOption();
                handlePaymentSelection();
            }
        });
    });

    // Observe the payment options container
    const paymentContainer = document.querySelector('#payment-option') ||
                            document.querySelector('.payment-options') ||
                            document.querySelector('#checkout-payment-step');

    if (paymentContainer) {
        observer.observe(paymentContainer, {
            childList: true,
            subtree: true
        });
    }
});
