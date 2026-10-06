# Rencana Iterasi — Modul PRB (Program Rujuk Balik)
**Panduan langkah demi langkah untuk vibecoding**

Lanjutan dari modul Prolanis yang sudah berjalan. Setiap langkah dirancang kecil, test dulu sebelum lanjut ke langkah berikutnya. Modul ini memakai ulang parser Register Poliklinik yang sudah dibangun di modul Prolanis — jangan menulis ulang dari nol.

---

## FASE 1 — Fondasi Database

1. **Migration `pasien_prb`** — kolom: id, pasien_id (FK ke `pasien`), status_keaktifan (aktif/tidak_aktif), tanggal_terdaftar, timestamps.
2. **Migration `riwayat_pengambilan_obat_prb`** — kolom: id, pasien_prb_id (FK), tanggal_jatuh_tempo, tanggal_diambil (nullable), sumber (enum: import/manual), dicatat_oleh (FK ke pengguna, nullable), terakhir_diingatkan (timestamp, nullable), timestamps.
3. **Migration `obat_prb`** — kolom: id, riwayat_pengambilan_obat_prb_id (FK), nama_obat, **kategori (enum: prb / non_prb)**, sumber (enum: import/manual), timestamps. Catatan: untuk versi ini, kedua kategori sama-sama milik pasien PRB (bukan populasi terpisah) — kolom ini hanya menandai dari sumber pencatatan apoteker yang mana data itu berasal.
4. **Test:** jalankan `php artisan migrate`, pastikan semua tabel dan FK terbentuk tanpa error.
5. **Buat Model + relasi Eloquent** — `PasienPrb` (belongsTo Pasien, hasMany RiwayatPengambilanObatPrb), `RiwayatPengambilanObatPrb` (belongsTo PasienPrb, hasMany ObatPrb), `ObatPrb` (belongsTo RiwayatPengambilanObatPrb). Tambahkan juga relasi `pasienPrb()` di model `Pasien` yang sudah ada.
6. **Test:** buat data dummy lewat Tinker, pastikan semua relasi berjalan dua arah.
7. **Buat accessor `status` di model `RiwayatPengambilanObatPrb`** — logic status & selisih hari dihitung dari `tanggal_jatuh_tempo` dan `tanggal_diambil` (lihat PRD bagian 6.3.1 untuk aturan lengkap: Sudah Ambil Tepat Waktu/Terlambat, Belum Ambil Terlambat, Belum Jatuh Tempo). **Ini bukan kolom database**, murni accessor/computed property.
8. **Test:** buat beberapa data dummy riwayat dengan kombinasi tanggal berbeda (sudah ambil tepat waktu, sudah ambil terlambat, belum ambil & sudah lewat jatuh tempo, belum ambil & belum jatuh tempo), pastikan accessor `status` mengembalikan label dan warna yang benar untuk tiap kasus.

---

## FASE 2 — Data Pasien PRB (Manual Dulu, Sebelum Import)

9. **Halaman daftar pasien PRB** — tabel dengan pencarian (nama/NIK), menampilkan status keaktifan dan tanggal jatuh tempo terakhir/terdekat.
10. **Form tambah pasien PRB manual** — input NIK dulu: kalau NIK sudah ada di `pasien`, tampilkan konfirmasi "pasien ditemukan: [nama]" lalu tinggal tandai sebagai PRB; kalau belum ada, tampilkan form lengkap data pasien baru (nama, alamat, tanggal lahir, dll — sama seperti field `pasien` yang sudah ada).
11. **Test:** coba tambah pasien PRB dengan NIK yang sudah ada di sistem (dari data Prolanis) — pastikan tidak membuat pasien duplikat, hanya menambah baris `pasien_prb`. Coba juga dengan NIK baru — pastikan membuat `pasien` baru sekaligus `pasien_prb`.
12. **Halaman detail pasien PRB** — profil pasien + daftar riwayat pengambilan obat (kronologis, terbaru di atas), menampilkan status & selisih hari dari accessor yang sudah dibuat (langkah 7).
13. **Test:** buka detail pasien yang sudah punya beberapa riwayat dummy, pastikan tampil berurutan dengan benar dan status/warna sesuai accessor.

---

## FASE 3 — Pencatatan Pengambilan Obat (Manual)

14. **Form catat pengambilan obat** — dari halaman detail pasien PRB: input tanggal diambil (default hari ini), tanggal jatuh tempo berikutnya (field terpisah, dengan teks bantuan "saran: 30 hari dari tanggal diambil" tapi tetap bisa diubah bebas oleh petugas — JANGAN hitung otomatis saat submit).
15. **Input obat manual dengan kategori** — di form yang sama, field dinamis untuk menambah beberapa nama obat sekaligus (tambah baris, hapus baris) menggunakan Alpine.js, masing-masing baris obat punya pilihan kategori: PRB atau Non-PRB.
16. **Test:** catat satu pengambilan dengan beberapa obat manual (campuran kategori PRB dan Non-PRB), pastikan tersimpan dengan benar di `riwayat_pengambilan_obat_prb` dan `obat_prb` (termasuk kategorinya), dan tanggal jatuh tempo yang disimpan **persis** sesuai yang diinput petugas (bukan hasil hitungan otomatis).

