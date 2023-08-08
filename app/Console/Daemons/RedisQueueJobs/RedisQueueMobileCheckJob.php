<?php

namespace App\Console\Daemons\RedisQueueJobs;

use App\ClientApi\ClientTelegramSdk;
use App\Helpers\EmailMobileHelper;
use App\Model\Database\MobileModel;
use App\Model\Database\MobileSocialModel;
use App\Model\Database\SocialModel;
use YusamHub\AppExt\Helpers\ExceptionHelper;
use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\DaemonJob;

class RedisQueueMobileCheckJob extends DaemonJob
{
    const QUEUE_DEFAULT = 'default';

    /**
     * @param array $contact
     * @param string $queue
     * @return void
     */
    public static function push(array $contact, string $queue = self::QUEUE_DEFAULT): void
    {
        app_ext_logger(LOGGING_CHANNEL_TELEGRAM_DAEMON)->debug(__METHOD__, ['contact' => $contact]);

        app_ext_redis_global()->redisExt()->queuePush($queue, [
            'jobClass' => static::class,
            'jobData' => $contact,
        ]);
    }

    protected ?int $user_id = null;
    protected ?string $phone_number = null;
    protected ?string $language_code = null;

    /**
     * @param array $jobData
     */
    public function __construct(array $jobData)
    {
        foreach($jobData as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * @param Daemon $daemon
     * @return void
     */
    public function handle(Daemon $daemon): void
    {
        try {
            if (EmailMobileHelper::isMobile(
                app_ext_redis_global(),
                app_ext_logger(LOGGING_CHANNEL_REDIS_QUEUE_DAEMON),
                app_ext_db_global(),
                $this->phone_number,
                $mobilePrefix,
                $num,
                $mobilePrefixId
            )) {
                $mobileModel = MobileModel::findOrCreateMobile(app_ext_db_global(), $mobilePrefix, $num);
                if (is_null($mobileModel->verifiedAt)) {
                    $mobileModel->verifiedAt = app_ext_date();
                    $mobileModel->saveOrFail();
                }

                MobileSocialModel::findOrCreateMobileSocial(
                    app_ext_db_global(),
                    SocialModel::SOCIAL_TELEGRAM_ABBR,
                    $mobileModel->id,
                    $this->user_id
                );

                $clientTelegramSdk = new ClientTelegramSdk();
                $clientTelegramSdk->sendMessage($this->user_id, sprintf('Congratulation, the mobile number [ %s ] has been successfully added', $this->phone_number));
            } else {
                throw new \Exception(sprintf("Invalid mobile number [ %s ]", $this->phone_number));
            }
        } catch (\Throwable $e) {

            $clientTelegramSdk = new ClientTelegramSdk();
            $clientTelegramSdk->sendMessage($this->user_id, 'Sorry, some error happen in server, please try late or contact to administrator');

            app_ext_logger(LOGGING_CHANNEL_REDIS_QUEUE_DAEMON)->error($e->getMessage(), ExceptionHelper::e2a($e));
        }
    }
}