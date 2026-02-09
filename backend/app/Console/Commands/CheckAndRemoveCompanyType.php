<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckAndRemoveCompanyType extends Command
{
    protected $signature = 'company-type:check-and-remove';
    protected $description = 'Проверяет и удаляет тип "Заказчик-Поставщик" с полной очисткой кеша';

    public function handle()
    {
        $this->info('=== Проверка типов компаний ===');
        
        // Показываем все типы
        $allTypes = DB::table('company_types')->get();
        $this->info('Текущие типы в базе данных:');
        foreach ($allTypes as $type) {
            $count = DB::table('companies')->where('company_type_id', $type->id)->count();
            $this->line("  - ID: {$type->id}, Название: '{$type->title}', Компаний: {$count}");
        }
        
        // Ищем тип "Заказчик-Поставщик"
        $customerSupplierType = DB::table('company_types')
            ->where('title', 'Заказчик-Поставщик')
            ->orWhere('title', 'like', '%Заказчик%Поставщик%')
            ->first();
        
        if ($customerSupplierType) {
            $this->warn("Найден тип 'Заказчик-Поставщик' с ID: {$customerSupplierType->id}");
            
            // Находим тип "Поставщик"
            $supplierType = DB::table('company_types')->where('title', 'Поставщик')->first();
            
            if (!$supplierType) {
                $this->error('Тип "Поставщик" не найден!');
                return Command::FAILURE;
            }
            
            // Обновляем компании
            $companiesCount = DB::table('companies')
                ->where('company_type_id', $customerSupplierType->id)
                ->count();
            
            if ($companiesCount > 0) {
                $updated = DB::table('companies')
                    ->where('company_type_id', $customerSupplierType->id)
                    ->update(['company_type_id' => $supplierType->id]);
                $this->info("Обновлено компаний: {$updated}");
            }
            
            // Удаляем тип
            DB::table('company_types')->where('id', $customerSupplierType->id)->delete();
            $this->info('Тип "Заказчик-Поставщик" удален.');
        } else {
            $this->info('Тип "Заказчик-Поставщик" не найден в базе данных.');
        }
        
        // Полная очистка кеша
        $this->info('Очистка кеша...');
        Cache::flush();
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->info('Кеш полностью очищен.');
        
        // Показываем финальный список
        $finalTypes = DB::table('company_types')->get();
        $this->info('Финальные типы компаний:');
        foreach ($finalTypes as $type) {
            $count = DB::table('companies')->where('company_type_id', $type->id)->count();
            $this->line("  - ID: {$type->id}, Название: '{$type->title}', Компаний: {$count}");
        }
        
        $this->info('Готово! Перезапустите backend сервер и обновите страницу в браузере.');
        
        return Command::SUCCESS;
    }
}
