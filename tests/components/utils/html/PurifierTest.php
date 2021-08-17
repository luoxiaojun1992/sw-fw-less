<?php

namespace SwFwLessTest\components\utils\html;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\utils\html\Purifier;

class PurifierTest extends TestCase
{
    public function testPurify()
    {
        if (!class_exists('HTMLPurifier')) {
            $this->assertTrue(true);
            return;
        }

        $html = <<<EOF
<html>
<div>html、script</div>
<script></script>
</html>
EOF;
        $cleanHtml = Purifier::purify($html);
        $this->assertStringNotContainsString('<html>', $cleanHtml);
        $this->assertStringNotContainsString('</html>', $cleanHtml);
        $this->assertStringNotContainsString('<script>', $cleanHtml);
        $this->assertStringNotContainsString('</script>', $cleanHtml);
        $this->assertStringContainsString('html', $cleanHtml);
        $this->assertStringContainsString('script', $cleanHtml);
    }
}
