# PRD — Sistem Informasi Pasien Prolanis
**Puskesmas Kalijudan**

Versi: 1.0
Status: Draft untuk mentor/kepala puskesmas

---

## 1. Latar Belakang

Puskesmas Kalijudan menyelenggarakan layanan **Program Pengelolaan Penyakit Kronis (Prolanis)** untuk pasien BPJS dengan kondisi kronis (Diabetes Melitus, Hipertensi, dll). Saat ini, data hasil pemeriksaan dan pemberian obat per sesi Prolanis tersebar di file Excel hasil export dari sistem poliklinik dan apotek, tanpa ada tempat terpusat untuk melihat riwayat pasien dari waktu ke waktu.

Sistem ini dibangun untuk mengonsolidasikan data tersebut, menyediakan riwayat pemeriksaan per pasien secara berkelanjutan, dan memberi pasien akses mandiri untuk memantau tren kondisi kesehatannya sendiri.

## 2. Tujuan Produk

1. Memusatkan data kunjungan Prolanis (pemeriksaan + obat) dari beberapa sumber Excel ke satu sistem, tanpa duplikasi data pasien.
2. Menjaga riwayat kunjungan tetap utuh dan bertambah dari waktu ke waktu (bukan menimpa data lama).
3. Memberi admin/petugas kemudahan mengelola jadwal layanan dan mengimpor data hasil kunjungan.
4. Memberi pasien akses mandiri untuk melihat riwayat kesehatannya dalam bentuk grafik yang mudah dipahami.
5. Tampilan aplikasi harus **user-friendly** dan tidak terkesan generik/"AI slop" — permintaan eksplisit dari Kepala Puskesmas.

## 3. Target Pengguna

| Peran | Deskripsi |
|---|---|
| **Admin/Petugas** | Mengelola jadwal layanan Prolanis, mengimpor data Excel dari poliklinik & apotek, mengelola data referensi |
| **Nakes (dokter/perawat)** | Tercatat sebagai pihak yang menangani pemeriksaan (data historis, bukan pengguna aktif sistem di versi awal) |
| **Pasien Prolanis** | Login mandiri untuk melihat riwayat & grafik kondisi kesehatannya sendiri |

## 4. Ruang Lingkup

### Termasuk (in scope)
- Import data pemeriksaan dari Excel register poliklinik
- Import data pemberian obat dari Excel apotek
- Pencegahan duplikasi pasien & kunjungan saat import berulang
- Manajemen jadwal layanan Prolanis (tanggal dibuka)
- Portal pasien: login mandiri + grafik riwayat berat badan & tensi
- Riwayat kunjungan & obat per pasien (dilihat admin maupun pasien)

### Tidak termasuk (out of scope, untuk versi awal)
- Integrasi API langsung ke SIMPUS atau sistem puskesmas lain (sistem ini **standalone**, hanya berbasis import Excel)
- Pendaftaran akun pasien secara mandiri (akun pasien mengikuti data yang sudah diimport, bukan self-registration)
- Rate limiting login & fitur ganti ke password (didiskusikan, sengaja tidak dimasukkan ke versi ini atas keputusan pemilik produk)
- Modul surat-menyurat (rencana awal proyek ini sempat berupa aplikasi generator surat, namun diubah total menjadi sistem Prolanis)

## 5. Alur Data & Sumber Data

Sistem ini **tidak terhubung API** ke sistem poliklinik manapun. Semua data masuk lewat **import file Excel**, dengan dua sumber berbeda peran:

| Sumber | Diambil untuk | Contoh kolom relevan |
|---|---|---|
| **Excel Register Poliklinik** | Identitas pasien (sumber utama/tunggal) + data pemeriksaan lengkap | NIK, Nama, Alamat, Tanggal Lahir, JK, Pendidikan, Pekerjaan, No HP, No Kartu BPJS, Tanggal Periksa, Keluhan, Kode ICD, BB, TB, Tensi Atas/Bawah, Respiratory Rate, Heart Rate, Temperature |
| **Excel Apotek** | Nama obat yang diberikan saja | NIK (untuk pencocokan), Nama Obat |

Kolom-kolom lain di Excel apotek yang tumpang tindih dengan data poliklinik (nama, tanggal, dll) **diabaikan** saat import — sistem hanya mengambil NIK (kunci pencocokan) dan nama obat dari sumber ini.

