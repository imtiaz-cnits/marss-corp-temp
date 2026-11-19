<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investor_infos')) {
            Schema::create('investor_infos', function (Blueprint $table) {
                $table->id();
                $table->string('investor_id')->unique();
                $table->string('name');
                $table->string('mobile');
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('status')->default('Active');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('invests')) {
            Schema::create('invests', function (Blueprint $table) {
                $table->id();
                $table->decimal('invest_amount', 15, 2)->default(0);
                $table->longText('invest_details')->nullable();
                $table->date('date');
                $table->unsignedBigInteger('investor_info_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('investor_info_id')->references('id')->on('investor_infos')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invests');
        Schema::dropIfExists('investor_infos');
    }
};
