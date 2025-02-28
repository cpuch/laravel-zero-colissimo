<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use PrettyXml\Formatter;
use Saloon\Laravel\Events\SentSaloonRequest;

class SentSaloonRequestNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(SentSaloonRequest $event): void
    {
        if (env('APP_DEBUG')) {
            $formatter = new Formatter;
            Log::debug($formatter->format($event->pendingRequest->body()));
            Log::debug($formatter->format(utf8_encode($event->response->body())));
        }
    }
}
