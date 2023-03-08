<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_managers', static function (Blueprint $table): void {
            $table->string('prefix', 60)->index();
            $table->uuid('process_id')->index();
            $table->string('next_event');
            $table->json('extra')->nullable();
            $table->timestampsTz(6);

            $table->unique(['prefix', 'process_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_managers');
    }
};