### Kunci Pencocokan
- **Identitas pasien**: NIK — satu NIK selalu merujuk ke satu record pasien. Pasien juga memiliki **No. Rekam Medis (`no_rm`)**, diambil dari kolom "No Index" (sheet poliklinik) / "RM" (sheet apotek) — kedua kolom ini terbukti berisi nilai yang sama (perlu di-trim whitespace saat import), dan konsisten untuk pasien yang sama di kunjungan berbeda. Disimpan sebagai identitas sekunder, bukan pengganti NIK.
- **Kunjungan**: kombinasi **NIK + tanggal periksa** (tervalidasi dari data sampel: satu pasien hanya melakukan satu kunjungan per hari, dan 100% baris apotek berhasil dicocokkan ke kunjungan poliklinik lewat kombinasi ini).
- Data obat dari Excel apotek dicocokkan ke kunjungan yang sudah ada berdasarkan NIK + tanggal yang sama. **Satu kunjungan sah memiliki lebih dari satu baris pemberian obat** (bukan dianggap duplikat) — sesuai desain `pemberian_obat` yang sudah one-to-many terhadap `pemeriksaan`.

### Detail Teknis Hasil Analisis Data Sampel
- Kolom **"Obat"** di sheet apotek sering berisi banyak nama obat dalam satu sel, dipisah karakter **`;`** — harus di-split saat import menjadi beberapa baris `pemberian_obat` terpisah, bukan disimpan sebagai satu string panjang.
- Kolom **kode ICD** (poliklinik) kadang berisi lebih dari satu kode, juga dipisah **`;`** (mis. `E11.7;I10` untuk pasien dengan DM + Hipertensi) — disimpan **apa adanya sebagai teks utuh** di kolom `kode_icd` (tidak dipecah menjadi baris/tabel terpisah), sesuai keputusan tanpa tabel ICD master.
- Kolom **Tanggal** dan **Tanggal Lahir** di kedua sheet berformat teks `DD-MM-YYYY`, bukan tipe tanggal native Excel — harus di-parse eksplisit saat import.
- Kolom **"Nama ICDX"** di sheet apotek bisa kosong (nullable) — sejumlah baris pemberian obat tidak selalu mencantumkan diagnosa terkait.
- **"Nakes"** (dokter pemeriksa) dan **"idcreate"** (petugas yang menginput data, biasanya perawat) adalah dua peran berbeda di sheet poliklinik — relevan untuk pengisian tabel `pegawai`.

### Aturan Dedup & Riwayat
- Import bisa dilakukan **kapan saja**, mencakup beberapa tanggal pembukaan layanan sekaligus (mis. layanan dibuka tanggal 5 dan 15, lalu diimport tanggal 19 — kedua kunjungan tetap tercatat terpisah).
- Jika NIK sudah pernah ada di database, **tidak dibuat pasien baru** — hanya ditambahkan kunjungan baru.
- Jika kombinasi NIK + tanggal periksa sudah pernah diimport sebelumnya, baris tersebut dianggap duplikat dan **dilewati** (tidak menimpa/menduplikasi data).
- Riwayat kunjungan lama **tidak pernah dihapus atau tertimpa** oleh import berikutnya.

## 6. Fitur — Sisi Admin/Petugas

### 6.1 Manajemen Jadwal Layanan
- Admin dapat menambahkan tanggal-tanggal layanan Prolanis dibuka.
- Riwayat jadwal layanan sebelumnya tetap tersimpan.

### 6.2 Import Data
- Upload file Excel register poliklinik → sistem memproses baris per baris (buat/pakai pasien existing via NIK, buat kunjungan baru via NIK+tanggal, lewati duplikat).
- Upload file Excel apotek → sistem mencocokkan ke kunjungan yang sudah ada via NIK+tanggal, menambahkan data obat.
- Sistem menampilkan **ringkasan hasil import** (berapa baris berhasil, berapa dilewati karena duplikat, berapa gagal/perlu dicek manual).

### 6.3 Data Pasien & Riwayat
- Daftar pasien Prolanis dengan pencarian (nama/NIK).
- Detail per pasien: profil + seluruh riwayat kunjungan (pemeriksaan & obat) secara kronologis.

## 7. Fitur — Sisi Pasien

