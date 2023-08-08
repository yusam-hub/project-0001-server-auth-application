<?php

namespace App\Console\Daemons\Jobs;

use App\ClientApi\ClientTelegramSdk;
use App\Helpers\EmailMobileHelper;
use App\Model\Database\SocialModel;
use App\Services\MobileSocialService;
use App\Services\UserRegistrationService;
use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\DaemonJob;

class TelegramMobileCheckRedisQueueJob extends DaemonJob
{
    const QUEUE_DEFAULT = 'default';

    /**
     * @param array $contact
     * @param string $queue
     * @return void
     */
    public static function push(array $contact, string $queue = self::QUEUE_DEFAULT): void
    {
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
                app_ext_db_global(),
                app_ext_logger(LOGGING_CHANNEL_REDIS_QUEUE_DAEMON),
                $this->phone_number,
                $prefix,
                $num
            )) {
                $mobileModel = UserRegistrationService::findOrCreateMobile(app_ext_db_global(), $prefix, $num);
                if (is_null($mobileModel->verifiedAt)) {
                    $mobileModel->verifiedAt = app_ext_date();
                    $mobileModel->saveOrFail();
                }

                MobileSocialService::findOrCreateMobileSocial(
                    app_ext_db_global(),
                    SocialModel::SOCIAL_TELEGRAM_ABBR,
                    $mobileModel->id,
                    $this->user_id
                );

                $clientTelegramSdk = new ClientTelegramSdk();
                $clientTelegramSdk->sendMessage($this->user_id, 'Congratulation, the mobile number has been successfully added');
            } else {
                throw new \Exception(sprintf("Invalid mobile number [ %s ]", $this->phone_number));
            }
        } catch (\Throwable $e) {

            $clientTelegramSdk = new ClientTelegramSdk();
            $clientTelegramSdk->sendMessage($this->user_id, 'Sorry, some error happen in server, please try late or contact to administrator');

            app_ext_logger(LOGGING_CHANNEL_REDIS_QUEUE_DAEMON)->error($e->getMessage(), [
                'errorCode' => $e->getCode(),
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);
        }
    }
}