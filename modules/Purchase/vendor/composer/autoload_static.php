<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc6d874c8ba1b153a1e3b61cd6e3eb584
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Modules\\Purchase\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Modules\\Purchase\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseDatabaseSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseDatabaseSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseProcessesTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseProcessesTableSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseRoleAndPermissionsTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseRoleAndPermissionsTableSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseSupplierBranchesTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseSupplierBranchesTableSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseSupplierObjectsTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseSupplierObjectsTableSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseSupplierSpecialtiesTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseSupplierSpecialtiesTableSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseSupplierTypesTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseSupplierTypesTableSeeder.php',
        'Modules\\Purchase\\Database\\Seeders\\PurchaseTypeOperationsTableSeeder' => __DIR__ . '/../..' . '/Database/Seeders/PurchaseTypeOperationsTableSeeder.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseBaseBudgetController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseBaseBudgetController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseBudgetaryAvailabilityController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseBudgetaryAvailabilityController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseDirectHireController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseDirectHireController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseOrderController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseOrderController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchasePlanController' => __DIR__ . '/../..' . '/Http/Controllers/PurchasePlanController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseProcessController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseProcessController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseQuotationController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseQuotationController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseRequirementController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseRequirementController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseSettingController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseSettingController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseSupplierBranchController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseSupplierBranchController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseSupplierController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseSupplierController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseSupplierObjectController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseSupplierObjectController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseSupplierSpecialtyController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseSupplierSpecialtyController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseSupplierTypeController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseSupplierTypeController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseTypeController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseTypeController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseTypeHiringController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseTypeHiringController.php',
        'Modules\\Purchase\\Http\\Controllers\\PurchaseTypeOperationController' => __DIR__ . '/../..' . '/Http/Controllers/PurchaseTypeOperationController.php',
        'Modules\\Purchase\\Http\\Controllers\\Reports\\Base\\ReportRepository' => __DIR__ . '/../..' . '/Http/Controllers/Reports/Base/ReportRepository.php',
        'Modules\\Purchase\\Http\\Controllers\\Reports\\DirectHire\\PurchaseOrderController' => __DIR__ . '/../..' . '/Http/Controllers/Reports/DirectHire/PurchaseOrderController.php',
        'Modules\\Purchase\\Http\\Controllers\\Reports\\DirectHire\\PurchaseStartCertificateController' => __DIR__ . '/../..' . '/Http/Controllers/Reports/DirectHire/PurchaseStartCertificateController.php',
        'Modules\\Purchase\\Http\\Controllers\\Reports\\PurchaseBaseBudgetController' => __DIR__ . '/../..' . '/Http/Controllers/Reports/PurchaseBaseBudgetController.php',
        'Modules\\Purchase\\Http\\Controllers\\Reports\\PurchaseRequirementController' => __DIR__ . '/../..' . '/Http/Controllers/Reports/PurchaseRequirementController.php',
        'Modules\\Purchase\\Jobs\\PurchaseManageBaseBudget' => __DIR__ . '/../..' . '/Jobs/PurchaseManageBaseBudget.php',
        'Modules\\Purchase\\Jobs\\PurchaseManageRequirements' => __DIR__ . '/../..' . '/Jobs/PurchaseManageRequirements.php',
        'Modules\\Purchase\\Models\\BudgetCompromise' => __DIR__ . '/../..' . '/Models/BudgetCompromise.php',
        'Modules\\Purchase\\Models\\BudgetCompromiseDetail' => __DIR__ . '/../..' . '/Models/BudgetCompromiseDetail.php',
        'Modules\\Purchase\\Models\\BudgetStage' => __DIR__ . '/../..' . '/Models/BudgetStage.php',
        'Modules\\Purchase\\Models\\City' => __DIR__ . '/../..' . '/Models/City.php',
        'Modules\\Purchase\\Models\\Country' => __DIR__ . '/../..' . '/Models/Country.php',
        'Modules\\Purchase\\Models\\Currency' => __DIR__ . '/../..' . '/Models/Currency.php',
        'Modules\\Purchase\\Models\\Department' => __DIR__ . '/../..' . '/Models/Department.php',
        'Modules\\Purchase\\Models\\Document' => __DIR__ . '/../..' . '/Models/Document.php',
        'Modules\\Purchase\\Models\\DocumentStatus' => __DIR__ . '/../..' . '/Models/DocumentStatus.php',
        'Modules\\Purchase\\Models\\Estate' => __DIR__ . '/../..' . '/Models/Estate.php',
        'Modules\\Purchase\\Models\\FiscalYear' => __DIR__ . '/../..' . '/Models/FiscalYear.php',
        'Modules\\Purchase\\Models\\HistoryTax' => __DIR__ . '/../..' . '/Models/HistoryTax.php',
        'Modules\\Purchase\\Models\\Institution' => __DIR__ . '/../..' . '/Models/Institution.php',
        'Modules\\Purchase\\Models\\Pivot' => __DIR__ . '/../..' . '/Models/Pivot.php',
        'Modules\\Purchase\\Models\\Profile' => __DIR__ . '/../..' . '/Models/Profile.php',
        'Modules\\Purchase\\Models\\PurchaseBaseBudget' => __DIR__ . '/../..' . '/Models/PurchaseBaseBudget.php',
        'Modules\\Purchase\\Models\\PurchaseBudgetaryAvailability' => __DIR__ . '/../..' . '/Models/PurchaseBudgetaryAvailability.php',
        'Modules\\Purchase\\Models\\PurchaseCompromise' => __DIR__ . '/../..' . '/Models/PurchaseCompromise.php',
        'Modules\\Purchase\\Models\\PurchaseCompromiseDetail' => __DIR__ . '/../..' . '/Models/PurchaseCompromiseDetail.php',
        'Modules\\Purchase\\Models\\PurchaseDirectHire' => __DIR__ . '/../..' . '/Models/PurchaseDirectHire.php',
        'Modules\\Purchase\\Models\\PurchaseDocumentRequiredDocument' => __DIR__ . '/../..' . '/Models/PurchaseDocumentRequiredDocument.php',
        'Modules\\Purchase\\Models\\PurchaseOrder' => __DIR__ . '/../..' . '/Models/PurchaseOrder.php',
        'Modules\\Purchase\\Models\\PurchasePivotModelsToRequirementItem' => __DIR__ . '/../..' . '/Models/PurchasePivotModelsToRequirementItem.php',
        'Modules\\Purchase\\Models\\PurchasePlan' => __DIR__ . '/../..' . '/Models/PurchasePlan.php',
        'Modules\\Purchase\\Models\\PurchaseProcess' => __DIR__ . '/../..' . '/Models/PurchaseProcess.php',
        'Modules\\Purchase\\Models\\PurchaseQuotation' => __DIR__ . '/../..' . '/Models/PurchaseQuotation.php',
        'Modules\\Purchase\\Models\\PurchaseRequirement' => __DIR__ . '/../..' . '/Models/PurchaseRequirement.php',
        'Modules\\Purchase\\Models\\PurchaseRequirementItem' => __DIR__ . '/../..' . '/Models/PurchaseRequirementItem.php',
        'Modules\\Purchase\\Models\\PurchaseStates' => __DIR__ . '/../..' . '/Models/PurchaseStates.php',
        'Modules\\Purchase\\Models\\PurchaseSupplier' => __DIR__ . '/../..' . '/Models/PurchaseSupplier.php',
        'Modules\\Purchase\\Models\\PurchaseSupplierBranch' => __DIR__ . '/../..' . '/Models/PurchaseSupplierBranch.php',
        'Modules\\Purchase\\Models\\PurchaseSupplierObject' => __DIR__ . '/../..' . '/Models/PurchaseSupplierObject.php',
        'Modules\\Purchase\\Models\\PurchaseSupplierSpecialty' => __DIR__ . '/../..' . '/Models/PurchaseSupplierSpecialty.php',
        'Modules\\Purchase\\Models\\PurchaseSupplierType' => __DIR__ . '/../..' . '/Models/PurchaseSupplierType.php',
        'Modules\\Purchase\\Models\\PurchaseType' => __DIR__ . '/../..' . '/Models/PurchaseType.php',
        'Modules\\Purchase\\Models\\PurchaseTypeHiring' => __DIR__ . '/../..' . '/Models/PurchaseTypeHiring.php',
        'Modules\\Purchase\\Models\\PurchaseTypeOperation' => __DIR__ . '/../..' . '/Models/PurchaseTypeOperation.php',
        'Modules\\Purchase\\Models\\RequiredDocument' => __DIR__ . '/../..' . '/Models/RequiredDocument.php',
        'Modules\\Purchase\\Models\\Tax' => __DIR__ . '/../..' . '/Models/Tax.php',
        'Modules\\Purchase\\Models\\TaxUnit' => __DIR__ . '/../..' . '/Models/TaxUnit.php',
        'Modules\\Purchase\\Models\\User' => __DIR__ . '/../..' . '/Models/User.php',
        'Modules\\Purchase\\Providers\\PurchaseServiceProvider' => __DIR__ . '/../..' . '/Providers/PurchaseServiceProvider.php',
        'Modules\\Purchase\\Providers\\RouteServiceProvider' => __DIR__ . '/../..' . '/Providers/RouteServiceProvider.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc6d874c8ba1b153a1e3b61cd6e3eb584::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc6d874c8ba1b153a1e3b61cd6e3eb584::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc6d874c8ba1b153a1e3b61cd6e3eb584::$classMap;

        }, null, ClassLoader::class);
    }
}
