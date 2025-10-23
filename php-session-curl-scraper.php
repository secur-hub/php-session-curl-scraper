<?php
/**
 * php-session-curl-scraper.php
 *
 * Descrizione:
 * Script PHP CLI che simula l'accesso di un browser a un sito web (login POST opzionale), mantiene la sessione
 * (cookie, PHPSESSID), poi visita una seconda pagina adottando gli stessi cookie e legge i valori di uno o più
 * elementi HTML (id, classe o tag). Restituisce i risultati in JSON.
 *
 * Come usarlo (CLI):
 * php php-session-curl-scraper.php \
 *   --login-url="https://example.com/login.php" \
 *   --login-fields="username=user&password=pass&submit=Login" \
 *   --page2-url="https://example.com/area/protected.php" \
 *   --selectors="#element1,.className,h1" \
 *   --user-agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
 *
 * Parametri:
 * - login-url      : URL dove fare POST per il login (o semplice GET se login-fields è vuoto)
 * - login-fields   : stringa query (es. "user=myuser&pass=mypass"), se vuota salta il login
 * - page2-url      : URL della pagina protetta da visitare dopo il login
 * - selectors      : lista separata da virgole di selettori CSS (#id, .class, tag)
 * - user-agent     : (opzionale) User-Agent da emulare
 *
 * Output JSON (stdout):
 * {
 *   "success": true,
 *   "values": {
 *       "#element1": ["Testo dell'elemento 1"],
 *       ".className": ["Valore 1", "Valore 2"],
 *       "h1": ["Titolo principale"]
 *   },
 *   "cookies": {
 *       "PHPSESSID": "abc123xyz",
 *       "user": "john_doe"
 *   },
 *   "error": null
 * }
 *
 * Esempio senza login (login-fields vuoti):
 * php php-session-curl-scraper.php \
 *   --login-url="https://example.com/" \
 *   --login-fields="" \
 *   --page2-url="https://example.com/page2.php" \
 *   --selectors="#content,.price"
 */

$options = getopt('', ['login-url:', 'login-fields::', 'page2-url:', 'selectors:', 'user-agent::']);
if (!isset($options['login-url']) || !isset($options['page2-url']) || !isset($options['selectors'])) {
    fwrite(STDERR, "Uso: php php-session-curl-scraper.php --login-url=URL [--login-fields='a=1&b=2'] --page2-url=URL --selectors='#id,.class' [--user-agent='UA']\n");
    exit(2);
}
$loginUrl = $options['login-url'];
$loginFields = $options['login-fields'] ?? '';
$page2Url = $options['page2-url'];
$selectors = array_map('trim', explode(',', $options['selectors']));
$userAgent = $options['user-agent'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36';

$cookieFile = sys_get_temp_dir() . '/php_curl_cookies_' . uniqid() . '.txt';

function curl_request($url, $cookieFile, $userAgent, $postFields = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    if ($postFields !== null && $postFields !== '') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$resp, $err, $info];
}

function read_cookies_from_file($cookieFile) {
    if (!file_exists($cookieFile)) return [];
    $lines = file($cookieFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $cookies = [];
    foreach ($lines as $line) {
        if ($line === '' || $line[0] === '#') continue;
        $parts = preg_split('/\t/', $line);
        if (count($parts) >= 7) {
            $cookies[$parts[5]] = $parts[6];
        }
    }
    return $cookies;
}

function extract_elements($html, $selectors) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $results = [];

    foreach ($selectors as $selector) {
        if ($selector === '') continue;
        if ($selector[0] === '#') {
            $id = substr($selector, 1);
            $nodes = $xpath->query("//*[@id='" . addslashes($id) . "']");
        } elseif ($selector[0] === '.') {
            $class = substr($selector, 1);
            $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' " . addslashes($class) . " ')]");
        } else {
            if (!preg_match('/^[a-zA-Z0-9\-]+$/', $selector)) continue;
            $nodes = $xpath->query("//" . $selector);
        }

        $values = [];
        foreach ($nodes as $node) {
            $values[] = trim($node->textContent);
        }
        $results[$selector] = $values;
    }

    return $results;
}

if ($loginFields !== '') {
    list($respLogin, $errLogin, $infoLogin) = curl_request($loginUrl, $cookieFile, $userAgent, $loginFields);
    if ($errLogin) {
        echo json_encode(['success' => false, 'values' => null, 'cookies' => null, 'error' => "curl error during login: $errLogin"], JSON_PRETTY_PRINT);
        @unlink($cookieFile);
        exit;
    }
}

list($respPage2, $errPage2, $infoPage2) = curl_request($page2Url, $cookieFile, $userAgent);
if ($errPage2) {
    echo json_encode(['success' => false, 'values' => null, 'cookies' => null, 'error' => "curl error fetching page2: $errPage2"], JSON_PRETTY_PRINT);
    @unlink($cookieFile);
    exit;
}

$cookies = read_cookies_from_file($cookieFile);
$elements = extract_elements($respPage2, $selectors);

$out = [
    'success' => true,
    'values' => $elements,
    'cookies' => $cookies,
    'error' => null
];

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@unlink($cookieFile);
exit;

