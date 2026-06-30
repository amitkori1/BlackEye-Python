<?php

use PHPUnit\Framework\TestCase;

class InstagramIpTest extends TestCase
{
    private $ipTxtPath = __DIR__ . '/../sites/instagram/ip.txt';
    private $originalServer;
    private $originalIpTxtContent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;

        // Back up ip.txt if it exists, or just clear it so we don't delete the tracked file
        if (file_exists($this->ipTxtPath)) {
            $this->originalIpTxtContent = file_get_contents($this->ipTxtPath);
        } else {
            $this->originalIpTxtContent = false;
        }
        file_put_contents($this->ipTxtPath, '');

        // Reset superglobals used in the script
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent/1.0';
    }

    protected function tearDown(): void
    {
        // Restore $_SERVER
        $_SERVER = $this->originalServer;

        // Restore ip.txt content
        if ($this->originalIpTxtContent !== false) {
            file_put_contents($this->ipTxtPath, $this->originalIpTxtContent);
        } else if (file_exists($this->ipTxtPath)) {
            unlink($this->ipTxtPath);
        }

        parent::tearDown();
    }

    private function executeScript()
    {
        // Execute the script
        $oldDir = getcwd();
        chdir(__DIR__ . '/../sites/instagram');
        include 'ip.php';
        chdir($oldDir);
    }

    public function testHttpClientIpIsUsedFirst()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '1.1.1.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
        $_SERVER['REMOTE_ADDR'] = '3.3.3.3';

        $this->executeScript();

        $content = file_get_contents($this->ipTxtPath);
        $this->assertStringContainsString('IP: 1.1.1.1', $content);
    }

    public function testHttpXForwardedForIsUsedSecond()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
        $_SERVER['REMOTE_ADDR'] = '3.3.3.3';

        $this->executeScript();

        $content = file_get_contents($this->ipTxtPath);
        $this->assertStringContainsString('IP: 2.2.2.2', $content);
    }

    public function testRemoteAddrIsUsedAsFallback()
    {
        $_SERVER['REMOTE_ADDR'] = '3.3.3.3';

        $this->executeScript();

        $content = file_get_contents($this->ipTxtPath);
        $this->assertStringContainsString('IP: 3.3.3.3', $content);
    }

    public function testMissingAllIpHeadersDefaultsToEmptyString()
    {
        $this->executeScript();

        $content = file_get_contents($this->ipTxtPath);
        $this->assertStringContainsString('IP: ' . "\r\n", $content);
    }
}
