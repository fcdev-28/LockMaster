document.addEventListener('DOMContentLoaded', () => {
    const everydayRadio = document.getElementById('all');
    const separateRadio = document.getElementById('separate');
    const selectContainer = document.querySelector('.select-container');

    if (everydayRadio && separateRadio && selectContainer) {
        const checkboxes = selectContainer.querySelectorAll('input[type="checkbox"]');

        everydayRadio.addEventListener('change', () => {
            if (everydayRadio.checked) {
                checkboxes.forEach(cb => cb.checked = true);
            }
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const allChecked = [...checkboxes].every(checkbox => checkbox.checked);
                if (!allChecked) {
                    separateRadio.checked = true;
                }
            });
        });
    }

    const radioUser = document.getElementById('select-user');
    const radioGroup = document.getElementById('select-group');

    const userContainer = document.getElementById('user-container');
    const groupContainer = document.getElementById('group-container');

    const toggleContainers = () => {
        if (radioUser.checked) {
            // Escondemos uno y mostramos otro y viceversa
            userContainer.style.display = 'block';
            groupContainer.style.display = 'none';

            // Desactivar campos dentro de groupContainer
            groupContainer.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
            // Activar campos dentro de userContainer
            userContainer.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = false);
        } else if (radioGroup.checked) {
            // Lo mismo pero al revés
            userContainer.style.display = 'none';
            groupContainer.style.display = 'block';

            userContainer.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
            groupContainer.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = false);
        }
    };

    // Activar al inicio
    toggleContainers();

    // Añadir listeners
    radioUser.addEventListener('change', toggleContainers);
    radioGroup.addEventListener('change', toggleContainers);
});