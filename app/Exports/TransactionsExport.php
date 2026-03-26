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

    // 1. Menerima query dan parameter tanggal dari Controller
    public function __construct($query, $startDate, $endDate)
    {
        $this->query = $query;
        
        // Buat string rentang tanggal
        $start = $startDate ? Carbon::parse($startDate)->format('d/m/Y') : 'Awal';
        $end = $endDate ? Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang';
        $this->dateRange = $start . ' - ' . $end;

        // Hitung Grand Total HANYA untuk transaksi yang tidak batal
        // Kita wajib pakai (clone) agar query asli untuk data tabel tidak rusak
        $this->grandTotal = (clone $query)
            ->where(function($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['canceled', 'void']); // Sesuaikan dengan nama status batal di DB-mu
            })
            ->sum('total_amount');
    }

    // 2. Eksekusi query (Otomatis di-chunk oleh package)
    public function query()
    {
        return $this->query->orderBy('transaction_date', 'desc');
    }

    // 3. Meracik Header Laporan (Multi-baris)
    public function headings(): array
    {
        return [
            ['LAPORAN PENJUALAN HEALINK'],
            ['Rentang Tanggal: ' . $this->dateRange],
            ['Dicetak pada: ' . Carbon::now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i')],
            [''], // Baris kosong sebagai jarak
            [
                'No. Transaksi',
                'Tanggal',
                'Nama Kasir',
                'Pelanggan',
                'Subtotal (Rp)',
                'Status',
                'Catatan'
            ]
        ];
    }

    // 4. Mapping Data per Baris
    public function map($trx): array
    {
        $status = ($trx->status === 'canceled' || $trx->status === 'void') ? 'Batal' : 'Sukses';
        
        return [
            $trx->trx_no,
            Carbon::parse($trx->transaction_date)->format('d/m/Y H:i'),
            $trx->user ? $trx->user->name : 'Umum',
            $trx->customer_name ?? '-',
            $trx->total_amount, // Biarkan berupa angka mentah agar format kolom Excel bekerja
            $status,
            $trx->note ?? '-'
        ];
    }

    // 5. Format Kolom Excel (Agar Subtotal otomatis pakai pemisah ribuan)
    public function columnFormats(): array
    {
        return [
            // Kolom E adalah kolom 'Subtotal (Rp)'
            'E' => '#,##0', 
        ];
    }

    // 6. Styling Dasar
    public function styles(Worksheet $sheet)
    {
        return [
            // Judul Laporan
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '3A7CF0']]],
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '7C8895']]],
            3 => ['font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '7C8895']]],
            
            // Header Tabel (Berada di baris ke-5 karena ada 4 baris header di atasnya)
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '3A7CF0']]
            ],
        ];
    }

    // 7. Event Listener (Untuk Inject Baris Grand Total di Bawah & Merge Cell Judul)
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Merge cell untuk 3 baris judul di atas
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');

                // Cari baris terakhir yang ada datanya
                $highestRow = $sheet->getHighestRow();
                
                // Siapkan baris baru untuk Grand Total (Baris terakhir + 1)
                $totalRow = $highestRow + 1;

                // Tulis teks Grand Total
                $sheet->setCellValue('A' . $totalRow, 'TOTAL PENDAPATAN BERSIH (Sukses)');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow); // Gabungkan kolom A sampai D
                
                // Masukkan angka Grand Total di kolom E
                $sheet->setCellValue('E' . $totalRow, $this->grandTotal);

                // Percantik baris Grand Total
                $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '3A7CF0']
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'EAF2FF']
                    ]
                ]);

                // Rata Kanan untuk tulisan Grand Total
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Set format ribuan untuk angka Grand Total
                $sheet->getStyle('E' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}