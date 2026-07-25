<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

$root = __DIR__;
$index = $root . '/index.html';
$contactDir = $root . '/kontakt';
$contactIndex = $contactDir . '/index.html';

if (!is_file($index)) {
    exit('<h1>Instalace zastavena</h1><p>Soubor index.html nebyl nalezen. Nahrajte balíček přímo do kořene webu mesocyclone.cz.</p>');
}

$backup = $root . '/data/backups/v18-' . date('Ymd-His');
if (!is_dir($backup) && !mkdir($backup, 0755, true)) {
    exit('<h1>Instalace zastavena</h1><p>Nepodařilo se vytvořit bezpečnostní zálohu.</p>');
}
copy($index, $backup . '/index.html');
if (is_file($contactIndex)) {
    copy($contactIndex, $backup . '/kontakt-index.html');
}

$html = file_get_contents($index);
if ($html === false) {
    exit('<h1>Instalace zastavena</h1><p>Soubor index.html nelze načíst.</p>');
}

$contactSection = <<<'HTML'
<section class="section shell" id="kontakt" data-section="contact">
  <div class="contact-card">
    <div>
      <p class="section-kicker" id="contact-kicker">KONTAKT</p>
      <h2 id="contact-title">Spojte se s projektem Mesocyclone</h2>
      <p id="contact-description">E-mail je zobrazen pouze jako text. Kontaktovat nás můžete také prostřednictvím sociálních sítí.</p>
    </div>
    <div class="contact-options">
      <div class="contact-option"><small id="contact-email-label">E-mail</small><strong id="contact-email">info@mesocyclone.cz</strong></div>
      <a class="contact-option contact-social" id="contact-facebook" href="https://facebook.com/MesocycloneCZ" target="_blank" rel="noopener"><small id="contact-facebook-label">Facebook</small><strong id="contact-facebook-text">Mesocyclone ↗</strong></a>
      <a class="contact-option contact-social" id="contact-instagram" href="https://www.instagram.com/mesocyclonecz/" target="_blank" rel="noopener"><small id="contact-instagram-label">Instagram</small><strong id="contact-instagram-text">mesocyclonecz ↗</strong></a>
      <a class="contact-option contact-social" id="contact-youtube" href="https://www.youtube.com/@MesoCyclone-h7m" target="_blank" rel="noopener"><small id="contact-youtube-label">YouTube</small><strong id="contact-youtube-text">Mesocyclone ↗</strong></a>
      <div class="contact-option" id="contact-phone-option" hidden><small id="contact-phone-label">Telefon</small><strong id="contact-phone"></strong></div>
    </div>
  </div>
</section>
HTML;

if (stripos($html, 'id="kontakt"') === false) {
    $position = strripos($html, '</main>');
    if ($position === false) {
        exit('<h1>Instalace zastavena</h1><p>V index.html nebyla nalezena značka &lt;/main&gt;. Původní soubor zůstal nezměněn.</p>');
    }
    $html = substr_replace($html, "\n" . $contactSection . "\n", $position, 0);
}

if (stripos($html, 'mesocyclone-fix.css') === false) {
    $html = str_ireplace('</head>', "  <link rel=\"stylesheet\" href=\"/mesocyclone-fix.css?v=18\">\n</head>", $html);
}
if (stripos($html, 'mesocyclone-fix.js') === false) {
    $html = str_ireplace('</body>', "  <script src=\"/mesocyclone-fix.js?v=18\" defer></script>\n</body>", $html);
}

$temp = $index . '.v18-temp';
if (file_put_contents($temp, $html, LOCK_EX) === false || !rename($temp, $index)) {
    @unlink($temp);
    exit('<h1>Instalace zastavena</h1><p>Index nebylo možné bezpečně uložit. Záloha je v ' . htmlspecialchars($backup) . '.</p>');
}

if (!is_dir($contactDir)) {
    mkdir($contactDir, 0755, true);
}
$redirect = '<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="robots" content="noindex"><meta http-equiv="refresh" content="0;url=/#kontakt"><title>Kontakt</title><script>location.replace("/#kontakt")</script></head><body><a href="/#kontakt">Přejít na kontakt</a></body></html>';
file_put_contents($contactIndex, $redirect, LOCK_EX);

echo '<!doctype html><html lang="cs"><meta charset="utf-8"><title>Mesocyclone V18</title><style>body{font:16px system-ui;background:#081019;color:#eef6ff;padding:40px;max-width:850px;margin:auto}section{background:#111c28;border:1px solid #2c4154;border-radius:18px;padding:28px}h1{color:#72d5ff}code{background:#07101a;padding:3px 7px;border-radius:6px}</style><section><h1>Oprava V18 byla nainstalována</h1><p>Kontakt je nyní běžná karta hlavní stránky. Odkaz O projektu používá stabilní posun a funguje na první klik.</p><p>Záloha původních souborů: <code>' . htmlspecialchars($backup) . '</code></p><p><strong>Nyní smažte soubor MESOCYCLONE_INSTALACE_V18.php a obnovte web pomocí Ctrl + F5.</strong></p></section></html>';
