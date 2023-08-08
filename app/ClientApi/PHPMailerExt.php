<?php

namespace App\ClientApi;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use YusamHub\AppExt\Helpers\ExceptionHelper;

class PHPMailerExt extends PHPMailer
{
    /**
     * @throws Exception
     */
    public function __construct(?string $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = PHP_MAILER_CONNECTION_DEFAULT;
        }

        $config = app_ext_config('php-mailer.connections.'. $connectionName);

        $this->isSMTP();
        $this->SMTPDebug = $config['debug']??0;
        $this->Host = $config['host']??'';
        $this->Username = $config['user']??'';
        $this->Password = $config['pass']??'';
        $this->Port = $config['port']??25;
        $this->SMTPAuth = !empty($this->Username) && !empty($this->Password);
        $this->SMTPSecure = $config['secure']??'';
        $this->setFrom($config['fromAddress']??'',$config['fromName']??'');

        parent::__construct(true);
    }

    /**
     * @param string $toEmail
     * @param string $subject
     * @param string $body
     * @param string|null $altBody
     * @return bool
     */
    public static function sendTo(string $toEmail, string $subject, string $body, ?string $altBody = null): bool
    {
        try {
            $mail = new static();
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;
            $mail->Body = $body;
            if (!is_null($altBody)) {
                $mail->AltBody = $altBody;
            }
            $mail->isHTML(!is_null($altBody));
            return $mail->send();
        } catch (\Throwable $e) {
            app_ext_logger(LOGGING_CHANNEL_PHP_MAILER)
                ->error($e->getMessage(), ExceptionHelper::e2a($e));
            return false;
        }
    }
}