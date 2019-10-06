@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-6">
                <form method="POST" action="{{ route('channel.create') }}">
                    @csrf
                    <div class="form-group">
                        <label for="channel-key">Channel key</label>
                        <input name="channel_key" type="text" class="form-control" id="channel-key" aria-describedby="channel-key" placeholder="Enter channel key">
                    </div>
                    <div class="form-group">
                        <label for="service">Service</label>
                        <select name="service" class="form-control" id="service">
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discord-channel">Discord channel</label>
                        <input name="discord_channel" type="text" class="form-control" id="discord-channel" aria-describedby="channel-key" placeholder="Enter discord channel id">
                    </div>
                    <button type="submit" class="btn btn-light float-right mr-3">Create</button>
                </form>
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        {{ $error }} <br>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection