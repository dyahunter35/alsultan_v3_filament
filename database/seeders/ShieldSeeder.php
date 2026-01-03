<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_category","view_any_category","create_category","update_category","restore_category","restore_any_category","replicate_category","reorder_category","delete_category","delete_any_category","force_delete_category","force_delete_any_category","view_customer","view_any_customer","create_customer","update_customer","restore_customer","restore_any_customer","replicate_customer","reorder_customer","delete_customer","delete_any_customer","force_delete_customer","force_delete_any_customer","view_order","view_any_order","create_order","update_order","restore_order","restore_any_order","replicate_order","reorder_order","delete_order","delete_any_order","force_delete_order","force_delete_any_order","view_product","view_any_product","create_product","update_product","restore_product","restore_any_product","replicate_product","reorder_product","delete_product","delete_any_product","force_delete_product","force_delete_any_product","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_stock::history","view_any_stock::history","create_stock::history","update_stock::history","restore_stock::history","restore_any_stock::history","replicate_stock::history","reorder_stock::history","delete_stock::history","delete_any_stock::history","force_delete_stock::history","force_delete_any_stock::history","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","page_MainDashboard","page_Setting","page_BranchReport","page_ProductStockReport","ViewAny:Branch","View:Branch","Create:Branch","Update:Branch","Delete:Branch","Restore:Branch","ForceDelete:Branch","ForceDeleteAny:Branch","RestoreAny:Branch","Replicate:Branch","Reorder:Branch","ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Customer","View:Customer","Create:Customer","Update:Customer","Delete:Customer","Restore:Customer","ForceDelete:Customer","ForceDeleteAny:Customer","RestoreAny:Customer","Replicate:Customer","Reorder:Customer","ViewAny:Order","View:Order","Create:Order","Update:Order","Delete:Order","Restore:Order","ForceDelete:Order","ForceDeleteAny:Order","RestoreAny:Order","Replicate:Order","Reorder:Order","ViewAny:Product","View:Product","Create:Product","Update:Product","Delete:Product","Restore:Product","ForceDelete:Product","ForceDeleteAny:Product","RestoreAny:Product","Replicate:Product","Reorder:Product","ViewAny:StockHistory","View:StockHistory","Create:StockHistory","Update:StockHistory","Delete:StockHistory","Restore:StockHistory","ForceDelete:StockHistory","ForceDeleteAny:StockHistory","RestoreAny:StockHistory","Replicate:StockHistory","Reorder:StockHistory","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","View:MainDashboard","View:Setting","View:BranchReport","View:ProductStockReport","test_custom_permission"]},{"name":"sales","guard_name":"web","permissions":[]}]';
        $directPermissions = '{"175":{"name":"ViewAny:Company","guard_name":"web"},"176":{"name":"View:Company","guard_name":"web"},"177":{"name":"Create:Company","guard_name":"web"},"178":{"name":"Update:Company","guard_name":"web"},"179":{"name":"Delete:Company","guard_name":"web"},"180":{"name":"Restore:Company","guard_name":"web"},"181":{"name":"ForceDelete:Company","guard_name":"web"},"182":{"name":"ForceDeleteAny:Company","guard_name":"web"},"183":{"name":"RestoreAny:Company","guard_name":"web"},"184":{"name":"Replicate:Company","guard_name":"web"},"185":{"name":"Reorder:Company","guard_name":"web"},"186":{"name":"ViewAny:Contract","guard_name":"web"},"187":{"name":"View:Contract","guard_name":"web"},"188":{"name":"Create:Contract","guard_name":"web"},"189":{"name":"Update:Contract","guard_name":"web"},"190":{"name":"Delete:Contract","guard_name":"web"},"191":{"name":"Restore:Contract","guard_name":"web"},"192":{"name":"ForceDelete:Contract","guard_name":"web"},"193":{"name":"ForceDeleteAny:Contract","guard_name":"web"},"194":{"name":"RestoreAny:Contract","guard_name":"web"},"195":{"name":"Replicate:Contract","guard_name":"web"},"196":{"name":"Reorder:Contract","guard_name":"web"},"197":{"name":"ViewAny:Expense","guard_name":"web"},"198":{"name":"View:Expense","guard_name":"web"},"199":{"name":"Create:Expense","guard_name":"web"},"200":{"name":"Update:Expense","guard_name":"web"},"201":{"name":"Delete:Expense","guard_name":"web"},"202":{"name":"Restore:Expense","guard_name":"web"},"203":{"name":"ForceDelete:Expense","guard_name":"web"},"204":{"name":"ForceDeleteAny:Expense","guard_name":"web"},"205":{"name":"RestoreAny:Expense","guard_name":"web"},"206":{"name":"Replicate:Expense","guard_name":"web"},"207":{"name":"Reorder:Expense","guard_name":"web"},"208":{"name":"ViewAny:Port","guard_name":"web"},"209":{"name":"View:Port","guard_name":"web"},"210":{"name":"Create:Port","guard_name":"web"},"211":{"name":"Update:Port","guard_name":"web"},"212":{"name":"Delete:Port","guard_name":"web"},"213":{"name":"Restore:Port","guard_name":"web"},"214":{"name":"ForceDelete:Port","guard_name":"web"},"215":{"name":"ForceDeleteAny:Port","guard_name":"web"},"216":{"name":"RestoreAny:Port","guard_name":"web"},"217":{"name":"Replicate:Port","guard_name":"web"},"218":{"name":"Reorder:Port","guard_name":"web"},"219":{"name":"ViewAny:Truck","guard_name":"web"},"220":{"name":"View:Truck","guard_name":"web"},"221":{"name":"Create:Truck","guard_name":"web"},"222":{"name":"Update:Truck","guard_name":"web"},"223":{"name":"Delete:Truck","guard_name":"web"},"224":{"name":"Restore:Truck","guard_name":"web"},"225":{"name":"ForceDelete:Truck","guard_name":"web"},"226":{"name":"ForceDeleteAny:Truck","guard_name":"web"},"227":{"name":"RestoreAny:Truck","guard_name":"web"},"228":{"name":"Replicate:Truck","guard_name":"web"},"229":{"name":"Reorder:Truck","guard_name":"web"},"230":{"name":"View:CompaniesReport","guard_name":"web"},"231":{"name":"View:ExpensesList","guard_name":"web"},"232":{"name":"View:StoreExpense","guard_name":"web"}}';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
