-- ============================================================
-- LPSE APELBAJA — DATABASE MIGRATION v4
-- Modifikasi: Penambahan fitur Pengajuan Ganti Jabatan
-- Dibuat: 2026-06-23
-- ============================================================

USE db_apelbaja;

CREATE TABLE IF NOT EXISTS role_change_requests (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED    NOT NULL,
    role_tujuan         ENUM('PPK','PP') NOT NULL,
    -- Data SK Baru
    sk_nomor            VARCHAR(150)    NOT NULL,
    sk_tanggal          DATE            NOT NULL,
    sk_berlaku_dari     DATE            NOT NULL,
    sk_berlaku_sampai   DATE            NULL,
    file_sk             VARCHAR(500)    NOT NULL,
    
    alasan              TEXT            NOT NULL,
    status              ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    catatan_admin       TEXT            NULL,
    disetujui_oleh      INT UNSIGNED    NULL,
    disetujui_at        DATETIME        NULL,
    
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    INDEX idx_rc_user_id (user_id),
    INDEX idx_rc_status  (status),
    CONSTRAINT fk_rc_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rc_admin_id FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
