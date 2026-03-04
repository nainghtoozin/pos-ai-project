<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function ($table) {
            if (!Schema::hasColumn('sales', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('sales', 'subtotal')) {
                $table->unsignedInteger('subtotal')->default(0)->after('invoice_no');
            }
            if (!Schema::hasColumn('sales', 'discount')) {
                $table->unsignedInteger('discount')->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('sales', 'tax')) {
                $table->unsignedInteger('tax')->default(0)->after('discount');
            }
            if (!Schema::hasColumn('sales', 'grand_total')) {
                $table->unsignedInteger('grand_total')->default(0)->after('tax');
            }
            if (!Schema::hasColumn('sales', 'total_cost')) {
                $table->unsignedInteger('total_cost')->default(0)->change();
            }
            if (!Schema::hasColumn('sales', 'total_profit')) {
                $table->unsignedInteger('total_profit')->default(0)->change();
            }
            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->enum('payment_status', ['paid', 'partial', 'due'])->default('due')->after('due_amount');
            }
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('sales', 'note') && !Schema::hasColumn('sales', 'notes')) {
                $table->text('note')->nullable();
            }
        });

        Schema::table('sales', function ($table) {
            if (!Schema::hasIndex('sales', 'sales_branch_id_index')) {
                $table->index('branch_id');
            }
            if (!Schema::hasIndex('sales', 'sales_user_id_index')) {
                $table->index('user_id');
            }
            if (!Schema::hasIndex('sales', 'sales_sale_date_index')) {
                $table->index('sale_date');
            }
            if (!Schema::hasIndex('sales', 'sales_payment_status_index')) {
                $table->index('payment_status');
            }
        });
    }

    public function down(): void
    {
    }
};