---

## FASE 4 — Import Data PRB (Reuse Parser Poliklinik)

17. **Cek ulang parser Register Poliklinik dari modul Prolanis** — pastikan logicnya memang general (tidak hardcode untuk tujuan Prolanis saja). Kalau masih tercampur, pisahkan dulu: bagian "baca & parsing Excel" vs bagian "simpan ke tabel tujuan Prolanis".
18. **Buat halaman upload khusus PRB** — upload Excel dengan format yang sama (kolom-kolom Register Poliklinik).
19. **Tambahkan filter saat parsing** — validasi/filter baris berdasarkan `Poli/Klinik` (LANSIA/UMUM) dan `Keluhan` (mengandung "ambil obat prb", tidak case-sensitive) sebagai pengaman tambahan, meskipun file yang diupload harusnya sudah terfilter dari sumbernya.
20. **Hubungkan hasil parsing ke tujuan PRB** — bukan ke `pemeriksaan` Prolanis, tapi ke `pasien_prb` (buat/pakai yang sudah ada via NIK) dan `riwayat_pengambilan_obat_prb` (buat baris baru dengan sumber = import; tanggal_jatuh_tempo dikosongkan/menunggu diisi petugas setelah import, karena data ini belum tentu ada di Excel sumber).
21. **Test:** import file `prb.xlsx` contoh, pastikan pasien baru/sudah ada tertangani benar, dan baris riwayat PRB baru terbentuk tanpa duplikat kalau diimport dua kali.
22. **Ringkasan hasil import** — tampilkan jumlah pasien baru, pasien sudah ada, riwayat baru dibuat.

---

## FASE 5 — Dashboard Pengingat & WhatsApp

23. **Query "pasien perlu dihubungi"** — riwayat dengan `tanggal_jatuh_tempo` ≤ hari ini DAN `tanggal_diambil` masih kosong (status "Belum Ambil (Terlambat)" dari accessor langkah 7). Urutkan dari yang paling lama terlambat (selisih hari terbesar).
24. **Halaman dashboard pengingat** — tabel menampilkan nama pasien, NIK, tanggal jatuh tempo, berapa hari terlambat, dan tombol aksi.
25. **Fungsi normalisasi nomor HP** — buat helper/function yang mengubah format `08xxxxxxxxxx` menjadi `62xxxxxxxxxx` (hapus spasi/strip, hapus angka 0 di depan, tambah `62`). Terapkan saat data pasien disimpan (baik manual maupun import), bukan saat tombol WA diklik.
26. **Test:** masukkan beberapa variasi format nomor HP (dengan spasi, strip, awalan 0, awalan +62), pastikan semuanya ternormalisasi konsisten ke format `62xxx`.
27. **Tombol "Hubungi via WhatsApp"** — generate link `https://wa.me/[nomor]?text=[pesan]` dengan pesan berisi nama pasien dan tanggal jatuh tempo, ter-encode dengan benar untuk URL.
28. **Tandai "sudah dihubungi"** — saat tombol WA diklik, update `terakhir_diingatkan` ke waktu sekarang (lewat request kecil terpisah/background, tidak mengganggu pembukaan tab WhatsApp). Tampilkan penanda visual (misal label "Dihubungi hari ini") di baris yang sudah diklik.
29. **Test:** klik tombol WA pada satu baris, pastikan WhatsApp terbuka dengan nomor dan pesan benar, dan baris tersebut langsung menampilkan penanda "sudah dihubungi" tanpa perlu reload manual (gunakan Alpine.js untuk update tampilan).

---

## FASE 6 — Penyempurnaan

30. **Review konsistensi desain** — pastikan dashboard pengingat dan halaman PRB lainnya selaras dengan Preline UI dan font yang sudah dipakai di modul Prolanis.
31. **Uji alur end-to-end** — import data PRB → tandai beberapa riwayat sebagai jatuh tempo hari ini (lewat data dummy) → buka dashboard → hubungi via WA → catat pengambilan obat baru (dengan kategori obat PRB & Non-PRB) dengan tanggal jatuh tempo berikutnya → pastikan pasien hilang dari dashboard pengingat begitu `tanggal_diambil` terisi.
32. **Minta feedback ke mentor/kepala puskesmas**, khususnya soal alur pencatatan manual, kategori obat, dan tampilan dashboard pengingat.

---

## Catatan Penting

- **Jangan otomatiskan perhitungan `tanggal_jatuh_tempo`** — ini keputusan eksplisit, petugas yang menentukan sendiri meskipun sistem boleh menampilkan saran 30 hari sebagai teks bantuan.
- **Status pengambilan obat & selisih hari dihitung lewat accessor (langkah 7), bukan kolom database** — jangan membuat migration tambahan untuk ini.
- Format data obat PRB/non-PRB dari apoteker **masih berubah kemungkinan** — field `obat_prb` sengaja dirancang sederhana (nama obat + kategori) supaya mudah disesuaikan begitu format resmi dari rapat apoteker sudah didapat, tanpa membongkar struktur tabel lain.
- Kerjakan satu nomor per prompt/sesi ke AI coding tool, uji dulu sebelum lanjut — sama seperti pola di rencana iterasi Prolanis.