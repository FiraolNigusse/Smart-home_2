<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\AuditLog;
use App\Models\Rule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class RestoreSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:restore {file} {--force : Force restore without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore system data from a backup file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Check if file exists
        if (!File::exists($filePath)) {
            $this->error("Backup file not found: {$filePath}");
            return 1;
        }

        // Extract ZIP if needed
        $jsonPath = $filePath;
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
            $this->info('Extracting ZIP archive...');
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $extractPath = sys_get_temp_dir() . '/restore_' . uniqid();
                File::makeDirectory($extractPath, 0755, true);
                $zip->extractTo($extractPath);
                $zip->close();
                
                // Find JSON file in extracted files
                $files = File::files($extractPath);
                $jsonPath = null;
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                        $jsonPath = $file->getPathname();
                        break;
                    }
                }
                
                if (!$jsonPath) {
                    $this->error('No JSON file found in ZIP archive');
                    return 1;
                }
            } else {
                $this->error('Failed to open ZIP archive');
                return 1;
            }
        }

        // Read and parse JSON
        $jsonContent = File::get($jsonPath);
        $data = json_decode($jsonContent, true);

        if (!$data) {
            $this->error('Invalid JSON file');
            return 1;
        }

        // Display backup info
        $this->info("Backup Date: " . ($data['backup_date'] ?? 'Unknown'));
        $this->info("Devices: " . count($data['devices'] ?? []));
        $this->info("Audit Logs: " . count($data['audit_logs'] ?? []));
        $this->info("Rules: " . count($data['rules'] ?? []));

        // Confirm restore
        if (!$this->option('force')) {
            if (!$this->confirm('This will restore data from backup. Continue?')) {
                $this->info('Restore cancelled.');
                return 0;
            }
        }

        $this->info('Starting restore...');

        try {
            // Restore devices
            if (isset($data['devices'])) {
                $this->info('Restoring devices...');
                foreach ($data['devices'] as $deviceData) {
                    Device::updateOrCreate(
                        ['id' => $deviceData['id']],
                        [
                            'name' => $deviceData['name'],
                            'type' => $deviceData['type'],
                            'location' => $deviceData['location'],
                            'status' => $deviceData['status'] ?? 'off',
                            'settings' => $deviceData['settings'] ?? null,
                            'is_active' => $deviceData['is_active'] ?? true,
                            'min_role_hierarchy' => $deviceData['min_role_hierarchy'] ?? 1,
                            'created_at' => $deviceData['created_at'] ?? now(),
                            'updated_at' => $deviceData['updated_at'] ?? now(),
                        ]
                    );
                }
                $this->info('Devices restored.');
            }

            // Restore audit logs
            if (isset($data['audit_logs'])) {
                $this->info('Restoring audit logs...');
                foreach ($data['audit_logs'] as $logData) {
                    AuditLog::updateOrCreate(
                        ['id' => $logData['id']],
                        [
                            'user_id' => $logData['user_id'] ?? null,
                            'device_id' => $logData['device_id'] ?? null,
                            'action' => $logData['action'],
                            'status' => $logData['status'],
                            'message' => $logData['message'] ?? null,
                            'ip_address' => $logData['ip_address'] ?? null,
                            'user_agent' => $logData['user_agent'] ?? null,
                            'metadata' => $logData['metadata'] ?? null,
                            'performed_at' => $logData['performed_at'] ?? now(),
                            'created_at' => $logData['created_at'] ?? now(),
                            'updated_at' => $logData['updated_at'] ?? now(),
                        ]
                    );
                }
                $this->info('Audit logs restored.');
            }

            // Restore rules
            if (isset($data['rules'])) {
                $this->info('Restoring rules...');
                foreach ($data['rules'] as $ruleData) {
                    Rule::updateOrCreate(
                        ['id' => $ruleData['id']],
                        [
                            'name' => $ruleData['name'],
                            'description' => $ruleData['description'] ?? null,
                            'role_id' => $ruleData['role_id'] ?? null,
                            'device_id' => $ruleData['device_id'] ?? null,
                            'action' => $ruleData['action'] ?? null,
                            'condition_type' => $ruleData['condition_type'],
                            'condition_params' => $ruleData['condition_params'] ?? [],
                            'is_active' => $ruleData['is_active'] ?? true,
                            'effect' => $ruleData['effect'],
                            'denial_message' => $ruleData['denial_message'] ?? null,
                            'created_at' => $ruleData['created_at'] ?? now(),
                            'updated_at' => $ruleData['updated_at'] ?? now(),
                        ]
                    );
                }
                $this->info('Rules restored.');
            }

            $this->info('Restore completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Restore failed: ' . $e->getMessage());
            return 1;
        }
    }
}
