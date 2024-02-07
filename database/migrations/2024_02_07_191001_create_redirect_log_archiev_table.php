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
    public function up()
    {
        Schema::create('redirect_log_archiev', function (Blueprint $table) {
            $table->id();
            $table->foreignId('redirect_id')->constrained('redirect');
            $table->string('ip_request', 15);
            $table->text('user_agent_request');
            $table->text('header_referer_request')->nullable();
            $table->text('query_param_request')->nullable();
            $table->timestamp('access_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->index('redirect_id');
            $table->index('access_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redirect_log_archiev');
    }
};
