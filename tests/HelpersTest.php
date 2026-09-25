<?php
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {
    public function testSanitizeStripsTagsAndTrims(): void {
        $this->assertSame('Hello World', sanitize('  <script>alert(1)</script>Hello World  '));
    }

    public function testSanitizeHandlesEmptyString(): void {
        $this->assertSame('', sanitize('   '));
    }

    public function testEscapesHtmlSpecialChars(): void {
        $this->assertSame('&lt;b&gt;bold&lt;/b&gt;', e('<b>bold</b>'));
    }

    public function testEscapesQuotes(): void {
        $result = e('He said "hello" & \'goodbye\'');
        $this->assertStringNotContainsString('"', $result);
        $this->assertStringContainsString('&amp;', $result);
    }

    public function testIsLoggedInFalseWithoutSession(): void {
        unset($_SESSION['user']);
        $this->assertFalse(isLoggedIn());
    }

    public function testIsAdminFalseForRegularUser(): void {
        $_SESSION['user'] = ['id' => 1, 'role' => 'user'];
        $this->assertFalse(isAdmin());
    }

    public function testIsAdminTrueForAdminRole(): void {
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin'];
        $this->assertTrue(isAdmin());
    }
}
