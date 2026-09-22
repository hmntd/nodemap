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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('diagrams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagram_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('service'); // service, database, queue, client, external_api
            $table->string('label');
            $table->double('position_x')->default(0);
            $table->double('position_y')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagram_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_node_id')->constrained('nodes')->cascadeOnDelete();
            $table->foreignId('target_node_id')->constrained('nodes')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('type')->default('HTTP'); // HTTP, gRPC, TCP, WebSocket, Queue
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edges');
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('diagrams');
        Schema::dropIfExists('projects');
    }
};
