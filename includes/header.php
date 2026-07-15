<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>APELBAJA — LPSE Kabupaten Bangkalan</title>
    <meta name="description" content="Sistem Manajemen Paket Pengadaan UKPBJ Kabupaten Bangkalan — Biro PBJ Provinsi Jawa Timur">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        * { 
            font-family: 'Inter', sans-serif; 
            -webkit-tap-highlight-color: transparent;
        }

        /* ─── Sidebar Transition ─── */
        #sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #sidebar-overlay {
            transition: opacity 0.3s ease;
        }

        /* ─── Nav link active glow ─── */
        .nav-link { transition: all 0.2s ease; }
        .nav-link:hover { transform: translateX(2px); }

        /* ─── Custom scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ─── Hide scrollbar but keep functionality ─── */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ─── Table card stacking on mobile ─── */
        @media (max-width: 1023px) {
            .responsive-table thead { display: none; }
            .responsive-table tbody tr {
                display: block;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                margin-bottom: 12px;
                padding: 0;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            }
            .responsive-table tbody td {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 16px;
                border-bottom: 1px solid #f1f5f9;
                font-size: 13px;
            }
            .responsive-table tbody td:last-child { border-bottom: none; }
            .responsive-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                font-size: 11px;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                min-width: 110px;
                flex-shrink: 0;
            }
            .responsive-table tbody td.no-label::before { display: none; }
            .responsive-table tbody td.no-label { justify-content: flex-end; padding: 12px 16px; }
            .td-full { flex-direction: column; }
            .td-full::before { min-width: unset; }
        }

        /* ─── Status badge pulse animation ─── */
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }
        .badge-urgent { animation: pulse-red 2s infinite; }

        /* ─── Smooth page transitions ─── */
        .page-content { animation: fadeInUp 0.25s ease; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Card hover effect ─── */
        .stat-card { transition: all 0.2s ease; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }

        /* ─── Progress stepper ─── */
        .step-active { background: #2563eb; color: #fff; }
        .step-done   { background: #10b981; color: #fff; }
        .step-todo   { background: #e2e8f0; color: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50">