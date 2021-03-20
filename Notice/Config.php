<?php declare(strict_types=1);

/*
 * This file is part of the VV package.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VV\Notice;

use VV\Cache\Cache;

/**
 * Class Config
 *
 * @package VV\Notice
 */
class Config {

    /** @var Logger[] */
    private array $loggers = [];
    /** @var Mailer[] */
    private array $mailers = [];
    /** @var Syslogger[] */
    private array $sysloggers = [];
    /** @var Smser[] */
    private array $smsers = [];

    private ?int $mailRepeatTimeout = null;
    private ?int $logRepeatTimeout = null;
    private ?int $smsRepeatTimeout = null;
    private ?int $syslogRepeatTimeout = null;

    private ?Cache $cache = null;

    /**
     * @return Mailer[]
     */
    public function &mailers(): array {
        return $this->mailers;
    }

    /**
     * @return Smser[]
     */
    public function &smsers(): array {
        return $this->smsers;
    }

    /**
     * @return Logger[]
     */
    public function &loggers(): array {
        return $this->loggers;
    }

    /**
     * @return Syslogger[]
     */
    public function &sysloggers(): array {
        return $this->sysloggers;
    }

    public function addAllNoticer(AllNoticer $er): Config {
        return $this->addLogger($er)
            ->addMailer($er)
            ->addSmser($er)
            ->addSyslogger($er);
    }

    public function addLogger(Logger $logger): static {
        $this->loggers[] = $logger;

        return $this;
    }

    public function addMailer(Mailer $mailer): static {
        $this->mailers[] = $mailer;

        return $this;
    }

    public function addSmser(Smser $smser): static {
        $this->smsers[] = $smser;

        return $this;
    }

    public function addSyslogger(Syslogger $syslogger): static {
        $this->sysloggers[] = $syslogger;

        return $this;
    }

    /**
     * @return int|null
     */
    public function mailRepeatTimeout(): ?int {
        return $this->mailRepeatTimeout;
    }

    /**
     * @param int|null $timeout
     *
     * @return $this
     */
    public function setMailRepeatTimeout(?int $timeout): static {
        $this->mailRepeatTimeout = $timeout;

        return $this;
    }

    /**
     * @return int|null
     */
    public function logRepeatTimeout(): ?int {
        return $this->logRepeatTimeout;
    }

    /**
     * @param int|null $timeout
     *
     * @return $this
     */
    public function setLogRepeatTimeout(?int $timeout): static {
        $this->logRepeatTimeout = $timeout;

        return $this;
    }

    /**
     * @return int|null
     */
    public function smsRepeatTimeout(): ?int {
        return $this->smsRepeatTimeout;
    }

    /**
     * @param int|null $timeout
     *
     * @return $this
     */
    public function setSmsRepeatTimeout(?int $timeout): static {
        $this->smsRepeatTimeout = $timeout;

        return $this;
    }

    /**
     * @return int|null
     */
    public function syslogRepeatTimeout(): ?int {
        return $this->syslogRepeatTimeout;
    }

    /**
     * @param int|null $timeout
     *
     * @return $this
     */
    public function setSyslogRepeatTimeout(?int $timeout): static {
        $this->syslogRepeatTimeout = $timeout;

        return $this;
    }

    /**
     * @return Cache
     */
    public function cache(): Cache {
        return $this->cache;
    }

    /**
     * @param Cache|null $cache
     *
     * @return $this
     */
    public function setCache(?Cache $cache): static {
        $this->cache = $cache;

        return $this;
    }
}
