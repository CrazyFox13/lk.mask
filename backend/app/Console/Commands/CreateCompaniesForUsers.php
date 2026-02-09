<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyType;
use App\Models\User;
use Illuminate\Console\Command;

class CreateCompaniesForUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает компании для пользователей, у которых нет компании';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Поиск пользователей без компаний...');
        
        // Находим тип "Заказчик"
        $customerType = CompanyType::where('title', 'Заказчик')->first();
        
        if (!$customerType) {
            $this->error('Тип компании "Заказчик" не найден в базе данных!');
            return Command::FAILURE;
        }
        
        $this->info("Найден тип компании 'Заказчик' с ID: {$customerType->id}");
        
        // Находим всех обычных пользователей без компании
        $users = User::query()
            ->where(function ($query) {
                $query->where('type', 'user')
                    ->orWhereNull('type');
            })
            ->whereNull('company_id')
            ->get();
        
        $count = $users->count();
        $this->info("Найдено пользователей без компании: {$count}");
        
        if ($count === 0) {
            $this->info('Все пользователи уже имеют компании.');
            return Command::SUCCESS;
        }
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $created = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            try {
                // Создаем компанию
                $company = new Company([
                    'company_type_id' => $customerType->id,
                    'moderation_status' => Company::MODERATION_STATUSES['DRAFT'],
                ]);
                $company->save();
                
                // Связываем пользователя с компанией как босса
                $user->company_id = $company->id;
                $user->company_role = User::COMPANY_ROLES[1]; // 'boss'
                $user->save();
                
                $created++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Ошибка при создании компании для пользователя ID {$user->id}: {$e->getMessage()}");
                $errors++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Успешно создано компаний: {$created}");
        if ($errors > 0) {
            $this->warn("Ошибок при создании: {$errors}");
        }
        
        return Command::SUCCESS;
    }
}
