<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $filename ?? 'Resep Dokter' }}</title>
    <style>
        @page {
            size: a5 portrait;
            margin: 8mm 14mm 8mm 14mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.25;
            background: #ffffff;
        }
        .resep-card {
            width: 100%;
            margin: 0 auto;
        }
        /* KOP SURAT */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #000000;
            padding-bottom: 5px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 52px;
            text-align: left;
        }
        .kop-logo img {
            width: 50px;
            height: 50px;
            display: block;
        }
        .kop-text {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
        }
        .kop-instansi {
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .kop-puskesmas {
            font-size: 12.5pt;
            font-weight: bold;
            letter-spacing: 0.8px;
            line-height: 1.2;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 8pt;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.2;
            color: #111111;
        }

        /* INFORMASI DOKTER & TANGGAL */
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
        }
        .meta-table td {
            vertical-align: top;
        }
        .dokter-nama {
            font-weight: bold;
            font-size: 11.5pt;
        }
        .dokter-sip {
            font-size: 9.5pt;
            color: #222222;
            margin-top: 1px;
        }
        .meta-kanan {
            text-align: right;
            font-size: 10pt;
        }

        /* DAFTAR OBAT */
        .resep-daftar {
            width: 100%;
        }
        .resep-item {
            margin-bottom: 10px;
        }
        .resep-baris {
            width: 100%;
            border-collapse: collapse;
        }
        .resep-baris td {
            vertical-align: baseline;
        }
        .r-simbol {
            font-size: 16pt;
            font-weight: bold;
            font-style: italic;
            width: 25px;
            line-height: 1;
        }
        .obat-nama {
            font-weight: bold;
            font-size: 11pt;
            text-align: left;
        }
        .obat-nomor {
            text-align: right;
            font-weight: bold;
            font-size: 10.5pt;
            white-space: nowrap;
        }
        .obat-nomor span {
            text-decoration: underline;
        }
        .signa-teks {
            padding-left: 25px;
            font-size: 9.5pt;
            margin-top: 1px;
            font-weight: normal;
            color: #111111;
        }
        .garis-pemisah-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }
        .garis-pemisah-table td {
            vertical-align: middle;
            padding: 0;
        }
        .garis-line {
            border-bottom: 1px solid #000000;
            height: 1px;
        }
        .paraf-kecil {
            width: 22px;
            text-align: right;
            vertical-align: middle;
            padding-left: 4px;
            line-height: 0;
        }

        /* PARAF PENUTUP AKHIR RESEP */
        .paraf-akhir {
            text-align: center;
            padding: 6px 0 4px 0;
            margin: 0 auto;
        }
        .paraf-akhir img {
            width: 75px;
            height: 30px;
            display: inline-block;
            margin: 0 auto;
        }

        /* CATATAN ANJURAN DOKTER */
        .catatan-box {
            border-top: 1px dashed #444444;
            padding-top: 5px;
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 9.5pt;
            font-family: 'Times New Roman', Times, serif;
        }
        .catatan-judul {
            font-weight: bold;
            font-style: italic;
            margin-bottom: 2px;
        }

        /* FOOTER PRO PASIEN */
        .pro-table {
            width: 100%;
            border-top: 1.5px solid #000000;
            padding-top: 7px;
            margin-top: 8px;
            font-size: 9.5pt;
            line-height: 1.45;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
        }
        .pro-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        .pro-label {
            width: 55px;
            font-weight: bold;
        }
        .pro-titik {
            width: 10px;
            font-weight: bold;
        }
        .pro-nama {
            font-weight: bold;
            font-size: 10.5pt;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="resep-card">

    <!-- KOP PUSKESMAS -->
    <table class="kop-table" cellspacing="0" cellpadding="0">
        <tr>
            @if(!empty($logoBase64))
            <td class="kop-logo">
                <img src="{{ $logoBase64 }}" alt="Logo Kota Surabaya">
            </td>
            @endif
            <td class="kop-text">
                <div class="kop-instansi">PEMERINTAH KOTA SURABAYA</div>
                <div class="kop-instansi">DINAS KESEHATAN KOTA SURABAYA</div>
                <div class="kop-puskesmas">PUSKESMAS KALIJUDAN</div>
                <div class="kop-alamat">Jl. Kalijudan No. 123 Surabaya 60114</div>
                <div class="kop-alamat">Telp. (031) 3824566</div>
            </td>
        </tr>
    </table>

    <!-- INFORMASI DOKTER & TANGGAL -->
    <table class="meta-table" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left;">
                <div class="dokter-nama">{{ $dokter }}</div>
                <div class="dokter-sip">{{ $sip }}</div>
            </td>
            <td class="meta-kanan">
                {{ $kota }}, {{ $tanggal }}
            </td>
        </tr>
    </table>

    <!-- DAFTAR R/ RESEP OBAT -->
    <div class="resep-daftar">
        @foreach($obatList as $obat)
            <div class="resep-item">
                <table class="resep-baris" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="r-simbol">R/</td>
                        <td class="obat-nama">{{ $obat['nama'] }}</td>
                        <td class="obat-nomor">
                            no. <span>{{ $obat['jumlah_romawi'] }}</span>
                        </td>
                    </tr>
                </table>
                @if(!empty($obat['signa_bersih']))
                    <div class="signa-teks">{{ $obat['signa_bersih'] }}</div>
                @endif
                <!-- Garis Pembatas Obat & Paraf Kecil -->
                <table class="garis-pemisah-table" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="garis-line"></td>
                        <td class="paraf-kecil">
                            @if(!empty($parafKecilBase64))
                                <img src="{{ $parafKecilBase64 }}" alt="paraf" style="width: 18px; height: 9px; display: inline-block;">
                            @else
                                <span style="font-size: 11pt; line-height: 1;">~</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach

        <!-- Paraf Penutup Akhir Resep (Tanda Obat Terakhir Dokter di Bagian Tengah) -->
        <div class="paraf-akhir">
            @if(!empty($parafAkhirBase64))
                <img src="{{ $parafAkhirBase64 }}" alt="paraf akhir">
            @endif
        </div>
    </div>

    <!-- CATATAN / ANJURAN DOKTER -->
    @if(!empty($catatan))
        <div class="catatan-box">
            <div class="catatan-judul">Catatan / Anjuran Dokter:</div>
            <div>{!! nl2br(e($catatan)) !!}</div>
        </div>
    @endif

    <!-- IDENTITAS PASIEN (PRO) -->
    <table class="pro-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="pro-label">Pro</td>
            <td class="pro-titik">:</td>
            <td class="pro-nama">{{ $pasien['nama'] ?? $pasien_nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="pro-label">Umur</td>
            <td class="pro-titik">:</td>
            <td>
                <table style="width: 100%; border-collapse: collapse; border: none;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding: 0; vertical-align: top;">{{ $pasien['umur'] ?? $pasien_umur ?? '-' }}</td>
                        <td style="padding: 0; text-align: right; vertical-align: top; padding-right: 15px;">BB : ............... kg</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="pro-label">No. RM</td>
            <td class="pro-titik">:</td>
            <td>{{ $pasien['no_rm'] ?? $pasien_no_rm ?? '-' }}</td>
        </tr>
        <tr>
            <td class="pro-label">Alamat</td>
            <td class="pro-titik">:</td>
            <td>{{ $pasien['alamat'] ?? $pasien_alamat ?? '-' }}</td>
        </tr>
    </table>

</div>
</body>
</html>