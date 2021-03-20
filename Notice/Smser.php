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
 * Interface Smser
 *
 * @package VV\Notice
 */
interface Smser {

    public function sendSms(\VV\Notice $notice): void;
}