### 7.1 Login
- Menggunakan kombinasi **nomor identitas (NIK) + tanggal lahir**.
- Dapat diakses publik lewat internet.
- Pasien hanya bisa login jika datanya sudah pernah masuk lewat proses import (tidak ada pendaftaran mandiri).

### 7.2 Dashboard & Grafik Riwayat
- Menampilkan grafik tren dari waktu ke waktu untuk:
  - **Berat Badan**
  - **Tensi** (sistol & diastol, digambarkan sebagai dua garis dalam satu grafik dengan area di antaranya)
- **Tidak** menampilkan heart rate, temperature, dan tinggi badan pada grafik pasien.
- Riwayat kunjungan (tanggal, keluhan, diagnosa/ICD, obat yang diberikan) ditampilkan dalam bentuk yang mudah dibaca oleh pasien awam.

## 8. Persyaratan Desain (Non-Fungsional)

- **User-friendly dan tidak generik ("AI slop")** — permintaan eksplisit Kepala Puskesmas.
- Untuk sisi pasien khususnya (mayoritas peserta Prolanis berusia lanjut): navigasi minim langkah, label deskriptif (bukan sekadar angka), grafik besar dan sederhana.
- Komponen UI diambil dari **Preline UI**, tidak membuat komponen custom dari nol.
- Font UI: **Inter** (opsional **Plus Jakarta Sans** untuk heading).
- Satu warna aksen konsisten, spacing teratur, hindari gradient/shadow berlebihan.

## 9. Struktur Data (Ringkasan Entitas)

Penamaan tabel menggunakan Bahasa Indonesia.

| Tabel | Fungsi |
|---|---|
| `pasien` | Identitas pasien (NIK sebagai kunci unik, `no_rm` sebagai identitas sekunder), sumber dari register poliklinik |
| `pemeriksaan` | Satu baris = satu kunjungan; kode ICD & diagnosa disimpan langsung di sini (tanpa tabel referensi ICD terpisah) |
| `pemberian_obat` | Obat yang diberikan per kunjungan, dicocokkan via NIK + tanggal |
| `jadwal_layanan` | Tanggal-tanggal layanan Prolanis dibuka, diinput admin |
| `pegawai` | Data nakes (dokter/perawat) yang menangani pemeriksaan |
| `pengguna` | Akun login admin/petugas sistem |

*Catatan: struktur detail (kolom, tipe data, relasi) akan dirinci pada tahap migration database, mengikuti keputusan di atas.*

## 10. Pertimbangan Keamanan

- Login pasien (NIK + tanggal lahir) adalah kredensial dengan tingkat keamanan rendah karena kedua data tersebut tidak sepenuhnya rahasia. Ini adalah keputusan yang telah disadari dan diterima oleh pemilik produk untuk versi ini.
- Data yang ditangani adalah data kesehatan (kondisi Prolanis, hasil pemeriksaan, riwayat obat) sehingga tetap perlu penanganan hati-hati di sisi admin: pembatasan akses berbasis role, dan data mentah (file Excel asli) tidak diedit/dimodifikasi, hanya diproses lewat alur import.

## 11. Pertanyaan Terbuka / Perlu Divalidasi Saat Implementasi

- Perlu contoh nyata/sampel file Excel apotek untuk memastikan format kolomnya sesuai asumsi (NIK + nama obat).
- Volume data pasien Prolanis aktual (untuk pertimbangan performa pencarian & indexing database).
- Lingkungan hosting akhir (server lokal puskesmas vs domain publik) — relevan untuk keputusan infrastruktur di luar cakupan PRD ini.

## 12. Rencana Bertahap

**Fase 1 — Fondasi**
- Skema database inti (pasien, pemeriksaan, pemberian_obat, jadwal_layanan, pengguna)
- Autentikasi admin/petugas
- CRUD jadwal layanan

**Fase 2 — Import**
- Import Excel register poliklinik (dengan logic dedup pasien & kunjungan)
- Import Excel apotek (pencocokan via NIK + tanggal)
- Ringkasan hasil import

**Fase 3 — Portal Pasien**
- Login pasien (NIK + tanggal lahir)
- Dashboard riwayat + grafik BB & tensi

**Fase 4 — Penyempurnaan**
- Pencarian & filter data pasien di sisi admin
- Penyesuaian tampilan sesuai masukan Kepala Puskesmas (user-friendly, desain akhir)