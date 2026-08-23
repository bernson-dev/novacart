<?php
namespace Mail;

class Mail extends \stdClass {
    public function send() {
        // Проверка обязательных полей
        if (!$this->to) return;

        $to = is_array($this->to) ? implode(',', $this->to) : $this->to;

        // Определяем перевод строки
        $eol = (version_compare(PHP_VERSION, '8.0', '>=') || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? "\r\n" : PHP_EOL;

        $boundary = '----=_NextPart_' . md5(time());

        // Заголовки
        $header  = 'MIME-Version: 1.0' . $eol;
        $header .= 'Date: ' . date('D, d M Y H:i:s O') . $eol;
        $header .= 'From: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . $eol;

        $reply_to = $this->reply_to ?: $this->from;
        $sender = $this->sender ?: $this->from;

        $header .= 'Reply-To: =?UTF-8?B?' . base64_encode($sender) . '?= <' . $reply_to . '>' . $eol;
        $header .= 'Return-Path: ' . $this->from . $eol;
        $header .= 'X-Mailer: PHP/' . PHP_VERSION . $eol;
        $header .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . $eol . $eol;

        // Тело письма
        if (!$this->html) {
            $message  = '--' . $boundary . $eol;
            $message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
            $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
            $message .= chunk_split(base64_encode($this->text)) . $eol;
        } else {
            $message  = '--' . $boundary . $eol;
            $message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $eol . $eol;
            $message .= '--' . $boundary . '_alt' . $eol;
            $message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
            $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
            $message .= chunk_split(base64_encode($this->text ?: 'This is a HTML email.')) . $eol;
            $message .= '--' . $boundary . '_alt' . $eol;
            $message .= 'Content-Type: text/html; charset="utf-8"' . $eol;
            $message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
            $message .= chunk_split(base64_encode($this->html)) . $eol;
            $message .= '--' . $boundary . '_alt--' . $eol;
        }

        // Вложения (добавлена проверка на массив)
        if (!empty($this->attachments) && is_array($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                if (file_exists($attachment)) {
                    $content = file_get_contents($attachment);

                    $message .= '--' . $boundary . $eol;
                    $message .= 'Content-Type: application/octet-stream; name="' . basename($attachment) . '"' . $eol;
                    $message .= 'Content-Transfer-Encoding: base64' . $eol;
                    $message .= 'Content-Disposition: attachment; filename="' . basename($attachment) . '"' . $eol;
                    $message .= 'Content-ID: <' . urlencode(basename($attachment)) . '>' . $eol . $eol;
                    $message .= chunk_split(base64_encode($content));
                }
            }
        }

        $message .= '--' . $boundary . '--' . $eol;

        ini_set('sendmail_from', $this->from);

        $subject = '=?UTF-8?B?' . base64_encode($this->subject) . '?=';

        if ($this->parameter) {
            return mail($to, $subject, $message, $header, $this->parameter);
        } else {
            return mail($to, $subject, $message, $header);
        }
    }
}
