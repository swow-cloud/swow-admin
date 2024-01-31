<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\SDB;

use CloudAdmin\SDB\Command\PsHandler;
use Exception;

enum CommandEnum: string implements CommandEnumInterface
{
    case PS = 'ps';
    case ATTACH = 'attach';
    case CO = 'co';
    case COROUTINE = 'coroutine';
    case BT = 'bt';
    case BACKTRACE = 'backtrace';
    case F = 'f';
    case FRAME = 'frame';
    case B = 'b';
    case BREAKPOINT = 'breakpoint';
    case N = 'n';
    case NEXT = 'next';
    case S = 's';
    case STEP = 'step';
    case STEP_IN = 'step_in';
    case C = 'c';
    case CONTINUE = 'continue';
    case L = 'l';
    case LIST = 'list';
    case P = 'p';
    case PRINT = 'print';
    case EXEC = 'exec';
    case VARS = 'vars';
    case Z = 'z';
    case ZOMBIE = 'zombie';
    case ZOMBIES = 'zombies';
    case KILL = 'kill';
    case KILLALL = 'killall';
    case CLEAR = 'clear';
    case POOL = 'pool';
    case CONFIG = 'config';
    case ROUTE = 'route';
    case PING = 'ping';

    public function handler(): string
    {
        return match ($this) {
            self::PS => PsHandler::class,
            self::ATTACH => throw new Exception('To be implemented'),
            self::CO => throw new Exception('To be implemented'),
            self::COROUTINE => throw new Exception('To be implemented'),
            self::BT => throw new Exception('To be implemented'),
            self::BACKTRACE => throw new Exception('To be implemented'),
            self::F => throw new Exception('To be implemented'),
            self::FRAME => throw new Exception('To be implemented'),
            self::B => throw new Exception('To be implemented'),
            self::BREAKPOINT => throw new Exception('To be implemented'),
            self::N => throw new Exception('To be implemented'),
            self::NEXT => throw new Exception('To be implemented'),
            self::S => throw new Exception('To be implemented'),
            self::STEP => throw new Exception('To be implemented'),
            self::STEP_IN => throw new Exception('To be implemented'),
            self::C => throw new Exception('To be implemented'),
            self::CONTINUE => throw new Exception('To be implemented'),
            self::L => throw new Exception('To be implemented'),
            self::LIST => throw new Exception('To be implemented'),
            self::P => throw new Exception('To be implemented'),
            self::PRINT => throw new Exception('To be implemented'),
            self::EXEC => throw new Exception('To be implemented'),
            self::VARS => throw new Exception('To be implemented'),
            self::Z => throw new Exception('To be implemented'),
            self::ZOMBIE => throw new Exception('To be implemented'),
            self::ZOMBIES => throw new Exception('To be implemented'),
            self::KILL => throw new Exception('To be implemented'),
            self::KILLALL => throw new Exception('To be implemented'),
            self::CLEAR => throw new Exception('To be implemented'),
            self::POOL => throw new Exception('To be implemented'),
            self::CONFIG => throw new Exception('To be implemented'),
            self::ROUTE => throw new Exception('To be implemented'),
            self::PING => throw new Exception('To be implemented'),
        };
    }
}
