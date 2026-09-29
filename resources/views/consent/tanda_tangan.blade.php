<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tanda Tangan - Informed Consent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-font-smoothing: antialiased; }
        .signature-canvas {
            touch-action: none;
            cursor: crosshair;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-gray-800">Informed Consent</p>
                <p class="text-xs text-gray-500">Puskesmas Kalijudan</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Berlaku hingga</p>
                <p class="text-xs font-semibold text-red-600">{{ $persetujuan->kedaluwarsa_pada->translatedFormat('H:i, d M Y') }}</p>
            </div>
        </div>
    </header>

    <main class="max-w-lg mx-auto px-4 py-5 space-y-4">

        {{-- Greeting --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm text-blue-900">
                Halo <strong>{{ $persetujuan->pasien->nama }}</strong>, silakan baca isi surat persetujuan di bawah ini dengan teliti, lalu bubuhkan tanda tangan Anda di bagian bawah.
            </p>
        </div>

        {{-- Isi Dokumen --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Isi Surat Persetujuan</h2>
            </div>
            <div class="p-4 space-y-4">
                <div class="text-center text-sm font-bold uppercase tracking-wide border-b border-gray-300 pb-3">
                    {{ $persetujuan->salinan_judul }}
                </div>

                <div class="text-sm text-gray-800 leading-relaxed">
                    {!! nl2br(e($persetujuan->salinan_teks_pembuka)) !!}
                </div>

                @if(is_array($persetujuan->salinan_poin) && count($persetujuan->salinan_poin) > 0)
                <div class="text-sm text-gray-800 space-y-2">
                    @foreach($persetujuan->salinan_poin as $i => $poin)
                    <div class="flex gap-2">
                        <span class="font-semibold min-w-[20px] text-gray-700">{{ $i + 1 }}.</span>
                        <div class="leading-relaxed">{{ $poin }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="text-sm text-gray-800 leading-relaxed">
                    {!! nl2br(e($persetujuan->salinan_teks_penutup)) !!}
                </div>
            </div>
        </div>

        {{-- Area Tanda Tangan --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanda Tangan</h2>
                <button type="button" id="btn-hapus" class="text-xs text-red-600 font-medium hover:underline">
                    Hapus
                </button>
            </div>
            <div class="p-4">
                <p class="text-xs text-gray-500 mb-3">Gunakan jari Anda untuk menandatangani di kotak di bawah ini:</p>
                <div class="border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 overflow-hidden" id="canvas-wrapper">
                    <canvas id="signature-canvas" class="signature-canvas w-full" height="200"></canvas>
                </div>
                <p class="text-xs text-gray-400 mt-2 text-center" id="canvas-hint">Sentuh dan geser untuk menandatangani</p>
            </div>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            @foreach($errors->all() as $error)
            <p class="text-sm text-red-700">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Tombol Kirim --}}
        <form action="{{ route('consent.sign', $persetujuan->token_qr) }}" method="POST" id="form-consent">
            @csrf
            <input type="hidden" name="tanda_tangan" id="input-tanda-tangan">
            
            <button type="submit" id="btn-kirim" disabled
                class="w-full py-3.5 px-4 text-sm font-semibold rounded-xl border border-transparent bg-gray-300 text-gray-500 cursor-not-allowed transition-all">
                Tandatangani & Kirim
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center pb-4">
            Dengan mengirimkan tanda tangan, Anda menyatakan telah membaca dan menyetujui isi surat di atas.
        </p>

    </main>

    <script>
        const canvas = document.getElementById('signature-canvas');
        const ctx = canvas.getContext('2d');
        const wrapper = document.getElementById('canvas-wrapper');
        const btnHapus = document.getElementById('btn-hapus');
        const btnKirim = document.getElementById('btn-kirim');
        const inputTTD = document.getElementById('input-tanda-tangan');
        const formConsent = document.getElementById('form-consent');
        const hint = document.getElementById('canvas-hint');
        
        let isDrawing = false;
        let hasDrawn = false;

        // Set canvas size to match wrapper
        function resizeCanvas() {
            const rect = wrapper.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 200;
            ctx.strokeStyle = '#1a1a1a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        function startDraw(e) {
            e.preventDefault();
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            hasDrawn = true;
            hint.style.display = 'none';
            updateButton();
        }

        function stopDraw(e) {
            if (isDrawing) {
                e.preventDefault();
                isDrawing = false;
            }
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        
        // Touch events
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw, { passive: false });

        // Clear
        btnHapus.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
            hint.style.display = 'block';
            updateButton();
        });

        function updateButton() {
            if (hasDrawn) {
                btnKirim.disabled = false;
                btnKirim.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                btnKirim.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700', 'shadow-sm');
            } else {
                btnKirim.disabled = true;
                btnKirim.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                btnKirim.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-700', 'shadow-sm');
            }
        }

        // Submit
        formConsent.addEventListener('submit', function(e) {
            if (!hasDrawn) {
                e.preventDefault();
                alert('Silakan tanda tangan terlebih dahulu.');
                return;
            }
            inputTTD.value = canvas.toDataURL('image/png');
            btnKirim.disabled = true;
            btnKirim.textContent = 'Mengirim...';
        });
    </script>
</body>
</html>
