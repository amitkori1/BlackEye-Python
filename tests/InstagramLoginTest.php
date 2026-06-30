<?php

use PHPUnit\Framework\TestCase;

class InstagramLoginTest extends TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/login_test_' . uniqid();
        mkdir($this->tempDir);
        copy(__DIR__ . '/../sites/instagram/login.php', $this->tempDir . '/login.php');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempDir . '/usernames.txt')) {
            chmod($this->tempDir . '/usernames.txt', 0777);
            unlink($this->tempDir . '/usernames.txt');
        }
        if (file_exists($this->tempDir . '/wrapper.php')) {
            unlink($this->tempDir . '/wrapper.php');
        }
        unlink($this->tempDir . '/login.php');
        rmdir($this->tempDir);
    }

    public function testHappyPath()
    {
        $wrapper = <<<PHP
<?php
\$_POST['username'] = 'myuser';
\$_POST['password'] = 'mypass';
require 'login.php';
PHP;
        file_put_contents($this->tempDir . '/wrapper.php', $wrapper);

        $output = [];
        $return_var = 0;
        exec('cd ' . escapeshellarg($this->tempDir) . ' && php wrapper.php 2>&1', $output, $return_var);

        $this->assertFileExists($this->tempDir . '/usernames.txt');
        $content = file_get_contents($this->tempDir . '/usernames.txt');
        $this->assertEquals("Account: myuser Pass: mypass\n", $content);
    }

    public function testErrorPathMissingPermissions()
    {
        touch($this->tempDir . '/usernames.txt');
        chmod($this->tempDir . '/usernames.txt', 0400); // Read only

        $wrapper = <<<PHP
<?php
\$_POST['username'] = 'myuser';
\$_POST['password'] = 'mypass';
require 'login.php';
PHP;
        file_put_contents($this->tempDir . '/wrapper.php', $wrapper);

        $output = [];
        $return_var = 0;
        exec('cd ' . escapeshellarg($this->tempDir) . ' && php wrapper.php 2>&1', $output, $return_var);

        // Expect to see a permission denied warning
        $outputStr = implode("\n", $output);
        $this->assertStringContainsString('Failed to open stream: Permission denied', $outputStr);

        // However, the script should still exit gracefully (it calls exit() without throwing a fatal error)
        $this->assertEquals(0, $return_var);

        // Ensure file contents were not modified
        $content = file_get_contents($this->tempDir . '/usernames.txt');
        $this->assertEquals("", $content);
    }
}
