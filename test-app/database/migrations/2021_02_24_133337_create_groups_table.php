<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias');
            $table->string('type');
            $table->foreignId('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('groupables', function(Blueprint $table) {
            $table->foreignIdFor(Group::class);
            $table->morphs('groupable');
            $table->integer('priority')->default(99);
            $table->boolean('visiable')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('groupables');
    }
}
