<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'Создан', // Заказ сформирован и подтвержден
                'Выполняется', // Отдел разработки приступил к выполнению заказа
                'Срок выполнения перенесён по просьбе исполнителя',
                'Срок выполнения перенесён по просьбе заказчика',
                'Отменён',
                'Выполнен', // Отдел разработки завершил свою работу и заказ ожидает проверки со стороны заказчика
                'Завершён', // Заказчик доволен выполненным заказом и последнему присваивается финальный статус
            ])->default('Создан');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
