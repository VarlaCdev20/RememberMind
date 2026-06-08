<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            
            // Definición manual de morphs para soportar claves primarias tipo string (como cod_am, cod_usu)
            $table->string('subject_type', 255)->nullable();
            $table->string('subject_id', 255)->nullable();
            $table->string('causer_type', 255)->nullable();
            $table->string('causer_id', 255)->nullable();
            
            $table->string('event', 191)->nullable();
            $table->char('batch_uuid', 36)->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'subject_idx');
            $table->index(['causer_type', 'causer_id'], 'causer_idx');
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
