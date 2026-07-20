<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Regression guard for the Backpack 3 -> 6 upgrade: the admin models and
 * controllers pull in Backpack traits/base classes whose namespaces moved
 * (e.g. Backpack\CRUD\CrudTrait -> Backpack\CRUD\app\Models\Traits\CrudTrait,
 * Backpack\Base\... removed). Those paths only load on /admin/* routes, so the
 * HTTP smoke tests miss them. Autoloading each class here resolves its traits
 * and fails loudly if a namespace regresses.
 */
class AdminClassesLoadTest extends TestCase
{
    /**
     * @dataProvider adminClasses
     */
    public function testAdminClassLoads(string $class)
    {
        $this->assertTrue(class_exists($class), "$class should autoload (Backpack traits must resolve)");
    }

    public static function adminClasses(): array
    {
        return [
            [\App\Models\Icon::class],
            [\App\Models\Log::class],
            [\App\Models\BackpackUser::class],
            [\App\Http\Controllers\Auth\MyAccountController::class],
            [\App\Http\Controllers\Admin\IconCrudController::class],
            [\App\Http\Controllers\Admin\LogCrudController::class],
            [\App\Http\Controllers\Admin\UserCrudController::class],
        ];
    }
}
