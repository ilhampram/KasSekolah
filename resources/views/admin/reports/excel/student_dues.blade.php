<table>
    <thead>
        <tr>
            <th colspan="9" style="font-size: 16px; font-weight: bold; text-align: center;">REKAPITULASI IURAN DAN TUNGGAKAN SISWA</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center;">Filter Kelas: {{ e($filter['class_name']) }} | Kategori: {{ e($filter['category']) }} | Dicetak: {{ $filter['generated_at'] }}</th>
        </tr>
        <tr><th></th></tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #f3f4f6;">RINGKASAN</th>
            <th colspan="5" style="font-weight: bold; background-color: #f3f4f6;">NILAI</th>
        </tr>
        <tr>
            <td colspan="4">Total Target Tagihan</td>
            <td colspan="5" style="font-weight: bold; text-align: right;">{{ $summary['total_target'] }}</td>
        </tr>
        <tr>
            <td colspan="4">Total Iuran Terkumpul</td>
            <td colspan="5" style="font-weight: bold; color: #16a34a; text-align: right;">{{ $summary['total_paid'] }}</td>
        </tr>
        <tr>
            <td colspan="4">Total Sisa Tunggakan</td>
            <td colspan="5" style="font-weight: bold; color: #dc2626; text-align: right;">{{ $summary['total_debt'] }}</td>
        </tr>
        <tr>
            <td colspan="4">Jumlah Siswa Lunas / Belum</td>
            <td colspan="5" style="font-weight: bold; text-align: right;">{{ $summary['lunas_count'] }} Lunas / {{ $summary['belum_count'] }} Menunggak</td>
        </tr>
        <tr><th></th></tr>
        <tr style="background-color: #4f46e5; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000;">#</th>
            <th style="border: 1px solid #000;">Nama Siswa</th>
            <th style="border: 1px solid #000;">NISN</th>
            <th style="border: 1px solid #000;">Kelas</th>
            <th style="border: 1px solid #000;">Judul Tagihan</th>
            <th style="border: 1px solid #000;">Nominal Tagihan (Rp)</th>
            <th style="border: 1px solid #000;">Sudah Dibayar (Rp)</th>
            <th style="border: 1px solid #000;">Sisa Tunggakan (Rp)</th>
            <th style="border: 1px solid #000;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($duesData as $i => $item)
            <tr>
                <td style="border: 1px solid #ddd; text-align: center;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #ddd; font-weight: bold;">{{ $item['student_name'] }}</td>
                <td style="border: 1px solid #ddd;">{{ $item['nisn'] }}</td>
                <td style="border: 1px solid #ddd;">{{ $item['class_name'] }}</td>
                <td style="border: 1px solid #ddd;">{{ $item['bill_title'] }}</td>
                <td style="border: 1px solid #ddd; text-align: right;">{{ $item['amount'] }}</td>
                <td style="border: 1px solid #ddd; text-align: right; color: #16a34a;">{{ $item['paid'] }}</td>
                <td style="border: 1px solid #ddd; text-align: right; color: #dc2626; font-weight: bold;">{{ $item['remaining'] }}</td>
                <td style="border: 1px solid #ddd; text-align: center; text-transform: uppercase;">{{ $item['status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
