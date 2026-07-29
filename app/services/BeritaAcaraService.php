<?php
/**
 * Service Berita Acara — pengelolaan BA
 */
class BeritaAcaraService
{
    private BeritaAcara $baModel;
    private Signature $sigModel;
    private AuditService $auditService;
    private QrSignatureService $qrService;

    public function __construct(mysqli $db)
    {
        $this->baModel = new BeritaAcara($db);
        $this->sigModel = new Signature($db);
        $this->auditService = new AuditService($db);
        $this->qrService = new QrSignatureService();
    }

    public function sign(int $paketId, string $signatureImagePath = ''): bool
    {
        $ba = $this->baModel->findByPaketId($paketId);
        if (!$ba) {
            // Generate BA if not exists
            $nomorBa = 'BA/' . date('Y/m/d/') . $paketId;
            $data = [
                'paket_id' => $paketId,
                'nomor_ba' => $nomorBa,
                'tanggal_ba' => date('Y-m-d'),
                'konten' => 'Dokumen Berita Acara Persetujuan Paket',
                'hash_konten' => hash('sha256', 'Dokumen Berita Acara Persetujuan Paket'),
                'status' => 'draft'
            ];
            $baId = $this->baModel->create($data);
            $ba = $this->baModel->findById($baId);
        }

        $role = $_SESSION['jabatan_aktif'];
        $userId = $_SESSION['user_id'];

        if ($this->sigModel->hasSigned($ba['id'], $userId)) {
            return false; // Already signed
        }

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/LPSE/";
        $qrData = $baseUrl . $signatureImagePath;

        $filename = 'qr_' . $ba['id'] . '_' . $userId . '_' . time();
        $qrPath = $this->qrService->generateAndSave($qrData, $filename);

        $urutan = ($role === 'PP') ? 1 : 2;

        $sigData = [
            'berita_acara_id' => $ba['id'],
            'user_id' => $userId,
            'role_penandatangan' => $role,
            'urutan' => $urutan,
            'qr_data' => $qrData,
            'qr_image_path' => $qrPath,
            'hash_dokumen' => $ba['hash_konten'],
            'signed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];

        $this->sigModel->create($sigData);

        // Update status
        if ($role === 'PP') {
            $newStatus = 'ditandatangani_pp';
        } else {
            // PPK signing – if PP already signed, move to final status "tanda_tangan_kedua"
            $newStatus = 'tanda_tangan_kedua';
        }
        $this->baModel->updateStatus($ba['id'], $newStatus);

        if ($newStatus === 'tanda_tangan_kedua') {
            // Generate PDF Report only after both parties have signed
            global $conn;
            $paketService = new PaketService($conn);
            $paketService->updateStatus($paketId, 'selesai', 'Berita Acara ditandatangani lengkap');

            $paketModel = new Paket($conn);
            $paketInfo = $paketModel->findById($paketId);

            $signatures = $this->sigModel->getByBeritaAcaraId($ba['id']);
            $ppk = null;
            $pp = null;
            foreach ($signatures as $s) {
                if ($s['role_penandatangan'] === 'PPK') $ppk = $s;
                if ($s['role_penandatangan'] === 'PP') $pp = $s;
            }

            // Generate HTML
            $html = '<html><head><style>
                body { font-family: sans-serif; font-size: 14px; }
                .text-center { text-align: center; }
                .font-bold { font-weight: bold; }
                table.data { width: 100%; border-collapse: collapse; margin-top: 20px; }
                table.data th, table.data td { border: 1px solid black; padding: 8px; text-align: left; }
                table.no-border { width: 100%; border-collapse: collapse; margin-top: 40px; }
                table.no-border th, table.no-border td { border: none; padding: 8px; text-align: center; }
                .qr-img { width: 100px; height: 100px; }
            </style></head><body>';
            
            $html .= '<div class="text-center">
                        <h2 class="font-bold">BERITA ACARA PERSETUJUAN PAKET</h2>
                        <p>Nomor: ' . htmlspecialchars($ba['nomor_ba']) . '</p>
                      </div>
                      <br><br>';
                      
            $html .= '<p>Pada hari ini, tanggal <b>' . date('d F Y', strtotime($ba['tanggal_ba'])) . '</b>, telah disetujui dokumen persiapan pengadaan paket dengan rincian sebagai berikut:</p>';
            
            $html .= '<table class="data">
                        <tr><td width="30%">Nama Paket</td><td>' . htmlspecialchars($paketInfo['nama_paket']) . '</td></tr>
                        <tr><td>Kode RUP</td><td>' . htmlspecialchars($paketInfo['kode_rup']) . '</td></tr>
                        <tr><td>Tahun Anggaran</td><td>' . htmlspecialchars($paketInfo['tahun_anggaran']) . '</td></tr>
                        <tr><td>Pagu</td><td>Rp ' . number_format($paketInfo['pagu'], 0, ',', '.') . '</td></tr>
                        <tr><td>HPS</td><td>Rp ' . number_format($paketInfo['hps'], 0, ',', '.') . '</td></tr>
                      </table>
                      <br><br>';
                      
            $html .= '<p>Demikian Berita Acara ini dibuat dan ditandatangani secara elektronik (QR Code) untuk dipergunakan sebagaimana mestinya.</p><br><br>';
            
            $html .= '<table class="no-border">
                        <tr>
                            <td width="50%">
                                <b>Pejabat Pengadaan (PP)</b><br><br>';
            if ($pp && file_exists(BASEPATH . '/' . $pp['qr_image_path'])) {
                $imgData = base64_encode(file_get_contents(BASEPATH . '/' . $pp['qr_image_path']));
                $html .= '<img src="data:image/png;base64,' . $imgData . '" class="qr-img"><br>';
            }
            $html .= '          <br><b>' . htmlspecialchars($pp['nama'] ?? '') . '</b><br>
                                NIP: ' . htmlspecialchars($pp['nip'] ?? '') . '
                            </td>
                            <td width="50%">
                                <b>Pejabat Pembuat Komitmen (PPK)</b><br><br>';
            if ($ppk && file_exists(BASEPATH . '/' . $ppk['qr_image_path'])) {
                $imgData = base64_encode(file_get_contents(BASEPATH . '/' . $ppk['qr_image_path']));
                $html .= '<img src="data:image/png;base64,' . $imgData . '" class="qr-img"><br>';
            }
            $html .= '          <br><b>' . htmlspecialchars($ppk['nama'] ?? '') . '</b><br>
                                NIP: ' . htmlspecialchars($ppk['nip'] ?? '') . '
                            </td>
                        </tr>
                      </table>';
                      
            $html .= '</body></html>';

            require_once BASEPATH . '/vendor/autoload.php';
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $targetPdfName = 'BA_Paket_' . $paketId . '_' . time() . '.pdf';
            $targetPdfPath = BASEPATH . '/uploads/berita_acara/' . $targetPdfName;

            if (!is_dir(BASEPATH . '/uploads/berita_acara/')) {
                mkdir(BASEPATH . '/uploads/berita_acara/', 0777, true);
            }

            file_put_contents($targetPdfPath, $dompdf->output());
            $pdfUrl = 'uploads/berita_acara/' . $targetPdfName;
            
            // Update file_laporan
            $this->baModel->updateFileLaporan($ba['id'], $pdfUrl);
        }

        $this->auditService->log('SIGN', 'berita_acara', $ba['id'], null, $sigData, "Tanda tangan BA oleh {$role}");
        return true;
    }

    public function generateManualInApp(int $paketId, string $nomorBa, string $tanggalBa, string $kontenBa, string $signatureImagePath, string $signatureImagePathPpk = ''): array
    {
        global $conn;
        $paketModel = new Paket($conn);
        $paketInfo = $paketModel->findById($paketId);
        
        $userId = $_SESSION['user_id'];
        $userModel = new User($conn);
        $pp = $userModel->findById($userId);
        
        // Fetch PPK if available
        $ppk = null;
        if ($paketInfo && $paketInfo['ppk_id']) {
            $ppk = $userModel->findById($paketInfo['ppk_id']);
        }

        // Cari atau buat draf BA
        $ba = $this->baModel->findByPaketId($paketId);
        if (!$ba) {
            $data = [
                'paket_id' => $paketId,
                'nomor_ba' => $nomorBa,
                'tanggal_ba' => $tanggalBa,
                'konten' => $kontenBa,
                'hash_konten' => hash('sha256', $kontenBa),
                'status' => 'selesai',
                'jenis_ba' => 'manual'
            ];
            $baId = $this->baModel->create($data);
            $ba = $this->baModel->findById($baId);
        } else {
            // Update BA yang sudah ada
            $stmt = $conn->prepare("UPDATE berita_acara SET nomor_ba = ?, tanggal_ba = ?, konten = ?, hash_konten = ?, status = 'selesai', jenis_ba = 'manual' WHERE id = ?");
            $hashKonten = hash('sha256', $kontenBa);
            $stmt->bind_param("ssssi", $nomorBa, $tanggalBa, $kontenBa, $hashKonten, $ba['id']);
            $stmt->execute();
            $baId = $ba['id'];
            $ba['nomor_ba'] = $nomorBa;
            $ba['tanggal_ba'] = $tanggalBa;
            $ba['konten'] = $kontenBa;
        }

        // Generate QR Code untuk PP
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/LPSE/";
        $qrData = $baseUrl . $signatureImagePath;
        $filename = 'qr_' . $ba['id'] . '_' . $userId . '_' . time();
        $qrPath = $this->qrService->generateAndSave($qrData, $filename);
        
        // Generate QR Code untuk PPK
        $qrPathPpk = null;
        $qrDataPpk = null;
        if (!empty($signatureImagePathPpk) && $ppk) {
            $qrDataPpk = $baseUrl . $signatureImagePathPpk;
            $filenamePpk = 'qr_ppk_' . $ba['id'] . '_' . $ppk['id'] . '_' . time();
            $qrPathPpk = $this->qrService->generateAndSave($qrDataPpk, $filenamePpk);
        }

        // Siapkan data paket untuk template PDF
        $bulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $tanggalPdf = date('d', strtotime($tanggalBa)) . ' ' . $bulanIndo[(int)date('n', strtotime($tanggalBa))] . ' ' . date('Y', strtotime($tanggalBa));

        // Generate HTML untuk PDF — format dokumen resmi
        $html = '<html><head><style>
            @page { margin: 2cm 2.5cm; }
            body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.6; color: #111; }
            h2 { font-size: 14pt; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
            .subtitle { text-align: center; font-size: 12pt; margin-bottom: 30px; }
            .opening { text-align: justify; margin-bottom: 20px; }
            table.data { width: 100%; border-collapse: collapse; margin: 20px 0; }
            table.data td, table.data th { border: 1px solid #555; padding: 8px 12px; font-size: 11pt; }
            table.data td:first-child { width: 38%; font-weight: normal; }
            .closing { text-align: justify; margin-top: 24px; }
            table.sig { width: 100%; border-collapse: collapse; margin-top: 50px; }
            table.sig td { text-align: center; vertical-align: top; padding: 10px; width: 50%; border: none; }
            .qr-img { width: 90px; height: 90px; margin: 10px auto; display: block; }
            .sig-name { font-weight: bold; margin-top: 4px; }
            .sig-nip { font-size: 10pt; }
        </style></head><body>';

        $html .= '<h2>Berita Acara Persetujuan Paket</h2>';
        $html .= '<p class="subtitle">Nomor: ' . htmlspecialchars($nomorBa) . '</p>';

        $html .= '<p class="opening">Pada hari ini, tanggal <strong>' . $tanggalPdf . '</strong>, telah disetujui dokumen persiapan pengadaan paket dengan rincian sebagai berikut:</p>';

        $html .= '<table class="data">
                    <tbody>
                        <tr><td>Nama Paket</td><td>' . htmlspecialchars($paketInfo['nama_paket'] ?? $kontenBa) . '</td></tr>
                        <tr><td>Kode RUP</td><td>' . htmlspecialchars($paketInfo['kode_rup'] ?? '-') . '</td></tr>
                        <tr><td>Tahun Anggaran</td><td>' . htmlspecialchars($paketInfo['tahun_anggaran'] ?? '-') . '</td></tr>
                        <tr><td>Pagu</td><td>Rp ' . number_format((float)($paketInfo['pagu'] ?? 0), 0, ',', '.') . '</td></tr>
                        <tr><td>HPS</td><td>Rp ' . number_format((float)($paketInfo['hps'] ?? 0), 0, ',', '.') . '</td></tr>
                    </tbody>
                  </table>';

        $html .= '<p class="closing">Demikian Berita Acara ini dibuat dan ditandatangani secara elektronik (QR Code) untuk dipergunakan sebagaimana mestinya.</p>';
        // Tabel Tanda Tangan: PP di kiri, PPK di kanan (sesuai gambar)
        $html .= '<table class="sig"><tr>';
        
        // Kolom PP (kiri)
        $html .= '<td><b>Pejabat Pengadaan (PP)</b><br>';
        if (file_exists(BASEPATH . '/' . $qrPath)) {
            $imgData = base64_encode(file_get_contents(BASEPATH . '/' . $qrPath));
            $html .= '<img src="data:image/png;base64,' . $imgData . '" class="qr-img">';
        }
        $html .= '<br><p class="sig-name">' . htmlspecialchars($pp['nama'] ?? '') . '</p>
                  <p class="sig-nip">NIP: ' . htmlspecialchars($pp['nip'] ?? '') . '</p></td>';
        
        // Kolom PPK (kanan) — tampil jika ada tanda tangan PPK
        if ($ppk && $qrPathPpk) {
            $html .= '<td><b>Pejabat Pembuat Komitmen (PPK)</b><br>';
            if (file_exists(BASEPATH . '/' . $qrPathPpk)) {
                $imgDataPpk = base64_encode(file_get_contents(BASEPATH . '/' . $qrPathPpk));
                $html .= '<img src="data:image/png;base64,' . $imgDataPpk . '" class="qr-img">';
            }
            $html .= '<br><p class="sig-name">' . htmlspecialchars($ppk['nama'] ?? '') . '</p>
                      <p class="sig-nip">NIP: ' . htmlspecialchars($ppk['nip'] ?? '') . '</p></td>';
        } else {
            $html .= '<td></td>';
        }
        
        $html .= '</tr></table>';
        $html .= '</body></html>';

        require_once BASEPATH . '/vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $targetPdfName = 'BA_Manual_InApp_' . $paketId . '_' . time() . '.pdf';
        $targetPdfPath = BASEPATH . '/uploads/berita_acara/' . $targetPdfName;

        if (!is_dir(BASEPATH . '/uploads/berita_acara/')) {
            mkdir(BASEPATH . '/uploads/berita_acara/', 0777, true);
        }

        file_put_contents($targetPdfPath, $dompdf->output());
        $pdfUrl = 'uploads/berita_acara/' . $targetPdfName;
        
        // Simpan referensi file laporan
        $this->baModel->updateFileLaporan($ba['id'], $pdfUrl);
        
        // Simpan data signature PP di database
        $sigData = [
            'berita_acara_id' => $ba['id'],
            'user_id' => $userId,
            'role_penandatangan' => 'PP',
            'urutan' => 1,
            'qr_data' => $qrData,
            'qr_image_path' => $qrPath,
            'hash_dokumen' => $ba['hash_konten'],
            'signed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];
        $this->sigModel->create($sigData);
        
        // Simpan data signature PPK di database jika ada
        if ($ppk && $qrPathPpk) {
            $sigDataPpk = [
                'berita_acara_id' => $ba['id'],
                'user_id' => $ppk['id'],
                'role_penandatangan' => 'PPK',
                'urutan' => 2,
                'qr_data' => $qrDataPpk,
                'qr_image_path' => $qrPathPpk,
                'hash_dokumen' => $ba['hash_konten'],
                'signed_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ];
            $this->sigModel->create($sigDataPpk);
        }

        // Update Paket Status
        $paketService = new PaketService($conn);
        $paketService->updateStatus($paketId, 'selesai', 'Berita Acara Manual di-generate dan ditandatangani oleh PP');

        $this->auditService->log('UPLOAD', 'berita_acara', $ba['id'], null, null, "Generate & Sign BA Manual In-App oleh PP");

        return ['success' => true, 'pdfUrl' => $pdfUrl];
    }
}
