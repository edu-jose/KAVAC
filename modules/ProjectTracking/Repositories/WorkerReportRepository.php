<?php

namespace Modules\ProjectTracking\Repositories;

use Carbon\Carbon;
use App\Models\Institution;
use Elibyy\TCPDF\TCPDF as PDF;
use App\Repositories\ReportRepository;

class WorkerReportRepository extends ReportRepository
{
    /**
     * Establece la orientación de la página, los posibles valores son P o L
     *
     * @var string $orientation
     */
    protected $orientation;

    /**
     * Establece la unidad de medida a implementar en el reporte
     *
     * @var string $units
     */
    protected $units;

    /**
     * Establece el formato de la página (A4, Letter, ...)
     *
     * @var string $format
     */
    protected $format;

    /**
     * Establece el tipo de fuente a usar en el reporte
     *
     * @var string $fontFamily
     */
    protected $fontFamily;

    /**
     * Estilos a implementar en códigos QR a generar
     *
     * @var array $qrCodeStyle
     */
    protected $qrCodeStyle;

    /**
     * Estilos a implementar en códigos de barras a generar
     *
     * @var array $barCodeStyle
     */
    protected $barCodeStyle;

    /**
     * Estilos para líneas de separación entre encabezado cuerpo y pie de página
     *
     * @var string $lineStyle
     */
    protected $lineStyle;

    /**
     * URL de verificación del reporte
     *
     * @var string $urlVerify
     */
    protected $urlVerify;

    /**
     * Fecha en la que se genera el reporte
     *
     * @var string $reportDate
     */
    protected $reportDate;

    /**
     * Identificador de la institución que genera el reporte
     *
     * @var Institution $institution
     */
    protected $institution;

    /**
     * Nombre del archivo a generar con el reporte
     *
     * @var string $filename
     */
    protected $filename;

    /**
     * Título del reporte
     *
     * @var string $title
     */
    protected $title;

    /**
     * Asunto del reporte
     *
     * @var string $subject
     */
    protected $subject;

    /**
     * Establece el eje de las Y en donde comienza a mostrarse el encabezado del reporte
     *
     * @var integer $headerY
     */
    protected $headerY;

    /**
     * Establece el eje de las Y para el texto de subtítulo y fecha del reporte
     *
     * @var integer $headerTextY
     */
    protected $headerTextY;

    /**
     * Crea y gestiona el objeto PDF
     *
     * @var object $pdf
     */
    protected $pdf;

    /**
     * Método constructor de la clase
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Call the parent constructor first (or at any necessary point)
        parent::__construct();
    }

    /**
     * Método que permite agregar el contenido del reporte a generar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string         $body        Plantilla a utilizar para el reporte en caso de estar establecido
     *                                         como isHTML, en caso contrario será un texto a incluir en el cuerpo
     *                                         del reporte
     * @param      boolean        $isHTML      Establece si el cuerpo del reporte es una plantilla de blade a renderizar
     * @param      array          $htmlParams  Conjunto de parámetros requeridos por la plantilla de blade
     *
     * @return     void
     */
    public function setBody($body, $isHTML = true, $htmlParams = [], $storeAction = "", $images = []): mixed
    {
        /* Contenido del reporte */
        $htmlContent = $body;
        /* Configuración sobre el autor del reporte */
        $this->pdf->SetAuthor('Sistema de Gestión de Recursos - ' . config('app.name'));
        /* Configuración del título de reporte */
        $this->pdf->SetTitle($this->title);
        /* Configuración sobre el asunto del reporte */
        $this->pdf->SetSubject($this->subject);
        /* Configuración de los márgenes del cuerpo del reporte */
        $this->pdf->SetMargins(7, 45, 7);
        /* Establece si se configura o no las fuentes para sub configuraciones */
        $this->pdf->SetFontSubsetting(false);
        /* Configuración de la fuente por defecto del cuerpo del reporte */
        $this->pdf->SetFontSize('10px');
        /* Configuración de calidad de la imagen */
        $this->pdf->setJPEGQuality(90);
        /* set image scale factor */
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        /*
         | Configuración que permite realizar un salto de página automático al alcanzar el límite inferior del cuerpo
         | del reporte
         */
        $this->pdf->SetAutoPageBreak(true, 15); //PDF_MARGIN_BOTTOM
        /* Agrega las respectivas páginas del reporte */
        $this->pdf->AddPage($this->orientation, $this->format);

        if ($isHTML) {
            $view = \Illuminate\Support\Facades\View::make($body, $htmlParams);
            $htmlContent = $view->render();
        }
        /* Escribre el contenido del reporte */
        $this->pdf->writeHTML($htmlContent, true, false, true, false, '');
        if (isset($htmlParams['image'])) {
            $this->pdf->Image(
                $htmlParams['image'],
                ($this->pdf->getPageWidth() / 2) - 50,
            );
        }
        /* Establece el apuntador del reporte a la última página generada */
        $this->pdf->lastPage();
        /*
         | Genera el reporte. Las opciones disponibles son:
         |
         | I: Genera el archivo directamente para ser visualizado en el navegador
         | D: Genera el archivo y forza la descarga del mismo
         | F: Guarda el archivo generado en la ruta del servidor establecida por defecto
         | S: Devuelve el documento generado como una cadena de texto
         | FI: Es equivalente a las opciones F + I
         | FD: Es equivalente a las opciones F + D
         | E: Devuelve el documento del tipo mime base64 para ser adjuntado en correos electrónicos
         */
        return $this->pdf->Output(storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $this->filename, 'F');
    }

    /**
     * Verifica el tamaño de la hoja para el salto de página
     *
     * @return float
     */
    public function getCheckBreak()
    {
        return $this->pdf->getPageHeight() - $this->pdf->getBreakMargin();
    }

    /**
     * Obtiene la posición Y actual
     *
     * @return mixed
     */
    public function getPositionY()
    {
        return $this->pdf->GetY();
    }
}
