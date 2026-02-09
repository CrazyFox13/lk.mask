<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyType;
use Illuminate\Console\Command;

class RemoveCompanyType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company-type:remove-customer-supplier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет тип "Заказчик-Поставщик" и заменяет его на "Поставщик" во всех компаниях';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Показываем все существующие типы
        $allTypes = CompanyType::all();
        $this->info('Существующие типы компаний в базе данных:');
        foreach ($allTypes as $type) {
            $companiesCount = Company::where('company_type_id', $type->id)->count();
            $this->line("  - ID: {$type->id}, Название: '{$type->title}', Компаний: {$companiesCount}");
        }
        
        // Находим тип "Заказчик-Поставщик" (пробуем разные варианты)
        $customerSupplierType = CompanyType::where('title', 'Заказчик-Поставщик')
            ->orWhere('title', 'Заказчик - Поставщик')
            ->orWhere('title', 'Заказчик/Поставщик')
            ->orWhere('title', 'like', '%Заказчик%Поставщик%')
            ->first();
        
        if (!$customerSupplierType) {
            $this->warn('Тип "Заказчик-Поставщик" не найден в базе данных.');
            $this->info('Возможно, он уже был удален или типы еще не созданы.');
            
            // Проверяем, есть ли компании с несуществующим типом (orphaned records)
            $allCompanyTypeIds = CompanyType::pluck('id')->toArray();
            $orphanedCompanies = Company::whereNotNull('company_type_id')
                ->whereNotIn('company_type_id', $allCompanyTypeIds)
                ->count();
            
            if ($orphanedCompanies > 0) {
                $this->warn("Найдено {$orphanedCompanies} компаний с несуществующим типом.");
                $this->info('Эти компании нужно обновить вручную через админ-панель.');
            }
            
            $this->info('Если типы не созданы, выполните: php artisan db:seed --class=CompanyTypeSeeder');
            return Command::SUCCESS;
        }

        // Находим тип "Поставщик"
        $supplierType = CompanyType::where('title', 'Поставщик')->first();
        
        if (!$supplierType) {
            $this->error('Тип "Поставщик" не найден в базе данных.');
            return Command::FAILURE;
        }

        // Подсчитываем количество компаний с типом "Заказчик-Поставщик"
        $companiesCount = Company::where('company_type_id', $customerSupplierType->id)->count();
        
        $this->info("Найдено компаний с типом 'Заказчик-Поставщик': {$companiesCount}");

        if ($companiesCount > 0) {
            // Обновляем все компании
            $updated = Company::where('company_type_id', $customerSupplierType->id)
                ->update(['company_type_id' => $supplierType->id]);
            
            $this->info("Обновлено компаний: {$updated}");
        }

        // Удаляем тип "Заказчик-Поставщик"
        $customerSupplierType->delete();
        
        $this->info('Тип "Заказчик-Поставщик" успешно удален.');
        
        // Очищаем кеш приложения
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->info('Кеш очищен.');
        
        // Показываем финальный список типов
        $finalTypes = CompanyType::all();
        $this->info('Текущие типы компаний после удаления:');
        foreach ($finalTypes as $type) {
            $this->line("  - ID: {$type->id}, Название: '{$type->title}'");
        }

        return Command::SUCCESS;
    }
}
