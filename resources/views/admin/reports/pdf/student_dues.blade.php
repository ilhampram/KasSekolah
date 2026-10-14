<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Tunggakan Iuran Siswa</title>
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
            font-size: 15px;
            margin: 0;
            text-transform: uppercase;
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
            padding: 5px 8px;
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
            padding: 5px 6px;
            font-size: 9px;
        }
        .data-table th {
            background-color: #1e293b;
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
            margin-top: 25px;
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
        <p>REKAPITULASI IURAN & STATUS TUNGGAKAN SISWA</p>
        <p>Kelas: {{ $filter['class_name'] }} | Kategori: {{ $filter['category'] }} | Tanggal Cetak: {{ $filter['generated_at'] }}</p>
    </div>

    {{-- Ringkasan Box --}}
    <table class="summary-box">
        <tr>
            <td class="title" width="25%">Total Target Tagihan</td>
            <td width="25%" class="text-right">Rp {{ number_format($summary['total_target'], 0, ',', '.') }}</td>
            <td class="title" width="25%">Total Terkumpul</td>
            <td width="25%" class="text-right text-success">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="title">Total Sisa Tunggakan</td>
            <td class="text-right text-danger">Rp {{ number_format($summary['total_debt'], 0, ',', '.') }}</td>
            <td class="title">Rasio Siswa Lunas</td>
            <td class="text-right font-bold">{{ $summary['lunas_count'] }} Lunas / {{ $summary['belum_count'] }} Menunggak</td>
        </tr>
    </table>

    {{-- Tabel Data --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="20%">Nama Siswa</th>
                <th width="12%">NISN</th>
                <th width="10%">Kelas</th>
                <th>Tagihan Kas</th>
                <th width="13%" class="text-right">Tagihan (Rp)</th>
                <th width="13%" class="text-right">Dibayar (Rp)</th>
                <th width="13%" class="text-right">Sisa (Rp)</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($duesData as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $item['student_name'] }}</strong></td>
                    <td style="font-family: monospace;">{{ $item['nisn'] }}</td>
                    <td>{{ $item['class_name'] }}</td>
                    <td>{{ $item['bill_title'] }}</td>
                    <td class="text-right">{{ number_format($item['amount'], 0, ',', '.') }}</td>
                    <td class="text-right text-success">{{ number_format($item['paid'], 0, ',', '.') }}</td>
                    <td class="text-right text-danger">{{ number_format($item['remaining'], 0, ',', '.') }}</td>
                    <td class="text-center" style="text-transform: uppercase; font-weight: bold;">
                        {{ $item['status'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #888;">
                        Tidak ada data tagihan yang sesuai.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <table class="signatures">
        <tr>
            <td width="50%">
                <p>Wali Kelas / Guru Pembina,</p>
                <br><br><br><br>
                <p><strong>( .................................................... )</strong></p>
                <p>NIP. ........................................</p>
            </td>
            <td width="50%">
                <p>Bendahara Sekolah / Operator,</p>
                <br><br><br><br>
                <p><strong>{{ auth()->user()->name }}</strong></p>
                <p>Admin / Bendahara</p>
            </td>
        </tr>
    </table>
</body>
</html>
