<?php
declare(strict_types=1);

namespace CloudAdmin\SDB;


enum CommandEnum implements \StringBackedEnum
{
    case GET = 'get';
}
