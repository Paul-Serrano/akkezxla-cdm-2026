<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('role')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // Migrate existing single-role data into the pivot
        $users = DB::table('user')->select('id', 'role')->get();
        foreach ($users as $user) {
            $roleRow = DB::table('role')->where('name', $user->role)->first();
            if ($roleRow) {
                DB::table('role_user')->insertOrIgnore([
                    'user_id' => $user->id,
                    'role_id' => $roleRow->id,
                ]);
            }
        }

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('role')->default('regular')->after('alias');
        });

        // Restore primary role from pivot (take any one role per user)
        $pivotRows = DB::table('role_user')
            ->join('role', 'role.id', '=', 'role_user.role_id')
            ->select('role_user.user_id', 'role.name')
            ->get();

        foreach ($pivotRows as $row) {
            DB::table('user')->where('id', $row->user_id)->update(['role' => $row->name]);
        }

        Schema::dropIfExists('role_user');
    }
};
