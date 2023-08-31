<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property string $ptype 
 * @property string $v0 
 * @property string $v1 
 * @property string $v2 
 * @property string $v3 
 * @property string $v4 
 * @property string $v5 
 */
class CasbinRule extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'casbin_rule';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer'];
}
