<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas Sekolah</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #555;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 6px 10px;
            border: 1px solid #ccc;
            font-size: 10px;
        }
        .summary-box .title {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 5px 7px;
            font-size: 10px;
        }
        .data-table th {
            background-color: #2e384d;
            color: white;
            text-align: left;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #16a34a; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .signatures {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signatures td {
            text-align: center;
            font-size: 10px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SISTEM MANAJEMEN KAS SEKOLAH</h1>
        <p>LAPORAN ARUS KAS & MUTASI KEUANGAN</p>
        <p>Periode: {{ \Carbon\Carbon::parse($filter['start_date'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filter['end_date'])->format('d M Y') }} | Kelas: {{ $filter['class_name'] }} | Kategori: {{ $filter['category'] }}</p>
    </div>

    {{-- Rekapitulasi Otomatis Box --}}
    <table class="summary-box">
        <tr>
            <td class="title" width="25%">Saldo Awal</td>
            <td width="25%" class="text-right">Rp {{ number_format($summary['saldo_awal'], 0, ',', '.') }}</td>
            <td class="title" width="25%">Mutasi Masuk (+)</td>
            <td width="25%" class="text-right text-success">Rp {{ number_format($summary['mutasi_masuk'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="title">Mutasi Keluar (-)</td>
            <td class="text-right text-danger">Rp {{ number_format($summary['mutasi_keluar'], 0, ',', '.') }}</td>
            <td class="title">Saldo Akhir</td>
            <td class="text-right" style="font-weight: bold; background-color: #eef2ff;">
                Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}
            </td>
        </tr>
    </table>

    {{-- Tabel Transaksi --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="8%" class="text-center">Tanggal</th>
                <th width="15%">No. Transaksi</th>
                <th width="12%">Kategori</th>
                <th width="12%">Kelas / Lingkup</th>
                <th>Keterangan</th>
                <th width="12%" class="text-right">Masuk (Rp)</th>
                <th width="12%" class="text-right">Keluar (Rp)</th>
                <th width="13%" class="text-right">Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $row)
                @php $trx = $row['model']; @endphp
                <tr>
                    <td class="text-center">{{ $trx->transaction_date->format('d/m/Y') }}</td>
                    <td style="font-family: monospace;">{{ $trx->transaction_number }}</td>
                    <td>{{ $trx->category->name }}</td>
                    <td>{{ $trx->schoolClass ? $trx->schoolClass->class_name : 'Kas Umum' }}</td>
                    <td>{{ $trx->title }}</td>
                    <td class="text-right {{ $trx->type === 'in' ? 'text-success' : '' }}">
                        {{ $trx->type === 'in' ? number_format($trx->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right {{ $trx->type === 'out' ? 'text-danger' : '' }}">
                        {{ $trx->type === 'out' ? number_format($trx->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        Rp {{ number_format($row['running_balance'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #888;">
                        Tidak ada transaksi kas dalam periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tanda Tangan Pengesahan --}}
    <table class="signatures">
        <tr>
            <td width="50%">
                <p>Mengetahui,</p>
                <p>Kepala Sekolah / Penanggung Jawab</p>
                <br><br><br><br>
                <p><strong>( .................................................... )</strong></p>
                <p>NIP. ........................................</p>
            </td>
            <td width="50%">
                <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
                <p>Bendahara Sekolah / Petugas Kas</p>
                <br><br><br><br>
                <p><strong>{{ auth()->user()->name }}</strong></p>
                <p>Admin / Bendahara</p>
            </td>
        </tr>
    </table>
</body>
</html>
