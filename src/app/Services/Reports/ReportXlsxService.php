<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class ReportXlsxService
{
    public function generate(array $headers, iterable $rows, string $filename): array
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $this->writeRow($sheet, 1, $headers);

        $rowNumber = 2;
        foreach ($rows as $row) {
            $this->writeRow($sheet, $rowNumber, array_values($row));
            $rowNumber++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = max(1, $rowNumber - 1);
        $headerRange = "A1:{$lastColumn}1";
        $dataRange = "A1:{$lastColumn}{$lastRow}";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF211A17'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($dataRange);

        foreach (range(1, count($headers)) as $columnIndex) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $path = tempnam(sys_get_temp_dir(), 'pqrsf-report-');

        if ($path === false) {
            $spreadsheet->disconnectWorksheets();

            throw new RuntimeException('No fue posible crear el archivo temporal del reporte.');
        }

        try {
            (new Xlsx($spreadsheet))->save($path);
            $content = file_get_contents($path);

            if ($content === false) {
                throw new RuntimeException('No fue posible leer el archivo XLSX generado.');
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
            unlink($path);
        }

        return [
            'content' => $content,
            'filename' => $filename,
        ];
    }

    private function writeRow($sheet, int $rowNumber, array $values): void
    {
        foreach ($values as $columnIndex => $value) {
            $coordinate = Coordinate::stringFromColumnIndex($columnIndex + 1).$rowNumber;
            $sheet->setCellValueExplicit(
                $coordinate,
                (string) ($value ?? ''),
                DataType::TYPE_STRING,
            );
        }
    }
}
