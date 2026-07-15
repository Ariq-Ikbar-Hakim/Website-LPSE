-- ============================================================
-- LPSE APELBAJA — DATABASE MIGRATION v3
-- Modifikasi Tabel: assignment_transfer
-- Dibuat: 2026-06-23
-- ============================================================
-- Menambahkan kolom status, catatan_admin, dan disetujui_at

USE db_apelbaja;

ALTER TABLE assignment_transfer
    ADD COLUMN status ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu' AFTER tipe_transfer,
    ADD COLUMN catatan_admin TEXT NULL AFTER disetujui_oleh,
    ADD COLUMN disetujui_at DATETIME NULL AFTER catatan_admin,
    ADD INDEX idx_at_status (status);
