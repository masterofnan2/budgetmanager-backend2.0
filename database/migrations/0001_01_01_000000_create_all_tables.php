<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('description', 255)->nullable();
            $table->float('amount', 2);
            $table->foreignId('user_id');
            $table->foreignId('cycle_id');
        });

        Schema::create('confirmation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('content', 10);
            $table->timestamp('expires_at');
            $table->foreignId('user_id');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id');
            $table->float('budget')->nullable();
            $table->foreignId('cycle_id');
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('description')->nullable();
            $table->float('amount', 2);
            $table->foreignId('user_id');
            $table->foreignId('category_id');
            $table->foreignId('cycle_id');
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->float('amount', 2);
            $table->foreignId('user_id');
            $table->foreignId('cycle_id');
            $table->timestamps();
        });

        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->timestamps();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('cycles');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('confirmation_codes');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
