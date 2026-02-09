<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ForceRemoveCompanyType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company-type:force-remove-customer-supplier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Принудительно удаляет тип "Заказчик-Поставщик" через SQL и заменяет на "Поставщик"';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Поиск типа "Заказчик-Поставщик"...');
        
        // Ищем тип через SQL напрямую
        $customerSupplierType = DB::table('company_types')
            ->where('title', 'Заказчик-Поставщик')
            ->orWhere('title', 'like', '%Заказчик%Поставщик%')
            ->first();
        
        if (!$customerSupplierType) {
            $this->warn('Тип "Заказчик-Поставщик" не найден.');
            
            // Показываем все типы
            $allTypes = DB::table('company_types')->get();
            $this->info('Текущие типы в базе данных:');
            foreach ($allTypes as $type) {
                $count = DB::table('companies')->where('company_type_id', $type->id)->count();
                $this->line("  - ID: {$type->id}, Название: '{$type->title}', Компаний: {$count}");
            }
            
            return Command::SUCCESS;
        }
        
        $this->info("Найден тип ID: {$customerSupplierType->id}, Название: '{$customerSupplierType->title}'");
        
        // Находим тип "Поставщик"
        $supplierType = DB::table('company_types')->where('title', 'Поставщик')->first();
        
        if (!$supplierType) {
            $this->error('Тип "Поставщик" не найден!');
            return Command::FAILURE;
        }
        
        $this->info("Тип 'Поставщик' найден с ID: {$supplierType->id}");
        
        // Подсчитываем компании
        $companiesCount = DB::table('companies')
            ->where('company_type_id', $customerSupplierType->id)
            ->count();
        
        $this->info("Найдено компаний с типом 'Заказчик-Поставщик': {$companiesCount}");
        
        if ($companiesCount > 0) {
            // Обновляем компании через SQL
            $updated = DB::table('companies')
                ->where('company_type_id', $customerSupplierType->id)
                ->update(['company_type_id' => $supplierType->id]);
            
            $this->info("Обновлено компаний: {$updated}");
        }
        
        // Удаляем тип через SQL
        DB::table('company_types')->where('id', $customerSupplierType->id)->delete();
        
        $this->info('Тип "Заказчик-Поставщик" успешно удален из базы данных.');
        
        // Очищаем кеш
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->info('Кеш очищен.');
        
        // Показываем финальный список
        $finalTypes = DB::table('company_types')->get();
        $this->info('Текущие типы компаний:');
        foreach ($finalTypes as $type) {
            $this->line("  - ID: {$type->id}, Название: '{$type->title}'");
        }
        
        return Command::SUCCESS;
    }
}
