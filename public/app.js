document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

const type = document.querySelector('#type');
const reason = document.querySelector('#reason');
if (type && reason) {
    const updateReasons = () => {
        reason.querySelectorAll('optgroup').forEach(group => {
            const active = group.dataset.type === type.value;
            group.disabled = !active;
            group.hidden = !active;
        });
        if (reason.selectedOptions[0]?.parentElement.disabled) {
            reason.querySelector('optgroup:not([disabled]) option').selected = true;
        }
    };
    type.addEventListener('change', updateReasons);
    updateReasons();
}

document.querySelectorAll('form[method="post"]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!event.defaultPrevented && form.checkValidity()) {
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => {
                button.dataset.originalLabel = button.textContent;
                button.disabled = true;
                button.textContent = 'Salvando…';
            });
        }
    });
});

const condition = document.querySelector('#condition');
const guide = document.querySelector('#condition-guide');
if (condition && guide) {
    condition.addEventListener('change', () => { guide.textContent = condition.selectedOptions[0].dataset.guide; });
}

window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[data-original-label]').forEach(button => {
        button.disabled = false;
        button.textContent = button.dataset.originalLabel;
    });
});
