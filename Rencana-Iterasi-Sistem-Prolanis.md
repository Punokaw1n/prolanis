# Rencana Iterasi — Sistem Informasi Pasien Prolanis
**Panduan langkah demi langkah untuk vibecoding**

Setiap langkah dirancang cukup kecil untuk dikerjakan/di-generate satu per satu, lalu ditest sebelum lanjut ke langkah berikutnya. Jangan minta AI coding tool mengerjakan lebih dari satu langkah sekaligus — ini untuk menghindari halusinasi/kesalahan yang menumpuk.

Stack: **Laravel 13, Blade + Alpine.js, Preline UI, MySQL (penamaan tabel Bahasa Indonesia), font Inter.**

---

## FASE 0 — Setup Proyek

1. **Init project Laravel 13** — `laravel new` atau via composer, pastikan versi 13.
2. **Setup Tailwind + Preline UI** — install Tailwind, tambahkan Preline sesuai dokumentasi resminya (npm package + import CSS/JS).
3. **Setup font Inter** — via Google Fonts, terapkan sebagai font default di `tailwind.config.js` dan layout utama.
4. **Buat layout dasar** — `layouts/app.blade.php` untuk admin, `layouts/guest.blade.php` untuk portal pasien (dua layout terpisah karena beda audiens).
5. **Setup database & `.env`** — buat database MySQL kosong, pastikan koneksi jalan (`php artisan migrate` tanpa migration dulu untuk tes koneksi).
6. **Test:** jalankan `php artisan serve`, pastikan halaman welcome/default tampil tanpa error, Tailwind & Preline ter-load (cek 1 komponen Preline sederhana seperti button).

---

## FASE 1 — Fondasi Database & Auth Admin

7. **Migration `pengguna`** — kolom: id, nama, email, password, peran (admin/petugas), gelar, no_sip, timestamps.
8. **Migration `pegawai`** — kolom: id, nik, nama, alamat, tanggal_lahir, jenis_kelamin, pendidikan, jabatan, no_hp, no_bpjs, timestamps.
9. **Migration `pasien`** — kolom: id, nik (unique), no_rm (dari kolom "No Index"/"RM" di data asli — trim whitespace saat import, boleh sama untuk kunjungan berbeda dari pasien yang sama), nama, alamat, tanggal_lahir, jenis_kelamin, pendidikan, pekerjaan, no_hp, no_bpjs, timestamps.
10. **Migration `jadwal_layanan`** — kolom: id, tanggal, keterangan (opsional), dibuat_oleh (FK ke pengguna), timestamps.
11. **Migration `pemeriksaan`** — kolom: id, pasien_id (FK), jadwal_layanan_id (FK, nullable), tanggal_periksa, keluhan, kode_icd (tipe **string panjang/text**, karena bisa berisi lebih dari satu kode dipisah `;`, mis. `E11.7;I10` — disimpan apa adanya, tidak dipecah), nama_diagnosa, berat_badan, tinggi_badan, tensi_sistol, tensi_diastol, respiratory_rate, heart_rate, temperature, nakes_id (FK ke pegawai, nullable), timestamps. **Tambahkan unique constraint pada kombinasi (pasien_id, tanggal_periksa)** untuk mencegah duplikat kunjungan.
12. **Migration `pemberian_obat`** — kolom: id, pemeriksaan_id (FK), nama_obat, timestamps.
13. **Test:** jalankan `php artisan migrate`, pastikan semua tabel dan foreign key terbentuk tanpa error. Cek lewat `php artisan migrate:status` atau langsung ke MySQL.
14. **Buat Model + relasi Eloquent** — `Pasien` (hasMany Pemeriksaan), `Pemeriksaan` (belongsTo Pasien, hasMany PemberianObat, belongsTo JadwalLayanan), `PemberianObat` (belongsTo Pemeriksaan), `JadwalLayanan` (hasMany Pemeriksaan), `Pengguna` (implements Authenticatable), `Pegawai`.
15. **Test:** buat data dummy lewat Tinker (`php artisan tinker`), cek relasi berjalan (mis. `$pasien->pemeriksaans` mengembalikan data).
16. **Setup auth admin/petugas** — pakai Laravel built-in auth (bukan Livewire), login/logout untuk tabel `pengguna`. Pastikan `config/auth.php` mengarah ke model `Pengguna`.
17. **Middleware role** — buat middleware sederhana untuk membedakan akses admin vs petugas (kalau memang perlu dibedakan level aksesnya).
18. **Test:** login dengan user dummy, pastikan redirect ke dashboard admin berhasil, logout berfungsi, halaman admin tidak bisa diakses tanpa login.

