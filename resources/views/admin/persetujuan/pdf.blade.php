<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Informed Consent - {{ $persetujuan->pasien->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.6;
            padding: 40px 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1a1a1a;
        }
        .header h1 {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .content {
            margin-bottom: 15px;
        }
        .poin-list {
            margin: 15px 0;
            padding-left: 5px;
        }
        .poin-item {
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }
        .poin-number {
            display: table-cell;
            width: 25px;
            font-weight: bold;
            vertical-align: top;
        }
        .poin-text {
            display: table-cell;
            vertical-align: top;
        }
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            width: 200px;
            text-align: center;
        }
        .signature-box img {
            max-width: 180px;
            max-height: 80px;
            margin-bottom: 5px;
        }
        .signature-line {
            border-bottom: 1px solid #1a1a1a;
            margin-bottom: 4px;
        }
        .signature-name {
            font-size: 11px;
            font-weight: bold;
        }
        .metadata {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
            font-size: 9px;
            color: #888;
        }
        .metadata table {
            width: 100%;
        }
        .metadata td {
            padding: 2px 0;
            vertical-align: top;
        }
        .metadata .label {
            width: 120px;
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $persetujuan->salinan_judul }}</h1>
    </div>

    <div class="content">
        {!! nl2br(e($persetujuan->salinan_teks_pembuka)) !!}
    </div>

    @if(is_array($persetujuan->salinan_poin) && count($persetujuan->salinan_poin) > 0)
    <div class="poin-list">
        @foreach($persetujuan->salinan_poin as $i => $poin)
        <div class="poin-item">
            <div class="poin-number">{{ $i + 1 }}.</div>
            <div class="poin-text">{{ $poin }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="content">
        {!! nl2br(e($persetujuan->salinan_teks_penutup)) !!}
    </div>

    <div class="signature-section">
        <div class="signature-box">
            @if($persetujuan->tanda_tangan_path && $signatureBase64)
                <img src="{{ $signatureBase64 }}" alt="Tanda Tangan">
            @else
                <div style="height: 80px;"></div>
            @endif
            <div class="signature-line"></div>
            <div class="signature-name">{{ $persetujuan->pasien->nama }}</div>
        </div>
    </div>

    {{-- Metadata untuk arsip --}}
    <div class="metadata">
        <table>
            <tr>
                <td class="label">Diterbitkan oleh</td>
                <td>: {{ $persetujuan->petugas->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal terbit</td>
                <td>: {{ $persetujuan->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
            @if($persetujuan->ditandatangani_pada)
            <tr>
                <td class="label">Ditandatangani pada</td>
                <td>: {{ $persetujuan->ditandatangani_pada->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
            @endif
        </table>
    </div>

</body>
</html>
