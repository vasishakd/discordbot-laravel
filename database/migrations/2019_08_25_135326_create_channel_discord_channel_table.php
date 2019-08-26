<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelDiscordChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_discord_channel', function (Blueprint $table) {
            $table->unsignedBigInteger('discord_channel_id')->nullable()->index();
            $table->foreign('discord_channel_id')
                ->references('id')
                ->on('discord_channels')
                ->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');
            $table->unsignedBigInteger('channel_id')->nullable()->index();
            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->onDelete('cascade');

            $table->primary(['discord_channel_id', 'channel_id', 'service_id'], 'dis_chan_chan_serv_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_discord_channel');
    }
}
