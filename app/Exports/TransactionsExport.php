<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TransactionsExport implements FromQuery, WithMapping, WithHeadings, WithStyles, WithEvents, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    protected $query;
    protected $dateRange;
    protected $grandTotal;

    public function __construct($query, $startDate, $endDate)
    {
        $this->query = $query;

        $start = $startDate ? Carbon::parse($startDate)->format('d/m/Y') : 'Awal';
        $end = $endDate ? Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang';
        $this->dateRange = $start . ' - ' . $end;

        $this->grandTotal = (clone $query)
            ->where(function($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['canceled', 'void']);
            })
            ->sum('total_amount');
    }

    public function query()
    {
        return $this->query->orderBy('transaction_date', 'desc');
    }

    public function headings(): array
    {
        return [
            ['HEALINK'],
            ['Laporan Riwayat Penjualan | Rentang Tanggal: ' . $this->dateRange],
            ['Dicetak pada: ' . Carbon::now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i')],
            [''],
            [
                'No. Transaksi',
                'Tanggal',
                'Nama Kasir',
                'Pelanggan',
                'Subtotal (Rp)',
                'HPP (Rp)',
                'Laba Kotor (Rp)',
                'Margin (%)',
                'Status',
                'Catatan'
            ]
        ];
    }

    public function map($trx): array
    {
        $isSuccess = !in_array($trx->status, ['canceled', 'void']);
        $status = $isSuccess ? 'Sukses' : 'Batal';

        $subtotal = $trx->total_amount;
        $cogs = 0;

        if ($isSuccess) {
            foreach ($trx->items as $item) {
                $buyPrice = 0;

                if ($item->batch) {
                    $buyPrice = $item->batch->buy_price ?? 0;
                } elseif ($item->product && $item->product->batches->isNotEmpty()) {
                    $buyPrice = $item->product->batches->first()?->buy_price ?? 0;
                }

                $cogs += $buyPrice * $item->qty;
            }
        }

        $profit = $isSuccess ? ($subtotal - $cogs) : 0;
        $margin = $isSuccess && $subtotal > 0
            ? round(($profit / $subtotal) * 100)
            : null;

        return [
            $trx->trx_no,
            Carbon::parse($trx->transaction_date)->format('d/m/Y H:i'),
            $trx->user ? $trx->user->name : 'Umum',
            $trx->customer_name ?? '-',
            $subtotal,
            $cogs,
            $profit,
            $margin,
            $status,
            $trx->note ?? '-'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '0"%"',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '3A7CF0']]],
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '7C8895']]],
            3 => ['font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '7C8895']]],

            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '3A7CF0']]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');

                $grandTotalRevenue = 0;
                $grandTotalCogs = 0;

                $transactions = (clone $this->query)
                    ->where(function($q) {
                        $q->whereNull('status')
                        ->orWhereNotIn('status', ['canceled', 'void']);
                    })
                    ->with(['items' => function($q) {
                        $q->with([
                            'batch' => fn($b) => $b->withTrashed(),
                            'product.batches' => fn($b) => $b->withTrashed()->orderBy('exp_date', 'asc')->limit(1)
                        ]);
                    }])
                    ->get();

                foreach ($transactions as $trx) {
                    $isSuccess = !in_array($trx->status, ['canceled', 'void']);
                    $subtotal = $trx->total_amount;
                    $cogs = 0;

                    if ($isSuccess) {
                        foreach ($trx->items as $item) {
                            $buyPrice = 0;

                            if ($item->batch) {
                                $buyPrice = $item->batch->buy_price ?? 0;
                            } elseif ($item->product && $item->product->batches->isNotEmpty()) {
                                $buyPrice = $item->product->batches->first()?->buy_price ?? 0;
                            }

                            $cogs += $buyPrice * $item->qty;
                        }
                        $grandTotalRevenue += $subtotal;
                        $grandTotalCogs += $cogs;
                    }
                }

                $grandProfit = $grandTotalRevenue - $grandTotalCogs;
                $grandMargin = $grandTotalRevenue > 0
                    ? round(($grandProfit / $grandTotalRevenue) * 100)
                    : null;

                $highestRow = $sheet->getHighestRow();

                $totalRow = $highestRow + 1;
                $sheet->setCellValue('A' . $totalRow, 'TOTAL PENJUALAN (SUKSES)');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                $sheet->setCellValue('E' . $totalRow, $this->grandTotal);
                $sheet->setCellValue('F' . $totalRow, $grandTotalCogs);
                $sheet->setCellValue('G' . $totalRow, $grandTotalRevenue);
                $sheet->setCellValue('H' . $totalRow, '-');

                $profitRow = $totalRow + 1;
                $sheet->setCellValue('A' . $profitRow, 'TOTAL LABA KOTOR');
                $sheet->mergeCells('A' . $profitRow . ':D' . $profitRow);
                $sheet->setCellValue('G' . $profitRow, $grandProfit);
                $sheet->setCellValue('H' . $profitRow, $grandMargin . '%');

                $sheet->getStyle('A' . $totalRow . ':J'     . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '3A7CF0']
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'EAF2FF']
                    ],
                ]);

                $sheet->getStyle('A' . $profitRow . ':J' . $profitRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '2E7D32']
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'E8F5E9']
                    ],
                ]);

                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A' . $profitRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle('A' . $totalRow . ':J' . $profitRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => 'DDDDDD']
                        ]
                    ]
                ]);

                $sheet->getStyle('E' . $totalRow . ':H' . $profitRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('E' . $totalRow . ':H' . $profitRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            },
        ];
    }
}
