<?php
// 1. 数据库连接配置
$host = '127.0.0.1';
$dbname = 'school';
$username = 'root';
$password = 'wsw99726';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ===== 2. 处理表单提交（POST 请求） =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $realname = trim($_POST['realname'] ?? '');
        $gender   = trim($_POST['gender'] ?? '');

        if ($realname !== '' && $gender !== '') {
            $sql = "INSERT INTO student (realname, gender) VALUES (:realname, :gender)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':realname' => $realname,
                ':gender'   => $gender,
            ]);
            // PRG：重定向回当前页，携带成功提示
            header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=added');
            exit;
        } else {
            // 字段为空，重定向并携带错误提示
            header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=empty');
            exit;
        }
    }

    // ===== 3. 查询所有学生数据 =====
    $stmt = $pdo->query("SELECT id, realname, gender FROM student ORDER BY id DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 读取提示信息
$tip = '';
$tipType = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') {
        $tip = '录入成功，已刷新列表';
        $tipType = 'success';
    } elseif ($_GET['msg'] === 'empty') {
        $tip = '姓名和性别不能为空';
        $tipType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学生信息管理</title>
    <style>
        /* ---------- 基础变量 ---------- */
        :root {
            --bg: #0f1117;
            --card: #1a1d27;
            --border: #2a2d3a;
            --fg: #e4e4e7;
            --muted: #71717a;
            --accent: #22d3ee;
            --accent-dim: rgba(34,211,238,.12);
            --danger: #f87171;
            --danger-dim: rgba(248,113,113,.12);
            --radius: 10px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: var(--bg);
            color: var(--fg);
            min-height: 100vh;
            padding: 40px 20px;
        }

        /* ---------- 容器 ---------- */
        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -.5px;
            margin-bottom: 32px;
        }
        h1 span { color: var(--accent); }

        /* ---------- 提示条 ---------- */
        .toast {
            padding: 12px 18px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            animation: slideIn .35s ease;
        }
        .toast.success {
            background: var(--accent-dim);
            color: var(--accent);
            border: 1px solid rgba(34,211,238,.25);
        }
        .toast.error {
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid rgba(248,113,113,.25);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ---------- 表单卡片 ---------- */
        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 32px;
        }
        .form-card h2 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-row {
            display: flex;
            gap: 14px;
            align-items: flex-end;
        }
        .field { flex: 1; }
        .field label {
            display: block;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .field input,
        .field select {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--fg);
            font-size: 15px;
            outline: none;
            transition: border-color .2s;
        }
        .field input:focus,
        .field select:focus {
            border-color: var(--accent);
        }
        .field select option {
            background: var(--card);
            color: var(--fg);
        }

        .btn-submit {
            padding: 10px 28px;
            background: var(--accent);
            color: #0f1117;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            white-space: nowrap;
        }
        .btn-submit:hover { opacity: .85; }
        .btn-submit:active { transform: scale(.97); }

        /* ---------- 表格卡片 ---------- */
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .table-card h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 20px 28px 0;
        }
        .table-card .count {
            font-size: 13px;
            color: var(--muted);
            padding: 6px 28px 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            text-align: left;
            padding: 12px 28px;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .8px;
            background: rgba(255,255,255,.02);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        tbody td {
            padding: 14px 28px;
            font-size: 15px;
            border-bottom: 1px solid var(--border);
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr {
            transition: background .15s;
        }
        tbody tr:hover {
            background: rgba(255,255,255,.025);
        }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .badge-m { background: rgba(59,130,246,.15); color: #60a5fa; }
        .badge-f { background: rgba(244,114,182,.15); color: #f472b6; }

        .empty-row td {
            text-align: center;
            color: var(--muted);
            padding: 40px 0;
            font-size: 14px;
        }

        /* ---------- 新增行高亮 ---------- */
        .row-new {
            animation: flash .8s ease;
        }
        @keyframes flash {
            0%, 100% { background: transparent; }
            30% { background: var(--accent-dim); }
        }

        /* ---------- 响应式 ---------- */
        @media (max-width: 560px) {
            .form-row { flex-direction: column; }
            .btn-submit { width: 100%; }
            thead th, tbody td { padding: 12px 16px; }
        }
    </style>
</head>
<body>

<div class="container">

    <h1>学生信息<span>管理</span></h1>

    <!-- 提示信息 -->
    <?php if ($tip): ?>
        <div class="toast <?= $tipType ?>"><?= htmlspecialchars($tip) ?></div>
    <?php endif; ?>

    <!-- 录入表单 -->
    <div class="form-card">
        <h2>新增学生</h2>
        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
            <div class="form-row">
                <div class="field">
                    <label for="realname">姓名</label>
                    <input type="text" id="realname" name="realname" placeholder="请输入学生姓名" required>
                </div>
                <div class="field" style="max-width:160px;">
                    <label for="gender">性别</label>
                    <select id="gender" name="gender" required>
                        <option value="">请选择</option>
                        <option value="男">男</option>
                        <option value="女">女</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">录入</button>
            </div>
        </form>
    </div>

    <!-- 数据表格 -->
    <div class="table-card">
        <h2>数据列表</h2>
        <div class="count">共 <?= count($students) ?> 条记录</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>姓名</th>
                    <th>性别</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $i => $row): ?>
                        <?php
                            // 判断是否为刚插入的第一条（因为按 id DESC 排序）
                            $isNew = ($tipType === 'success' && $i === 0);
                            $badgeClass = $row['gender'] === '男' ? 'badge-m' : 'badge-f';
                        ?>
                        <tr class="<?= $isNew ? 'row-new' : '' ?>">
                            <td style="color:var(--muted)"><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['realname']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['gender']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-row"><td colspan="3">暂无数据，请先录入</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
