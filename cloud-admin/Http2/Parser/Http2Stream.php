<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Http2\Parser;

final class Http2Stream
{
    public const OPEN = 0;

    public const RESERVED = 0b0001;

    public const REMOTE_CLOSED = 0b0010;

    public const LOCAL_CLOSED = 0b0100;

    public const CLOSED = 0b0110;

    /** @var int Current max body length. */
    public int $maxBodySize;

    /** 已经收到的最大数据长度  @var int Bytes received on the stream. */
    public int $received = 0;

    public mixed $pendingResponse;

    public string $buffer = '';

    /** @var int Integer between 1 and 256 */
    public int $weight = 0;

    public int $dependency = 0;

    /** 预期还需要接收的长度 @var int|null */
    public ?int $expectedLength = null;

    public function __construct(/** 服务端数据流上面的窗口大小 @var int */
    public int $serverWindow, /** @var int 当前流客户端还剩余的串口大小 */
    public int $clientWindow, /** 客户端的流状态  @var int */
    public int $state = self::OPEN)
    {
        $this->maxBodySize = $serverWindow;
    }
}
