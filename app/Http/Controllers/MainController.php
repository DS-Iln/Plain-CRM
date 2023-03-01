<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\User;
use App\Models\Application;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MainController extends Controller
{
    /**
     * Переход на главную страницу
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('main.main');
    }

    /**
     * Функция обработки входящих заявок
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function application(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
//            , 'regex:/^[а-я\s-]+$/g'
            'name' => ['required'],
            'telephone_number' => ['required', 'size:11', 'regex:/^[\d]+$/'],
            'email' => ['required', 'email:rfc'],
//            'regex:/^[A-Za-zА-Яа-яёЁ\d()\-+_=!?\.\,\s%;:$#№@"\'\[\]]*$/'
            'message' => ['nullable'],
            'files' => ['nullable'],
        ],
        [
            'name.required' => 'Обязательное поле',
//            'name.regex' => 'Допустимый формат: первая заглавная буква, символы кириллицы',
            'telephone_number.required' => 'Обязательное поле',
            'telephone_number.regex' => 'Номер состоит из цифр',
            'telephone_number.size' => 'Длина номера должна составлять 11 символов',
            'email.required' => 'Обязательное поле',
            'email.email' => 'Недопустимый формат email-адреса',
            'message.' => 'Обязательное поле',
//            'message.regex' => 'Недопустимый формат сообщения',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'messages' => $validator->messages()], 302);
        }
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            // Создание записи заказчика
            $user = User::create($request->only(['name', 'email']));
        }
        // Создание заявки для существующего пользователя
        $application = Application::create([
            'user_id' => $user->id,
            'telephone_number' => $request->input('telephone_number'),
            'message' => $request->input('message') ? : '',
        ]);
        // При наличии прикрепленных файлов, их создание и привязка
        // ...
        return response()->json(['status' => true]);
    }

    /**
     * Функция обработки входящих звонков
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function call(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
//            , 'regex:/^[а-я\s-]+$/i'
            'name' => ['required'],
            'email' => ['required', 'email:rfc'],
            'telephone_number' => ['required', 'size:11', 'regex:/^[\d]+$/'],
        ],
        [
            'name.required' => 'Обязательное поле',
//            'name.regex' => 'Допустимый формат: первая заглавная буква, символы кириллицы',
            'email.required' => 'Обязательное поле',
            'email.email' => 'Недопустимый формат email-адреса',
            'telephone_number.required' => 'Обязательное поле',
            'telephone_number.regex' => 'Номер состоит из цифр',
            'telephone_number.size' => 'Длина номера должна составлять 11 символов',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'messages' => $validator->messages()], 302);
        }
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            // Создание записи заказчика
            $user = User::create($request->only('name', 'email'));
        }
        // Создание заказа для существующего пользователя
        $call = Call::create([
            'user_id' => $user->id,
            'telephone_number' => $request->input('telephone_number'),
        ]);
        return response()->json(['status' => true]);
    }
}
