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

use VV\Notice;

/**
 * Class Factory
 *
 * @package VV\Notice
 */
class Factory {

    private ?Config $config = null;
    /** @var Decorator[] */
    private array $decorators = [];

    /**
     * @return Config
     */
    public function config(): Config {
        if (!$this->config) $this->config = new Config;

        return $this->config;
    }

    /**
     * @param Config|null $config
     *
     * @return $this
     */
    public function setConfig(?Config $config): static {
        $this->config = $config;

        return $this;
    }

    /**
     * @param Decorator $decorator
     *
     * @return $this
     */
    public function addDecorator(Decorator $decorator): static {
        $this->decorators[] = $decorator;

        return $this;
    }

    /**
     * @param string   $message
     * @param int|null $code
     * @param int|null $status
     *
     * @return Notice
     */
    public function create(string $message, int $code = null, int $status = null): Notice {
        $notice = (new Notice($message, $code, $status))->setConfig($this->config());
        foreach ($this->decorators as $decorator) {
            $decorator->decorateNotice($notice);
        }

        return $notice;
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return Notice
     */
    public function info(string $message, int $code = null): Notice {
        return $this->create($message, $code, Notice::INFO);
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return Notice
     */
    public function warning(string $message, int $code = null): Notice {
        return $this->create($message, $code, Notice::WARNING);
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return Notice
     */
    public function error(string $message, int $code = null): Notice {
        return $this->create($message, $code, Notice::ERROR);
    }

    /**
     * @param \Throwable $e
     * @param int|null   $status
     * @param int|null   $code
     *
     * @return Notice
     */
    public function fromException(\Throwable $e, int $status = null, int $code = null): Notice {
        $message = (string)$e->getMessage() ?: get_class($e);

        if (!$code) {
            $code = $e->getCode();
            if ($code && !is_int($code)) {
                $message = "[notintcode:$code] $message";
                $code = null;
            }
        }
        if (!$status) $status = Notice::ERROR;

        return $this->create($message, $code, $status)->setException($e);
    }
}
