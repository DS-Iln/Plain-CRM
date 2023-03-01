@extends('template')

@section('title', 'Панель администратора')

@section('service-container')
    <div class="service-container d-none"
         data-users="{{ json_encode(count($users) > 0 ? $users : []) }}"
         data-calls="{{ json_encode(count($calls) > 0 ? $calls : []) }}"
         data-applications="{{ json_encode(count($applications) > 0 ? $applications : []) }}"
         data-orders="{{ json_encode(count($orders) > 0 ? $orders : []) }}"
         data-call-options="{{ json_encode($callOptions) }}"
         data-application-options="{{ json_encode($applicationOptions) }}"
         data-order-options="{{ json_encode($orderOptions) }}"
         data-delete-url="{{ route('delete') }}"
         data-refresh-url="{{ route('refresh') }}"
         data-update-url="{{ route('update') }}">
    </div>
@endsection

@section('content')
    <div class="container px-0">
        <div class="row mb-5 ps-3 justify-content-center justify-content-md-start">
            <div class="mb-4 d-flex align-items-center text-center text-md-start">
                <h3 class="text-dark m-0 me-3">
                    Актуальные данные
                </h3>
                <p class="text-secondary opacity-75 m-0">
                    <i class="fa-solid fa-circle-info me-1"></i>Для обновления перезагрузите страницу
                </p>
            </div>
            <div class="row row-gap-3 justify-content-center justify-content-md-start">
                <ul class="list-group">
                    <li class="list-group-item d-flex align-items-center py-2">
                        Ожидающих звонка <span class="ms-2 me-auto" style="font-size: 18px;"><i class="fa-solid fa-phone"></i></span>
                        <span class="badge {{ count($waitingCalls) > 0 ? 'bg-danger bg-gradient' : 'bg-danger-subtle' }} rounded-pill">{{ count($waitingCalls) }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center py-2">
                        Заявок в очереди <span class="ms-2 me-auto" style="font-size: 18px;"><i class="fa-solid fa-envelope"></i></span>
                        <span class="badge {{ count($queueApplications) > 0 ? 'bg-warning bg-gradient' : 'bg-warning-subtle' }} rounded-pill">{{ count($queueApplications) }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center py-2">
                        Заказов выполняется <span class="ms-2 me-auto" style="font-size: 18px;"><i class="fa-solid fa-file-code"></i></span>
                        <span class="badge {{ count($processingOrders) > 0 ? 'bg-info bg-gradient' : 'bg-info-subtle' }} rounded-pill">{{ count($processingOrders) }}</span>
                    </li>
                </ul>
                <ul class="list-group">
                    <li class="list-group-item d-flex align-items-center py-2">
                        Завершено звонков <span class="ms-2 me-auto" style="font-size: 18px;"><i class="fa-solid fa-phone-slash"></i></span>
                        <span class="badge {{ count($completedCalls) > 0 ? 'bg-success bg-gradient' : 'bg-success-subtle' }} rounded-pill">{{ count($completedCalls) }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center py-2">
                        Закрытых заявок <span class="ms-2 me-auto" style="font-size: 18px;"><i class="fa-solid fa-envelope-circle-check"></i></span>
                        <span class="badge {{ count($closedApplications) > 0 ? 'bg-success bg-gradient' : 'bg-success-subtle' }} rounded-pill">{{ count($closedApplications) }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center py-2">
                        Завершено заказов <span class="ms-2 me-auto" style="font-size: 18px;"><i class="fa-solid fa-box-archive"></i></span>
                        <span class="badge {{ count($completedOrders) > 0 ? 'bg-success bg-gradient' : 'bg-success-subtle' }} rounded-pill">{{ count($completedOrders) }}</span>
                    </li>
                </ul>
                <ul class="list-group">
                    <li class="list-group-item d-flex align-items-center py-2">
                        Количество обратившихся пользователей за всё время <span class="me-4" style="font-size: 18px;"><i class="fa-solid fa-users"></i></span>
                        <span class="badge {{ count($users) > 0 ? 'bg-primary bg-gradient' : 'bg-primary-subtle' }} rounded-pill">{{ count($users) }}</span>
                    </li>
                </ul>

            </div>
        </div>
        <div class="d-flex align-items-start flex-lg-row flex-column row-gap-3">
            <div class="nav flex-lg-column flex-row nav-pills me-lg-3 mx-auto mx-lg-0" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <button class="nav-link active" id="v-pills-users-tab" data-bs-toggle="pill" data-bs-target="#v-pills-users" type="button" role="tab" aria-controls="v-pills-users" aria-selected="true">Пользователи</button>
                <button class="nav-link" id="v-pills-calls-tab" data-bs-toggle="pill" data-bs-target="#v-pills-calls" type="button" role="tab" aria-controls="v-pills-calls" aria-selected="false">Звонки</button>
                <button class="nav-link" id="v-pills-applications-tab" data-bs-toggle="pill" data-bs-target="#v-pills-applications" type="button" role="tab" aria-controls="v-pills-applications" aria-selected="false">Заявки</button>
                <button class="nav-link" id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab" aria-controls="v-pills-orders" aria-selected="false">Заказы</button>
            </div>
            <div class="tab-content w-100" id="v-pills-tabContent">
                <section class="tab-pane fade show active flex-column" id="v-pills-users" role="tabpanel" aria-labelledby="v-pills-users-tab" tabindex="0">
                    <div class="controls d-flex justify-content-center mb-3">
                        <button class="btn btn-outline-danger me-3 disabled" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" type="button" id="delete-users-data">
                            Удалить выбранные
                        </button>
                        <button class="btn btn-outline-success" type="button" id="save-users-data">
                            Сохранить изменения
                        </button>
                    </div>
                    <div class="tab-pane__datatable-block" id="users-datatable">

                    </div>
                </section>
                <section class="tab-pane fade flex-column" id="v-pills-calls" role="tabpanel" aria-labelledby="v-pills-calls-tab" tabindex="0">
                    <div class="controls d-flex justify-content-center mb-3">
                        <button class="btn btn-outline-danger me-3 disabled" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" type="button" id="delete-calls-data">
                            Удалить выбранные
                        </button>
                        <button class="btn btn-outline-success" type="button" id="save-calls-data">
                            Сохранить изменения
                        </button>
                    </div>
                    <div class="tab-pane__datatable-block" id="calls-datatable">

                    </div>
                </section>
                <section class="tab-pane fade flex-column" id="v-pills-applications" role="tabpanel" aria-labelledby="v-pills-applications-tab" tabindex="0">
                    <div class="controls d-flex justify-content-center mb-3">
                        <button class="btn btn-outline-danger me-3 disabled" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" type="button" id="delete-applications-data">
                            Удалить выбранные
                        </button>
                        <button class="btn btn-outline-success" type="button" id="save-applications-data">
                            Сохранить изменения
                        </button>
                    </div>
                    <div class="tab-pane__datatable-block" id="applications-datatable">

                    </div>
                </section>
                <section class="tab-pane fade flex-column" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab" tabindex="0">
                    <div class="controls d-flex justify-content-center mb-3">
                        <button class="btn btn-outline-danger me-3 disabled" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" type="button" id="delete-orders-data">
                            Удалить выбранные
                        </button>
                        <button class="btn btn-outline-success" type="button" id="save-orders-data">
                            Сохранить изменения
                        </button>
                    </div>
                    <div class="tab-pane__datatable-block" id="orders-datatable">

                    </div>
                </section>
            </div>
        </div>
        {{-- Delete confirmation modal --}}
        <div class="modal modal-sm fade" id="deleteConfirmationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteConfirmationModalLabel">Подтвердите удаление</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="confirmation-title">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="delete-button">Удалить</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal modal-lg fade" id="parentEntityModal" tabindex="-1" aria-labelledby="parentEntityModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="parentEntityModalLabel">Запись-родитель</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="parent-entity-title">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div aria-live="polite" aria-atomic="true" class="toast-container position-absolute bottom-0 end-0 p-3"></div>
@endsection

@section('scripts')
    @parent
    {{-- Sortable.js for datatables smart sorting --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js" integrity="sha512-Eezs+g9Lq4TCCq0wae01s9PuNWzHYoCMkE97e2qdkYthpI0pzC3UGB03lgEHn2XM85hDOUF6qgqqszs+iXU4UA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- Datatables --}}
    <script src="./public/assets/js/datatables.js" type="module"></script>
@endsection
