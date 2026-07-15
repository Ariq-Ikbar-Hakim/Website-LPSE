-- ============================================================
-- LPSE APELBAJA — DATABASE MIGRATION (Consolidated)
-- Database: db_apelbaja
-- ============================================================
-- File ini merupakan gabungan dari migration v2, v3, v4, dan v5
-- Bisa langsung di-import untuk membuat skema database lengkap.
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_apelbaja
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_apelbaja;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- 1. TABEL: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nip           VARCHAR(50)     NOT NULL,
    nama          VARCHAR(150)    NOT NULL,
    email         VARCHAR(150)    NOT NULL,
    password      VARCHAR(255)    NOT NULL,
    no_telp       VARCHAR(25)     NULL,
    opd           VARCHAR(150)    NULL COMMENT 'Organisasi Perangkat Daerah',
    sub_unit_opd  VARCHAR(150)    NULL,
    jabatan_aktif ENUM('PPK','PP','admin') NOT NULL DEFAULT 'PPK',
    -- SK Data
    sk_nomor      VARCHAR(150)    NULL,
    sk_mulai      DATE            NULL,
    sk_sampai     DATE            NULL,
    sk_file       VARCHAR(500)    NULL,
    -- Status & Auth
    status_aktif  TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '0=menunggu verifikasi, 1=aktif',
    keterangan    TEXT            NULL,
    -- Reset Password
    reset_token          VARCHAR(64)  NULL,
    reset_token_expires  DATETIME     NULL,
    reset_requested_at   DATETIME     NULL,
    -- Audit
    last_login    DATETIME        NULL,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_nip   (nip),
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_jabatan   (jabatan_aktif),
    INDEX idx_users_status    (status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABEL: user_role_history (riwayat perpindahan jabatan)
-- ============================================================
CREATE TABLE IF NOT EXISTS user_role_history (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED    NOT NULL,
    role_lama     ENUM('PPK','PP','admin') NOT NULL,
    role_baru     ENUM('PPK','PP','admin') NOT NULL,
    alasan        TEXT            NULL,
    diubah_oleh   INT UNSIGNED    NOT NULL COMMENT 'user_id admin yang mengubah',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_urh_user_id (user_id),
    CONSTRAINT fk_urh_user_id    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_urh_diubah_oleh FOREIGN KEY (diubah_oleh) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABEL: paket
-- ============================================================
CREATE TABLE IF NOT EXISTS paket (
    id                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    ppk_id            INT UNSIGNED    NOT NULL COMMENT 'User PPK pembuat',
    pp_id             INT UNSIGNED    NOT NULL COMMENT 'User PP yang ditugaskan',
    -- Data SIRUP
    kode_rup          VARCHAR(100)    NOT NULL,
    nama_paket        TEXT            NOT NULL,
    pagu              DECIMAL(18,2)   NULL DEFAULT 0.00,
    hps               DECIMAL(18,2)   NULL DEFAULT 0.00,
    metode_pengadaan  VARCHAR(150)    NULL COMMENT 'Dari SIRUP',
    -- Data Paket
    tahun_anggaran    YEAR            NOT NULL,
    sumber_dana       VARCHAR(50)     NOT NULL DEFAULT 'APBD',
    jenis_pengadaan   VARCHAR(100)    NOT NULL DEFAULT 'JASA LAINNYA',
    jenis_kontrak     VARCHAR(100)    NULL,
    url_draft_spse    TEXT            NULL,
    keterangan        TEXT            NULL,
    -- Status Workflow
    status            ENUM(
                        'draft',
                        'dikirim',
                        'kaji_ulang',
                        'perlu_revisi',
                        'disetujui',
                        'selesai',
                        'gagal_pemilihan'
                      ) NOT NULL DEFAULT 'draft',
    catatan_koreksi   TEXT            NULL,
    -- Audit
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_paket_ppk_id        (ppk_id),
    INDEX idx_paket_pp_id         (pp_id),
    INDEX idx_paket_status        (status),
    INDEX idx_paket_tahun         (tahun_anggaran),
    INDEX idx_paket_kode_rup      (kode_rup),
    CONSTRAINT fk_paket_ppk_id   FOREIGN KEY (ppk_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_paket_pp_id    FOREIGN KEY (pp_id)  REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TABEL: assignment_transfer (alih tanggung jawab paket)
--    Note: Dimodifikasi berdasarkan v3 dan v5 (Bulk Transfer)
-- ============================================================
CREATE TABLE IF NOT EXISTS assignment_transfer (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    dari_user_id    INT UNSIGNED    NOT NULL,
    ke_user_id      INT UNSIGNED    NOT NULL,
    tipe_transfer   ENUM('ppk','pp') NOT NULL,
    status          ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    alasan          TEXT            NOT NULL,
    disetujui_oleh  INT UNSIGNED    NULL,
    catatan_admin   TEXT            NULL,
    disetujui_at    DATETIME        NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_at_status (status),
    CONSTRAINT fk_at_dari_user_id   FOREIGN KEY (dari_user_id)   REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_at_ke_user_id     FOREIGN KEY (ke_user_id)     REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TABEL: lampiran (dengan versioning)
-- ============================================================
CREATE TABLE IF NOT EXISTS lampiran (
    id                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    paket_id          INT UNSIGNED    NOT NULL,
    tipe_dokumen      VARCHAR(200)    NOT NULL COMMENT 'Jenis dokumen (SK PPK, KAK, dll)',
    versi             SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    nama_asli         VARCHAR(255)    NOT NULL COMMENT 'Nama file original dari user',
    nama_file         VARCHAR(255)    NOT NULL COMMENT 'Nama file tersimpan di server',
    file_path         VARCHAR(600)    NOT NULL,
    ukuran_file       BIGINT UNSIGNED NULL DEFAULT 0 COMMENT 'dalam bytes',
    mime_type         VARCHAR(100)    NULL,
    is_active         TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=versi aktif saat ini',
    status_validasi   ENUM('menunggu','disetujui','revisi') NOT NULL DEFAULT 'menunggu',
    uploaded_by       INT UNSIGNED    NOT NULL COMMENT 'user_id yang upload',
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_lamp_paket_id     (paket_id),
    INDEX idx_lamp_tipe         (tipe_dokumen(100)),
    INDEX idx_lamp_is_active    (is_active),
    INDEX idx_lamp_status       (status_validasi),
    INDEX idx_lamp_paket_tipe   (paket_id, tipe_dokumen(100), is_active),
    CONSTRAINT fk_lamp_paket_id     FOREIGN KEY (paket_id)    REFERENCES paket(id) ON DELETE CASCADE,
    CONSTRAINT fk_lamp_uploaded_by  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TABEL: document_comments (komentar PP & monitoring admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS document_comments (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    paket_id            INT UNSIGNED    NOT NULL,
    lampiran_id         INT UNSIGNED    NULL COMMENT 'NULL = komentar umum paket',
    user_id             INT UNSIGNED    NOT NULL,
    role_saat_komentar  ENUM('PPK','PP','admin') NOT NULL,
    komentar            TEXT            NOT NULL,
    is_monitoring       TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1=komentar monitoring admin',
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_dc_paket_id   (paket_id),
    INDEX idx_dc_lampiran   (lampiran_id),
    INDEX idx_dc_user_id    (user_id),
    CONSTRAINT fk_dc_paket_id    FOREIGN KEY (paket_id)   REFERENCES paket(id) ON DELETE RESTRICT,
    CONSTRAINT fk_dc_lampiran_id FOREIGN KEY (lampiran_id) REFERENCES lampiran(id) ON DELETE SET NULL,
    CONSTRAINT fk_dc_user_id     FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. TABEL: berita_acara
-- ============================================================
CREATE TABLE IF NOT EXISTS berita_acara (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    paket_id    INT UNSIGNED    NOT NULL,
    nomor_ba    VARCHAR(100)    NOT NULL COMMENT 'Nomor surat BA',
    tanggal_ba  DATE            NOT NULL,
    konten      LONGTEXT        NULL COMMENT 'Isi dokumen BA (HTML/text)',
    hash_konten VARCHAR(64)     NULL COMMENT 'SHA256 dari konten untuk integritas',
    status      ENUM('draft','ditandatangani_pp','selesai') NOT NULL DEFAULT 'draft',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ba_paket_id  (paket_id),
    UNIQUE KEY uq_ba_nomor     (nomor_ba),
    CONSTRAINT fk_ba_paket_id  FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. TABEL: signatures (digital signature QR)
-- ============================================================
CREATE TABLE IF NOT EXISTS signatures (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    berita_acara_id     INT UNSIGNED    NOT NULL,
    user_id             INT UNSIGNED    NOT NULL,
    role_penandatangan  ENUM('PP','PPK') NOT NULL,
    urutan              TINYINT UNSIGNED NOT NULL COMMENT '1=PP dulu, 2=PPK',
    qr_data             TEXT            NOT NULL COMMENT 'JSON data yang di-encode ke QR',
    qr_image_path       VARCHAR(600)    NULL COMMENT 'Path file gambar QR',
    hash_dokumen        VARCHAR(64)     NOT NULL COMMENT 'SHA256 dokumen saat ditandatangani',
    signed_at           DATETIME        NOT NULL,
    ip_address          VARCHAR(45)     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sig_ba_user (berita_acara_id, user_id),
    INDEX idx_sig_ba_id       (berita_acara_id),
    INDEX idx_sig_user_id     (user_id),
    CONSTRAINT fk_sig_ba_id   FOREIGN KEY (berita_acara_id) REFERENCES berita_acara(id) ON DELETE CASCADE,
    CONSTRAINT fk_sig_user_id FOREIGN KEY (user_id)         REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. TABEL: audit_logs (audit trail lengkap)
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED    NULL COMMENT 'NULL jika aksi sistem',
    role_saat_aksi      ENUM('PPK','PP','admin','system') NOT NULL DEFAULT 'system',
    tabel_terpengaruh   VARCHAR(100)    NULL,
    record_id           INT UNSIGNED    NULL,
    aksi                ENUM(
                          'CREATE','READ','UPDATE','DELETE',
                          'LOGIN','LOGOUT',
                          'UPLOAD','DOWNLOAD',
                          'SIGN','APPROVE','REJECT','RETURN',
                          'TRANSFER','ROLE_CHANGE',
                          'RESET_PASSWORD','PASSWORD_CHANGED',
                          'ACCOUNT_VERIFIED','COMMENT'
                        ) NOT NULL,
    detail_lama         JSON            NULL COMMENT 'Snapshot data sebelum perubahan',
    detail_baru         JSON            NULL COMMENT 'Snapshot data sesudah perubahan',
    keterangan          TEXT            NULL,
    ip_address          VARCHAR(45)     NULL,
    user_agent          VARCHAR(500)    NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_al_user_id      (user_id),
    INDEX idx_al_aksi         (aksi),
    INDEX idx_al_tabel        (tabel_terpengaruh),
    INDEX idx_al_record_id    (record_id),
    INDEX idx_al_created_at   (created_at),
    CONSTRAINT fk_al_user_id  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. TABEL: password_reset_requests
-- ============================================================
CREATE TABLE IF NOT EXISTS password_reset_requests (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED    NOT NULL,
    token           VARCHAR(64)     NULL COMMENT 'Token dikirim ke email (diisi saat admin approve)',
    status          ENUM('menunggu','disetujui','digunakan','kadaluarsa') NOT NULL DEFAULT 'menunggu',
    diminta_at      DATETIME        NOT NULL,
    disetujui_oleh  INT UNSIGNED    NULL,
    disetujui_at    DATETIME        NULL,
    digunakan_at    DATETIME        NULL,
    expires_at      DATETIME        NULL COMMENT 'Token valid 24 jam setelah disetujui',
    PRIMARY KEY (id),
    UNIQUE KEY uq_prr_token (token),
    INDEX idx_prr_user_id   (user_id),
    INDEX idx_prr_status    (status),
    CONSTRAINT fk_prr_user_id       FOREIGN KEY (user_id)       REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_prr_disetujui_oleh FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. TABEL: log_paket
-- ============================================================
CREATE TABLE IF NOT EXISTS log_paket (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    paket_id        INT UNSIGNED    NOT NULL,
    user_id         INT UNSIGNED    NULL,
    nama_pengguna   VARCHAR(150)    NULL,
    aksi            VARCHAR(255)    NOT NULL,
    status_dari     VARCHAR(100)    NULL,
    keterangan      TEXT            NULL,
    lampiran_file   VARCHAR(600)    NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_lp_paket_id (paket_id),
    INDEX idx_lp_user_id  (user_id),
    CONSTRAINT fk_lp_paket_id FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. TABEL: sk_opd (SK per user)
-- ============================================================
CREATE TABLE IF NOT EXISTS sk_opd (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    nomor_sk    VARCHAR(150)    NOT NULL,
    tanggal_sk  DATE            NOT NULL,
    berlaku_dari DATE           NOT NULL,
    berlaku_sampai DATE         NULL,
    file_sk     VARCHAR(600)    NULL,
    keterangan  TEXT            NULL,
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_sk_user_id (user_id),
    CONSTRAINT fk_sk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. TABEL: role_change_requests (Dari migration v4)
-- ============================================================
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

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin default (password: Admin@2026!)
INSERT IGNORE INTO users
    (nip, nama, email, password, opd, jabatan_aktif, status_aktif)
VALUES
    ('000000000001',
     'Administrator',
     'admin@apelbaja.go.id',
     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'UKPBJ Provinsi Jawa Timur',
     'admin',
     1);
