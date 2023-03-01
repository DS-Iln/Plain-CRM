// ИНИЦИАЛИЗАЦИЯ ТАБЛИЦ С ДАННЫМИ
import DataTable from "../js/frappe-datatable.min.js";
document.addEventListener('DOMContentLoaded', () => {
    // Занесение полученных от сервера данных в JS переменные
    const SERVICE_CONTAINER = document.querySelector('.service-container'),
          TOAST_CONTAINER = document.querySelector('.toast-container'),
          TOAST_DELAY = 5000, // По ум.
          CALL_STATUS_OPTIONS = JSON.parse(SERVICE_CONTAINER.dataset.callOptions)[0].possibleStatusValues.match(/([А-ЯёЁ\s])+/gi),
          APPLICATION_STATUS_OPTIONS = JSON.parse(SERVICE_CONTAINER.dataset.applicationOptions)[0].possibleStatusValues.match(/([А-ЯёЁ\s])+/gi),
          ORDER_STATUS_OPTIONS = JSON.parse(SERVICE_CONTAINER.dataset.orderOptions)[0].possibleStatusValues.match(/([А-ЯёЁ\s])+/gi),
          DELETE_URL = SERVICE_CONTAINER.dataset.deleteUrl,
          REFRESH_URL = SERVICE_CONTAINER.dataset.refreshUrl,
          UPDATE_URL = SERVICE_CONTAINER.dataset.updateUrl,
          TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    // Получение необходимых элементов документа
    const DELETE_BUTTON = document.querySelector('#delete-button'),
          CONFIRMATION_TITLE = document.querySelector('#confirmation-title'),
          PARENT_ENTITY_TITLE = document.querySelector('#parent-entity-title'),
          DELETE_DATA_BUTTONS = [
              document.querySelector('#delete-users-data'),
              document.querySelector('#delete-calls-data'),
              document.querySelector('#delete-applications-data'),
              document.querySelector('#delete-orders-data'),
          ],
          SAVE_DATA_BUTTONS = [
              document.querySelector('#save-users-data'),
              document.querySelector('#save-calls-data'),
              document.querySelector('#save-applications-data'),
              document.querySelector('#save-orders-data'),
          ];

    /**
     * Функция заполнения select`а option`ами
     * @param {*} options
     * @param {number|string} value
     * @returns {string}
     */
    function populatingSelect(options, value) {
        let selectOptions = options.filter(i => i !== value), selectTag = `<select class="form-select py-1 px-2" data-default="${value}">`;
        selectTag += `<option value="${value}" selected>${value}</option>`;
        selectOptions.forEach(i => {
            selectTag += `<option value="${i}">${i}</option>`;
        })
        selectTag += '</select>';
        return selectTag;
    }
    /**
     * Вспомогательная функция-обёртка для отправки fetch-запроса
     * @param url
     * @param method
     * @param body
     * @returns {Promise<any>}
     */
    function fetchRequest(url = '', method = 'GET', body = null) {
        if (method === 'GET') {
            return fetch(url).then(response => response.json())
                .catch(error => console.log(error))
        }
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(body),
        }).then(response => response.json())
            .catch(error => console.log(error))
    }
    /**
     * Функция сбора данных для множественного удаления
     * @param {string} table_type
     * @param {number} table_num
     */
    function selectiveDeletion(table_type, table_num) {
        CONFIRMATION_TITLE.textContent = 'Вы точно хотите удалить выбранные записи?';
        let checkedRows = document.querySelectorAll(`.dt-instance-${table_num} > .dt-scrollable > .dt-row--highlight`), data, elementsToDelete = [];
        checkedRows.forEach(i => { // Составление списка из отмеченных записей
            elementsToDelete.push({
                id: i.children[1].children[0].children[0].dataset.id,
                table_type: table_type,
            });
        })
        data = {
            elementsToDelete: elementsToDelete,
            _token: TOKEN,
        }
        handleDeleteButtonEvent(data, table_num, checkedRows, table_type);
    }
    /**
     * Функция сохранения изменённых данных
     * @param {string} table_type
     * @param {number} table_num
     */
    function saveEditedData(table_type, table_num) {
        let editedRows = document.querySelectorAll(`.dt-instance-${table_num} > .dt-scrollable > .dt-row--edited`), data, elementsToUpdate = [], arr;
        if (!editedRows.length) {
            setToastMessage(false, 'Нечего сохранять - редактированных данных не обнаружено.');
            return;
        }
        editedRows.forEach(i => { // Составление списка из редактированных записей
            arr = new Map();
            arr.set('id', i.children[1].children[0].children[0].dataset.id);
            switch (table_type) {
                case 'users':
                    arr.set('name', i.children[2].children[0].textContent.trim());
                    arr.set('email', i.children[3].children[0].children[0].textContent.trim());
                    arr.set('created_at', i.children[4].children[0].textContent.trim());
                    break;
                case 'calls':
                    arr.set('telephone_number', i.children[3].children[0].children[0].textContent.trim());
                    arr.set('status', i.children[4].children[0].children[0].value.trim());
                    arr.set('created_at', i.children[5].children[0].textContent.trim());
                    break;
                case 'applications':
                    arr.set('telephone_number', i.children[3].children[0].children[0].textContent.trim());
                    arr.set('message', i.children[4].children[0].textContent.trim());
                    arr.set('status', i.children[5].children[0].children[0].value.trim());
                    arr.set('created_at', i.children[6].children[0].textContent.trim());
                    break;
                default:
                    arr.set('status', i.children[4].children[0].children[0].value.trim());
                    arr.set('created_at', i.children[5].children[0].textContent.trim());
            }
            elementsToUpdate.push(Array.from(arr));
        })
        data = {
            elementsToUpdate: elementsToUpdate,
            table_type: table_type,
            _token: TOKEN,
        }
        fetchRequest(UPDATE_URL, 'POST', data)
            .then(data => {
                setToastMessage(data.status, data.message);
                refreshData().then(refresh => { // Запрос на получения обновлённых данных
                    handleDatatable(`#${table_type}-datatable`, table_type); // Повторная инициализация таблицы для вывода данных после изменений
                })
            })
    }
    /**
     * Функция отправки запроса на получение актуальных данных
     * @returns {Promise<any>}
     */
    function refreshData() {
        return fetchRequest(REFRESH_URL)
            .then(refresh => {
                if (refresh.status) {
                    ['users', 'calls', 'applications', 'orders'].forEach(i => SERVICE_CONTAINER.dataset[i] = refresh[i]); // Обновление данных на клиенте
                }
            })
    }
    /**
     *
     * @param status
     * @param {string} message
     * @returns {Toast|Toast}
     */
    function setToastMessage(status, message) {
        let toast = document.createElement('div'); // Обёртка
        toast.className = `toast align-items-center ${status ? 'text-bg-success' : 'text-bg-danger'} bg-opacity-75`;
        toast.setAttribute('role', status ? 'status' : 'alert');
        toast.ariaLive = status ? 'polite' : 'assertive';
        toast.ariaAtomic = 'true'
        const flex = document.createElement('div');
        flex.className = 'd-flex';
        const body = document.createElement('div');
        body.className = 'toast-body';
        body.textContent = message;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn-close btn-close-white me-2 m-auto';
        button.dataset.bsDismiss = 'toast';
        button.ariaLabel = 'Close';
        flex.append(...[body, button]);
        toast.append(flex);
        TOAST_CONTAINER.append(toast);
        toast = new bootstrap.Toast(toast, {
           delay: TOAST_DELAY,
        });
        toast.show();
    }
    /**
     * Функция отправки запросов на удаление
     * @param {*} data
     * @param {number} table_num
     * @param {NodeListOf<Element>|HTMLElement[]} rowsToDelete
     * @param {string} table_type
     */
    function handleDeleteButtonEvent(data, table_num, rowsToDelete, table_type = '') {
        DELETE_BUTTON.onclick = () => {
            fetchRequest(DELETE_URL, 'POST', data)
                .then(data => {
                    if (data.status) {
                        DELETE_BUTTON.nextElementSibling.click(); // Закрытие формы
                        DELETE_BUTTON.onclick = null; // Снятие с кнопки события клика
                        refreshData().then(refresh => {
                            handleDatatable(`#${table_type}-datatable`, table_type);
                        })
                    }
                    setToastMessage(data.status, data.message); // Инициализация уведомления
                })
        }
    }
    /**
     * Функция для инициализации таблиц
     * @param datatable_container
     * @param {string} table_type
     */
    function handleDatatable(datatable_container = '', table_type= '') {
        // Заполнение списка данных
        let table_data = JSON.parse(SERVICE_CONTAINER.dataset[table_type]),
            arr = [];
        table_data.forEach(i => {
            switch (table_type) {
                case 'users':
                    arr.push([
                        {
                            content: `<a href="#" class="delete-link" data-id="${i.id}" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"><span style="color: inherit;"><i class="fa-regular fa-trash-can"></i></span></a>`,
                        },
                        i.name,
                        i.email,
                        i.created_at,
                        i.updated_at,
                    ]);
                    break;
                case 'calls':
                    arr.push([
                        {
                            content: `<a href="#" class="delete-link" data-id="${i.id}" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"><span style="color: inherit;"><i class="fa-regular fa-trash-can"></i></span></a>`,
                        },
                        {
                            content: `<a href="#" class="parent-entity-link" data-parent-table-type="users" data-parent-id="${i.user_id}" data-email="${i.email}" data-bs-toggle="modal" data-bs-target="#parentEntityModal">${i.name}</a>`,
                        },
                        i.telephone_number,
                        {
                            content: populatingSelect(CALL_STATUS_OPTIONS, i.status),
                        },
                        i.created_at,
                        i.updated_at,
                    ]);
                    break;
                case 'applications':
                    arr.push(
                        [
                            {
                                content: `<a href="#" class="delete-link" data-id="${i.id}" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"><span style="color: inherit;"><i class="fa-regular fa-trash-can"></i></span></a>`,
                            },
                            {
                                content: `<a href="#" class="parent-entity-link" data-parent-table-type="users" data-parent-id="${i.user_id}" data-email="${i.email}" data-bs-toggle="modal" data-bs-target="#parentEntityModal">${i.name}</a>`,
                            },
                            i.telephone_number,
                            i.message,
                            {
                                content: populatingSelect(APPLICATION_STATUS_OPTIONS, i.status),
                            },
                            i.created_at,
                            i.updated_at,
                        ]
                    );
                    break;
                default:
                    arr.push(
                        [
                            {
                                content: `<a href="#" class="delete-link" data-id="${i.id}" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"><span style="color: inherit;"><i class="fa-regular fa-trash-can"></i></span></a>`,
                            },
                            {
                                content: `<a href="#" class="parent-entity-link" data-parent-table-type="users" data-parent-id="${i.user_id}" data-email="${i.email}" data-bs-toggle="modal" data-bs-target="#parentEntityModal">${i.name}</a>`,
                            },
                            {
                                content: `<a href="#" class="parent-entity-link" data-parent-table-type="applications" data-parent-id="${i.application_id}" data-bs-toggle="modal" data-bs-target="#parentEntityModal">${i.application_id}</a>`,
                            },
                            {
                                content: populatingSelect(ORDER_STATUS_OPTIONS, i.status),
                            },
                            i.created_at,
                            i.updated_at,
                        ]
                    );
            }
        })

        // Колонки в зависимости от типа таблицы
        let datatableColumns;
        switch (table_type) {
            case 'users':
                datatableColumns = [
                    {
                        editable: false,
                        sortable: false,
                        width: 1,
                    },
                    {
                        name: 'Имя',
                        id: 'name',
                        width: 4,
                    },
                    {
                        name: 'Email-адрес',
                        id: 'email',
                        format: value => `<a href='mailto:${value}' class='link-primary'>${value}</a>`,
                        width: 7,
                    },
                    {
                        name: 'Создан',
                        id: 'created_at',
                        width: 6,
                    },
                    {
                        editable: false,
                        name: 'Изменён',
                        id: 'updated_at',
                        width: 6,
                    },
                ];
                break;
            case 'calls':
                datatableColumns = [
                    {
                        editable: false,
                        sortable: false,
                        width: 1,
                    },
                    {
                        editable: false,
                        name: 'Имя',
                        id: 'name',
                        width: 4,
                    },
                    {
                        name: '№ телефона',
                        id: 'telephone_number',
                        format: value => `<a href='tel:+${value}' class='link-primary'>${value}</a>`,
                        width: 4,
                    },
                    {
                        editable: false,
                        name: 'Статус',
                        id: 'status',
                        width: 6,
                    },
                    {
                        name: 'Создан',
                        id: 'created_at',
                        width: 6,
                    },
                    {
                        editable: false,
                        name: 'Изменён',
                        id: 'updated_at',
                        width: 6,
                    },
                ];
                break;
            case 'applications':
                datatableColumns = [
                    {
                        editable: false,
                        sortable: false,
                        width: 2,
                    },
                    {
                        editable: false,
                        name: 'Имя',
                        id: 'name',
                        width: 4,
                    },
                    {
                        name: '№ телефона',
                        id: 'telephone_number',
                        format: value => `<a href='tel:+${value}' class='link-primary'>${value}</a>`,
                        width: 4,
                    },
                    {
                        name: 'Сообщение',
                        id: 'message',
                        width: 8,
                    },
                    {
                        editable: false,
                        name: 'Статус',
                        id: 'status',
                        width: 6,
                    },
                    {
                        name: 'Создан',
                        id: 'created_at',
                        width: 6,
                    },
                    {
                        editable: false,
                        name: 'Изменён',
                        id: 'updated_at',
                        width: 6,
                    },
                ];
                break;
            default:
                datatableColumns = [
                    {
                        editable: false,
                        sortable: false,
                        width: 2,
                    },
                    {
                        editable: false,
                        name: 'Имя',
                        id: 'name',
                        width: 4,
                    },
                    {
                        editable: false,
                        name: 'ID заявки',
                        id: 'user_id',
                        width: 3,
                    },
                    {
                        editable: false,
                        name: 'Статус',
                        id: 'status',
                        width: 6,
                    },
                    {
                        name: 'Создан',
                        id: 'created_at',
                        width: 6,
                    },
                    {
                        editable: false,
                        name: 'Изменён',
                        id: 'updated_at',
                        width: 6,
                    },
                ];
        }

        // Создание таблицы при помощи объекта DataTable от Frappe.io
        new DataTable(datatable_container, {
            columns: datatableColumns,
            data: arr,
            serialNoColumn: false,
            checkboxColumn: true,
            noDataMessage: 'Не найдено',
            layout: 'ratio',
            inlineFilters: true,
        });

        // Обработка событий в таблицах
        let datatable = document.querySelector(`${datatable_container} > .datatable`),
            checkCount = 0,
            table_num = +datatable.classList[1].slice(-1); // Поскольку скрипт frappe-datatables создаёт экземпляры таблиц данных с последовательным увеличением номера, заносим получившееся число в переменную
        // Делегирование по событию клик для совершения различных операций
        datatable.addEventListener('click', (e) => {
            let eventTarget = e.target;
            if (eventTarget.type !== 'checkbox') e.preventDefault(); // Без сбрасывания события инпутов

            // Проверка на нажатие по иконке удаления
            if (eventTarget.classList.contains('fa-trash-can')) {
                // Данные из удаляемой строки для вывода информации в модальные окна и дальнейшей отправки в теле запроса
                let row = eventTarget.parentElement.parentElement.parentElement.parentElement.parentElement,
                    email = table_type === 'users' ? row.children[3].children[0].children[0].textContent.trim() : row.children[2].children[0].children[0].dataset.email,
                    data;
                data = {
                    id: row.children[1].children[0].children[0].dataset.id,
                    table_type: table_type,
                    _token: TOKEN,
                };
                // Текст подтверждения удаления
                switch (table_type) {
                    case 'users':
                        CONFIRMATION_TITLE.textContent = `Вы точно хотите удалить запись пользователя с email-адресом: ${email}? Все связанные с ним данные (звонки, заявки и т.д.) будут удалены!`;
                        break
                    case 'calls':
                        CONFIRMATION_TITLE.textContent = `Вы точно хотите удалить запись звонка пользователя с email-адресом: ${email}?`;
                        break
                    case 'applications':
                        CONFIRMATION_TITLE.textContent = `Вы точно хотите удалить запись заявки пользователя с email-адресом: ${email}? Связанный с ней заказ будет удалён!`;
                        break
                    default:
                        CONFIRMATION_TITLE.textContent = `Вы точно хотите удалить запись заказа пользователя с email-адресом: ${email}? Связанная с ним заявка будет удалена!`;
                }
                handleDeleteButtonEvent(data, table_num, [row], table_type);
            }

            // Проверка на нажатие по ссылке на запись-родителя
            if (eventTarget.classList.contains('parent-entity-link')) {
                let parentTableType = eventTarget.dataset.parentTableType, parentId = +eventTarget.dataset.parentId, datatableColumns = [], // Колонки
                    parentData = JSON.parse(SERVICE_CONTAINER.dataset[parentTableType]).find(i => +i.id === parentId), parentTableData = []; // Данные
                // Заполнение колонок и данных для каждого типа таблиц
                datatableColumns.push(...[{
                    editable: false,
                    sortable: false,
                    name: 'ID',
                    id: 'id',
                    width: 3,
                },
                {
                    editable: false,
                    sortable: false,
                    name: 'Имя',
                    id: 'name',
                    width: 4,
                }]);
                parentTableData.push(...[parentData.id, parentData.name]);
                if (parentTableType === 'users') {
                    datatableColumns.push({
                        editable: false,
                        sortable: false,
                        name: 'Email-адрес',
                        id: 'email',
                        format: value => `<a href='mailto:${value}' class='link-primary'>${value}</a>`,
                        width: 7,
                    });
                    parentTableData.push(parentData.email);
                }
                if (parentTableType === 'applications') {
                    datatableColumns.push(...[{
                        editable: false,
                        sortable: false,
                        name: '№ телефона',
                        id: 'telephone_number',
                        format: value => `<a href='tel:+${value}' class='link-primary'>${value}</a>`,
                        width: 4,
                    },
                    {
                        editable: false,
                        sortable: false,
                        name: 'Сообщение',
                        id: 'message',
                        width: 8,
                    },
                    {
                        editable: false,
                        sortable: false,
                        name: 'Статус',
                        id: 'status',
                        width: 6,
                    }]);
                    parentTableData.push(...[parentData.telephone_number, parentData.message, parentData.status]);
                }
                datatableColumns.push(...[{
                    editable: false,
                    sortable: false,
                    name: 'Создан',
                    id: 'created_at',
                    width: 6,
                },
                {
                    editable: false,
                    sortable: false,
                    name: 'Изменён',
                    id: 'updated_at',
                    width: 6,
                }]);
                parentTableData.push(...[parentData.created_at, parentData.updated_at]);

                if (PARENT_ENTITY_TITLE.children.length) PARENT_ENTITY_TITLE.children[0].remove(); // Удаление существующей
                setTimeout(() => { // Примерная задержка для того, чтоб таблица нормально сформировалась
                    new DataTable(PARENT_ENTITY_TITLE, {
                        columns: datatableColumns,
                        data: [parentTableData],
                        serialNoColumn: false,
                        layout: 'ratio',
                    });
                    PARENT_ENTITY_TITLE.children[0].style.overflow = 'visible'; // No datatable scroll (приблизительный timeout)
                }, 200);
            }

            // Активация/деактивация кнопки удаления
            let checkAll = document.querySelector(`.dt-instance-${table_num} > .dt-header > div > .dt-row-header > .dt-cell--col-0 > .dt-cell__content > input`),
                rows = document.querySelectorAll(`.dt-instance-${table_num} > .dt-scrollable > .vrow`),
                targetChecked = eventTarget.checked,
                buttonNum = table_type === 'users' ? 0 : table_type === 'calls' ? 1 : table_type === 'applications' ? 2 : 3;
            if (eventTarget.type === 'checkbox') {
                if (eventTarget.parentElement.classList.contains('dt-cell__content--header-0')) {
                    if (targetChecked) { // Отмечен главный чекбокс
                        rows.forEach(i => {
                            if (!i.classList.contains('dt-row--highlight')) i.classList.add('dt-row--highlight');
                            i.classList.remove('dt-row--unhighlight');
                        })
                        checkCount = rows.length;
                    } else { // Сброшен главный чекбокс
                        rows.forEach(i => {
                            i.classList.remove('dt-row--highlight');
                        })
                        checkCount = 0;
                    }
                } else {
                    targetChecked ? checkCount += 1 : checkCount -= 1; // Отмечен чекбокс любой записи/Сброшен чекбокс любой записи
                }
                if (checkAll.checked && !checkCount) checkAll.checked = false; // Снимать галочку с чекбокса всех строк
            }

            // Дополнительная проверка на наличие минимум одной записи и активация/деактивация кнопки "удалить выбранные"
            if (!document.querySelectorAll(`.dt-instance-${table_num} > .dt-scrollable > .vrow`)) checkCount = 0;
            if (checkCount && DELETE_DATA_BUTTONS[buttonNum].classList.contains('disabled')) {
                DELETE_DATA_BUTTONS[buttonNum].classList.remove('disabled');
            }
            if (!checkCount && !DELETE_DATA_BUTTONS[buttonNum].classList.contains('disabled')) {
                DELETE_DATA_BUTTONS[buttonNum].classList.add('disabled');
            }
            DELETE_DATA_BUTTONS[buttonNum].onclick = () => { // Удаление по нажатию кнопки
                selectiveDeletion(table_type, table_num);
            }
            SAVE_DATA_BUTTONS[buttonNum].onclick = () => { // Сохранение по нажатию кнопки
                saveEditedData(table_type, table_num);
            }
        });

        // Делегирование по событию change для обнаружения изменений статусов
        datatable.addEventListener('change', ({target}) => {
            if (target.classList.contains('form-select') && target.value !== target.dataset.default && !target.parentElement.parentElement.parentElement.classList.contains('dt-row--edited')) {
                target.parentElement.parentElement.parentElement.classList.add('dt-row--edited');
            }
        });
    }

    // Изначальная инициализация первой таблицы при загрузке страницы
    handleDatatable('#users-datatable', 'users');
    // При переключении вкладок, инициализация соответствующих таблиц
    document.querySelector('.nav-pills').addEventListener('click', ({target}) => {
        if (target.classList.contains('nav-link')) {
            let datatableBlock = document.querySelector(target.dataset.bsTarget).children[1];
            if (datatableBlock.children[1]) datatableBlock.children[1].remove(); // Удаление существующей
            if (target.classList.contains('active')) { // Замена таблицы
                let type = datatableBlock.getAttribute('id').split('-')[0];
                handleDatatable(`#${type}-datatable`, type);
            }
        }
    });
});
