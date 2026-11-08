<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product page extras: a free-gift / offer line and a question-and-answer list.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('offer_text', 255)->nullable()->after('warranty');
            // list<array{question: string, answer: string}>
            $table->json('faqs')->nullable()->after('description_bn');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['offer_text', 'faqs']);
        });
    }
};
