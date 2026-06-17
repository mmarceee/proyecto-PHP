<?php

namespace App\Jobs;

use App\Models\MongoEventLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegistrarEventLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $eventType,
        public array $payload,
        public ?int $userId = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    public function handle(): void
    {
        MongoEventLog::create([
            'event_type' => $this->eventType,
            'payload' => $this->payload,
            'user_id' => $this->userId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ]);
    }
}