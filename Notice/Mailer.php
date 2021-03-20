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

/**
 * Interface Mailer
 *
 * @package VV\Notice
 */
interface Mailer {

    public function sendMail(\VV\Notice $notice, string $subject, string $message): void;
}
