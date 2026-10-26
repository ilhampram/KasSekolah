<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $payment->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 15px;
        }
        .receipt-card {
            border: 2px solid #2e384d;
            border-radius: 8px;
            padding: 0;
            overflow: hidden;
        }
        .header {
            background-color: #1e1b4b;
            color: #ffffff;
            padding: 16px 20px;
            border-bottom: 3px solid #fbbf24;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #c7d2fe;
        }
        .badge-no {
            text-align: right;
        }
        .badge-no .kwt-tag {
            background-color: #fbbf24;
            color: #1e1b4b;
            font-weight: bold;
            font-size: 9px;
            padding: 3px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-no .number {
            font-family: monospace;
            font-size: 11px;
            color: #e0e7ff;
            margin-top: 4px;
        }
        .content {
            padding: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 5px 0;
            vertical-align: top;
            font-size: 11px;
        }
        .label {
            width: 140px;
            color: #64748b;
            font-weight: bold;
        }
        .sep {
            width: 15px;
            color: #64748b;
        }
        .val {
            color: #0f172a;
        }
        .amount-box {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 14px 0;
        }
        .amount-table {
            width: 100%;
            border-collapse: collapse;
        }
        .amount-table .amt-label {
            font-size: 10px;
            font-weight: bold;
            color: #3730a3;
            text-transform: uppercase;
        }
        .amount-table .amt-val {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #312e81;
        }
        .terbilang {
            font-size: 11px;
            font-style: italic;
            color: #3730a3;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #c7d2fe;
        }
        .status-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 10px;
            margin-bottom: 16px;
        }
        .status-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            padding-top: 10px;
        }
        .signatures td {
            vertical-align: top;
            font-size: 10px;
        }
        .sign-col {
            width: 180px;
            text-align: center;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt-card">
        {{-- Kop Kwitansi --}}
        <div class="header">
            <table>
                <tr>
                    <td>
                        <h1>SISTEM KAS SEKOLAH</h1>
                        <p>Kwitansi Tanda Bukti Pembayaran Resmi</p>
                    </td>
                    <td class="badge-no">
                        <span class="kwt-tag">Kwitansi Kas</span>
                        <div class="number">No: {{ $payment->receipt_number }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Konten --}}
        <div class="content">
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal Bayar</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $payment->payment_date->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Metode Pembayaran</td>
                    <td class="sep">:</td>
                    <td class="val" style="text-transform: uppercase;">{{ $payment->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</td>
                </tr>
                <tr>
                    <td class="label">Telah Terima Dari</td>
                    <td class="sep">:</td>
                    <td class="val">
                        <strong>{{ $payment->student->name }}</strong>
                        @if($payment->student->nisn)
                            <span style="color: #64748b; font-size: 10px;">(NISN: {{ $payment->student->nisn }})</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Kelas</td>
                    <td class="sep">:</td>
                    <td class="val">{{ $bill->schoolClass->class_name }} ({{ $bill->schoolClass->academic_year }})</td>
                </tr>
                <tr>
                    <td class="label">Untuk Pembayaran</td>
                    <td class="sep">:</td>
                    <td class="val">
                        <strong>{{ $bill->title }}</strong>
                        <span style="color: #64748b; font-size: 10px;">— Kategori: {{ $bill->category->name }}</span>
                    </td>
                </tr>
            </table>

            {{-- Nominal Box --}}
            <div class="amount-box">
                <table class="amount-table">
                    <tr>
                        <td class="amt-label">Jumlah Pembayaran</td>
                        <td class="amt-val">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                    </tr>
                </table>
                <div class="terbilang">
                    Terbilang: # {{ $terbilang }} #
                </div>
            </div>

            {{-- Status Tagihan Siswa --}}
            <div class="status-box">
                <table>
                    <tr>
                        <td>
                            Status Pembayaran Siswa:
                            <strong style="color: {{ $statusInfo['status'] === 'lunas' ? '#166534' : '#b45309' }};">
                                {{ $statusInfo['status'] === 'lunas' ? '✓ LUNAS' : '⚡ CICILAN' }}
                            </strong>
                        </td>
                        <td style="text-align: right;">
                            Sisa Tagihan:
                            <strong>Rp {{ number_format($statusInfo['remaining'], 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </table>
            </div>

            @if($payment->notes)
                <p style="font-size: 10px; color: #64748b; margin: 4px 0 10px 0; font-style: italic;">
                    Catatan: {{ $payment->notes }}
                </p>
            @endif

            {{-- Tanda Tangan --}}
            <table class="signatures">
                <tr>
                    <td>
                        <span style="color: #64748b;">Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
                    </td>
                    <td class="sign-col">
                        <span style="color: #64748b;">Petugas Kasir / Bendahara,</span>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $payment->verifier->name ?? auth()->user()->name }}</div>
                        <span style="color: #94a3b8; font-size: 9px;">Tanda Tangan & Cap Sah</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
