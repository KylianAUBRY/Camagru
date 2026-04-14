<?php

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $host = MAIL_HOST;
        $port = MAIL_PORT;
        $from = MAIL_FROM;
        $fromName = MAIL_FROM_NAME;

        $sock = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$sock) {
            error_log("Mailer: cannot connect to $host:$port – $errstr");
            return false;
        }

        $boundary = bin2hex(random_bytes(16));
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";

        $message  = "Date: " . date('r') . "\r\n";
        $message .= $headers;
        $message .= "\r\n" . $body;

        self::smtpSend($sock, $from, $to, $message);
        fclose($sock);
        return true;
    }

    private static function smtpSend($sock, string $from, string $to, string $data): void
    {
        self::smtpCmd($sock, null, 220);
        self::smtpCmd($sock, "EHLO localhost", 250);
        self::smtpCmd($sock, "MAIL FROM:<$from>", 250);
        self::smtpCmd($sock, "RCPT TO:<$to>", 250);
        self::smtpCmd($sock, "DATA", 354);
        fwrite($sock, $data . "\r\n.\r\n");
        self::smtpRead($sock);
        self::smtpCmd($sock, "QUIT", 221);
    }

    private static function smtpCmd($sock, ?string $cmd, int $expect): string
    {
        if ($cmd !== null) {
            fwrite($sock, $cmd . "\r\n");
        }
        return self::smtpRead($sock);
    }

    private static function smtpRead($sock): string
    {
        $response = '';
        while ($line = fgets($sock, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
}
