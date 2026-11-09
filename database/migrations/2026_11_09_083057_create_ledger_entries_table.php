<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manually recorded income and expenses (rent, salaries, ads, stock purchases,
     * service income, ...). Order revenue is not stored here; the finance report
     * reads it from the orders themselves.
     */
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('category', 30);
            $table->string('title', 150);
            $table->decimal('amount', 12, 2);
            // Local shop date (app.display_timezone), not a UTC timestamp.
            $table->date('entry_date');
            $table->string('account', 20)->default('cash');
            $table->string('reference', 100)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'entry_date']);
            $table->index(['category', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
