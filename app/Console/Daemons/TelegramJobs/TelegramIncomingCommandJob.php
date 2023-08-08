<?php

namespace App\Console\Daemons\TelegramJobs;

use App\ClientApi\ClientTelegramSdk;
use App\Console\Daemons\RedisQueueJobs\RedisQueueMobileCheckJob;
use App\Model\Database\MobileSocialModel;
use App\Model\Database\SocialModel;
use App\Model\Database\UserMobileModel;
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
        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(__METHOD__, $this->update);

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
        $result =
            isset($this->update['message']['text'], $this->update['message']['from'], $this->update['message']['chat'])
            &&
            $this->update['message']['text'] === self::TELEGRAM_COMMAND_START;

        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(__METHOD__, ['return' => $result]);

        return $result;
    }

    protected function commandStart(array $from, array $chat): void
    {
        $result = isset($from['id'], $from['is_bot'], $from['first_name'], $from['last_name'], $from['username'], $from['language_code'])
            &&
            $from['is_bot'] === false
            &&
            isset($chat['id'], $chat['first_name'], $chat['last_name'], $chat['username'], $chat['type'])
            &&
            $chat['type'] === 'private'
            &&
            $from['id'] === $chat['id'];

        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(__METHOD__, ['return' => $result]);

        if ($result) {
            $clientTelegramSdk = new ClientTelegramSdk();

            $mobileId = MobileSocialModel::getMobileIdBySocialAbbrSocialExternalId(
                app_ext_db_global(),
                SocialModel::SOCIAL_TELEGRAM_ABBR,
                $chat['id']
            );

            $chat_id = $chat['id'];

            if (is_null($mobileId)) {
                $message = "Welcome! For add mobile number to our system, please click button `Send mobile number` on shown keyboard";
                $reply_markup = ReplyMarkupHelper::keyboardButtonRequestContact('Send mobile number');
            } else {
                $message = "Welcome!";
                $reply_markup = ReplyMarkupHelper::keyboardRemove();
            }

            app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug('SendMessage', [
                'chat_id' => $chat_id,
                'message' => $message,
            ]);

            $clientTelegramSdk->sendMessage(
                $chat_id,
                $message,
                $reply_markup
            );

        }
    }

    protected function isCommandReceivedContact(): bool
    {
        $result = isset($this->update['message']['from'], $this->update['message']['chat'], $this->update['message']['contact']);

        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(__METHOD__, ['return' => $result]);

        return $result;

    }

    protected function commandReceivedContact(array $from, array $chat, array $contact): void
    {
        $result = isset($from['id'], $from['is_bot'], $from['first_name'], $from['last_name'], $from['username'], $from['language_code'])
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
            $from['id'] === $contact['user_id'];

        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(__METHOD__, ['return' => $result]);

        if ($result) {
            $chat_id = $contact['user_id'];
            $message = sprintf('We are received mobile number [ %s ] and now we are checking them. Wait for result ...', $contact['phone_number']);

            app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug('SendMessage', [
                'chat_id' => $chat_id,
                'message' => $message,
            ]);

            $clientTelegramSdk = new ClientTelegramSdk();
            $clientTelegramSdk->sendMessage(
                $chat_id,
                $message,
                ReplyMarkupHelper::keyboardRemove()
            );

            RedisQueueMobileCheckJob::push([
                'user_id' => $contact['user_id'],
                'phone_number' => $contact['phone_number'],
                'language_code' => $from['language_code'],
            ]);
        }
    }
}