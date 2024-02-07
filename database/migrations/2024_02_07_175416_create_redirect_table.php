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
        Schema::create('redirect', function (Blueprint $table) {
            $table->id();
            $table->string('alias', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->text('redirect_url');
            $table->string('query_params', 100)->nullable();
            $table->foreignId('status_id')->constrained('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->unique(['alias', 'code']);
            $table->index('status_id');
            $table->index('alias');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redirect');
    }
};
