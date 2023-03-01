<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Переход на админ-панель с выводом данных
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        $users = DB::table('users')->where('isEmployee', '0')->get();

        $calls = DB::table('calls')
            ->join('users', 'users.id', '=', 'calls.user_id')
            ->select('calls.*', 'users.name', 'users.email')
            ->get(); // Звонки
        $waitingCalls = DB::table('calls')->where('status', 'В ожидании')->get(); // Ожидающие звонки
        $completedCalls = DB::table('calls')->where('status', 'Завершён')->get(); // Завершённые звонки
        $callOptions = DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->select('COLUMN_TYPE as possibleStatusValues')
            ->where('TABLE_NAME', 'calls')
            ->where('COLUMN_NAME', 'status')
            ->get(); // Возможные статусы звонков

        $applications = DB::table('applications')
            ->join('users', 'users.id', '=', 'applications.user_id')
            ->select('applications.*', 'users.name', 'users.email')
            ->get(); // Заявки
        $queueApplications = DB::table('applications')->where('status', 'В очереди')->get(); // Заявки в очереди
        $closedApplications = DB::table('applications')->where('status', 'Закрыта')->get(); // Закрытые заявки
        $applicationOptions = DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->select('COLUMN_TYPE as possibleStatusValues')
            ->where('TABLE_NAME', 'applications')
            ->where('COLUMN_NAME', 'status')
            ->get(); // Возможные статусы заявок

        $orders = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('applications', 'applications.id', '=', 'orders.application_id')
            ->select('orders.*', 'users.name', 'users.email', 'applications.id as application_id')
            ->get(); // Заказы
        $processingOrders = DB::table('orders')->where('status', 'Выполняется')->get(); // Выполняемые заказы
        $completedOrders = DB::table('orders')->where('status', 'Завершён')->get(); // Выполненные заказы
        $orderOptions = DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->select('COLUMN_TYPE as possibleStatusValues')
            ->where('TABLE_NAME', 'orders')
            ->where('COLUMN_NAME', 'status')
            ->get(); // Возможные статусы заказов

        return view('admin.admin_panel', [
            'users' => $users,
            'calls' => $calls,
            'waitingCalls' => $waitingCalls,
            'completedCalls' => $completedCalls,
            'callOptions' => $callOptions,
            'applications' => $applications,
            'queueApplications' => $queueApplications,
            'closedApplications' => $closedApplications,
            'applicationOptions' => $applicationOptions,
            'orders' => $orders,
            'processingOrders' => $processingOrders,
            'completedOrders' => $completedOrders,
            'orderOptions' => $orderOptions,
        ]);
    }

    /**
     * Функция отправки обновлённых данных по конкретной таблице
     * @return JsonResponse
     */
    public function refreshClientData(): JsonResponse
    {
        $users = DB::table('users')->where('isEmployee', '0')->get();
        $calls = DB::table('calls')
            ->join('users', 'users.id', '=', 'calls' . '.user_id')
            ->select('calls' . '.*', 'users.name', 'users.email')
            ->get();
        $applications = DB::table('applications')
            ->join('users', 'users.id', '=', 'applications' . '.user_id')
            ->select('applications' . '.*', 'users.name', 'users.email')
            ->get();
        $orders = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders' . '.user_id')
            ->join('applications', 'applications.id', '=', 'orders' . '.application_id')
            ->select('orders' . '.*', 'users.name', 'users.email')
            ->get();
        return response()->json(['status' => true, 'users' => json_encode($users), 'calls' => json_encode($calls), 'applications' => json_encode($applications), 'orders' => json_encode($orders)]);
    }

    /**
     * Функция внесения правок
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $table_type = $request->input('table_type');
        $requestArr = $request->input('elementsToUpdate');
        // Приведение данных к ассоциативному массиву
        $elementsToUpdate = [];
        foreach ($requestArr as $requestArrElem) {
            $arr = [];
            foreach ($requestArrElem as $pair) {
                $arr += [$pair[0] => $pair[1]];
            }
            $elementsToUpdate[] = $arr;
        }
        // Внесение изменений и занесение в массив удавшихся/неудавшихся попыток
        $affected = [];
        foreach ($elementsToUpdate as $elemContent) {
            $id = +$elemContent['id'];
            if ($table_type === 'applications') { // При смене статуса заявки на "закрыта", создавать экземпляр заказа
                $application = Application::where('id', $id)->first();
                if ($application->status !== $elemContent['status'] && $elemContent['status'] === 'Закрыта') { // Проверка на смену статуса
                    if (!Order::where('application_id', $id)->first()) {
                        Order::create([
                            'user_id' => $application->user_id,
                            'application_id' => $id,
                        ]);
                    }
                }
            }
            unset($elemContent['id']);
            $affected[] = DB::table($table_type)
                ->where('id', $id)
                ->update($elemContent);
        }
        return response()->json(['status' => !in_array(false, $affected, true), 'message' => !in_array(false, $affected, true) ? 'Данные успешно сохранены.' : 'Не удалось сохранить данные.']);
    }

    /**
     * Вспомогательная функция удаления записи
     * @param $table_type
     * @param $id
     * @return int|Expression
     */
    function deleteRecord($table_type, $id): int|Expression
    {
        if ($table_type === 'orders') { // В случае заказа, необходимо удалять соответствующую заявку
            $order = Order::where('id', $id)->first();
            $applicationId = $order->application_id;
            $order->delete();
            $deleted = DB::table('applications')->where('id', $applicationId)->delete();
        } else {
            $deleted = DB::table($table_type)->where('id', $id)->delete();
        }
        return $deleted;
    }

    /**
     * Функция удаления записей
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $elementsToDelete = $request->input('elementsToDelete');
        if ($elementsToDelete) {
            $deleted = [];
            foreach ($elementsToDelete as $elementToDelete) {
                $deleted[] = $this->deleteRecord($elementToDelete['table_type'], $elementToDelete['id']);
            }
            return response()->json(['status' => !in_array(false, $deleted, true), 'message' => !in_array(false, $deleted, true) ? 'Выбранные записи успешно удалены.' : 'Не удалось удалить выбранные записи.']);
        }
        $table_type = $request->input('table_type');
        $id = $request->input('id');
        $deleted = $this->deleteRecord($table_type, $id);
        return response()->json(['status' => !!$deleted, 'message' => !!$deleted ? 'Запись успешно удалена.' : 'Не удалось удалить запись.']);
    }
}
