<?php

namespace App\Services;

use Clegginabox\PDFMerger\PDFMerger;
use Dompdf\Dompdf;
use Dompdf\Options;
use File;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public const DEFAULT_DPI = 72;

    private int $dpi;
    private string $paper = 'A4';
    private float $ratio = 1;

    public function __construct($dpi = null)
    {
        $this->setDpi($dpi ?? static::DEFAULT_DPI);
    }

    public function setDpi(int $dpi): self
    {
        $this->ratio = $dpi / self::DEFAULT_DPI;
        $this->dpi = $dpi;

        return $this;
    }

    public function getDpi(): int
    {
        if (is_null($this->dpi)) {
            $this->dpi = self::DEFAULT_DPI;
        }
        return $this->dpi;
    }

    public function setPaper(string $paper): self
    {
        $this->paper = $paper;
        return $this;
    }

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function create(string $pdfContent, string $path): void
    {
        $dompdfTempDir = Storage::disk('temp')
            ->path('dompdf');
        File::ensureDirectoryExists(dirname($path));
        File::ensureDirectoryExists($dompdfTempDir);

        $options = new Options([
            'fontDir' => $dompdfTempDir,
            'fontCache' => $dompdfTempDir,
            'tempDir' => $dompdfTempDir,
            'isRemoteEnabled' => true,
        ]);
        $options->setDpi($this->getDpi());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfContent);

        $dompdf->setPaper($this->paper)
            ->render();

        file_put_contents($path, $dompdf->output());
    }

    public function merge(array $inputFiles, string $outputPath): void
    {
        $pdf = new PDFMerger();

        foreach ($inputFiles as $inputFile) {
            $pdf->addPDF($inputFile);
        }

        $pdf->merge('file', $outputPath);
    }
}
