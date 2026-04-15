<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIM — Enterprise Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg: #07091a; 
      --bg-card: #0c1230; 
      --bg-card2: #111a3e;
      --border: rgba(100,149,255,.13); 
      --accent: #4f8ef7; 
      --success: #22c55e; 
      --danger: #ef4444; 
      --warning: #f59e0b;
      --text: #e2e8f0; 
      --muted: #5a6a8a;
    }

    body { 
      font-family: 'Inter', sans-serif; 
      background: var(--bg); 
      color: var(--text); 
      padding: 0;
      margin: 0;
      font-size: 14px; 
    }

    .wrapper {
      padding: 2rem;
      max-width: 1440px;
      margin: 0 auto;
    }

    /* Base UI Components (Cards, Buttons, Tables) */
    .card { 
      background: var(--bg-card); 
      border: 1px solid var(--border); 
      padding: 1.5rem; 
      border-radius: 12px; 
      margin-bottom: 2rem; 
    }

    h1, h2, h3, h4 { color: #fff; margin-top: 0; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 12px; border-bottom: 1px solid var(--border); text-align: left; }
    th { background: var(--bg-card2); color: var(--text); font-weight: 600; }
    tr:hover { background: rgba(255, 255, 255, 0.02); }

    /* Input & Forms */
    input, select, textarea, button { 
      padding: 10px 14px; 
      border-radius: 6px; 
      border: 1px solid var(--border); 
      background: var(--bg-card2); 
      color: white; 
      margin-bottom: 15px; 
      width: 100%;
      font-family: 'Inter', sans-serif; 
      box-sizing: border-box;
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(79, 142, 247, 0.15);
    }
    
    input:disabled, select:disabled, input[readonly] {
      background: rgba(17, 26, 62, 0.4);
      color: var(--muted);
      cursor: not-allowed;
    }

    button { 
      background: var(--accent); 
      cursor: pointer; 
      font-weight: 600; 
      border: none;
      transition: all 0.2s ease; 
    }
    button:hover { background: #3b76e1; transform: translateY(-1px); }

    /* Alerts */
    .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: rgba(34,197,94,.1); border: 1px solid var(--success); color: var(--success); }
    .alert-danger { background: rgba(239,68,68,.1); border: 1px solid var(--danger); color: var(--danger); }
    .alert-warning { background: rgba(245,158,11,.1); border: 1px solid var(--warning); color: var(--warning); }

    /* Grids */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 768px) {
      .grid-2 { grid-template-columns: 1fr; }
    }
  </style>
  
  <!-- CSS Section untuk kustomisasi halaman spesifik -->
  <?= $this->renderSection('css') ?>
</head>
<body>

  <div class="wrapper">
    <!-- Konten Utama Halaman -->
    <?= $this->renderSection('content') ?>
  </div>

  <!-- JS Section untuk interaktivitas masing-masing DOM -->
  <?= $this->renderSection('js') ?>

</body>
</html>
