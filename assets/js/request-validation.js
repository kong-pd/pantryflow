(() => {
    'use strict';

    const form = document.getElementById('request-form');
    if (!form) {
        return;
    }

    const nameInput = document.getElementById('client_name');
    const contactInput = document.getElementById('contact');
    const pickupInput = document.getElementById('pickup_date');
    const itemSelect = document.getElementById('food_item_id');
    const quantityInput = document.getElementById('requested_qty');
    const availabilityHint = document.getElementById('availability-hint');
    const selectedItemName = document.getElementById('selected-item-name');
    const selectedItemAvailability = document.getElementById('selected-item-availability');
    const submitButton = document.getElementById('request-submit');
    const formStatus = document.getElementById('form-status');
    const contactPattern = /^\+?[0-9][0-9\s-]{7,19}$/;
    const touchedFields = new Set();

    const localDateString = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const tomorrow = new Date();
    tomorrow.setHours(0, 0, 0, 0);
    tomorrow.setDate(tomorrow.getDate() + 1);
    pickupInput.min = localDateString(tomorrow);

    const errorElement = (input) => form.querySelector(`[data-error-for="${input.id}"]`);

    const setError = (input, message) => {
        input.setCustomValidity(message);
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        const element = errorElement(input);
        if (element) {
            element.textContent = message;
        }
    };

    const clearError = (input) => setError(input, '');

    const validateName = () => {
        const value = nameInput.value.trim();
        if (!value) {
            setError(nameInput, 'Enter your full name.');
            return false;
        }
        clearError(nameInput);
        return true;
    };

    const validateContact = () => {
        const value = contactInput.value.trim();
        if (!contactPattern.test(value)) {
            setError(contactInput, 'Use 8 to 20 digits with optional +, spaces, or hyphens.');
            return false;
        }
        clearError(contactInput);
        return true;
    };

    const validatePickup = () => {
        if (!pickupInput.value || pickupInput.value < pickupInput.min) {
            setError(pickupInput, 'Choose a pickup date later than today.');
            return false;
        }
        clearError(pickupInput);
        return true;
    };

    const selectedOption = () => itemSelect.options[itemSelect.selectedIndex];

    const selectedAvailableQuantity = () => {
        const option = selectedOption();
        if (!option || !option.dataset.quantity) {
            return null;
        }
        const value = Number.parseInt(option.dataset.quantity, 10);
        return Number.isInteger(value) ? value : null;
    };

    const updateSelectionSummary = () => {
        const option = selectedOption();
        const available = selectedAvailableQuantity();

        if (!option || available === null) {
            quantityInput.removeAttribute('max');
            availabilityHint.textContent = 'Choose an item to see the maximum request quantity.';
            selectedItemName.textContent = 'No item selected';
            selectedItemAvailability.textContent = 'Choose an item in the form to view current availability.';
            return;
        }

        const itemName = option.dataset.name || option.textContent.trim();
        quantityInput.max = String(available);
        availabilityHint.textContent = `${available} unit${available === 1 ? '' : 's'} currently available.`;
        selectedItemName.textContent = itemName;
        selectedItemAvailability.textContent = `${available} unit${available === 1 ? '' : 's'} available for a future pickup.`;
    };

    const validateItem = () => {
        if (!itemSelect.value) {
            setError(itemSelect, 'Select an available food item.');
            return false;
        }
        clearError(itemSelect);
        return true;
    };

    const validateQuantity = () => {
        const available = selectedAvailableQuantity();
        const quantity = Number(quantityInput.value);

        if (!Number.isInteger(quantity) || quantity < 1) {
            setError(quantityInput, 'Enter a positive whole number.');
            return false;
        }

        if (available !== null && quantity > available) {
            setError(quantityInput, `Only ${available} unit${available === 1 ? ' is' : 's are'} available.`);
            return false;
        }

        clearError(quantityInput);
        return true;
    };

    const attachValidation = (input, validator, liveEvent = 'input') => {
        input.addEventListener('blur', () => {
            touchedFields.add(input.id);
            validator();
        });
        input.addEventListener(liveEvent, () => {
            if (touchedFields.has(input.id) || input.getAttribute('aria-invalid') === 'true') {
                validator();
            }
        });
    };

    attachValidation(nameInput, validateName);
    attachValidation(contactInput, validateContact);
    attachValidation(pickupInput, validatePickup, 'change');
    attachValidation(quantityInput, validateQuantity);

    itemSelect.addEventListener('change', () => {
        touchedFields.add(itemSelect.id);
        updateSelectionSummary();
        validateItem();
        if (touchedFields.has(quantityInput.id)) {
            validateQuantity();
        }
    });

    form.addEventListener('submit', (event) => {
        [nameInput, contactInput, pickupInput, itemSelect, quantityInput]
            .forEach((input) => touchedFields.add(input.id));

        const checks = [
            validateName(),
            validateContact(),
            validatePickup(),
            validateItem(),
            validateQuantity(),
        ];

        if (checks.includes(false)) {
            event.preventDefault();
            formStatus.textContent = 'Please correct the highlighted fields before submitting.';
            form.querySelector('[aria-invalid="true"]')?.focus();
            return;
        }

        form.setAttribute('aria-busy', 'true');
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting request\u2026';
        formStatus.textContent = 'Submitting your request and checking current stock.';
    });

    window.addEventListener('pageshow', () => {
        form.removeAttribute('aria-busy');
        submitButton.disabled = false;
        submitButton.innerHTML = 'Confirm request <span aria-hidden="true">&rarr;</span>';
    });

    updateSelectionSummary();
})();
