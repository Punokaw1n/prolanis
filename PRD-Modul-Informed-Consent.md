# PRD — Modul Informed Consent
**Sistem Informasi Puskesmas Kalijudan (modul tambahan di website yang sama dengan Prolanis)**

Versi: 1.0
Status: Draft untuk mentor/kepala puskesmas

---

## 1. Latar Belakang

Website ini awalnya dibangun untuk pengelolaan pasien Prolanis, dan kini akan dipakai untuk kebutuhan puskesmas lain juga. Permintaan tambahan dari mentor: fitur **Informed Consent** (surat persetujuan) untuk pasien, dengan tanda tangan online yang tersimpan di database puskesmas.

Isi surat persetujuan tidak boleh kaku di dalam kode. Petugas harus bisa mengubah poin-poinnya sendiri lewat sistem, sementara tata letak surat tetap baku.

## 2. Tujuan

1. Menyediakan template informed consent yang isinya (teks dan poin) dapat diubah petugas lewat antarmuka, tanpa mengubah kode.
2. Memungkinkan petugas menerbitkan consent untuk pasien tertentu, dengan data pasien terisi otomatis dari tabel `pasien`.
3. Memungkinkan pasien membaca dan menandatangani consent secara online lewat QR code.
4. Menyimpan consent yang sudah ditandatangani secara utuh dan tidak berubah di database puskesmas.
5. Consent hanya dapat dilihat oleh pihak internal puskesmas (sesuai permintaan mentor: pasien tidak memegang salinan).

## 3. Pengguna & Peran

| Peran | Kebutuhan pada modul ini |
|---|---|
| **Admin** | Mengelola template (mengubah judul, teks, poin) |
| **Petugas** | Menerbitkan consent untuk pasien, menampilkan QR, melihat arsip, mencetak PDF |
| **Pasien** | Membaca dan menandatangani consent lewat HP. Tidak memerlukan akun dan tidak dapat melihat dokumen setelah selesai |

*Catatan: apakah pengeditan template dibatasi hanya untuk admin atau boleh juga petugas masih menjadi pertanyaan terbuka (bagian 11).*

## 4. Ruang Lingkup

### Termasuk
- Satu template global yang dapat diedit (struktur database tetap mendukung banyak template untuk pengembangan berikutnya)
- Placeholder data pasien yang terisi otomatis
- Penerbitan consent dan pembuatan QR code
- Halaman tanda tangan pasien (mobile-first)
- Tanda tangan berupa coretan di layar (canvas)
- Arsip internal dan cetak/ekspor PDF untuk petugas

### Tidak termasuk (versi awal)
- Beberapa jenis consent per tindakan/layanan (belum ada kebutuhan yang jelas dari puskesmas)
- Tanda tangan digital bersertifikat
- Salinan dokumen untuk pasien dalam bentuk apa pun (unduh, email, cetak oleh pasien)
- Akun/login khusus pasien untuk modul ini

## 5. Alur Penggunaan

1. **Admin menyiapkan template**: judul, teks pembuka, daftar poin (tambah, hapus, urutkan ulang), dan teks penutup.
2. **Petugas menerbitkan consent**: mencari pasien (nama atau NIK), sistem menampilkan pratinjau surat dengan data pasien terisi, petugas menerbitkan.
3. **Sistem membuat dokumen** berstatus "menunggu tanda tangan" beserta QR code.
4. **Pasien memindai QR** dengan HP, membaca isi surat, lalu menandatangani di layar.
5. **Sistem menyimpan** tanda tangan dan metadata, mengubah status menjadi "sudah ditandatangani", dan menampilkan halaman "terima kasih" kepada pasien.
6. **Petugas/admin** dapat membuka arsip, melihat dokumen lengkap dengan tanda tangan, dan mencetak PDF.

## 6. Fitur

### 6.1 Template Persetujuan (Admin)
- Halaman "Template Persetujuan" dengan form: judul, teks pembuka, poin-poin, teks penutup.
- Poin dapat ditambah, dihapus, dan diurutkan ulang.
- Teks mendukung **placeholder** yang diganti otomatis saat penerbitan:
  `{nama_pasien}`, `{nik}`, `{no_rm}`, `{tanggal_lahir}`, `{alamat}`, `{tanggal}`
- Tata letak (kop, area tanda tangan, gaya cetak) bersifat tetap di kode. Hanya isi teks yang dapat diubah, sehingga tampilan surat tidak rusak oleh pengeditan.
- Perubahan template hanya berlaku untuk consent yang diterbitkan **setelah** perubahan.

### 6.2 Penerbitan Consent (Petugas)
- Pencarian pasien dari tabel `pasien` (nama atau NIK).
- Pratinjau surat dengan placeholder yang sudah terisi.
- Setelah diterbitkan: tampil QR code dan status dokumen. Petugas dapat menampilkan QR di layar untuk dipindai pasien.
- Consent yang belum ditandatangani dapat dibatalkan atau diterbitkan ulang.

### 6.3 Halaman Tanda Tangan Pasien
- Diakses lewat link dari QR code, tanpa login.
- Menampilkan isi surat secara lengkap dan mudah dibaca (teks besar, cocok untuk pasien lanjut usia).
- Area tanda tangan berupa canvas, dengan tombol "Ulangi" dan "Kirim".
- Setelah dikirim, halaman berubah menjadi konfirmasi singkat. Isi dokumen tidak dapat dibuka lagi oleh pasien.
- Link yang sudah dipakai atau sudah kedaluwarsa menampilkan pesan yang jelas.