---

## FASE 2 — Manajemen Jadwal Layanan (CRUD sederhana)

19. **Controller `JadwalLayananController`** — index, create, store, edit, update, destroy.
20. **View halaman daftar jadwal** — tabel Preline UI menampilkan tanggal-tanggal yang sudah dibuat.
21. **View form tambah/edit jadwal** — form sederhana (tanggal + keterangan opsional).
22. **Test:** admin bisa menambah, mengedit, menghapus jadwal layanan lewat UI, data tersimpan benar di database.

---

## FASE 3 — Import Data Poliklinik

23. **Buat halaman upload file** — form upload Excel, validasi ekstensi (.xlsx/.xls) dan ukuran file.
24. **Install package baca Excel** — `maatwebsite/excel` (paling umum dipakai di Laravel untuk import/export Excel).
25. **Buat class Import untuk poliklinik** — baca file baris per baris, MASIH TANPA logic simpan ke database dulu (hanya baca & tampilkan preview data di halaman, misal tabel sementara). **Catatan format data asli:** kolom Tanggal & Tanggal Lahir berformat teks `DD-MM-YYYY` (perlu parsing eksplisit, bukan native Excel date), dan kolom No Index kadang ada whitespace ekstra (perlu `trim()`).
26. **Test:** upload file Excel contoh, pastikan data terbaca benar dan tampil sebagai preview (nama kolom sesuai, tidak ada data korup).
27. **Tambahkan logic simpan: cek/buat pasien** — untuk tiap baris, cek NIK di tabel `pasien`; kalau belum ada, buat baru; kalau sudah ada, pakai yang ada (jangan buat duplikat).
28. **Test:** import file yang sama dua kali, pastikan jumlah baris di tabel `pasien` **tidak bertambah** pada import kedua (tidak duplikat).
29. **Tambahkan logic simpan: cek/buat kunjungan (`pemeriksaan`)** — cek kombinasi pasien_id + tanggal_periksa; kalau sudah ada, lewati (skip) baris tersebut; kalau belum ada, buat baru dengan seluruh data pemeriksaan (BB, TB, tensi, ICD, dll).
30. **Test:** import file yang sama dua kali, pastikan jumlah baris di tabel `pemeriksaan` **tidak bertambah** pada import kedua.
31. **Tambahkan ringkasan hasil import** — setelah proses, tampilkan: berapa baris diproses, berapa pasien baru dibuat, berapa kunjungan baru dibuat, berapa dilewati karena duplikat.
32. **Test:** import file berisi campuran data baru dan data yang sudah ada sebelumnya, pastikan angka ringkasan sesuai ekspektasi.

---

## FASE 4 — Import Data Apotek

