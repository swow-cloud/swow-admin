<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Casbin\Exceptions;

use Hyperf\HttpMessage\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(403, 'This action is unauthorized.');
    }
}
