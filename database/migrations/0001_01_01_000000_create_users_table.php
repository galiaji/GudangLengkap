<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Unique username for login. Make sure this column is unique.
            $table->string('username')->unique();

            // Optionally, a full name or display name.
            $table->string('name')->nullable();

            // Email field with uniqueness.
            $table->string('email')->unique();

            // Optional: verify when the email becomes verified.
            $table->timestamp('email_verified_at')->nullable();

            // Password field for authentication.
            $table->string('password');

            // "Remember me" token used by Laravel for session persistence.
            $table->rememberToken();

            // Timestamps for created and updated at.
            $table->timestamps();
        });

         Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}