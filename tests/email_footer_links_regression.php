<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/application/services/utilities/StrClickable.php';
class FooterLinkFixture { use \app\services\utilities\StrClickable; }
function verify($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
    echo "PASS {$message}\n";
}
$footer = '<p>Visit <a href="https://justsmartchoice.com" style="color:#0077CC">' . "\n    www.justsmartchoice.com\n" . '</a></p>'
    . '<a href="mailto:admin@justsmartchoice.com"> admin@justsmartchoice.com </a>'
    . '<a href="https://x.com/justsmartchoice"><img src="https://justsmartchoice.com/images/social/x.png" alt="X"></a>'
    . '<style>.icon{background:url(https://example.test/image.png)}</style>'
    . '<!-- https://example.test/comment -->';
verify(FooterLinkFixture::clickable($footer) === $footer, 'existing footer links, whitespace, images, styles and comments stay intact');
$plain = FooterLinkFixture::clickable('<p>Contact admin@example.test or visit https://example.test and www.example.test.</p>');
verify(substr_count($plain, '<a ') === 3, 'unlinked email addresses and URLs still become links');
verify(strpos($plain, 'href="mailto:admin@example.test"') !== false, 'email address gets mailto destination');
verify(strpos($plain, 'href="https://example.test"') !== false, 'explicit HTTPS is preserved');
verify(strpos($plain, 'href="http://www.example.test"') !== false, 'plain www addresses retain existing conversion behavior');
verify(FooterLinkFixture::clickable($plain) === $plain, 'converting a message twice does not create nested links');
verify(FooterLinkFixture::clickable('') === '', 'empty messages remain empty');
verify(FooterLinkFixture::clickable("<p>First line\nSecond line</p>") === "<p>First line\nSecond line</p>", 'unlinked text spacing stays intact');
echo "PASS email footer link regressions; no external messages sent\n";
