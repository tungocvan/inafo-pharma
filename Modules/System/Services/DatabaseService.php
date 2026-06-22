<?php

namespace Modules\System\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseService
{
    protected array $protectedTables = [
        'users',
        'migrations',
        'failed_jobs',
        'password_reset_tokens',
        'roles',
        'permissions',
    ];

    public function getAllTables(string $search = ''): array
    {
        $search = mb_substr($search, 0, 100);
        $tables = DB::select('SHOW TABLE STATUS WHERE Name LIKE ?', ['%' . $search . '%']);

        return array_map(function ($table) {
            $tableName = $table->Name;
            $fileName = $this->backupFileName($tableName);

            return [
                'name' => $tableName,
                'rows' => $table->Rows,
                'size_mb' => round(($table->Data_length + $table->Index_length) / 1024 / 1024, 2),
                'collation' => $table->Collation,
                'has_backup' => Storage::disk('local')->exists("private/backups/{$fileName}"),
                'backup_file' => $fileName,
                'is_protected' => in_array($tableName, $this->protectedTables, true),
            ];
        }, $tables);
    }

    public function backupTable(string $tableName): bool
    {
        $this->assertAllowedTable($tableName, allowProtected: true);

        $fileName = $this->backupFileName($tableName);
        $path = Storage::disk('local')->path("private/backups/{$fileName}");

        $this->ensureDirectory(dirname($path));
        $this->runDump([$tableName], $path, 120);

        return true;
    }

    public function backupFullDatabase(): bool
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "db_backup_full_{$timestamp}.sql";
        $path = Storage::disk('local')->path("private/backups/{$fileName}");

        $this->ensureDirectory(dirname($path));
        $this->runDump([], $path, 300);

        return true;
    }

    public function restoreTable(string $tableName): bool
    {
        $this->assertAllowedTable($tableName, allowProtected: true);

        $path = Storage::disk('local')->path('private/backups/' . $this->backupFileName($tableName));

        if (! file_exists($path)) {
            return false;
        }

        $this->runMysqlImport($path, 300);

        return true;
    }

    public function truncateTable(string $tableName): void
    {
        $this->assertAllowedTable($tableName);

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($tableName)->truncate();
            DB::statement('ANALYZE TABLE ' . $this->quoteIdentifier($tableName));
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function dropTable(string $tableName): void
    {
        $this->assertAllowedTable($tableName);

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement('DROP TABLE IF EXISTS ' . $this->quoteIdentifier($tableName));
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function getDownloadPath(string $fileName): ?string
    {
        return $this->resolveBackupIdentifier($fileName);
    }

    public function getAllBackupFiles(): array
    {
        $files = [];

        foreach (['private/backups', 'backups'] as $directory) {
            foreach (Storage::disk('local')->files($directory) as $path) {
                if (! str_ends_with($path, '.sql')) {
                    continue;
                }

                $fileName = basename($path);
                $files[] = [
                    'id' => $fileName,
                    'name' => $fileName,
                    'path' => $fileName,
                    'size' => Storage::disk('local')->size($path),
                    'time' => Storage::disk('local')->lastModified($path),
                ];
            }
        }

        usort($files, fn ($a, $b) => $b['time'] <=> $a['time']);

        return $files;
    }

    public function restoreFromFile(string $backupId): bool
    {
        if ($this->resolveBackupIdentifier($backupId) === null) {
            throw new Exception('Backup file not found.');
        }

        throw new Exception('Full database restore is disabled until the safe restore workflow is implemented.');
    }

    public function assertAllowedTable(string $tableName, bool $allowProtected = false): void
    {
        if (! preg_match('/\A[A-Za-z0-9_]+\z/', $tableName)) {
            throw new Exception('Invalid table identifier.');
        }

        if (! in_array($tableName, $this->getCurrentTableNames(), true)) {
            throw new Exception('Table does not exist.');
        }

        if (! $allowProtected && in_array($tableName, $this->protectedTables, true)) {
            throw new Exception('This table is protected.');
        }
    }

    private function runDump(array $tables, string $outputPath, int $timeout): void
    {
        $config = config('database.connections.mysql');
        $command = [
            'mysqldump',
            '--user=' . ($config['username'] ?? ''),
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? '3306'),
            $config['database'] ?? '',
            ...$tables,
        ];

        $process = new Process($command, null, $this->processEnvironment($config));
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Database dump failed.', [
                'exit_code' => $process->getExitCode(),
                'error' => $process->getErrorOutput(),
            ]);

            throw new ProcessFailedException($process);
        }

        file_put_contents($outputPath, $process->getOutput());
    }

    private function runMysqlImport(string $inputPath, int $timeout): void
    {
        $config = config('database.connections.mysql');
        $command = [
            'mysql',
            '--user=' . ($config['username'] ?? ''),
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? '3306'),
            $config['database'] ?? '',
        ];

        $process = new Process($command, null, $this->processEnvironment($config));
        $process->setInput(file_get_contents($inputPath));
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Database import failed.', [
                'exit_code' => $process->getExitCode(),
                'error' => $process->getErrorOutput(),
            ]);

            throw new ProcessFailedException($process);
        }
    }

    private function processEnvironment(array $config): array
    {
        return filled($config['password'] ?? null)
            ? ['MYSQL_PWD' => $config['password']]
            : [];
    }

    private function resolveBackupIdentifier(string $backupId): ?string
    {
        if ($backupId !== basename($backupId) || ! preg_match('/\A[A-Za-z0-9_.-]+\.sql\z/', $backupId)) {
            return null;
        }

        foreach (['private/backups', 'backups'] as $directory) {
            $path = "{$directory}/{$backupId}";

            if (Storage::disk('local')->exists($path)) {
                return Storage::disk('local')->path($path);
            }
        }

        return null;
    }

    private function backupFileName(string $tableName): string
    {
        return "backup_{$tableName}.sql";
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function getCurrentTableNames(): array
    {
        return array_map(function (object $table): string {
            $values = get_object_vars($table);

            return (string) reset($values);
        }, DB::select('SHOW TABLES'));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
