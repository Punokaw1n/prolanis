<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Pasien Rujuk Balik (PRB) - {{ $periodeJudul }}</title>
    
    <style>
        /* Standar Halaman Cetak Landscape A4 / F4 */
        /* Margin 0 menghilangkan header (tanggal/judul) & footer (URL/nomor halaman) bawaan browser */
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
            line-height: 1.25;
        }

        /* Container lembar dokumen */
        .sheet-container {
            width: 100%;
            max-width: 280mm;
            margin: 0 auto;
            background: #fff;
        }

        /* KOP SURAT PUSKESMAS */
        .kop-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-bottom: 3px double #000;
        }

        .kop-logo {
            position: absolute;
            left: 10px;
            top: 2px;
            width: 68px;
            height: 68px;
            object-fit: contain;
        }

        .kop-text {
            text-align: center;
            width: 100%;
            padding: 0 80px;
        }

        .kop-instansi {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .kop-dinas {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .kop-puskesmas {
            font-size: 15pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0 0 0;
        }

        .kop-alamat {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            margin-top: 3px;
            color: #222;
        }

        /* JUDUL REGISTER */
        .register-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .register-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            text-decoration: underline;
        }

        .register-subtitle {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        /* TABEL REGISTER RESMI */
        table.register-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 9.5pt;
        }

        table.register-table th,
        table.register-table td {
            border: 1px solid #000;
            padding: 6px 7px;
            vertical-align: middle;
        }

        table.register-table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9.5pt;
            letter-spacing: 0.3px;
        }

        /* Spesifik Kolom Sesuai Foto User */
        .col-no {
            width: 40px;
            text-align: center;
            font-weight: bold;
        }

        .col-tgl {
            width: 80px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            font-weight: 600;
        }

        .col-nama {
            width: 260px;
        }

        .col-obat {
            width: 320px;
        }

        .col-alamat {
            width: auto;
        }

        /* Styling data dalam sel */
        .px-nama {
            font-size: 10.5pt;
            font-weight: bold;
            color: #000;
        }

        .px-card {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #333;
            margin-top: 2px;
        }

        .px-note {
            font-style: italic;
            font-size: 8.5pt;
            color: #555;
            margin-top: 2px;
        }

        .obat-item {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 3px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
        }

        .obat-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .obat-nama {
            font-weight: 600;
            font-size: 9.5pt;
        }

        .obat-qty {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            font-size: 9.5pt;
            margin-left: 6px;
            white-space: nowrap;
        }

        .px-alamat {
            font-size: 9.5pt;
            line-height: 1.2;
        }

        .px-phone {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            margin-top: 3px;
            color: #222;
        }

        /* FLOATING ACTION TOOLBAR (SEMBUNYI SAAT PRINT) */
        .print-toolbar {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #1e293b;
            color: #fff;
            padding: 8px 14px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
        }

        .btn-action {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-close {
            background: #475569;
            color: #fff;
        }

        .btn-close:hover {
            background: #334155;
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }
            body {
                margin: 0 !important;
                padding: 8mm 10mm 10mm 10mm !important;
                background: #fff !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Toolbar on Browser -->
    <div class="print-toolbar no-print">
        <span><strong>Preview Cetak Register PRB</strong> (Landscape A4/F4)</span>
        <button type="button" onclick="window.print()" class="btn-action">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v6.75a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 15.75V9a2.25 2.25 0 0 1 2.25-2.25Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25m-9 9h9m-9 3h9"/></svg>
            Cetak Dokumen
        </button>
        <button type="button" onclick="window.close()" class="btn-action btn-close">
            Tutup
        </button>
    </div>

    <div class="sheet-container">
        <!-- KOP SURAT RESMI PUSKESMAS -->
        <div class="kop-wrapper">
            <img src="{{ asset('surabaya-city.png') }}?v=2" alt="Logo Pemkot Surabaya" class="kop-logo">
            <div class="kop-text">
                <div class="kop-instansi">PEMERINTAH KOTA SURABAYA</div>
                <div class="kop-dinas">DINAS KESEHATAN KOTA SURABAYA</div>
                <div class="kop-puskesmas">PUSKESMAS KALIJUDAN</div>
                <div class="kop-alamat">
                    Jl. Kalijudan No. 123 Surabaya 60114 | Telp. (031) 3824566 | Email: pusk.kalijudan@surabaya.go.id
                </div>
            </div>
        </div>

        <!-- JUDUL REGISTER SESUAI FORMAT BUKU REGISTER RUJUK BALIK -->
        <div class="register-header">
            <div class="register-title">REGISTER PASIEN RUJUK BALIK</div>
            <div class="register-subtitle">{{ $periodeJudul }}</div>
        </div>

        <!-- TABEL DATA PASIEN REGISTER -->
        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-tgl">TGL</th>
                    <th class="col-nama">NAMA PASIEN</th>
                    <th class="col-obat">NAMA OBAT</th>
                    <th class="col-alamat">ALAMAT / NO HP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayatList as $index => $r)
                    @php
                        $pasien = $r->pasienPrb?->pasien;
                    @endphp
                    <tr>
                        <!-- 1. NO -->
                        <td class="col-no">{{ $index + 1 }}</td>

                        <!-- 2. TGL (Format dd/mm/yy seperti di lembar fisik: 1/8/26) -->
                        <td class="col-tgl">
                            {{ $r->tanggal_diambil ? $r->tanggal_diambil->format('j/n/y') : '-' }}
                        </td>

                        <!-- 3. NAMA PASIEN (Nama & No BPJS + Catatan) -->
                        <td class="col-nama">
                            <div class="px-nama">{{ $pasien?->nama ?? '-' }}</div>
                            <div class="px-card">{{ $pasien?->no_bpjs ?: ($pasien?->nik ?: '') }}</div>
                            @if($r->catatan)
                                <div class="px-note">{{ $r->catatan }}</div>
                            @endif
                        </td>

                        <!-- 4. NAMA OBAT (Nama, Signa, (Qty)) -->
                        <td class="col-obat">
                            @if($r->obatPrb->count() > 0)
                                @foreach($r->obatPrb as $obat)
                                    <div class="obat-item">
                                        <span class="obat-nama">
                                            {{ $obat->nama_obat }} {{ $obat->signa ? $obat->signa : '' }}
                                        </span>
                                        <span class="obat-qty">
                                            ({{ $obat->jumlah_obat ?? 30 }})
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span style="font-style: italic; color: #777;">-</span>
                            @endif
                        </td>

                        <!-- 5. ALAMAT / NO HP -->
                        <td class="col-alamat">
                            <div class="px-alamat">{{ $pasien?->alamat ?: '-' }}</div>
                            @if($pasien?->no_hp)
                                <div class="px-phone">{{ $pasien->no_hp }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; font-style: italic; color: #555;">
                            Tidak ada data register pasien mengambil obat pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(request('autoprint') == 1)
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 400);
            });
        </script>
    @endif
</body>
</html>
