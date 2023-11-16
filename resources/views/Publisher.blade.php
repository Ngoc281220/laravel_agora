<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>video Stream</title>
    <link rel="stylesheet" type="text/css" media="screen" href="{{asset('agoraVideo/main.css')}}">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"  crossorigin="anonymous"></script>

</head>
<body>
    @php
        $uid = Auth::id() ?? null;
        $appID = env('AGORA_APP_ID');
    @endphp
    <input type="text" id="linkname" value="{{$data['url']}}">
    <button id="join-btn2" onclick="joinStream()">Join Stream</button>
    <button id="join-btns">Copy link</button>
    <!-- Meeting Instance -->
    <div id="stream-wrapper" style="height: 100%; display:block">
        <div id="video-streams"></div>

        <div id="stream-controls">
            <button id="leave-btn">Leave Stream</button>
            <button id="mic-btn">Mic On</button>
            <button id="camera-btn">Camera on</button>
            <button id="inItScreen">share</button>

        </div>
    </div>
    <input type="hidden" type="hidden" id="uid" value="{{$uid}}">
    <input type="hidden" type="hidden" id="appid" value="{{$appID}}">
    <input type="hidden" type="hidden" id="channel" value="{{$data['channel']}}">
    <input type="hidden" type="hidden" id="token" value="{{$data['token']}}">
</body>
<script src="{{asset('agoraVideo/AgoraRTC_N-4.19.3.js')}}" ></script>
<script src="{{asset('agoraVideo/main.js')}}" ></script>
