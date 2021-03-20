<?php declare(strict_types=1);

/*
 * This file is part of the VV package.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VV;

/**
 * Class Notice
 *
 * @package VV
 */
final class Notice {

    const INFO = 1,
        WARNING = 2,
        ERROR = 3;

    const C_ERROR = 1,
        C_WARNING = 300,
        C_INFO = 700;

    const EMAIL_SUBJ_MAX_LEN = 256;

    const MTD_LOG = 1,
        MTD_MAIL = 2,
        MTD_SYSLOG = 4,
        MTD_SMS = 8;

    private string $message;
    private ?int $code;
    private ?int $status;

    private ?string $subject = null;
    private ?string $subjectPfx = null;
    private ?\Throwable $exception = null;
    private string|int|null $logId = null;
    private array $data = [];
    private ?string $hash = null;

    private ?int $repeatTimeout = null;
    private ?int $repeatTimeoutBkp = null;
    private array $methodTimeoutLocks = [];
    private array $methodTimeoutLocksBkp = [];
    private int $calledMethods = 0;

    private ?Notice\Config $config = null;

    public function __construct(string $message, int $code = null, int $status = null) {
        $this->setMessage($message)->setCode($code)->setStatus($status);
    }

    //region PROPERTY G/SETTERS

    /**
     * @return string
     */
    public function message(): string {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message): self {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function code(): int {
        if (!$this->code) {
            if ($status = $this->status) {
                if ($status == self::ERROR) {
                    $this->code = self::C_ERROR;
                } elseif ($status == self::WARNING) {
                    $this->code = self::C_WARNING;
                }
            }
            if (!$this->code) {
                $this->code = self::C_INFO;
            }
        }

        return $this->code;
    }

    public function hasCode(): bool {
        return (bool)$this->code;
    }

    /**
     * @param int|null $code
     *
     * @return $this
     */
    public function setCode(?int $code): self {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int
     */
    public function status(): int {
        if (!$this->status) $this->status = self::statusByCode($this->code);

        return $this->status;
    }

    public function hasStatus(): bool {
        return (bool)$this->status;
    }

    /**
     * @param int|null $status
     *
     * @return $this
     */
    public function setStatus(?int $status): self {
        $this->status = $status;

        return $this;
    }

    /**
     * @return $this
     */
    public function asInfo(): self {
        return $this->setStatus(self::INFO);
    }

    /**
     * @return $this
     */
    public function asWarning(): self {
        return $this->setStatus(self::WARNING);
    }

    /**
     * @return $this
     */
    public function asError(): self {
        return $this->setStatus(self::ERROR);
    }

    /**
     * @return \Throwable
     */
    public function exception(): \Throwable {
        if (!$this->exception) $this->exception = new \Exception($this->message(), $this->code());

        return $this->exception;
    }

    public function setException(?\Throwable $exception): self {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @return array
     */
    public function data(): array {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data): self {
        $this->data = $data;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function replaceData(array $data): self {
        $this->data = array_replace($this->data, $data);

        return $this;
    }

    /**
     * Returns repeat timeout
     *
     * @return int|null
     */
    public function repeatTimeout(): ?int {
        return $this->repeatTimeout;
    }

    /**
     * Sets repeat timeout
     *
     * @param int|null $timeout
     *
     * @return $this
     */
    public function setRepeatTimeout(?int $timeout): self {
        $this->repeatTimeout = $timeout;
        $this->methodTimeoutLocks = [];

        return $this;
    }

    /**
     * Returns hash
     *
     * @return mixed
     */
    public function hash(): string {
        if (!$this->hash) $this->hash = $this->genDfltHash();

        return $this->hash;
    }

    /**
     * @param null|string $hash
     *
     * @return $this
     */
    public function setHash(?string $hash): self {
        $this->hash = $hash;

        return $this;
    }

    public function logId(): int|string|null {
        return $this->logId;
    }

    public function forcedLogId(): int|string|null {
        if (!$this->logId) $this->force()->log()->unforce();

        return $this->logId;
    }

    /**
     * @return $this
     */
    public function force(): self {
        $this->methodTimeoutLocksBkp = $this->methodTimeoutLocks;
        $this->repeatTimeoutBkp = $this->repeatTimeout();

        return $this->setRepeatTimeout(0);
    }

    /**
     * @return $this
     */
    public function unforce(): self {
        $this->setRepeatTimeout($this->repeatTimeoutBkp);
        $this->methodTimeoutLocks = $this->methodTimeoutLocksBkp ?: [];

        return $this;
    }

    /**
     * @return string
     */
    public function subject(): string {
        if (!$this->subject) {
            $this->subject = $this->subjectPfx() . ': ' . strip_tags($this->message());
        }

        return $this->subject;
    }

    /**
     * @return string
     */
    public function subjectPfx(): string {
        if (!$this->subjectPfx) {
            $this->subjectPfx = ucfirst(self::statusTitle($this->status())) . " [{$this->code()}]";
        }

        return $this->subjectPfx;
    }

    /**
     * @return bool
     */
    public function isInfo(): bool {
        return $this->status() == self::INFO;
    }

    /**
     * @return bool
     */
    public function isWarning(): bool {
        return $this->status() == self::WARNING;
    }

    /**
     * @return bool
     */
    public function isError(): bool {
        return $this->status() == self::ERROR;
    }

    /**
     * @return bool
     */
    public function isWarnOrErr(): bool {
        return $this->isWarning() || $this->isError();
    }

    /**
     * @return Notice\Config
     */
    public function config(): Notice\Config {
        if (!$this->config) $this->config = self::factory()->config(); // default config from default factory

        return $this->config;
    }

    /**
     * @param Notice\Config|null $config
     *
     * @return $this
     */
    public function setConfig(?Notice\Config $config): self {
        $this->config = $config;

        return $this;
    }
    //endregion

    //region NOTICER RUN METHODS
    /**
     * Notices by log, mail, sms, syslog
     *
     * @return Notice
     */
    public function all(): self {
        return $this->logMail()->syslog()->sms();
    }

    /**
     * Notices by log and mail
     *
     * @return Notice
     */
    public function logMail(): self {
        return $this->log()->mail();
    }

    /**
     * Runs all logger
     *
     * @return Notice
     */
    public function log(): self {
        $senders = &$this->config()->loggers();
        if ($this->isMethodLocked(self::MTD_LOG, $senders)) return $this;

        try {
            // run loggers
            $this->forEachNoticer($senders,
                function (Notice\Logger $logger) {
                    $logId = $logger->log($this);
                    if ($this->logId === null) $this->logId = $logId ?: 0;
                }
            );
        } catch (\Throwable $e) {
            self::fromException($e)->all();
        }

        return $this;
    }

    /**
     * @param string $message
     * @param string $subject
     *
     * @return Notice
     */
    public function mail($message = null, $subject = null): self {
        $senders = &$this->config()->mailers();
        if ($this->isMethodLocked(self::MTD_MAIL, $senders)) return $this;

        try {
            // defend from recursion - off all mailers
            $mailers = $senders;
            $senders = null;

            if (!$message) $message = $this->message();
            if (!$subject) {
                $subjLen = mb_strlen($subject = $this->subject());
                if ($subjLen > self::EMAIL_SUBJ_MAX_LEN) {
                    $subject = mb_substr($subject, 0, self::EMAIL_SUBJ_MAX_LEN);
                }
            }

            // defend from recursion - restore all mailers
            $senders = $mailers;

            $this->forEachNoticer($senders,
                function (Notice\Mailer $mailer) use ($subject, $message) {
                    $mailer->sendMail($this, $subject, $message);
                }
            );
        } catch (\Throwable $e) {
            self::fromException($e)->all();
        }

        return $this;
    }

    /**
     * @return Notice
     */
    public function syslog(): self {
        $senders = &$this->config()->sysloggers();
        if ($this->isMethodLocked(self::MTD_SYSLOG, $senders)) return $this;

        $this->forEachNoticer($senders,
            function (Notice\Syslogger $syslogger) {
                $syslogger->syslog($this);
            }
        );

        return $this;
    }

    /**
     * @return Notice
     */
    public function sms(): self {
        $senders = &$this->config()->smsers();
        if ($this->isMethodLocked(self::MTD_SMS, $senders)) return $this;

        $this->forEachNoticer($senders,
            function (Notice\Smser $smser) {
                $smser->sendSms($this);
            }
        );

        return $this;
    }

    //endregion

    /**
     * @param int   $method
     * @param array $senders
     *
     * @return bool
     */
    protected function isMethodLocked(int $method, array $senders): bool {
        if (!$senders) return true;
        if ($this->isMethodLockedByTimeout($method)) return true;

        if ($method & $this->calledMethods) return true;
        $this->calledMethods |= $method;

        return false;
    }

    /**
     * @param int $method
     *
     * @return bool
     */
    protected function isMethodLockedByTimeout(int $method): bool {
        $locked = &$this->methodTimeoutLocks[$method];
        if ($locked === null) {
            $repeatTimeout = $this->repeatTimeout();
            if ($repeatTimeout === null) {
                $repeatTimeout = $this->repeatTimeoutForMethod($method);
            }

            $cache = $this->config()->cache();
            if (!$repeatTimeout || !$cache) {
                $locked = false;
            } else {
                $lockName = 'notice-' . $method . '-' . $this->hash();
                if ($cache->get($lockName) === null) {
                    $cache->set($lockName, 1, $repeatTimeout);
                    $locked = false;
                } else {
                    $locked = true;
                }
            }
        }

        return $locked;
    }

    protected function forEachNoticer(array &$noticers, \Closure $callback) {
        foreach ($noticers as $k => $noticer) {
            try {
                // defend from recursion - disable current logger
                unset($noticers[$k]);

                $callback($noticer);

                // defend from recursion - enable current logger
                $noticers[$k] = $noticer;
            } catch (\Throwable $e) {
                self::fromException($e)->all();
            }
        }
    }

    /**
     * @param int $method
     *
     * @return int|null
     */
    protected function repeatTimeoutForMethod(int $method): ?int {
        return match ($method) {
            self::MTD_LOG => $this->config()->logRepeatTimeout(),
            self::MTD_MAIL => $this->config()->mailRepeatTimeout(),
            self::MTD_SYSLOG => $this->config()->syslogRepeatTimeout(),
            self::MTD_SMS => $this->config()->smsRepeatTimeout(),
            default => null,
        };
    }

    /**
     * @return string
     */
    protected function genDfltHash(): string {
        $tomd5 = $this->code() . '|' . $this->status();

        $exc = $this->exception();
        while ($exc) {
            $tomd5 .= '|' . $exc->getMessage();
            $exc = $exc->getPrevious();
        }

        return md5($tomd5);
    }

    //region Factories

    /**
     * @return Notice\Factory
     */
    public static function factory(): Notice\Factory {
        static $factory;
        if (!$factory) $factory = new Notice\Factory;

        return $factory;
    }

    /**
     * @return \VV\Notice\Factory
     */
    public static function createFactory(): Notice\Factory {
        return (new Notice\Factory)->setConfig(clone self::factory()->config());
    }

    public static function create(string $message, int $code = null, int $status = null): Notice {
        return self::factory()->create($message, $code, $status);
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return Notice
     */
    public static function info(string $message, int $code = null): Notice {
        return self::factory()->info($message, $code);
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return Notice
     */
    public static function warning(string $message, int $code = null): Notice {
        return self::factory()->warning($message, $code);
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return Notice
     */
    public static function error(string $message, int $code = null): Notice {
        return self::factory()->error($message, $code);
    }

    /**
     * @param \Throwable $e
     * @param int|null   $status
     * @param int|null   $code
     *
     * @return Notice
     */
    public static function fromException(\Throwable $e, int $status = null, int $code = null): Notice {
        return self::factory()->fromException($e, $status, $code);
    }
    //endregion

    //region UTILS
    public static function statusByCode($code): int {
        $c = $code % 1000;
        if (!$code || $c >= 700) return self::INFO;
        if ($c >= 300) return self::WARNING;

        return self::ERROR;
    }

    public static function statusTitle($status): string {
        static $map = [
            self::INFO => 'information',
            self::WARNING => 'warning',
            self::ERROR => 'error',
        ];

        return $map[$status] ?? '';
    }

    /**
     * @param $what
     * @param $instead
     */
    public static function deprecate($what, $instead) {
        $args = [&$what, &$instead];
        foreach ($args as &$v) {
            if (is_array($v)) {
                if (is_object($v[0])) $v[0] = get_class($v[0]);
                $v = implode('::', $v);
            }
        }

        self::warning("!!! Deprecated usage of <b>$what</b>. Use <b>$instead</b> instead.")
            ->setRepeatTimeout(3600)
            ->mail();
    }
    //endregion
}
