<?php

namespace App\Console\Commands;

use App\Services\ConfigChangeLogger;
use Illuminate\Console\Command;

class LogConfigCacheEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:cache:log {event : The cache event (cleared, cached, etc.)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log a configuration cache event';

    /**
     * Execute the console command.
     */
    public function handle(ConfigChangeLogger $logger): int
    {
        $event = $this->argument('event');
        
        $logger->logConfigCacheEvent($event);

        $this->info("Configuration cache event '{$event}' logged.");

        return Command::SUCCESS;
    }
}

