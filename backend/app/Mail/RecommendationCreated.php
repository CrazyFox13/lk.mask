<?php

namespace App\Mail;

use App\Models\NotificationTypeUser;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecommendationCreated extends BaseMailer
{
    use Queueable, SerializesModels;

    public string $author;

    public string $key = 'personal';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Recommendation $recommendation)
    {
        $target = User::find($recommendation->target_user_id);
        parent::__construct($this->key, $target->email);
        $authorUser = $recommendation->author()->first();
        $this->author = "$authorUser->name $authorUser->surname";

        $company = $authorUser->company()->published()->first();
        if ($company) {
            $this->author = $company->title;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.recommendation_created')->subject('У вас новая рекомендация');
    }
}
