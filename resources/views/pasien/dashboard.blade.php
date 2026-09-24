@extends('layouts.app-pasien')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Kesehatan Anda</h1>
        <p class="text-sm text-gray-500 mt-1">Pantau grafik perkembangan dan riwayat pemeriksaan Anda secara mandiri.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Card Info Pasien -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 lg:col-span-1 flex flex-col">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Informasi Profil</h2>
        
        <div class="flex-1 space-y-4">
            <div>
                <span class="block text-sm text-gray-500 uppercase mb-1">Nama Lengkap</span>
                <span class="block text-base font-bold text-gray-900">{{ $pasien->nama }}</span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 uppercase mb-1">No RM (Rekam Medis)</span>
                <span class="block text-base font-semibold text-gray-900">{{ $pasien->no_rm ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 uppercase mb-1">NIK</span>
                <span class="block text-base font-semibold text-gray-900">{{ substr($pasien->nik, 0, 12) }}****</span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 uppercase mb-1">Tanggal Lahir / Usia</span>
                <span class="block text-base font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y') }}
                    <span class="text-gray-600 font-normal">({{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->age }} th)</span>
                </span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 uppercase mb-1">No BPJS</span>
                <span class="block text-base font-semibold text-gray-900">{{ $pasien->no_bpjs ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-sm text-gray-500 uppercase mb-1">Alamat</span>
                <span class="block text-base font-semibold text-gray-900">{{ $pasien->alamat ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Card Kondisi Terakhir -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Kondisi Kunjungan Terakhir</h2>
        
        @if($kunjunganTerakhir)
        <div class="mb-4">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                {{ \Carbon\Carbon::parse($kunjunganTerakhir->tanggal_periksa)->translatedFormat('l, d F Y') }}
            </span>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 flex flex-col justify-between">
                <div class="text-sm font-semibold text-blue-800 uppercase mb-2">Tekanan Darah</div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ $kunjunganTerakhir->tensi_sistol ?? '-' }}<span class="text-base font-semibold text-gray-600">/{{ $kunjunganTerakhir->tensi_diastol ?? '-' }}</span>
                    </div>
                    @php
                        $sistol = (int) $kunjunganTerakhir->tensi_sistol;
                        $diastol = (int) $kunjunganTerakhir->tensi_diastol;
                        $statusTensi = 'Normal';
                        $colorClass = 'text-emerald-700 bg-emerald-100 border-emerald-200';
                        
                        if ($sistol >= 140 || $diastol >= 90) {
                            $statusTensi = 'Tinggi';
                            $colorClass = 'text-red-700 bg-red-100 border-red-200';
                        } elseif ($sistol > 0 && ($sistol <= 90 || $diastol <= 60)) {
                            $statusTensi = 'Rendah';
                            $colorClass = 'text-yellow-700 bg-yellow-100 border-yellow-200';
                        }
                    @endphp
                    <div class="mt-3 inline-block px-2.5 py-1 rounded-md text-xs font-bold border {{ $colorClass }}">
                        Kondisi: {{ $statusTensi }}
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-sm font-semibold text-gray-600 uppercase mb-2">Berat Badan</div>
                <div class="text-2xl font-bold text-gray-900 mt-auto">
                    {{ $kunjunganTerakhir->berat_badan ?? '-' }}<span class="text-base font-semibold text-gray-500"> kg</span>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-sm font-semibold text-gray-600 uppercase mb-2">Tinggi Badan</div>
                <div class="text-2xl font-bold text-gray-900 mt-auto">
                    {{ $kunjunganTerakhir->tinggi_badan ?? '-' }}<span class="text-base font-semibold text-gray-500"> cm</span>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-sm font-semibold text-gray-600 uppercase mb-2">Suhu</div>
                <div class="text-2xl font-bold text-gray-900 mt-auto">
                    {{ $kunjunganTerakhir->temperature ?? '-' }}<span class="text-base font-semibold text-gray-500"> °C</span>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500 text-sm">Belum ada data riwayat kunjungan yang tercatat.</p>
        </div>
        @endif
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Chart Tensi -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Grafik Tekanan Darah</h3>
        <div class="relative h-72">
            <canvas id="tensiChart"></canvas>
        </div>
    </div>

    <!-- Chart BB -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Grafik Berat Badan</h3>
        <div class="relative h-72">
            <canvas id="bbChart"></canvas>
        </div>
    </div>
</div>

<!-- Riwayat Detail -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wider">Riwayat Lengkap Kunjungan</h2>
    </div>
    
    <div class="divide-y divide-gray-200">
        @forelse($pasien->pemeriksaans as $pemeriksaan)
        <div class="p-6 hover:bg-gray-50 transition-colors">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-md text-xs font-semibold bg-gray-800 text-white mb-2">
                        {{ \Carbon\Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('d M Y') }}
                    </span>
                    <h4 class="text-sm font-bold text-gray-900">{{ $pemeriksaan->nama_diagnosa ?? 'Pemeriksaan Rutin' }}</h4>
                </div>
                <div class="text-left md:text-right">
                    <p class="text-sm text-gray-600"><span class="font-medium">Dokter:</span> {{ $pemeriksaan->nakes ? $pemeriksaan->nakes->nama : '-' }}</p>
                    <p class="text-sm text-gray-600"><span class="font-medium">Tensi:</span> {{ $pemeriksaan->tensi_sistol }}/{{ $pemeriksaan->tensi_diastol }} mmHg</p>
                </div>
            </div>
            
            @if($pemeriksaan->keluhan)
            <div class="mb-4">
                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-1">Keluhan:</h5>
                <p class="text-sm text-gray-800">{{ $pemeriksaan->keluhan }}</p>
            </div>
            @endif

            @if($pemeriksaan->pemberianObats->count() > 0)
            <div>
                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">Obat yang Diresepkan:</h5>
                <div class="flex flex-wrap gap-2">
                    @foreach($pemeriksaan->pemberianObats as $obat)
                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            {{ $obat->nama_obat }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="p-8 text-center text-gray-500 text-sm">
            Belum ada riwayat kunjungan.
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const labels = {!! json_encode($labels) !!};
        
        // Chart Tensi (Line Chart dengan 2 data: Sistol & Diastol)
        const tensiCtx = document.getElementById('tensiChart').getContext('2d');
        const tensiSistol = {!! json_encode($tensiSistol) !!};
        const tensiDiastol = {!! json_encode($tensiDiastol) !!};
        
        new Chart(tensiCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sistolik',
                        data: tensiSistol,
                        borderColor: 'rgb(220, 38, 38)', // Red 600
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: 'rgb(220, 38, 38)',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Diastolik',
                        data: tensiDiastol,
                        borderColor: 'rgb(59, 130, 246)', // Blue 500
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    annotation: {
                        annotations: {
                            lineSistolik: {
                                type: 'line',
                                yMin: 120,
                                yMax: 120,
                                borderColor: 'rgba(220, 38, 38, 0.4)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                label: {
                                    display: true,
                                    content: 'Batas Normal Sistolik (120)',
                                    position: 'start',
                                    backgroundColor: 'rgba(220, 38, 38, 0.7)',
                                    color: 'white',
                                    font: { size: 10 }
                                }
                            },
                            lineDiastolik: {
                                type: 'line',
                                yMin: 80,
                                yMax: 80,
                                borderColor: 'rgba(59, 130, 246, 0.4)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                label: {
                                    display: true,
                                    content: 'Batas Normal Diastolik (80)',
                                    position: 'start',
                                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                                    color: 'white',
                                    font: { size: 10 }
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        offset: true,
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: false,
                        suggestedMin: 60,
                        suggestedMax: 180,
                        title: { display: true, text: 'mmHg' }
                    }
                }
            }
        });

        // Chart Berat Badan (Bar/Line Chart)
        const bbCtx = document.getElementById('bbChart').getContext('2d');
        const bbData = {!! json_encode($beratBadan) !!};
        
        new Chart(bbCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Berat Badan (kg)',
                    data: bbData,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)', // Emerald 500
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        offset: true,
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: false,
                        suggestedMin: Math.min(...bbData.filter(v => v > 0)) - 5 || 40,
                        suggestedMax: Math.max(...bbData) + 5 || 100,
                        title: { display: true, text: 'Kilogram (kg)' }
                    }
                }
            }
        });
    });
</script>
@endpush
