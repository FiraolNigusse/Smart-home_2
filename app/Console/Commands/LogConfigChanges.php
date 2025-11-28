<?php

namespace App\Console\Commands;

use App\Services\ConfigChangeLogger;
use Illuminate\Console\Command;

class LogConfigChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:log-config-changes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and log any configuration file changes';

    /**
     * Execute the console command.
     */
    public function handle(ConfigChangeLogger $logger): int
    {
        $this->info('Checking for configuration changes...');
        
        $logger->logConfigChanges();

        $this->info('Configuration check completed.');

        return Command::SUCCESS;
    }
}

