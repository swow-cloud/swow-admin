<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Profiler\DataAnalysis;

use Hyperf\Stringable\Str;
use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function array_shift;
use function count;
use function current;
use function explode;
use function in_array;
use function uasort;
use function usort;

class Profile
{
    public const NO_PARENT = '__top__';

    protected array $collapsed;

    protected array $indexed;

    protected array $visited;

    protected array $keys = ['ct', 'wt', 'cpu', 'mu', 'pmu'];

    protected array $exclusiveKeys = ['ewt', 'ecpu', 'emu', 'epmu'];

    private array $nodes;

    public function __construct($profile)
    {
        $result = [];
        foreach ($profile as $name => $values) {
            [$parent, $func] = $this->splitName($name);
            if (isset($result[$func])) {
                $result[$func] = $this->_sumKeys($result[$func], $values);
                $result[$func]['p'][] = $parent;
            } else {
                foreach ($this->keys as $v) {
                    $result[$func][$v] = $values[$v];
                    $result[$func]['e' . $v] = $values[$v];
                }
                $result[$func]['p'] = [$parent];
            }
            // Build the indexed data.
            if ($parent === null) {
                $parent = self::NO_PARENT;
            }
            if (! isset($this->indexed[$parent])) {
                $this->indexed[$parent] = [];
            }
            $this->indexed[$parent][$func] = $values;
        }
        $this->collapsed = $result;
    }

    public function splitName($name): array
    {
        $a = explode('==>', $name);
        return isset($a[1]) ? $a : [null, $a[0]];
    }

    public function getProfileBySort(): array
    {
        $arr = [];
        foreach ($this->collapsed as $k => $val) {
            $arr[] = [
                'id' => $k,
                'ct' => $val['ct'],
                'ecpu' => $val['ecpu'],
                'ewt' => $val['wt'] - $val['ewt'],
                'emu' => $val['mu'] - $val['emu'],
                'epmu' => $val['pmu'] - $val['epmu'],
            ];
        }
        usort($arr, function ($a, $b) {
            return $a['ewt'] > $b['ewt'] ? -1 : 1;
        });
        return $arr;
    }

    /**
     * Generate the approximate exclusive values for each metric.
     *
     * We get a==>b as the name, we need a key for a and b in the array
     * to get exclusive values for A we need to subtract the values of B (and any other children);
     * call passing in the entire profile only, should return an array of
     * functions with their regular timing, and exclusive numbers inside ['exclusive']
     *
     * Consider:
     *              /---c---d---e
     *          a -/----b---d---e
     *
     * We have c==>d and b==>d, and in both instances d invokes e, yet we will
     * have but a single d==>e result. This is a known and documented limitation of XHProf
     *
     * We have one d==>e entry, with some values, including ct=2
     * We also have c==>d and b==>d
     *
     * We should determine how many ==>d options there are, and equally
     * split the cost of d==>e across them since d==>e represents the sum total of all calls.
     *
     * Notes:
     *  Function names are not unique, but we're merging them
     *
     * @return self a new instance with exclusive data set
     */
    public function calculateSelf(): self
    {
        // Init exclusive values
        foreach ($this->collapsed as &$data) {
            $data['ewt'] = $data['wt'];
            $data['emu'] = $data['mu'];
            $data['ecpu'] = $data['cpu'];
            $data['ect'] = $data['ct'];
            $data['epmu'] = $data['pmu'];
        }
        unset($data);

        // Go over each method and remove each childs metrics
        // from the parent.
        foreach ($this->collapsed as $name => $data) {
            $children = $this->_getChildren($name);
            foreach ($children as $child) {
                $this->collapsed[$name]['ewt'] -= $child['wt'];
                $this->collapsed[$name]['emu'] -= $child['mu'];
                $this->collapsed[$name]['ecpu'] -= $child['cpu'];
                $this->collapsed[$name]['ect'] -= $child['ct'];
                $this->collapsed[$name]['epmu'] -= $child['pmu'];
            }
        }
        return $this;
    }

    /**
     * Sort data by a dimension.
     *
     * @param string $dimension the dimension to sort by
     * @param array $data the data to sort
     * @return array the sorted data
     */
    public function sort(string $dimension, array $data): array
    {
        $sorter = function ($a, $b) use ($dimension) {
            if ($a[$dimension] == $b[$dimension]) {
                return 0;
            }
            return $a[$dimension] > $b[$dimension] ? -1 : 1;
        };
        uasort($data, $sorter);
        return $data;
    }

