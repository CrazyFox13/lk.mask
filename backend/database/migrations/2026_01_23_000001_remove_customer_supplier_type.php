<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Находим ID типа "Заказчик-Поставщик"
        $customerSupplierType = DB::table('company_types')
            ->where('title', 'Заказчик-Поставщик')
            ->orWhere('title', 'like', '%Заказчик%Поставщик%')
            ->first();
        
        if ($customerSupplierType) {
            // Находим ID типа "Поставщик"
            $supplierType = DB::table('company_types')
                ->where('title', 'Поставщик')
                ->first();
            
            if ($supplierType) {
                // Обновляем все компании с типом "Заказчик-Поставщик" на "Поставщик"
                DB::table('companies')
                    ->where('company_type_id', $customerSupplierType->id)
                    ->update(['company_type_id' => $supplierType->id]);
            }
            
            // Удаляем тип "Заказчик-Поставщик"
            DB::table('company_types')
                ->where('id', $customerSupplierType->id)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Восстанавливать тип не будем, так как он больше не нужен
    }
};
