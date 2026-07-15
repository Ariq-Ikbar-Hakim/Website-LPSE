-- ============================================================
-- LPSE APELBAJA — DATABASE MIGRATION v5
-- Modifikasi Tabel: assignment_transfer (Bulk Transfer)
-- Dibuat: 2026-06-23
-- ============================================================
-- Menghapus kolom paket_id karena fitur transfer kini bersifat
-- Bulk (semua paket bertukar tempat), bukan per-paket.

USE db_apelbaja;

-- Hapus Foreign Key terlebih dahulu
ALTER TABLE assignment_transfer DROP FOREIGN KEY fk_at_paket_id;

-- Hapus index
ALTER TABLE assignment_transfer DROP INDEX idx_at_paket_id;

-- Hapus kolom paket_id
ALTER TABLE assignment_transfer DROP COLUMN paket_id;
