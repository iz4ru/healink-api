<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Healink</title>
    <style>
        /* CSS Sederhana yang 100% didukung DomPDF */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #3A7CF0;
            padding-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            color: #3A7CF0;
            font-size: 24px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 4px 0;
            color: #7C8895;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #E3E3E3;
            padding: 10px 8px;
        }
        th {
            background-color: #FAFAFA;
            color: #556271;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            text-align: left;
        }
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* Pewarnaan Status & Total */
        .status-success { color: #4CAF50; font-weight: bold; }
        .status-canceled { color: #E03C27; font-weight: bold; }
        
        .total-row td {
            background-color: #EAF2FF;
            color: #3A7CF0;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>HEALINK</h1>
        <p>Laporan Riwayat Transaksi Penjualan</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="18%">No. Transaksi</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Kasir</th>
                <th width="17%">Pelanggan</th>
                <th class="text-center" width="10%">Status</th>
                <th class="text-right" width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            
            @forelse ($transactions as $index => $trx)
                @php
                    // Logika: Transaksi yang batal (canceled) tidak dihitung ke pendapatan bersih
                    if($trx->status !== 'canceled' && $trx->status !== 'void') {
                        $grandTotal += $trx->total_amount;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $trx->trx_no }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $trx->user ? $trx->user->name : 'Umum' }}</td>
                    <td>{{ $trx->customer_name ?? '-' }}</td>
                    <td class="text-center">
                        @if($trx->status === 'canceled' || $trx->status === 'void')
                            <span class="status-canceled">Batal</span>
                        @else
                            <span class="status-success">Sukses</span>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 30px; color: #7C8895;">
                        Tidak ada data transaksi yang ditemukan pada rentang filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL PENDAPATAN BERSIH (Sukses)</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>