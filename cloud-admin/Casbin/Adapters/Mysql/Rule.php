<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Casbin\Adapters\Mysql;

use Hyperf\DbConnection\Model\Model;

class Rule extends Model
{
    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [], string $table = 'rule')
    {
        $this->setTable($table);
        $this->timestamps = false;
        $this->fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];
        parent::__construct($attributes);
    }
}
