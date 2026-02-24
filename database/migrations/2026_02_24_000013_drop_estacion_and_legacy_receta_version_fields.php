<?php
/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function isMysql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $database = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($rows as $row) {
            $constraint = $row->CONSTRAINT_NAME ?? null;
            if (!is_string($constraint) || $constraint === '') {
                continue;
            }

            $safeTable = str_replace('`', '``', $table);
            $safeConstraint = str_replace('`', '``', $constraint);
            DB::statement("ALTER TABLE `{$safeTable}` DROP FOREIGN KEY `{$safeConstraint}`");
        }
    }

    private function dropKnownForeignIfExists(string $table, string $constraint): void
    {
        try {
            $safeTable = str_replace('`', '``', $table);
            $safeConstraint = str_replace('`', '``', $constraint);
            DB::statement("ALTER TABLE `{$safeTable}` DROP FOREIGN KEY `{$safeConstraint}`");
        } catch (\Throwable $e) {
            // Ignore missing/invalid constraints.
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        try {
            if ($this->isMysql()) {
                $safeTable = str_replace('`', '``', $table);
                $safeIndex = str_replace('`', '``', $index);
                DB::statement("ALTER TABLE `{$safeTable}` DROP INDEX `{$safeIndex}`");
                return;
            }

            DB::statement("DROP INDEX IF EXISTS {$index}");
        } catch (\Throwable $e) {
            // Ignore missing index.
        }
    }

    private function migrateRecetaVersionIdToRecetaId(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'receta_version_id')) {
            return;
        }

        if (!Schema::hasColumn($table, 'receta_id')) {
            Schema::table($table, function (Blueprint $table): void {
                $table->uuid('receta_id')->nullable();
            });
        }

        DB::table($table)
            ->whereNull('receta_id')
            ->update(['receta_id' => DB::raw('receta_version_id')]);

        Schema::table($table, function (Blueprint $table): void {
            $table->dropColumn('receta_version_id');
        });
    }

    private function hasForeignToReceta(string $table): bool
    {
        if (!Schema::hasTable($table) || !Schema::hasTable('receta') || !Schema::hasColumn($table, 'receta_id')) {
            return false;
        }

        $database = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            [$database, $table, 'receta_id', 'receta']
        );

        return $rows !== [];
    }

    private function addForeignToRecetaIfPossible(string $table, string $constraintName): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable('receta') || !Schema::hasColumn($table, 'receta_id')) {
            return;
        }

        if ($this->hasForeignToReceta($table)) {
            return;
        }

        // Evita fallo al crear FK por datos legacy huérfanos
        DB::statement(
            "UPDATE `{$table}` t
             LEFT JOIN `receta` r ON r.id = t.receta_id
             SET t.receta_id = NULL
             WHERE t.receta_id IS NOT NULL AND r.id IS NULL"
        );

        try {
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`receta_id`) REFERENCES `receta`(`id`) ON DELETE SET NULL"
            );
        } catch (\Throwable $e) {
            // Si ya existe (otro nombre) o el motor no permite, no romper migración.
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('receta_version') && !Schema::hasTable('receta')) {
            Schema::rename('receta_version', 'receta');
        }

        if (Schema::hasTable('produccion_batch')) {
            $this->dropKnownForeignIfExists('produccion_batch', 'produccion_batch_estacion_id_foreign');
            $this->dropKnownForeignIfExists('produccion_batch', 'produccion_batch_receta_version_id_foreign');
            $this->dropForeignKeysForColumn('produccion_batch', 'estacion_id');
            $this->dropForeignKeysForColumn('produccion_batch', 'receta_version_id');

            $this->dropIndexIfExists('produccion_batch', 'idx_pb_est');
            $this->dropIndexIfExists('produccion_batch', 'idx_pb_rec_por');

            if (Schema::hasColumn('produccion_batch', 'estacion_id')) {
                Schema::table('produccion_batch', function (Blueprint $table): void {
                    $table->dropColumn('estacion_id');
                });
            }

            $this->migrateRecetaVersionIdToRecetaId('produccion_batch');
            $this->dropIndexIfExists('produccion_batch', 'idx_pb_rec_por');
            if (Schema::hasColumn('produccion_batch', 'receta_id') && Schema::hasColumn('produccion_batch', 'porcion_id')) {
                Schema::table('produccion_batch', function (Blueprint $table): void {
                    $table->index(['receta_id', 'porcion_id'], 'idx_pb_rec_por');
                });
            }
            $this->addForeignToRecetaIfPossible('produccion_batch', 'produccion_batch_receta_id_foreign');
        }

        if (Schema::hasTable('etiqueta')) {
            $this->dropKnownForeignIfExists('etiqueta', 'etiqueta_receta_version_id_foreign');
            $this->dropForeignKeysForColumn('etiqueta', 'receta_version_id');
            $this->dropIndexIfExists('etiqueta', 'uk_etq_compuesta');
            $this->migrateRecetaVersionIdToRecetaId('etiqueta');
            $this->addForeignToRecetaIfPossible('etiqueta', 'etiqueta_receta_id_foreign');
        }

        if (Schema::hasTable('estacion')) {
            Schema::dropIfExists('estacion');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('produccion_batch') && Schema::hasColumn('produccion_batch', 'receta_id')) {
            $this->dropKnownForeignIfExists('produccion_batch', 'produccion_batch_receta_id_foreign');
            if (!Schema::hasColumn('produccion_batch', 'receta_version_id')) {
                Schema::table('produccion_batch', function (Blueprint $table): void {
                    $table->uuid('receta_version_id')->nullable();
                });
            }
            DB::table('produccion_batch')
                ->whereNull('receta_version_id')
                ->update(['receta_version_id' => DB::raw('receta_id')]);
            Schema::table('produccion_batch', function (Blueprint $table): void {
                $table->dropColumn('receta_id');
            });
        }

        if (Schema::hasTable('etiqueta') && Schema::hasColumn('etiqueta', 'receta_id')) {
            $this->dropKnownForeignIfExists('etiqueta', 'etiqueta_receta_id_foreign');
            if (!Schema::hasColumn('etiqueta', 'receta_version_id')) {
                Schema::table('etiqueta', function (Blueprint $table): void {
                    $table->uuid('receta_version_id')->nullable();
                });
            }
            DB::table('etiqueta')
                ->whereNull('receta_version_id')
                ->update(['receta_version_id' => DB::raw('receta_id')]);
            Schema::table('etiqueta', function (Blueprint $table): void {
                $table->dropColumn('receta_id');
            });
        }

        if (Schema::hasTable('receta') && !Schema::hasTable('receta_version')) {
            Schema::rename('receta', 'receta_version');
        }
    }
};
