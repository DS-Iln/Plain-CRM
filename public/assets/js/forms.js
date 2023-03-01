// ОБРАБОТКА ОТПРАВОК ФОРМ
document.addEventListener('DOMContentLoaded', () => {
    // Call form
    const callForm = document.querySelector('#callForm'),
        inputCallName = document.querySelector('#inputCallName'),
        inputCallTel = document.querySelector('#inputCallTel'),
        inputCallEmail = document.querySelector('#inputCallEmail');
    callForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetch(callForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: inputCallName.value.trim(),
                email: inputCallEmail.value.trim(),
                telephone_number: inputCallTel.value.trim(),
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            })
        }).then(res => res.json())
            .then(data => {
                // Вывод статуса
                if (!Array.from(callForm.children).at(-1).classList.contains('alert')) {
                    const div = document.createElement('div');
                    div.className = `alert ${data.status ? 'alert-success' : 'alert-danger'} mt-2`;
                    div.textContent = `${data.status ? 'Звонок успешно создан. Скоро менеджер с вами свяжется' : 'Ошибка'}`;
                    callForm.reset();
                    callForm.append(div);
                    setTimeout(() => {
                        div.remove();
                    }, 4000);
                }
                // Вывод ошибок
                if (!data.status) {
                    const VALIDATION_ERRORS = Object.entries(data.messages).map(elem => [elem[0], elem[1][0]]);
                    VALIDATION_ERRORS.forEach(elem => {
                        let div = document.createElement('div'),
                            inputParent = document.querySelector(`#callForm input[name="${elem[0]}"]`).parentElement,
                            inputParentLastchild = Array.from(inputParent.children).at(-1);
                        if (!inputParentLastchild.classList.contains('invalid-feedback') && inputParentLastchild.classList.contains('form-label')) {
                            div.className = 'invalid-feedback';
                            div.style.display = 'block';
                            div.textContent = elem[1];
                            inputParent.append(div);
                        }
                    });
                } else {
                    document.querySelectorAll('#callForm .invalid-feedback').forEach(elem => elem.remove());
                }
            })
            .catch(error => console.log(error))
    });

    // Application form
    const applicationForm = document.querySelector('#applicationForm'),
        inputApplicationName = document.querySelector('#inputApplicationName'),
        inputApplicationTel = document.querySelector('#inputApplicationTel'),
        inputApplicationEmail = document.querySelector('#inputApplicationEmail'),
        textareaApplicationMessage = document.querySelector('#textareaApplicationMessage'),
        inputApplicationFiles = document.querySelector('#inputApplicationFiles');
    applicationForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetch(applicationForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: inputApplicationName.value.trim(),
                telephone_number: inputApplicationTel.value.trim(),
                email: inputApplicationEmail.value.trim(),
                message: textareaApplicationMessage.value.trim(),
                // files: inputApplicationFiles.value,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            })
        }).then(res => res.json())
            .then(data => {
                // Вывод статуса
                if (!Array.from(applicationForm.children).at(-1).classList.contains('alert')) {
                    const div = document.createElement('div');
                    div.className = `alert ${data.status ? 'alert-success' : 'alert-danger'} mt-2`;
                    div.textContent = `${data.status ? 'Заявка успешно отправлена' : 'Ошибка'}`;
                    applicationForm.reset();
                    applicationForm.append(div);
                    setTimeout(() => {
                        div.remove();
                    }, 4000);
                }
                // Вывод ошибок
                if (!data.status) {
                    const VALIDATION_ERRORS = Object.entries(data.messages).map(elem => [elem[0], elem[1][0]]);
                    VALIDATION_ERRORS.forEach(elem => {
                        let div = document.createElement('div'),
                            inputParent = document.querySelector(`#applicationForm input[name="${elem[0]}"]`).parentElement,
                            inputParentLastchild = Array.from(inputParent.children).at(-1);
                        if (!inputParentLastchild.classList.contains('invalid-feedback') && inputParentLastchild.classList.contains('form-label')) {
                            div.className = 'invalid-feedback';
                            div.style.display = 'block';
                            div.textContent = elem[1];
                            inputParent.append(div);
                        }
                    });
                } else {
                    document.querySelectorAll('#applicationForm .invalid-feedback').forEach(elem => elem.remove());
                }
            })
            .catch(error => console.log(error));
    });
});
// Желательно оптимизировать скрипт, путем слияния двух обработчиков форм в одну функцию