### 6.4 Arsip Internal (Petugas/Admin)
- Daftar consent dengan pencarian (nama/NIK), filter status, dan filter tanggal.
- Detail dokumen: isi surat, data pasien, gambar tanda tangan, waktu penandatanganan.
- Cetak/ekspor PDF untuk keperluan arsip puskesmas.

## 7. Aturan Bisnis

1. **Salinan isi saat penerbitan.** Saat consent diterbitkan, isi surat (setelah placeholder diganti) disimpan sebagai salinan pada dokumen itu sendiri. Perubahan template atau perubahan data `pasien` di kemudian hari tidak boleh mengubah dokumen yang sudah diterbitkan.
2. **Dokumen yang sudah ditandatangani bersifat final.** Tidak dapat diedit; hanya dapat dilihat dan dicetak.
3. **Satu link tanda tangan = satu kali pakai**, dan memiliki masa berlaku.
4. **Pasien tidak memperoleh salinan** dalam bentuk apa pun, sesuai permintaan mentor.
5. **Satu template aktif** pada satu waktu untuk versi awal.

## 8. Struktur Data (Ringkasan Entitas)

Penamaan tabel menggunakan Bahasa Indonesia.

| Tabel | Fungsi |
|---|---|
| `template_persetujuan` | Template: judul, teks pembuka, teks penutup, status aktif |
| `poin_template` | Poin-poin milik template (isi, urutan) |
| `persetujuan` | Satu baris = satu consent yang diterbitkan: relasi ke `pasien` dan `template_persetujuan`, **salinan isi** (setelah placeholder terisi), token, status, masa berlaku, petugas penerbit, data tanda tangan |

Data tanda tangan pada `persetujuan`:
- path file gambar tanda tangan (disimpan di storage **privat**, bukan folder publik)
- waktu penandatanganan
- IP dan user agent perangkat penanda tangan
- hash isi dokumen, untuk membuktikan isi tidak berubah setelah ditandatangani

Status `persetujuan`: `menunggu_tanda_tangan`, `ditandatangani`, `kedaluwarsa`, `dibatalkan`.

*Struktur kolom dan tipe data dirinci pada tahap migration.*

## 9. Keamanan & Privasi

- **Token QR acak dan panjang**, bukan ID berurutan, agar tidak dapat ditebak.
- Token bersifat sekali pakai dan punya masa berlaku.
- **Usulan (belum diputuskan):** pasien memasukkan tanggal lahir sebelum isi surat tampil, sebagai verifikasi ringan bahwa yang memegang QR memang pasien yang dimaksud. Ini konsisten dengan login portal pasien.
- File tanda tangan disimpan di storage privat dan hanya dapat diakses lewat halaman yang dilindungi login petugas/admin.
- Dokumen consent berisi data kesehatan dan identitas pasien, sehingga akses dibatasi berdasarkan peran.
- **Catatan hukum:** tanda tangan coretan di layar tergolong tanda tangan elektronik tidak tersertifikasi. Penerimaannya perlu dikonfirmasi ke mentor/kepala puskesmas sesuai kebijakan puskesmas dan dinas kesehatan. Dokumen ini bukan nasihat hukum.

## 10. Persyaratan Desain

- Tampilan konsisten dengan sistem yang sudah berjalan: **Preline UI**, font **Inter**, satu warna aksen.
- Halaman tanda tangan pasien **mobile-first**: dibuka dari HP, teks besar, tombol besar, area tanda tangan lega.
- Bahasa antarmuka sederhana dan langsung, tanpa istilah teknis.
- User-friendly dan tidak terkesan generik, sesuai permintaan kepala puskesmas untuk keseluruhan sistem.

## 11. Pertanyaan Terbuka

- Apakah pengeditan template hanya untuk admin, atau boleh oleh petugas juga?
- Berapa lama masa berlaku link QR (misalnya 24 jam, atau hingga hari yang sama)?
- Apakah verifikasi tanggal lahir sebelum surat tampil ingin dipakai?
- Isi teks template awal: minta contoh surat informed consent yang saat ini dipakai puskesmas.
- Apakah consent perlu dikaitkan ke kunjungan atau jadwal tertentu, atau cukup ke pasien saja?
- Kebijakan penyimpanan dan retensi dokumen (berapa lama disimpan).
- Kebijakan pasien tidak mendapat salinan: apakah perlu dicatat sebagai kebijakan resmi puskesmas.

## 12. Rencana Bertahap

**Fase 1: Template**
- Migration `template_persetujuan` dan `poin_template`
- Halaman kelola template (tambah, hapus, urutkan poin, placeholder)

**Fase 2: Penerbitan**
- Migration `persetujuan`
- Pencarian pasien, pratinjau, penerbitan dengan salinan isi
- Pembuatan token dan QR code

**Fase 3: Tanda Tangan Pasien**
- Halaman publik berbasis token (mobile-first)
- Canvas tanda tangan dan penyimpanan file + metadata + hash
- Penanganan link kedaluwarsa, sudah dipakai, dan dibatalkan

**Fase 4: Arsip**
- Daftar dan detail consent untuk petugas/admin
- Cetak/ekspor PDF

**Fase 5: Penyempurnaan**
- Uji end-to-end (buat template, terbitkan, tanda tangan dari HP, cek arsip dan PDF)
- Masukan mentor dan kepala puskesmas terhadap teks template dan tampilan
