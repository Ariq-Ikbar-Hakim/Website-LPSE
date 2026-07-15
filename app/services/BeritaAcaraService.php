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
}
