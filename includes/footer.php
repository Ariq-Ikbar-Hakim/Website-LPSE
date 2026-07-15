<footer class="w-full bg-white border-t border-slate-200 py-4 mt-auto">
    <div class="px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
        <small class="text-xs text-slate-400">
            &copy; <?= date('Y') ?> <span class="font-semibold text-slate-600">Biro PBJ Provinsi Jawa Timur</span>
        </small>
        <small class="text-xs text-slate-400">
            LPSE Kabupaten Bangkalan — <span class="font-medium text-blue-600">APELBAJA</span>
        </small>
    </div>
</footer>

<?php 
if(isLogin()) {
    include 'includes/modal_buat_paket.php';
}
?>
</body>
</html>