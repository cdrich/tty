<?php

use App\Models\TransactionNumber;
use App\Models\User;
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
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->enum('type',['Transfert','Retrait'])->default('Transfert');
            $table->foreignIdFor(User::class)->constrained()->cascadeOnUpdate();
            $table->foreignIdFor(TransactionNumber::class)->constrained()->cascadeOnDelete();
            $table->string('recever');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};
