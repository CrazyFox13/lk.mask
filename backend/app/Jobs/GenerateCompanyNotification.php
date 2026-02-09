<?php

namespace App\Jobs;

use App\Models\CompanyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject;
    public string $text;
    public string $key;
    public string $resource_key;
    public int $resource_id;
    public array $recipients; // [ [user_id=>1,company_id=>1] ]
    public array $additional_data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->recipients as $recipient) {
            $data = [
                'key' => $this->key,
            ];
            $data[$this->resource_key] = $this->resource_id;

            $notification = new CompanyNotification([
                'company_id' => $recipient['company_id'],
                'user_id' => $recipient['user_id'],
                'subject' => $this->subject,
                'text' => $this->text,
                'data' => array_merge($data, $this->additional_data),
                'push' => true,
            ]);
            $notification->save();
        }
    }
}
