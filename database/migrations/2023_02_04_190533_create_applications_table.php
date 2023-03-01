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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('telephone_number');
            $table->string('message')->nullable();
            $table->enum('status', [
                'В очереди', // Статус при создании заявки
                'Информация предоставлена', // Информация поступила к менеджерам, но они еще не приступили к ознакомлению
                'Обрабатывается', // Менеджеры ознакомляются с предоставленной информацией
                'Отменена', // В случае каких-либо несостыковок
                'Закрыта', // Заявка закрыта в случае отмены, либо она перешла в форму ЗАКАЗА
            ])->default('В очереди');
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
        Schema::dropIfExists('applications');
    }
};
