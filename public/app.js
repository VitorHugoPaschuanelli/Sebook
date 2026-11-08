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
                button.disabled = true;
                button.textContent = 'Salvando…';
            });
        }
    });
});
