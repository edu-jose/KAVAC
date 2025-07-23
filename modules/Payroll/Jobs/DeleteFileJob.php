<?php

namespace Modules\Payroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DeleteFileJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $pdfPath;

    /**
     * Create a new job instance.
     *
     * @param string $pdfPath La ruta completa del archivo PDF a eliminar.
     * @return void
     */
    public function __construct(string $pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (File::exists($this->pdfPath)) {
            File::delete($this->pdfPath);
            Log::info('Archivo PDF eliminado por DeletePdfFileJob: ' . $this->pdfPath);
        } else {
            Log::warning('DeletePdfFileJob intentó eliminar archivo PDF que no existe: ' . $this->pdfPath);
        }
    }
}
