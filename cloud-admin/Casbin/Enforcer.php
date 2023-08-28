<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Casbin;

use Casbin\Enforcer as BaseEnforcer;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Enforcer.
 * @method static bool enforce(...$rvals)
 * @method static array getRolesForUser(string $name, string ...$domain)
 * @method static array getUsersForRole(string $name, string ...$domain)
 * @method static bool hasRoleForUser(string $name, string $role, string ...$domain)
 * @method static bool addRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRolesForUser(string $user, string ...$domain)
 * @method static bool deleteUser(string $user)
 * @method static bool deleteRole(string $role)
 * @method static bool deletePermission(string ...$permission)
 * @method static bool addPermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionsForUser(string $user)
 * @method static array getPermissionsForUser(string $user)
 * @method static bool hasPermissionForUser(string $user, string ...$permission)
 * @method static array getImplicitRolesForUser(string $name, string ...$domain)
 * @method static array getImplicitPermissionsForUser(string $user, string ...$domain)
 * @method static array getImplicitUsersForPermission(string ...$permission)
 * @method static array getUsersForRoleInDomain(string $name, string $domain)
 * @method static array getRolesForUserInDomain(string $name, string $domain)
 * @method static array getPermissionsForUserInDomain(string $name, string $domain)
 * @method static bool addRoleForUserInDomain(string $user, string $role, string $domain)
 * @method static bool deleteRoleForUserInDomain(string $user, string $role, string $domain)
 */
class Enforcer
{
    public function __construct(public ContainerInterface $container)
    {
    }

    public function __call($method, $parameters)
    {
        try {
            return $this
                ->container
                ->get(BaseEnforcer::class)
                ->{$method}(...$parameters);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
    }

    /**
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return ApplicationContext::getContainer()
            ->get(BaseEnforcer::class)
            ->{$method}(...$parameters);
    }
}