    /**
     * Return a structured array suitable for generating flamegraph visualizations.
     *
     * Functions whose inclusive time is less than 1% of the total time will
     * be excluded from the callgraph data.
     *
     * @param mixed|string $metric
     * @param float|mixed $threshold
     */
    public function getFlamegraph(string $metric = 'wt', float $threshold = 0.01): array
    {
        $valid = array_merge($this->keys, $this->exclusiveKeys);
        if (! in_array($metric, $valid)) {
            throw new InvalidArgumentException("Unknown metric '{$metric}'. Cannot generate flamegraph.");
        }
        $this->calculateSelf();

        // Non exclusive metrics are always main() because it is the root call scope.
        if (in_array($metric, $this->exclusiveKeys)) {
            $main = $this->_maxValue($metric);
        } else {
            $main = $this->collapsed['main()'][$metric];
        }

        $this->visited = $this->nodes = $links = [];
        $flamegraph = $this->_flamegraphData(self::NO_PARENT, $main, $metric, $threshold);
        return ['data' => array_shift($flamegraph), 'sort' => $this->visited];
    }

    /**
     * Sum up the values in $this->keys;.
     *
     * @param array $a The first set of profile data
     * @param array $b the second set of profile data
     * @return array merged profile data
     */
    protected function _sumKeys(array $a, array $b): array
    {
        foreach ($this->keys as $key) {
            if (! isset($a[$key])) {
                $a[$key] = 0;
            }
            $a[$key] += $b[$key] ?? 0;
        }
        return $a;
    }

    /**
     * Get the parent methods for a given symbol.
     *
     * @param string $symbol the name of the function/method to find
     *                       parents for
     * @return array List of parents
     */
    protected function _getParents(string $symbol): array
    {
        $parents = [];
        $current = $this->collapsed[$symbol];
        foreach ($current['parents'] as $parent) {
            if (isset($this->collapsed[$parent])) {
                $parents[] = ['function' => $parent] + $this->collapsed[$parent];
            }
        }
        return $parents;
    }

    /**
     * Find symbols that are the children of the given name.
     *
     * @param string $symbol the name of the function to find children of
     * @param null|string $metric the metric to compare $threshold with
     * @param float|int $threshold The threshold to exclude functions at. Any
     *                             function that represents less than
     * @return array an array of child methods
     */
    protected function _getChildren(string $symbol, string $metric = null, float|int $threshold = 0): array
    {
        $children = [];
        if (! isset($this->indexed[$symbol])) {
            return $children;
        }

        $total = 0;
        if (isset($metric)) {
            $top = $this->indexed[self::NO_PARENT];
            // Not always 'main()'
            $mainFunc = current($top);
            $total = $mainFunc[$metric];
        }

        foreach ($this->indexed[$symbol] as $name => $data) {
            if (
                $metric && $total > 0 && $threshold > 0
                && ($this->collapsed[$name][$metric] / $total) < $threshold
            ) {
                continue;
            }
            $children[] = $data + ['function' => $name];
        }
        return $children;
    }

    /**
     * Get the max value for any give metric.
     *
     * @param string $metric the metric to get a max value for
     */
    protected function _maxValue(string $metric): int
    {
        return array_reduce(
            $this->collapsed,
            function ($result, $item) use ($metric) {
                if ($item[$metric] > $result) {
                    return $item[$metric];
                }
                return $result;
            },
            0
        );
    }

    protected function _flamegraphData($parentName, $main, $metric, $threshold): array
    {
        $result = [];
        // Leaves don't have children, and don't have links/nodes to add.
        if (! isset($this->indexed[$parentName])) {
            return $result;
        }

        $children = $this->indexed[$parentName];
        foreach ($children as $childName => $metrics) {
            $metrics = $this->collapsed[$childName];
            if ($metrics[$metric] / $main <= $threshold) {
                continue;
            }
            $current = [
                'name' => $childName,
                'id' => Str::random(18),
                'value' => $metrics[$metric],
            ];
            $revisit = false;

            // Keep track of which nodes we've visited and their position
            // in the node list.
            if (! isset($this->visited[$childName])) {
                $index = count($this->nodes);
                $this->visited[$childName] = $index;
                $this->nodes[] = $current;
            } else {
                $revisit = true;
                $index = $this->visited[$childName];
            }

            // If the current function has more children,
            // walk that call subgraph.
            if (isset($this->indexed[$childName]) && ! $revisit) {
                $grandChildren = $this->_flamegraphData($childName, $main, $metric, $threshold, $index);
                if (! empty($grandChildren)) {
                    $current['children'] = $grandChildren;
                }
            }

            $result[] = $current;
        }
        return $result;
    }
}
