<?php

namespace App\Http\Controllers;

use App\Channel;
use App\DiscordChannel;
use App\Events\ChannelCreated;
use App\Events\ChannelDeleted;
use App\Service;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index()
    {
        $channels = Channel::with('service')->get();

        return view('channel.index', compact('channels'));
    }

    public function destroy(Channel $channel)
    {
        $channel->delete();

        event(new ChannelDeleted);

        return response()->json([
            'channel' => $channel,
        ]);
    }

    public function create()
    {
        $services = Service::all();

        return view('channel.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'channel_key' => 'required',
            'discord_channel' => 'required',
            'service' => 'required|exists:services,id',
        ]);

        $discordChannelId = $request->discord_channel;

        $discordChannel = DiscordChannel::where('channel_id', $discordChannelId)->first();
        if (!$discordChannel) {
            $discordChannel = DiscordChannel::create([
                'channel_id' => $discordChannelId,
            ]);
        }

        $service = Service::find($request->service);
        $channelKey = $request->channel_key;

        $channelExists = Channel::where('key', $channelKey)->whereHas('service', function($query) use ($service) {
            $query->whereTitle($service->title);
        })->whereHas('discordChannels', function($query) use ($discordChannel) {
            $query->where('id', $discordChannel->id);
        })->exists();

        if ($channelExists) {
            return back()->withErrors('Channel ' . $channelKey . ' already following');
        }

        $channel = Channel::firstOrCreate([
            'key' => $channelKey,
            'service_id' => $service->id,
        ]);
        $channel->discordChannels()->attach($discordChannel, ['service_id' => $service->id]);

        event(new ChannelCreated($channel));

        return back();
    }
}
