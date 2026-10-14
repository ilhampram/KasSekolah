<table>
    <thead>
        <tr>
            <th colspan="7" style="font-size: 16px; font-weight: bold; text-align: center;">LAPORAN ARUS KAS SEKOLAH</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">Periode: {{ \Carbon\Carbon::parse($filter['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filter['end_date'])->format('d/m/Y') }} | Kelas: {{ $filter['class_name'] }} | Kategori: {{ $filter['category'] }}</th>
        </tr>
        <tr><th></th></tr>
        <tr>
            <th colspan="3" style="font-weight: bold; background-color: #f3f4f6;">REKAPITULASI SALDO</th>
            <th colspan="4" style="font-weight: bold; background-color: #f3f4f6;">NOMINAL (RP)</th>
        </tr>
        <tr>
            <td colspan="3">Saldo Awal (Sebelum {{ \Carbon\Carbon::parse($filter['start_date'])->format('d/m/Y') }})</td>
            <td colspan="4" style="font-weight: bold; text-align: right;">{{ $summary['saldo_awal'] }}</td>
        </tr>
        <tr>
            <td colspan="3">Total Mutasi Masuk (Pemasukan)</td>
            <td colspan="4" style="font-weight: bold; color: #16a34a; text-align: right;">{{ $summary['mutasi_masuk'] }}</td>
        </tr>
        <tr>
            <td colspan="3">Total Mutasi Keluar (Pengeluaran)</td>
            <td colspan="4" style="font-weight: bold; color: #dc2626; text-align: right;">{{ $summary['mutasi_keluar'] }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold;">Saldo Akhir</td>
            <td colspan="4" style="font-weight: bold; text-align: right;">{{ $summary['saldo_akhir'] }}</td>
        </tr>
        <tr><th></th></tr>
        <tr style="background-color: #4f46e5; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000;">Tanggal</th>
            <th style="border: 1px solid #000;">No. Transaksi</th>
            <th style="border: 1px solid #000;">Kategori</th>
            <th style="border: 1px solid #000;">Kelas / Lingkup</th>
            <th style="border: 1px solid #000;">Keterangan</th>
            <th style="border: 1px solid #000;">Pemasukan (Rp)</th>
            <th style="border: 1px solid #000;">Pengeluaran (Rp)</th>
            <th style="border: 1px solid #000;">Saldo Berjalan (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $row)
            @php $trx = $row['model']; @endphp
            <tr>
                <td style="border: 1px solid #ddd;">{{ $trx->transaction_date->format('d/m/Y') }}</td>
                <td style="border: 1px solid #ddd;">{{ $trx->transaction_number }}</td>
                <td style="border: 1px solid #ddd;">{{ $trx->category->name }}</td>
                <td style="border: 1px solid #ddd;">{{ $trx->schoolClass ? $trx->schoolClass->class_name : 'Kas Umum' }}</td>
                <td style="border: 1px solid #ddd;">{{ $trx->title }}</td>
                <td style="border: 1px solid #ddd; text-align: right;">{{ $trx->type === 'in' ? $trx->amount : 0 }}</td>
                <td style="border: 1px solid #ddd; text-align: right;">{{ $trx->type === 'out' ? $trx->amount : 0 }}</td>
                <td style="border: 1px solid #ddd; text-align: right; font-weight: bold;">{{ $row['running_balance'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
