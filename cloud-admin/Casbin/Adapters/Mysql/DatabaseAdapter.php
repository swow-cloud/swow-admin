<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Casbin\Adapters\Mysql;

use Casbin\Exceptions\InvalidFilterTypeException;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Casbin\Persist\Adapters\Filter;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\UpdatableAdapter;
use Closure;
use CloudAdmin\Casbin\Event\PolicyChanged;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DatabaseAdapter implements Adapter, BatchAdapter, UpdatableAdapter, FilteredAdapter
{
    use AdapterHelper;

    /**
     * Rules eloquent model.
     */
    protected Rule $eloquent;

    /**
     * Db.
     */
    protected Db $db;

    /**
     * Db.
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * tableName.
     *
     * @var string tableName
     */
    protected string $tableName;

    private bool $filtered = false;

    private ContainerInterface $container;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container, string $tableName)
    {
        $this->tableName = $tableName;
        $this->eloquent = \Hyperf\Support\make(Rule::class, ['attributes' => [], 'table' => $this->tableName]);
        $this->db = $container->get(Db::class);
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        $this->_initTable();
    }

    /**
     * savePolicyLine function.
     */
    public function savePolicyLine(string $ptype, array $rule): array
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . $key] = $value;
        }
        return $col;
    }

    public function loadPolicy(Model $model): void
    {
        $rows = $this->eloquent::select(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])->get()->toArray();

        foreach ($rows as $row) {
            $line = implode(
                ', ',
                array_filter($row, static function ($val) {
                    return $val !== '' && ! is_null($val);
                })
            );
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $row = $this->savePolicyLine($ptype, $rule);
                $this->eloquent::create($row);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $row = $this->savePolicyLine($ptype, $rule);
                $this->eloquent::create($row);
            }
        }
        $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
    }

    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $row = $this->savePolicyLine($ptype, $rule);
        $this->eloquent::create($row);
        $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
    }

    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $query = $this->eloquent::where('ptype', $ptype);
        foreach ($rule as $key => $value) {
            $query->where('v' . $key, $value);
        }
        $query->delete();
        $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
    }

    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $query = $this->eloquent::where('ptype', $ptype);
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count(
                $fieldValues
            ) && $fieldValues[$value - $fieldIndex] !== '') {
                $query->where('v' . $value, $fieldValues[$value - $fieldIndex]);
            }
        }
        $query->delete();
        $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
    }

    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $rows = [];
        foreach ($rules as $rule) {
            $rows[] = $this->savePolicyLine($ptype, $rule);
        }
        $this->eloquent::insert($rows);
        $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
    }

    /**
     * @throws Throwable
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        $this->db::beginTransaction();
        try {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
            $this->db::commit();
            $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
        } catch (Throwable $e) {
            $this->db::rollback();
            throw $e;
        }
    }

    /**
     * @param mixed $filter
     * @throws InvalidFilterTypeException
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $query = $this->eloquent->newQuery();

        if (is_string($filter)) {
            $query->whereRaw($filter);
        } elseif ($filter instanceof Filter) {
            foreach ($filter->p as $k => $v) {
                $query->where($v, $filter->g[$k]);
            }
        } elseif ($filter instanceof Closure) {
            $query->where($filter);
        } else {
            throw new InvalidFilterTypeException('invalid filter type');
        }
        $rows = $query->get()->makeHidden(['id'])->toArray();
        foreach ($rows as $row) {
            $row = array_filter($row, static function ($value) {
                return ! is_null($value) && $value !== '';
            });
            $line = implode(', ', array_filter($row, static function ($val) {
                return $val !== '' && ! is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
        $this->setFiltered(true);
    }

    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $query = $this->eloquent::where('ptype', $ptype);
        foreach ($oldRule as $k => $v) {
            $query->where('v' . $k, $v);
        }
        $update = [];
        foreach ($newPolicy as $k => $v) {
            $update['v' . $k] = $v;
        }
        $query->update($update);
        $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
    }

    /**
     * @throws Throwable
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        $this->db::beginTransaction();
        try {
            foreach ($oldRules as $i => $oldRule) {
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }
            $this->db::commit();
            $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
        } catch (Throwable $e) {
            $this->db::rollback();
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function updateFilteredPolicies(
        string $sec,
        string $ptype,
        array $newPolicies,
        int $fieldIndex,
        string ...$fieldValues
    ): array {
        $query = $this->eloquent::where('ptype', $ptype);
        foreach (range(0, 5) as $idx) {
            if ($fieldIndex <= $idx && $idx < $fieldIndex + count($fieldValues)) {
                $value = $fieldValues[$idx - $fieldIndex];
                if ($value) {
                    $query->where('v' . $idx, $value);
                }
            }
        }
        $wheres = \Hyperf\Collection\collect($query->getQuery()->wheres);
        $wheres->shift(); // remove ptype
        $oldRules = [];
        $oldRules[] = $wheres->pluck('value')->all();
        $this->db::beginTransaction();
        try {
            $this->addPolicies($sec, $ptype, $newPolicies);
            $query->delete();
            $this->db::commit();
            $this->eventDispatcher->dispatch(new PolicyChanged(__METHOD__, func_get_args()));
            return $oldRules;
        } catch (Throwable $e) {
            $this->db::rollback();
            throw $e;
        }
    }

    /**
     * Returns true if the loaded policy has been filtered.
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * Sets filtered parameter.
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    protected function _initTable(): void
    {
        if (! Schema::hasTable($this->tableName)) {
            Schema::create($this->tableName, static function (Blueprint $table) {
                $table->increments('id');
                $table->string('ptype')->nullable();
                $table->string('v0')->nullable();
                $table->string('v1')->nullable();
                $table->string('v2')->nullable();
                $table->string('v3')->nullable();
                $table->string('v4')->nullable();
                $table->string('v5')->nullable();
            });
        }
    }
}
