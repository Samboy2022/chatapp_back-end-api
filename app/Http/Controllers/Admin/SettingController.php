<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function __construct()
    {
        // Middleware is applied at the route level
    }

    public function index()
    {
        $settingsGrouped = \App\Models\Setting::getAllGrouped();
        return view('admin.settings.index', compact('settingsGrouped'));
    }

    public function update(Request $request)
    {
        // Get all settings to validate against
        $allSettings = \App\Models\Setting::all()->keyBy('key');

        // Build validation rules based on settings
        $rules = [];
        foreach ($allSettings as $key => $setting) {
            switch ($setting->type) {
                case 'boolean':
                    $rules[$key] = 'nullable|boolean';
                    break;
                case 'integer':
                    $rules[$key] = 'nullable|integer';
                    break;
                case 'string':
                    $rules[$key] = 'nullable|string';
                    break;
                case 'json':
                    $rules[$key] = 'nullable|json';
                    break;
                default:
                    $rules[$key] = 'nullable|string';
            }
        }

        // Add specific validation rules
        $rules['app_name'] = 'required|string|max:255';
        $rules['app_description'] = 'nullable|string|max:1000';
        $rules['app_url'] = 'required|url';
        $rules['admin_email'] = 'required|email';
        $rules['max_file_size'] = 'required|integer|min:1|max:100';
        $rules['max_group_size'] = 'required|integer|min:2|max:1000';
        $rules['message_retention_days'] = 'required|integer|min:1|max:365';

        // Validate the request
        $validated = $request->validate($rules);

        try {
            // First, set all boolean settings to false by default
            foreach ($allSettings as $key => $setting) {
                if ($setting->type === 'boolean' && !$request->has($key)) {
                    \App\Models\Setting::set($key, '0', 'boolean', $setting->group);
                }
            }

            // Update settings from the request
            foreach ($request->except(['_token', 'logo']) as $key => $value) {
                if (isset($allSettings[$key])) {
                    $setting = $allSettings[$key];

                    // Convert checkbox values
                    if ($setting->type === 'boolean') {
                        // Handle various checkbox value formats
                        if ($value === 'on' || $value === '1' || $value === 'true' || $value === true) {
                            $value = '1';
                        } else {
                            $value = '0';
                        }
                    }

                    // Update the setting
                    \App\Models\Setting::set($key, $value, $setting->type, $setting->group);
                }
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $this->handleLogoUpload($request->file('logo'));
            }

            // Clear cache
            \App\Models\Setting::clearCache();

            return redirect()->back()->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            Log::error('Settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function clearCache()
    {
        try {
            // Clear Laravel caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            // Clear settings cache
            \App\Models\Setting::clearCache();

            return redirect()->back()->with('success', 'All caches cleared successfully! (Application, Config, Routes, Views, Settings)');
        } catch (\Exception $e) {
            Log::error('Cache clear failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimizeSystem()
    {
        try {
            // For API requests, return a quick response
            if (request()->expectsJson()) {
                // Run optimization in the background
                $this->runOptimizationInBackground();

                return response()->json([
                    'success' => true,
                    'message' => 'System optimization started in the background. This may take a few minutes to complete.'
                ]);
            }

            // For web requests, optimize directly
            // First clear caches to ensure fresh optimization
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            // Then optimize (only the essential parts)
            Artisan::call('optimize');

            // Get the output
            $output = Artisan::output();
            Log::info('System optimization output: ' . $output);

            return redirect()->back()->with('success', 'System optimized successfully! Application performance has been improved.');
        } catch (\Exception $e) {
            Log::error('System optimization failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to optimize system: ' . $e->getMessage());
        }
    }

    /**
     * Run optimization in the background
     */
    private function runOptimizationInBackground()
    {
        try {
            // Log the start of optimization
            Log::info('Starting background system optimization');

            // Run optimization commands one by one
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize');

            // Log completion
            Log::info('Background system optimization completed successfully');
        } catch (\Exception $e) {
            Log::error('Background system optimization failed: ' . $e->getMessage());
        }
    }

    public function backupDatabase()
    {
        try {
            // For API requests, return JSON response
            if (request()->expectsJson()) {
                return $this->createDatabaseBackup();
            }

            // For web requests
            $result = $this->createDatabaseBackup();

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create backup: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Create database backup using available method
     */
    private function createDatabaseBackup()
    {
        try {
            $filename = 'farmersnetwork_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups/' . $filename);

            // Create backups directory if it doesn't exist
            $backupDir = dirname($backupPath);
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Try mysqldump first if available
            if ($this->isMysqldumpAvailable()) {
                try {
                    return $this->createMysqldumpBackup($backupPath, $filename);
                } catch (\Exception $e) {
                    Log::warning('mysqldump failed, falling back to PHP backup: ' . $e->getMessage());
                    // Fall through to PHP backup
                }
            }

            // Fallback to PHP-based backup
            return $this->createPhpBackup($backupPath, $filename);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Check if mysqldump is available
     */
    private function isMysqldumpAvailable()
    {
        $output = [];
        $returnVar = 0;

        // Check if mysqldump is available
        exec('mysqldump --version 2>&1', $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Create backup using mysqldump
     */
    private function createMysqldumpBackup($backupPath, $filename)
    {
        $dbConfig = config('database.connections.' . config('database.default'));

        // Build mysqldump command with proper password handling
        $commandParts = [
            'mysqldump',
            '--user=' . escapeshellarg($dbConfig['username']),
            '--host=' . escapeshellarg($dbConfig['host']),
            '--port=' . escapeshellarg($dbConfig['port'] ?? 3306),
            '--single-transaction',
            '--routines',
            '--triggers'
        ];

        // Only add password option if password exists
        if (!empty($dbConfig['password'])) {
            $commandParts[] = '--password=' . escapeshellarg($dbConfig['password']);
        }

        $commandParts[] = escapeshellarg($dbConfig['database']);

        $command = implode(' ', $commandParts);

        // Execute command and redirect output to file
        $fullCommand = $command . ' > ' . escapeshellarg($backupPath) . ' 2>&1';

        $output = [];
        $returnCode = 0;
        exec($fullCommand, $output, $returnCode);

        if ($returnCode !== 0) {
            // If mysqldump failed, try to get more details
            $errorDetails = implode("\n", $output);
            if (empty($errorDetails) && file_exists($backupPath)) {
                $errorDetails = file_get_contents($backupPath);
            }
            throw new \Exception('mysqldump failed (code: ' . $returnCode . '): ' . $errorDetails);
        }

        // Check if backup file was created and has content
        if (!file_exists($backupPath) || filesize($backupPath) === 0) {
            throw new \Exception('Backup file was not created or is empty');
        }

        $fileSize = $this->formatBytes(filesize($backupPath));
        Log::info("Database backup created using mysqldump: {$filename} ({$fileSize})");

        return [
            'success' => true,
            'message' => "Database backup created successfully using mysqldump: {$filename} ({$fileSize})",
            'filename' => $filename,
            'path' => $backupPath,
            'method' => 'mysqldump',
            'size' => $fileSize
        ];
    }

    /**
     * Create backup using PHP and Laravel's database connection
     */
    private function createPhpBackup($backupPath, $filename)
    {
        $database = config('database.connections.' . config('database.default') . '.database');

        // Get all tables
        $tables = \DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;

        $backup = "-- Database Backup\n";
        $backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $backup .= "-- Database: {$database}\n";
        $backup .= "-- Method: PHP Laravel Backup\n\n";

        $backup .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $backup .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $backup .= "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Get table structure
            $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
            $backup .= "-- Table structure for `{$tableName}`\n";
            $backup .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $backup .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = \DB::table($tableName)->get();

            if ($rows->count() > 0) {
                $backup .= "-- Data for table `{$tableName}`\n";
                $backup .= "LOCK TABLES `{$tableName}` WRITE;\n";

                $columns = array_keys((array)$rows->first());
                $columnList = '`' . implode('`, `', $columns) . '`';

                $insertValues = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ((array)$row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $insertValues[] = '(' . implode(', ', $values) . ')';
                }

                // Insert in batches to avoid memory issues
                $batchSize = 100;
                $batches = array_chunk($insertValues, $batchSize);

                foreach ($batches as $batch) {
                    $backup .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";
                    $backup .= implode(",\n", $batch) . ";\n";
                }

                $backup .= "UNLOCK TABLES;\n\n";
            }
        }

        $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Write backup to file
        file_put_contents($backupPath, $backup);

        $fileSize = $this->formatBytes(filesize($backupPath));
        Log::info("Database backup created using PHP method: {$filename} ({$fileSize})");

        return [
            'success' => true,
            'message' => "Database backup created successfully using PHP method: {$filename} ({$fileSize})",
            'filename' => $filename,
            'path' => $backupPath,
            'method' => 'php',
            'size' => $fileSize
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function testEmail()
    {
        try {
            $adminEmail = \App\Models\Setting::get('admin_email', config('mail.from.address'));

            // Send test email
            Mail::raw('This is a test email from FarmersNetwork Admin Panel.', function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                        ->subject('Test Email - FarmersNetwork Admin');
            });

            return redirect()->back()->with('success', 'Test email sent successfully to ' . $adminEmail . '!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function resetSettings()
    {
        try {
            // Reset to default settings
            $defaults = $this->getDefaultSettings();
            foreach ($defaults as $key => $value) {
                $this->setSetting($key, $value);
            }

            $this->clearSettingsCache();

            return redirect()->back()->with('success', 'Settings reset to defaults successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    public function exportSettings()
    {
        try {
            $settings = \App\Models\Setting::all()->keyBy('key')->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'options' => $setting->options,
                    'is_public' => $setting->is_public
                ];
            });

            $filename = 'farmersnetwork_settings_' . date('Y-m-d_H-i-s') . '.json';

            $headers = [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->json($settings, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export settings: ' . $e->getMessage());
        }
    }

    public function importSettings(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json'
        ]);

        try {
            $file = $request->file('settings_file');
            $content = file_get_contents($file->getRealPath());
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON file');
            }

            $importedCount = 0;

            // Import settings
            foreach ($settings as $key => $settingData) {
                if (is_array($settingData) && isset($settingData['key'])) {
                    // New format with full setting data
                    \App\Models\Setting::updateOrCreate(
                        ['key' => $settingData['key']],
                        [
                            'value' => $settingData['value'],
                            'type' => $settingData['type'] ?? 'string',
                            'group' => $settingData['group'] ?? 'general',
                            'label' => $settingData['label'] ?? null,
                            'description' => $settingData['description'] ?? null,
                            'options' => $settingData['options'] ?? null,
                            'is_public' => $settingData['is_public'] ?? false
                        ]
                    );
                    $importedCount++;
                } else {
                    // Legacy format with just key-value pairs
                    \App\Models\Setting::set($key, $settingData);
                    $importedCount++;
                }
            }

            \App\Models\Setting::clearCache();

            return redirect()->back()->with('success', "Settings imported successfully! {$importedCount} settings updated.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }



    private function handleLogoUpload($file)
    {
        try {
            // Validate file
            if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new \Exception('Invalid image file. Only JPG, PNG, and GIF files are allowed.');
            }

            if ($file->getSize() > 2048 * 1024) {
                throw new \Exception('File size exceeds the maximum limit of 2MB.');
            }

            // Delete old logo if exists
            $oldLogo = \App\Models\Setting::get('logo_url');
            if ($oldLogo && Storage::exists('public/logos/' . basename($oldLogo))) {
                Storage::delete('public/logos/' . basename($oldLogo));
            }

            // Store new logo
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/logos', $filename);
            $url = Storage::url($path);

            // Update setting
            \App\Models\Setting::set('logo_url', $url, 'string', 'general');

            return $url;
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload logo: ' . $e->getMessage());
        }
    }
} 