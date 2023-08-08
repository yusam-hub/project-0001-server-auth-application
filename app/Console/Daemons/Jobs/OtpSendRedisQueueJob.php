<?php

namespace App\Console\Daemons\Jobs;

use App\ClientApi\ClientTelegramSdk;
use App\ClientApi\PHPMailerExt;
use App\Model\Database\MobileSocialModel;
use App\Model\Database\SocialModel;
use App\Services\MobileSocialService;
use App\Services\UserRegistrationService;
use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\DaemonJob;

class OtpSendRedisQueueJob extends DaemonJob
{
    const QUEUE_DEFAULT = 'default';

    /**
     * @param string $emailOrMobile
     * @param string $otp
     * @param string $queue
     * @return void
     */
    public static function push(string $emailOrMobile, string $otp, string $queue = self::QUEUE_DEFAULT): void
    {
        app_ext_redis_global()->redisExt()->queuePush($queue, [
            'jobClass' => static::class,
            'jobData' => [
                'emailOrMobile' => $emailOrMobile,
                'otp' => $otp,
            ],
        ]);
    }

    protected ?string $emailOrMobile = null;
    protected ?string $otp = null;


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
     * @throws \Exception
     */
    public function handle(Daemon $daemon): void
    {
        $message = 'OTP: ' . $this->otp;

        $type = UserRegistrationService::getRegistrationType(
            app_ext_redis_global(),
            app_ext_db_global(),
            app_ext_logger(LOGGING_CHANNEL_REDIS_QUEUE_DAEMON),
            $this->emailOrMobile,
            $mobilePrefix,
            $num);

        if ($type === UserRegistrationService::REGISTRATION_BY_EMAIL) {

            PHPMailerExt::sendTo($this->emailOrMobile, $message, $message);

        } elseif($type === UserRegistrationService::REGISTRATION_BY_MOBILE) {

            $socialExternalId = MobileSocialModel::findMobileSocialAsSocialExternalId(
                app_ext_db_global(),
                SocialModel::SOCIAL_TELEGRAM_ABBR,
                $mobilePrefix,
                $num
            );

            if (!is_null($socialExternalId)) {
                $clientTelegramSdk = new ClientTelegramSdk();
                $clientTelegramSdk->sendMessage($socialExternalId, $message);
            } else {
                throw new \Exception(sprintf("Mobile number is not registered [ %s ]", $this->emailOrMobile));
            }

        } else {
            throw new \Exception(sprintf("Invalid type [ %s ]", $this->emailOrMobile));
        }
    }
}