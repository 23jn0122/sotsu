<?php


class SmtpMail {
    private $smtp_port;    //SMTPポート
    private $time_out;     //タイムアウト時間
    private $host_name;    //ホスト名
    private $log_file;     //ログファイル
    private $relay_host;   //サーバーアドレス
    private $auth;         //認証
    private $user;         //ユーザー名
    private $pass;         //パスワード
    private $sock;         //サーバーハンドル
    private $debug;        //デバッグモード
    
    public function __construct($user, $pass, $relay_host = "smtp.qq.com", $smtp_port = 465, $auth = true) {
        $this->debug = false;
        $this->smtp_port = $smtp_port;
        $this->relay_host = $relay_host;
        $this->time_out = 30; //タイムアウト時間
        $this->auth = $auth;
        $this->user = $user;
        $this->pass = $pass;
        $this->host_name = "localhost";
        $this->log_file = "";
        $this->sock = FALSE;
    }

    public function sendMail($to, $from, $subject = "", $body = "", $mailtype = "HTML", $cc = "", $bcc = "", $additional_headers = "") {
        $header = "";
        $mail_from = $this->getAddress($this->stripComment($from));
        $body = preg_replace("/(^|(\r\n))(\\.)/", "\\1.\\3", $body);
        $header .= "MIME-Version:1.0\r\n";
        if($mailtype=="HTML"){
            $header .= "Content-Type:text/html; charset=utf-8\r\n";
        }
        $header .= "To: ".$to."\r\n";
        if ($cc != "") {
            $header .= "Cc: ".$cc."\r\n";
        }
        $header .= "From: $from\r\n";
        $header .= "Subject: $subject\r\n";
        $header .= $additional_headers;
        $header .= "Date: ".date("r")."\r\n";
        $header .= "X-Mailer:By SmtpMail (PHP) \r\n";
        list($msec, $sec) = explode(" ", microtime());
        $header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".".$mail_from.">\r\n";
        $TO = explode(",", $this->stripComment($to));

        if ($cc != "") {
            $TO = array_merge($TO, explode(",", $this->stripComment($cc)));
        }
        if ($bcc != "") {
            $TO = array_merge($TO, explode(",", $this->stripComment($bcc)));
        }

        $sent = TRUE;
        foreach ($TO as $rcpt_to) {
            $rcpt_to = $this->getAddress($rcpt_to);
            if (!$this->smtp_sockopen($rcpt_to)) {
                $this->log_write("Error: Cannot send email to ".$rcpt_to."\n");
                $sent = FALSE;
                continue;
            }
            if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body)) {
                $this->log_write("E-mail has been sent to <".$rcpt_to.">\n");
            } else {
                $this->log_write("Error: Cannot send email to <".$rcpt_to.">\n");
                $sent = FALSE;
            }
            fclose($this->sock);
            $this->log_write("Disconnected from remote host\n");
        }
        return $sent;
    }

    private function smtp_send($helo, $from, $to, $header, $body = "") {
        if (!$this->smtp_putcmd("HELO", $helo)) {
            return $this->smtp_error("sending HELO command");
        }
        if($this->auth){
            if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user))) {
                return $this->smtp_error("sending AUTH LOGIN command");
            }
            if (!$this->smtp_putcmd("", base64_encode($this->pass))) {
                return $this->smtp_error("sending AUTH LOGIN password");
            }
        }
        if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">")) {
            return $this->smtp_error("sending MAIL FROM command");
        }
        if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">")) {
            return $this->smtp_error("sending RCPT TO command");
        }
        if (!$this->smtp_putcmd("DATA")) {
            return $this->smtp_error("sending DATA command");
        }
        if (!$this->smtp_message($header, $body)) {
            return $this->smtp_error("sending message");
        }
        if (!$this->smtp_eom()) {
            return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
        }
        if (!$this->smtp_putcmd("QUIT")) {
            return $this->smtp_error("sending QUIT command");
        }
        return TRUE;
    }

    private function smtp_sockopen($address) {
        if ($this->relay_host == "") {
            return $this->smtp_sockopen_mx($address);
        } else {
            return $this->smtp_sockopen_relay();
        }
    }

    private function smtp_sockopen_relay() {
        $this->log_write("Trying to ".$this->relay_host.":".$this->smtp_port."\n");
        $errno = 0;
        $errstr = "";
        
        // SSL対応を追加する
        if($this->smtp_port == 465) {
            $this->sock = @fsockopen("ssl://".$this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);
        } else {
            $this->sock = @fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);
        }
        
        if (!($this->sock && $this->smtp_ok())) {
            $this->log_write("Error: Cannot connect to relay host ".$this->relay_host."\n");
            $this->log_write("Error: ".$errstr." (".$errno.")\n");
            return FALSE;
        }
        $this->log_write("Connected to relay host ".$this->relay_host."\n");
        return TRUE;
    }

    private function smtp_sockopen_mx($address) {
        $domain = preg_replace("/^.+@([^@]+)$/", "\\1", $address);
        if (!@getmxrr($domain, $MXHOSTS)) {
            $this->log_write("Error: Cannot resolve MX \"".$domain."\"\n");
            return FALSE;
        }
        foreach ($MXHOSTS as $host) {
            $this->log_write("Trying to ".$host.":".$this->smtp_port."\n");
            $this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);
            if (!($this->sock && $this->smtp_ok())) {
                $this->log_write("Warning: Cannot connect to mx host ".$host."\n");
                $this->log_write("Error: ".$errstr." (".$errno.")\n");
                continue;
            }
            $this->log_write("Connected to mx host ".$host."\n");
            return TRUE;
        }
        $this->log_write("Error: Cannot connect to any mx hosts (".implode(", ", $MXHOSTS).")\n");
        return FALSE;
    }

    private function smtp_message($header, $body) {
        fputs($this->sock, $header."\r\n".$body);
        $this->smtp_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));
        return TRUE;
    }

    private function smtp_eom() {
        fputs($this->sock, "\r\n.\r\n");
        $this->smtp_debug(". [EOM]\n");
        return $this->smtp_ok();
    }

    private function smtp_ok() {
        $response = str_replace("\r\n", "", fgets($this->sock, 512));
        $this->smtp_debug($response."\n");
        if (!preg_match("/^[23]/", $response)) {
            fputs($this->sock, "QUIT\r\n");
            fgets($this->sock, 512);
            $this->log_write("Error: Remote host returned \"".$response."\"\n");
            return FALSE;
        }
        return TRUE;
    }

    private function smtp_putcmd($cmd, $arg = "") {
        if ($arg != "") {
            if($cmd=="") $cmd = $arg;
            else $cmd = $cmd." ".$arg;
        }
        fputs($this->sock, $cmd."\r\n");
        $this->smtp_debug("> ".$cmd."\n");
        return $this->smtp_ok();
    }

    private function smtp_error($string) {
        $this->log_write("Error: Error occurred while ".$string.".\n");
        return FALSE;
    }

    private function smtp_debug($message) {
        if ($this->debug) {
            echo $message;
        }
    }

    private function log_write($message) {
        if ($this->log_file == "") {
            return TRUE;
        }
        $message = date("M d H:i:s ").get_current_user()."[".getmypid()."]: ".$message;
        if (!@file_exists($this->log_file) || !($fp = @fopen($this->log_file, "a"))) {
            $this->debug = TRUE;
            $this->smtp_debug("Warning: Cannot open log file \"".$this->log_file."\"\n");
            return FALSE;
        }
        flock($fp, LOCK_EX);
        fputs($fp, $message);
        fclose($fp);
        return TRUE;
    }

    // 新しく追加された補助メソッド
    private function getAddress($address) {
        if (preg_match('/(.*)<(.+)>/', $address, $match)) {
            return $match[2];
        } else {
            return $address;
        }
    }

    private function stripComment($address) {
        $comment = "/\\([^()]*\\)/";
        while (preg_match($comment, $address)) {
            $address = preg_replace($comment, "", $address);
        }
        return $address;
    }

    // デバッグモードを設定する方法
    public function setDebug($debug) {
        $this->debug = $debug;
    }
}
?>