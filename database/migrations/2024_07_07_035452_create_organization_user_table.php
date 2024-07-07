<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Define as string to match userId
            $table->string('organization_id'); // Define as string to match orgId
            $table->timestamps();
            
            $table->foreign('user_id')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('orgId')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
