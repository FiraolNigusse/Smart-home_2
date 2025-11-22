<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\AuditLog;
use App\Models\Rule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:backup {--path=storage/backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup system data (devices, logs, rules) to a JSON/ZIP file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting system backup...');

        $backupPath = $this->option('path');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = base_path($backupPath);
        
        // Create backup directory if it doesn't exist
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Collect data
        $data = [
            'backup_date' => now()->toIso8601String(),
            'version' => '1.0',
            'devices' => Device::all()->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'type' => $device->type,
                    'location' => $device->location,
                    'status' => $device->status,
                    'settings' => $device->settings,
                    'is_active' => $device->is_active,
                    'min_role_hierarchy' => $device->min_role_hierarchy,
                    'created_at' => $device->created_at,
                    'updated_at' => $device->updated_at,
                ];
            })->toArray(),
            'audit_logs' => AuditLog::all()->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'device_id' => $log->device_id,
                    'action' => $log->action,
                    'status' => $log->status,
                    'message' => $log->message,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'metadata' => $log->metadata,
                    'performed_at' => $log->performed_at,
                    'created_at' => $log->created_at,
                    'updated_at' => $log->updated_at,
                ];
            })->toArray(),
            'rules' => Rule::all()->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'description' => $rule->description,
                    'role_id' => $rule->role_id,
                    'device_id' => $rule->device_id,
                    'action' => $rule->action,
                    'condition_type' => $rule->condition_type,
                    'condition_params' => $rule->condition_params,
                    'is_active' => $rule->is_active,
                    'effect' => $rule->effect,
                    'denial_message' => $rule->denial_message,
                    'created_at' => $rule->created_at,
                    'updated_at' => $rule->updated_at,
                ];
            })->toArray(),
        ];

        // Save JSON file
        $jsonFileName = "backup_{$timestamp}.json";
        $jsonPath = $backupDir . '/' . $jsonFileName;
        File::put($jsonPath, json_encode($data, JSON_PRETTY_PRINT));

        $this->info("JSON backup created: {$jsonFileName}");

        // Create ZIP archive
        $zipFileName = "backup_{$timestamp}.zip";
        $zipPath = $backupDir . '/' . $zipFileName;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFile($jsonPath, $jsonFileName);
            $zip->close();
            
            // Optionally delete JSON file after zipping
            // File::delete($jsonPath);
            
            $this->info("ZIP backup created: {$zipFileName}");
        } else {
            $this->error('Failed to create ZIP archive');
            return 1;
        }

        $this->info("Backup completed successfully!");
        $this->info("Location: {$zipPath}");
        $this->info("Total devices: " . count($data['devices']));
        $this->info("Total logs: " . count($data['audit_logs']));
        $this->info("Total rules: " . count($data['rules']));

        return 0;
    }
}
