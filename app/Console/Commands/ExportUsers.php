<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\UsersExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:users {--csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta todos los usuarios con sus roles y permisos a un archivo Excel o CSV.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando la exportación de usuarios...');

        $fileExtension = $this->option('csv') ? 'csv' : 'xlsx';
        $fileName = 'usuarios_exportados_' . now()->format('Y-m-d') . '.' . $fileExtension;

        try {
            if ($this->option('csv')) {
                // Guarda el archivo CSV en el directorio 'exports' dentro de 'storage/app'
                Excel::store(new UsersExport(), "exports/{$fileName}", 'local', \Maatwebsite\Excel\Excel::CSV);
            } else {
                // Guarda el archivo XLSX en el directorio 'exports' dentro de 'storage/app'
                Excel::store(new UsersExport(), "exports/{$fileName}");
            }

            $this->info("Exportación completada. Archivo guardado como: {$fileName}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Ocurrió un error durante la exportación: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