33. **Buat halaman upload terpisah untuk file apotek.**
34. **Buat class Import untuk apotek** — baca kolom NIK dan nama obat saja, abaikan kolom lain.
35. **Tambahkan logic pencocokan** — untuk tiap baris, cari `pasien` berdasarkan NIK, lalu cari `pemeriksaan` dengan pasien_id tersebut dan tanggal_periksa yang sesuai (perlu kolom tanggal juga dari sheet apotek untuk pencocokan). Kalau kunjungan ditemukan, **split isi kolom "Obat" berdasarkan karakter `;`** (satu sel bisa berisi beberapa nama obat) dan buat satu record `pemberian_obat` per obat. Kalau tidak ditemukan (NIK/tanggal tidak cocok dengan kunjungan manapun), catat sebagai baris gagal di ringkasan (jangan disimpan sembarangan). **Satu kunjungan boleh punya beberapa baris `pemberian_obat` dari beberapa baris Excel apotek sekaligus — ini valid, bukan duplikat.**
36. **Test:** import file obat setelah data poliklinik untuk tanggal yang sama sudah ada, pastikan obat tersimpan terhubung ke kunjungan yang benar. Coba juga import file obat untuk NIK yang belum ada datanya di poliklinik, pastikan tertangkap sebagai baris gagal, bukan error/crash.

---

## FASE 5 — Data Pasien & Riwayat (Sisi Admin)

37. **Halaman daftar pasien** — tabel dengan pencarian by nama/NIK, pagination.
38. **Halaman detail pasien** — profil pasien + daftar riwayat kunjungan (tanggal, keluhan, diagnosa, BB/TB/tensi, obat yang diberikan) diurutkan dari terbaru.
39. **Test:** cari pasien tertentu, buka detailnya, pastikan seluruh riwayat kunjungan dan obatnya tampil dengan benar dan berurutan.

---

## FASE 6 — Portal Pasien

40. **Buat guard/auth terpisah untuk pasien** (bukan tabel `pengguna`, tapi tabel `pasien` sendiri sebagai basis login).
41. **Halaman login pasien** — form input NIK + tanggal lahir, cocokkan ke tabel `pasien`.
42. **Test:** login dengan NIK+tanggal lahir yang ada di database berhasil; yang tidak ada di database ditolak dengan pesan error yang jelas.
43. **Halaman dashboard pasien** — tampilkan ringkasan singkat (kunjungan terakhir, kondisi terbaru).
44. **Ambil data untuk grafik** — buat endpoint/controller yang mengambil riwayat berat badan dan tensi (sistol/diastol) milik pasien yang login, diurutkan berdasarkan tanggal.
45. **Buat komponen grafik** — gunakan library chart ringan (mis. Chart.js) untuk menampilkan tren berat badan (satu garis) dan tensi (dua garis: sistol & diastol, dengan area di antaranya).
46. **Test:** login sebagai pasien dengan minimal 2-3 riwayat kunjungan berbeda tanggal, pastikan grafik menampilkan tren yang sesuai data asli (bandingkan manual dengan data di database).
47. **Halaman riwayat detail pasien** — daftar kunjungan lengkap (tanggal, keluhan, diagnosa, obat) dalam bahasa yang mudah dipahami pasien awam.
48. **Test:** pastikan pasien hanya bisa melihat datanya sendiri (tidak bisa akses data pasien lain lewat manipulasi URL/ID).

---

## FASE 7 — Penyempurnaan Desain & Finalisasi

49. **Review seluruh halaman terhadap requirement "user-friendly, bukan AI slop"** — cek konsistensi warna aksen, spacing, penggunaan komponen Preline yang seragam.
50. **Responsive check** — pastikan portal pasien nyaman diakses dari HP (mayoritas pasien kemungkinan akses via ponsel).
51. **Uji alur end-to-end** — dari admin buat jadwal → import poliklinik → import apotek → cek data di sisi admin → login sebagai pasien → cek grafik & riwayat, semua dalam satu skenario penuh.
52. **Minta feedback ke mentor/kepala puskesmas** sebelum dianggap selesai — khususnya soal tampilan (requirement langsung dari kepala puskesmas).

---

## Catatan untuk AI coding tool

- Kerjakan **satu nomor per prompt/sesi**, jangan digabung banyak sekaligus.
- Setelah tiap langkah "Test", jalankan pengujian itu sebelum lanjut — jangan lompat ke langkah berikutnya kalau test sebelumnya belum lolos.
- Kalau ada perubahan requirement di tengah jalan, cukup revisi langkah yang relevan tanpa mengulang dari Fase 0.