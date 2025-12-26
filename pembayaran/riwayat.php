<?php
// pembayaran/riwayat.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

// hanya admin / RT
require_login();

$pdo = db();

// ambil filter (optional)
$filter_tahun = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';
$filter_bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';

$params = [];
$sql = "
    SELECT 
        p.id,
        p.warga_id,
        p.tahun,
        p.bulan,
        p.tanggal_bayar,
        p.jumlah,
        p.metode,
        p.keterangan,
        p.bukti_path,
        w.nama AS nama_warga,
        w.nik,
        w.alamat
    FROM pembayaran p
    JOIN warga w ON w.id = p.warga_id
    WHERE 1=1
";

if ($filter_tahun !== '') {
    $sql .= " AND p.tahun = :tahun";
    $params[':tahun'] = (int)$filter_tahun;
}
if ($filter_bulan !== '') {
    $sql .= " AND p.bulan = :bulan";
    $params[':bulan'] = (int)$filter_bulan;
}

$sql .= " ORDER BY p.tahun DESC, p.bulan DESC, w.nama ASC";

$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

// nama bulan buat tampilan
$bulanNama = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pembayaran Iuran</title>
    <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
    <style>
        body{background:#020617;color:#e5e7eb;font-family:system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif}
        .page-wrap{max-width:1100px;margin:20px auto;padding:0 12px}
        h1{font-size:22px;margin-bottom:4px}
        .muted{color:#9ca3af;font-size:13px}
        .card{
            background:#020617;
            border:1px solid #1f2937;
            border-radius:12px;
            padding:16px;
            margin-top:16px;
            box-shadow:0 10px 30px rgba(0,0,0,.4);
        }
        table{width:100%;border-collapse:collapse;margin-top:10px;font-size:14px}
        th,td{padding:8px 10px;border-bottom:1px solid #1f2937;text-align:left}
        th{background:#020617;border-bottom:1px solid #374151}
        tr:nth-child(even){background:#020617}
        .filter-row{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end}
        label{font-size:13px;display:block;margin-bottom:3px}
        input[type="number"],select{
            background:#020617;border:1px solid #374151;border-radius:8px;
            color:#e5e7eb;padding:7px 9px;
        }
        button{
            padding:8px 14px;border-radius:8px;border:0;
            background:#38bdf8;color:#0f172a;font-weight:600;cursor:pointer;
        }
        .badge{display:inline-block;border-radius:999px;padding:3px 8px;font-size:11px}
        .badge-cash{background:#22c55e33;color:#4ade80}
        .badge-transfer{background:#38bdf833;color:#7dd3fc}
        .bukti-link a{color:#38bdf8;font-size:13px;text-decoration:none}
        .bukti-link a:hover{text-decoration:underline}
        .nav-top{margin-bottom:10px;font-size:13px}
        .nav-top a{color:#93c5fd;text-decoration:none;margin-right:8px}
        .nav-top a:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="page-wrap">

    <div class="nav-top">
        <a href="<?= base_url('dashboard.php') ?>">← Kembali ke Dashboard</a>
        <a href="<?= base_url('pembayaran/index.php') ?>">Input Pembayaran</a>
    </div>

    <h1>Riwayat Pembayaran Iuran</h1>
    <div class="muted">Halaman ini hanya dapat diakses oleh pengurus/RT untuk melihat seluruh pembayaran warga.</div>

    <div class="card">
        <form method="get">
            <div class="filter-row">
                <div>
                    <label for="tahun">Tahun</label>
                    <input type="number" name="tahun" id="tahun"
                           value="<?= htmlspecialchars($filter_tahun) ?>"
                           placeholder="<?= date('Y') ?>">
                </div>
                <div>
                    <label for="bulan">Bulan</label>
                    <select name="bulan" id="bulan">
                        <option value="">Semua</option>
                        <?php for ($b = 1; $b <= 12; $b++): ?>
                            <option value="<?= $b ?>" <?= ($filter_bulan !== '' && (int)$filter_bulan === $b) ? 'selected' : '' ?>>
                                <?= $bulanNama[$b] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <button type="submit">Terapkan Filter</button>
                </div>
                <?php if ($filter_tahun !== '' || $filter_bulan !== ''): ?>
                    <div>
                        <a href="<?= base_url('pembayaran/riwayat.php') ?>" class="muted">Reset</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <?php if (empty($rows)): ?>
            <p class="muted">Belum ada data pembayaran dengan filter ini.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Warga</th>
                    <th>Tahun</th>
                    <th>Bulan</th>
                    <th>Tanggal Bayar</th>
                    <th>Jumlah</th>
                    <th>Metode</th>
                    <th>Keterangan</th>
                    <th>Bukti</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?= esc($r['nama_warga']) ?></strong><br>
                            <span class="muted">
                                NIK: <?= esc($r['nik'] ?: '-') ?> ·
                                <?= esc($r['alamat'] ?: '-') ?>
                            </span>
                        </td>
                        <td><?= esc($r['tahun']) ?></td>
                        <td><?= $bulanNama[(int)$r['bulan']] ?? esc($r['bulan']) ?></td>
                        <td><?= esc($r['tanggal_bayar']) ?></td>
                        <td>Rp <?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                        <td>
                            <?php if (strtolower($r['metode']) === 'transfer'): ?>
                                <span class="badge badge-transfer">Transfer</span>
                            <?php else: ?>
                                <span class="badge badge-cash"><?= esc(ucfirst($r['metode'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($r['keterangan']) ?></td>
                        <td class="bukti-link">
                            <?php if (!empty($r['bukti_path'])): ?>
                                <a href="<?= base_url($r['bukti_path']) ?>" target="_blank">Lihat</a>
                            <?php else: ?>
                                <span class="muted">Belum ada</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
