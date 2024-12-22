<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateSlugFormatToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = DB::table('permissions')->get();

        foreach ($permissions as $permission) {
            $newSlug = Str::slug($permission->slug, config('roles.separator'));
            $permData = DB::table('permissions')->where('slug', $newSlug)->first();

            if ($permData) {
                continue;
            }
            DB::table('permissions')
                ->where('id', $permission->id)
                ->update(['slug' => $newSlug]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
