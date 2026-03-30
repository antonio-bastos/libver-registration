<?php

namespace App\Services;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupService
{
    private DatabaseManager $db;

    /**
     * Ordered for FK-safe restore.
     *
     * @var list<string>
     */
    private array $tableOrder = [
        'users',
        'activities',
        'activity_sessions',
        'children',
        'registrations',
        'waitlist_offers',
        'files',
        'gdpr_snapshots',
        'notifications',
        'archived_activities',
    ];

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function createBackup(): string
    {
        $tables = [];
        foreach ($this->tableOrder as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $tables[$table] = $this->db->table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'database_connection' => config('database.default'),
            'tables' => $tables,
        ];

        $filename = 'backups/libver-backup-' . now()->format('Ymd-His') . '.json';
        Storage::disk('local')->put($filename, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $filename;
    }

    /**
     * @return array{file:string, restored_tables:int, rows:int}
     */
    public function restoreLatest(): array
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files('backups'))
            ->filter(fn (string $path) => str_ends_with($path, '.json'))
            ->sortDesc()
            ->values();

        if ($files->isEmpty()) {
            throw new RuntimeException('No backups found in storage/app/private/backups.');
        }

        $latestFile = $files->first();
        $contents = $disk->get($latestFile);
        $decoded = json_decode($contents, true);

        if (!is_array($decoded) || !isset($decoded['tables']) || !is_array($decoded['tables'])) {
            throw new RuntimeException('Backup file is invalid.');
        }

        $tables = $decoded['tables'];
        $rowsRestored = 0;
        $restoredTables = 0;

        $this->db->beginTransaction();

        try {
            $this->disableForeignKeys();

            foreach (array_reverse($this->tableOrder) as $table) {
                if (!Schema::hasTable($table) || !array_key_exists($table, $tables)) {
                    continue;
                }

                $this->db->table($table)->delete();
            }

            foreach ($this->tableOrder as $table) {
                if (!Schema::hasTable($table) || !array_key_exists($table, $tables)) {
                    continue;
                }

                $rows = $tables[$table];
                if (!is_array($rows) || $rows === []) {
                    $restoredTables++;
                    continue;
                }

                foreach (array_chunk($rows, 200) as $chunk) {
                    $this->db->table($table)->insert($chunk);
                    $rowsRestored += count($chunk);
                }

                $restoredTables++;
            }

            $this->enableForeignKeys();
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            try {
                $this->enableForeignKeys();
            } catch (\Throwable $inner) {
                // no-op
            }

            throw $e;
        }

        return [
            'file' => $latestFile,
            'restored_tables' => $restoredTables,
            'rows' => $rowsRestored,
        ];
    }

    /**
     * @return list<array{file:string, size:int, modified_at:string}>
     */
    public function listBackups(int $limit = 5): array
    {
        $disk = Storage::disk('local');

        return collect($disk->files('backups'))
            ->filter(fn (string $path) => str_ends_with($path, '.json'))
            ->sortDesc()
            ->take($limit)
            ->map(function (string $file) use ($disk) {
                return [
                    'file' => $file,
                    'size' => (int) $disk->size($file),
                    'modified_at' => date('Y-m-d H:i:s', (int) $disk->lastModified($file)),
                ];
            })
            ->values()
            ->all();
    }

    private function disableForeignKeys(): void
    {
        $driver = $this->db->connection()->getDriverName();
        if ($driver === 'mysql') {
            $this->db->statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            $this->db->statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'pgsql') {
            $this->db->statement('SET session_replication_role = replica');
        }
    }

    private function enableForeignKeys(): void
    {
        $driver = $this->db->connection()->getDriverName();
        if ($driver === 'mysql') {
            $this->db->statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            $this->db->statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'pgsql') {
            $this->db->statement('SET session_replication_role = DEFAULT');
        }
    }
}
