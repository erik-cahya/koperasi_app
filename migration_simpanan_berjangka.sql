-- =============================================
-- Migration: Tambah Akun Simpanan Berjangka
-- untuk mapping Deposito ke Neraca (Pasiva)
-- =============================================

-- Tambah akun F6 - Simpanan Berjangka di tabel jns_akun
INSERT INTO `jns_akun` (`kd_aktiva`, `jns_trans`, `akun`, `laba_rugi`, `pemasukan`, `pengeluaran`, `aktif`) 
VALUES ('F6', 'Simpanan Berjangka', 'Pasiva', '', NULL, NULL, 'Y');

-- Verifikasi: pastikan akun berhasil ditambahkan
SELECT * FROM `jns_akun` WHERE `kd_aktiva` = 'F6';
