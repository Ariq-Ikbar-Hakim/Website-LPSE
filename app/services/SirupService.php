<?php
/**
 * Service SIRUP — untuk fetch data SIRUP via manual input mode
 */
class SirupService
{
    // Mode ini dipakai karena API LKPP butuh whitelist / tidak publik
    // jadi kita gunakan fallback untuk keperluan demo: Fetch mock/API public
    // atau biarkan form diisi manual jika API tidak ditemukan.

    public function fetchByKodeRup(string $kodeRup): array
    {
        // Di sini seharusnya menggunakan cURL untuk request ke API LKPP.
        // Karena tidak ada dokumentasi API publik yang diberikan (hanya URL web),
        // maka jika integrasi sesungguhnya butuh CORS proxy atau backend curl:
        
        /*
        $url = "https://sirup.lkpp.go.id/sirup/api/paket/" . $kodeRup;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        */

        // Contoh return jika API sukses
        // return json_decode($response, true);

        // Jika tidak ada API, kembalikan response false agar UI lanjut ke mode manual
        return ['success' => false, 'message' => 'API tidak tersedia, gunakan mode manual'];
    }
}
