<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Healink</title>
    <style>
        /* CSS Sederhana yang 100% didukung DomPDF */
        @font-face {
            font-family: 'Figtree';
            src: url('{{ public_path('fonts/Figtree-Medium.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Figtree';
            src: url('{{ public_path('fonts/Figtree-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'Figtree', sans-serif;
            font-size: 11px;
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

        th,
        td {
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
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Pewarnaan Status & Total */
        .status-success {
            color: #4CAF50;
            font-weight: bold;
        }

        .status-canceled {
            color: #E03C27;
            font-weight: bold;
        }

        .total-row td {
            background-color: #EAF2FF;
            color: #3A7CF0;
            font-weight: bold;
            font-size: 14px;
        }

        .text-canceled {
            color: #E03C27;
            text-decoration: line-through;
        }

        .text-profit-positive {
            color: #4CAF50;
            font-weight: bold;
        }

        .text-profit-negative {
            color: #E03C27;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>HEALINK</h1>
        <p>Laporan Riwayat Transaksi Penjualan</p>
        <p>Dicetak pada:
            {{ \Carbon\Carbon::now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="15%">No. Transaksi</th>
                <th width="15%">Tanggal</th>
                <th width="12%">Kasir</th>
                <th width="15%">Pelanggan</th>
                <th class="text-right" width="10%">Subtotal</th>
                <th class="text-right" width="10%">HPP</th>
                <th class="text-right" width="10%">Laba</th>
                <th class="text-center" width="8%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalRevenue = 0;
                $grandTotalCogs = 0;
            @endphp

            @forelse ($transactions as $index => $trx)
                @php
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

                    $profit = $subtotal - $cogs;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $trx->trx_no }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $trx->user ? $trx->user->name : 'Umum' }}</td>
                    <td>{{ $trx->customer_name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td class="text-right {{ !$isSuccess ? 'text-canceled' : '' }}">
                        Rp {{ number_format($cogs, 0, ',', '.') }}
                    </td>

                    <td class="text-right
                        {{ !$isSuccess
                            ? 'text-canceled'
                            : ($profit >= 0 ? 'text-profit-positive' : 'text-profit-negative') }}">
                        Rp {{ number_format($profit, 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        @if (!$isSuccess)
                            <span class="status-canceled">Batal</span>
                        @else
                            <span class="status-success">Sukses</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 30px; color: #7C8895;">
                        Tidak ada data transaksi yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php $grandProfit = $grandTotalRevenue - $grandTotalCogs; @endphp
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL PENJUALAN</strong></td>
                <td colspan="1" class="text-center">Rp {{ number_format($grandTotalCogs, 0, ',', '.') }}</td>
                <td colspan="1" class="text-center">Rp {{ number_format($grandTotalRevenue, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr class="total-row" style="background-color: #E8F5E9; color: #2E7D32;">
                <td colspan="6" class="text-right"><strong>TOTAL LABA KOTOR | MARGIN (%)</strong></td>
                <td colspan="2"class="text-center"><strong>Rp {{ number_format($grandProfit, 0, ',', '.') }}</strong></td>
                <td class="text-center">
                    <strong>{{ $grandTotalRevenue > 0 ? round(($grandProfit / $grandTotalRevenue) * 100) : 0 }}%</strong>
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
