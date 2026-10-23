<?php
declare(strict_types=1);
session_start();

$host = '127.0.0.1';
$dbName = 'kebab404';
$dbUser = 'root';
$dbPassword = '';
$dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(503);
    ?><!doctype html><html lang="pl"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>KEBAB.EXE — baza offline</title><link rel="stylesheet" href="style.css"><body><main class="container setup-error"><p class="eyebrow">SYSTEM OFFLINE</p><h1>Nie połączono z bazą.</h1><p>Uruchom Apache i MySQL w XAMPP, a potem zaimportuj plik <code>baza.sql</code> w phpMyAdmin.</p><p class="muted">Szczegóły: <?= htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p></main></body></html><?php
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'], $token)) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.'];
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '#zamowienia');
        exit;
    }

    $action = (string)($_POST['action'] ?? '');
    if ($action === 'create_order') {
        $client = trim((string)($_POST['klient'] ?? ''));
        $productId = filter_var($_POST['produkt_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['ilosc'] ?? null, FILTER_VALIDATE_INT);
        $sauce = (string)($_POST['sos'] ?? '');
        $allowedSauces = ['Łagodny', 'Ostry', 'Atomowy'];

        if ($client === '' || mb_strlen($client) > 50) {
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Podaj nick o długości od 1 do 50 znaków.'];
        } elseif (!$productId || !$quantity || $quantity < 1 || $quantity > 5) {
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Wybierz istniejący produkt i ustaw ilość od 1 do 5.'];
        } elseif (!in_array($sauce, $allowedSauces, true)) {
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Wybierz jeden z dostępnych sosów.'];
        } else {
            $productCheck = $pdo->prepare('SELECT id FROM produkty WHERE id = :id');
            $productCheck->execute(['id' => $productId]);
            if (!$productCheck->fetch()) {
                $_SESSION['flash'] = ['type' => 'error', 'text' => 'Wybrany produkt nie istnieje. Odśwież menu.'];
            } else {
                $insert = $pdo->prepare('INSERT INTO zamowienia (klient, produkt_id, ilosc, sos, status) VALUES (:klient, :produkt_id, :ilosc, :sos, \'NOWE\')');
                $insert->execute(['klient' => $client, 'produkt_id' => $productId, 'ilosc' => $quantity, 'sos' => $sauce]);
                $_SESSION['flash'] = ['type' => 'success', 'text' => 'Zamówienie przyjęte! Twój kebab jest właśnie kompilowany…'];
            }
        }
    } elseif ($action === 'mark_ready') {
        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
        if ($orderId && $orderId > 0) {
            $update = $pdo->prepare("UPDATE zamowienia SET status = 'GOTOWE' WHERE id = :id AND status = 'NOWE'");
            $update->execute(['id' => $orderId]);
            $_SESSION['flash'] = ['type' => $update->rowCount() ? 'success' : 'error', 'text' => $update->rowCount() ? 'Status zamówienia zmieniono na GOTOWE.' : 'Nie znaleziono nowego zamówienia o tym numerze.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Nieprawidłowy numer zamówienia.'];
        }
    }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '#obsluga');
    exit;
}

