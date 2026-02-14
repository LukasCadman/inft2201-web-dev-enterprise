<?php
use PHPUnit\Framework\TestCase;
use Application\Mail;

class MailTest extends TestCase {
    protected PDO $pdo;

    protected function setUp(): void
    {
        $dsn = "pgsql:host=" . getenv('DB_TEST_HOST') . ";dbname=" . getenv('DB_TEST_NAME');
        $this->pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Reset table for every test
        $this->pdo->exec("DROP TABLE IF EXISTS mail;");
        $this->pdo->exec("
            CREATE TABLE mail (
                id SERIAL PRIMARY KEY,
                subject TEXT NOT NULL,
                body TEXT NOT NULL
            );
        ");
    }

    public function testCreateMail()
    {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Alice", "Hello world");

        $this->assertIsInt($id);
        $this->assertEquals(1, $id);
    }

    public function testGetMail()
    {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Subject", "Body");

        $result = $mail->getMail($id);

        $this->assertEquals("Subject", $result['subject']);
        $this->assertEquals("Body", $result['body']);
    }

    public function testGetAllMail()
    {
        $mail = new Mail($this->pdo);

        $mail->createMail("A", "B");
        $mail->createMail("C", "D");

        $all = $mail->getAllMail();

        $this->assertCount(2, $all);
    }

    public function testUpdateMail()
    {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Old", "Old body");

        $updated = $mail->updateMail($id, "New", "New body");

        $this->assertTrue($updated);

        $updatedMail = $mail->getMail($id);
        $this->assertEquals("New", $updatedMail['subject']);
    }

    public function testDeleteMail()
    {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Delete", "Me");

        $deleted = $mail->deleteMail($id);

        $this->assertTrue($deleted);

        $result = $mail->getMail($id);
        $this->assertFalse($result);
    }
}
