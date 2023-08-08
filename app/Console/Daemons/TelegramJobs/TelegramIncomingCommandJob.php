<?php

namespace App\Console\Daemons\TelegramJobs;

use App\ClientApi\ClientTelegramSdk;
use App\Console\Daemons\RedisQueueJobs\RedisQueueMobileCheckJob;
use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\DaemonJob;
use YusamHub\TelegramSdk\Helpers\ReplyMarkupHelper;

class TelegramIncomingCommandJob extends DaemonJob
{
    const TELEGRAM_COMMAND_START = '/start';

    protected array $update;

    public function __construct(array $update)
    {
        $this->update = $update;
    }

    /**
     * @param Daemon $daemon
     * @return void
     */
    public function handle(Daemon $daemon): void
    {
        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(print_r($this->update, true));

        if ($this->isCommandStart()) {
            $this->commandStart($this->update['message']['from'], $this->update['message']['chat']);
            return;
        }

        if ($this->isCommandReceivedContact()) {
            $this->commandReceivedContact($this->update['message']['from'], $this->update['message']['chat'], $this->update['message']['contact']);
            return;
        }
    }

    protected function isCommandStart(): bool
    {
        return
            isset($this->update['message']['text'], $this->update['message']['from'], $this->update['message']['chat'])
            &&
            $this->update['message']['text'] === self::TELEGRAM_COMMAND_START;
    }

    protected function commandStart(array $from, array $chat): void
    {
        $clientTelegramSdk = new ClientTelegramSdk();
        if (
            isset($from['id'], $from['is_bot'], $from['first_name'], $from['last_name'], $from['username'], $from['language_code'])
            &&
            $from['is_bot'] === false
            &&
            isset($chat['id'], $chat['first_name'], $chat['last_name'], $chat['username'], $chat['type'])
            &&
            $chat['type'] === 'private'
            &&
            $from['id'] === $chat['id']
        ) {
            $clientTelegramSdk->sendMessage(
                $chat['id'],
                'Hello ' . $chat['first_name'],
                ReplyMarkupHelper::keyboardButtonRequestContact('Register mobile number')
            );
        }
    }

    protected function isCommandReceivedContact(): bool
    {
        return isset($this->update['message']['from'], $this->update['message']['chat'], $this->update['message']['contact']);
    }

    protected function commandReceivedContact(array $from, array $chat, array $contact): void
    {
        $clientTelegramSdk = new ClientTelegramSdk();
        if (
            isset($from['id'], $from['is_bot'], $from['first_name'], $from['last_name'], $from['username'], $from['language_code'])
            &&
            $from['is_bot'] === false
            &&
            isset($chat['id'], $chat['first_name'], $chat['last_name'], $chat['username'], $chat['type'])
            &&
            $chat['type'] === 'private'
            &&
            $from['id'] === $chat['id']
            &&
            isset($contact['phone_number'], $contact['first_name'], $contact['last_name'], $contact['user_id'])
            &&
            $from['id'] === $contact['user_id']
        ) {
            RedisQueueMobileCheckJob::push([
                'user_id' => $contact['user_id'],
                'phone_number' => $contact['phone_number'],
                'language_code' => $from['language_code'],
            ]);

            $clientTelegramSdk->sendMessage(
                $contact['user_id'],
                sprintf('We are received mobile number [ %s ] and now we are checking them. Wait for result ...', $contact['phone_number']),
                ReplyMarkupHelper::keyboardRemove()
            );
        }
    }
}