@extends('template')

@section('title', 'Pure-site')

@section('content')
    <div class="container mx-auto row">
        <div class="col-6">
            <h2 class="mb-4">
                Форма заказа звонка
            </h2>
            <form id="callForm" action="{{ route('call') }}">
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="inputCallName" placeholder="Иван">
                    <label for="inputCallName" class="form-label">Имя</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="inputCallEmail" placeholder="user@example.com">
                    <label for="inputCallEmail" class="form-label">Email-адрес</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" name="telephone_number" class="form-control" id="inputCallTel" placeholder="79268269201">
                    <label class="form-label" for="inputCallTel">Номер телефона</label>
                </div>
                <button type="submit" class="btn btn-warning">Заказать звонок</button>
            </form>
        </div>
        <div class="col-6">
            <h2 class="mb-4">
                Форма отправки заявки
            </h2>
            <form id="applicationForm" action="{{ route('application') }}">
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="inputApplicationName" placeholder="Иван">
                    <label for="inputApplicationName" class="form-label">Имя</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="inputApplicationEmail" placeholder="user@example.com">
                    <label for="inputApplicationEmail" class="form-label">Email-адрес</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" name="telephone_number" class="form-control" id="inputApplicationTel" placeholder="79268269201">
                    <label class="form-label" for="inputApplicationTel">Номер телефона</label>
                </div>
                <div class="form-floating">
                    <textarea class="form-control" name="message" placeholder="Введите сообщение" id="textareaApplicationMessage" style="height: 120px;"></textarea>
                    <label for="textareaApplicationMessage">Сообщение</label>
                </div>
                <div class="mb-3">
                    <label for="inputApplicationFiles" class="form-label"></label>
                    <input class="form-control" type="file" id="inputApplicationFiles" multiple>
                </div>
                <button type="submit" class="btn btn-success">Отправить заявку</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script src="/public/assets/js/forms.js"></script>
@endsection