$products = $pdo->query('SELECT id, nazwa, cena FROM produkty ORDER BY cena ASC')->fetchAll();
$orders = $pdo->query('SELECT z.id, z.klient, z.ilosc, z.sos, z.status, z.data_zamowienia, p.nazwa AS produkt, p.cena, (p.cena * z.ilosc) AS suma FROM zamowienia z JOIN produkty p ON z.produkt_id = p.id ORDER BY z.data_zamowienia DESC, z.id DESC')->fetchAll();
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money(float|string $amount): string { return number_format((float)$amount, 2, ',', ' ') . ' zł'; }
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#10120f">
  <title>KEBAB.EXE — Jedzenie dla programistów</title>
  <meta name="description" content="Najpierw kebab, potem debugowanie. Zamówienie zapisane w bazie KEBAB.EXE.">
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>
  <div class="system-bar"><div class="container bar-content"><span><i class="led"></i> SYSTEM ONLINE / WARSAW</span><span>STATUS: <strong>GŁODNI</strong></span></div></div>
  <header class="site-header"><div class="container nav-wrap"><a class="brand" href="#start" aria-label="KEBAB.EXE strona główna">KEBAB<span>.EXE</span></a><nav aria-label="Menu główne"><a href="#menu">Menu</a><a href="#zamowienia">Zamówienia</a><a href="#obsluga">Panel obsługi</a></nav></div></header>
  <main>
    <section class="container hero" id="start"><div class="hero-copy"><p class="eyebrow">// paliwo dla twojego runtime</p><h1>Najpierw kebab,<br><span>potem debugowanie.</span></h1><p class="intro">Gdy kod się nie kompiluje, a deadline patrzy — dobry kebab zawsze przechodzi testy.</p><div class="hero-actions"><a class="button" href="#zamowienia">Zamów teraz <span aria-hidden="true">↗</span></a><a class="quiet-link" href="#menu">[ zobacz menu ]</a></div></div><div class="hero-art" role="img" aria-label="Ilustracja kebaba"><span class="art-tag">BUILD: ŚWIEŻY</span><span class="food" aria-hidden="true">🌯</span><span class="art-caption">&gt; hungry.exe running...</span></div></section>

    <section class="section" id="menu"><div class="container"><div class="section-heading"><div><p class="eyebrow">01 / menu</p><h2>Wybierz swój build.</h2></div><p>Świeżo składane. Bez błędów krytycznych.</p></div><div class="product-grid">
      <?php foreach ($products as $index => $product): ?><article class="product-card"><div><span class="item-code">ITEM_<?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($product['nazwa']) ?></h3></div><p class="price"><?= money($product['cena']) ?> <small>/ szt.</small></p></article><?php endforeach; ?>
    </div></div></section>

    <section class="section" id="zamowienia"><div class="container order-layout"><div class="section-copy"><p class="eyebrow">02 / zamówienia</p><h2>Deployuj głód.</h2><p>Wypełnij formularz. Zapiszemy zamówienie w bazie, a obsługa od razu zobaczy je w kolejce.</p><p class="micro">// czas realizacji: szybciej niż npm install</p></div><div class="form-panel"><div class="panel-title"><span>●</span> order.form <span class="method">[ POST ]</span></div>
      <?php if ($flash): ?><p class="notice <?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></p><?php endif; ?>
      <form id="order-form" method="post" action="#zamowienia"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="create_order">
        <label for="klient">Imię lub nick<input id="klient" name="klient" type="text" maxlength="50" autocomplete="name" placeholder="np. Ada Lovelace" required></label>
        <label for="produkt_id">Wybierz danie<select id="produkt_id" name="produkt_id" required><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>" data-price="<?= e((string)$product['cena']) ?>"><?= e($product['nazwa']) ?> — <?= money($product['cena']) ?></option><?php endforeach; ?></select></label>
        <label for="ilosc">Liczba sztuk<input id="ilosc" name="ilosc" type="number" min="1" max="5" value="1" required></label>
        <label for="sos">Rodzaj sosu<select id="sos" name="sos" required><option value="Łagodny">Łagodny — dla frontendowców</option><option value="Ostry">Ostry — dla backendowców</option><option value="Atomowy">Atomowy — bez zapisywania plików</option></select></label>
        <p class="live-total" aria-live="polite">Łącznie do zapłaty: <strong id="total-price">—</strong></p>
        <button class="button submit-button" type="submit">Zamawiam! <span aria-hidden="true">↗</span></button>
      </form></div></div></section>

    <section class="section" id="obsluga"><div class="container"><div class="section-heading"><div><p class="eyebrow">03 / panel obsługi</p><h2>Kolejka zadań.</h2></div><p><?= count($orders) ?> zamówień w systemie</p></div>
      <?php if ($flash): ?><p class="notice <?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></p><?php endif; ?>
      <div class="table-wrap"><table><thead><tr><th>ID</th><th>Klient</th><th>Produkt</th><th>Ilość</th><th>Sos</th><th>Łączna cena</th><th>Status</th><th>Akcja</th></tr></thead><tbody>
      <?php if (!$orders): ?><tr><td class="empty" colspan="8">Kolejka jest pusta. Czekamy na pierwszy deploy głodu.</td></tr><?php else: foreach ($orders as $order): ?><tr><td class="mono">#<?= (int)$order['id'] ?></td><td><strong><?= e($order['klient']) ?></strong></td><td><?= e($order['produkt']) ?></td><td><?= (int)$order['ilosc'] ?></td><td><?= e($order['sos']) ?></td><td class="mono"><?= money($order['suma']) ?></td><td><span class="status <?= $order['status'] === 'GOTOWE' ? 'ready' : 'new' ?>"><?= e($order['status']) ?></span></td><td><?php if ($order['status'] === 'NOWE'): ?><form class="status-form" method="post" action="#obsluga"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="mark_ready"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><button class="small-button" type="submit">Oznacz jako GOTOWE</button></form><?php else: ?><span class="done-label">✓ gotowe</span><?php endif; ?></td></tr><?php endforeach; endif; ?>
      </tbody></table></div></div></section>
  </main>
  <footer><div class="container footer-content"><span>© 2026 <strong>KEBAB.EXE</strong> — Powered by głodni programiści</span><span>exit(0); // smacznego</span></div></footer>
  <button class="easter-egg" id="error-button" type="button" aria-label="Ukryty tryb awarii serwera">404</button><div id="error-message" class="error-message" role="status" hidden>ERROR 418 — Jestem czajnikiem, nie serwerem kebabowym</div>
</body>
</html>
