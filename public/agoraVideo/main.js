const APP_ID = $('#appid').val();
const TOKEN = $('#token').val();
const CHANNEL = $('#channel').val();
const uid = $('#uid').val();
const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' })

let localTracks = []
let remoteUsers = {}


let joinAndDisplayLocalStream = async () => {


    console.log({
        APP_ID, TOKEN, CHANNEL, uid, client
    })

    let UID = await client.join(APP_ID, CHANNEL, TOKEN, uid);
    client.on('user-published', handleUserJoined)

    client.on('user-left', handleUserLeft)

    client.on('stream-added', handleStreamAdd);

    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks()

    let player = `<div class="video-container" id="user-container-${UID}">
                        <div class="video-player" id="user-${UID}"></div>
                  </div>`
    document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

    localTracks[1].play(`user-${UID}`)

    await client.publish([localTracks[0], localTracks[1]])
}


let handleStreamAdd = async (evt) => {
    let stream = evt.stream;
    console.log('Stream Added: ', stream);
}

let joinStream = async () => {

    try {
        await joinAndDisplayLocalStream();
        document.getElementById('stream-controls').style.display = 'flex';
        // document.getElementById('join-btn').style.display = 'none'
        // document.getElementById('stream-controls').style.display = 'flex'
        $('#timer').val(1);
        RecordTime();
    }
    catch (err) {

        // alert(err.message);
        //   alert('Link has been expired please contact host provider');
        return;
    }
}


let handleUserJoined = async (user, mediaType) => {
    remoteUsers[user.uid] = user;
    console.log({user, mediaType});
    await client.subscribe(user, mediaType)

    if (mediaType === 'video') {
        let player = document.getElementById(`user-container-${user.uid}`)
        if (player != null) {
            player.remove()
        }

        player = `<div class="video-container" id="user-container-${user.uid}">
                        <div class="video-player" id="user-${user.uid}"></div>
                 </div>`
        document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

        user.videoTrack.play(`user-${user.uid}`)
    }

    if (mediaType === 'audio') {
        user.audioTrack.play()
    }
}


// function addVideoPlayer(uid) {
//     const playerContainer = document.createElement('div');
//     playerContainer.id = `player-container-${uid}`;
//     document.getElementById('video-streams').appendChild(playerContainer);

//     const playerElement = document.createElement('div');
//     playerElement.id = `user-${uid}`;
//     playerContainer.appendChild(playerElement);

//     return `user-${uid}`;
// }

let handleUserLeft = async (user) => {
    delete remoteUsers[user.uid]
    document.getElementById(`user-container-${user.uid}`).remove()
}

let leaveAndRemoveLocalStream = async () => {
    for (let i = 0; localTracks.length > i; i++) {
        localTracks[i].stop()
        localTracks[i].close()
    }

    await client.leave()
    document.getElementById('join-btn').style.display = 'block'
    document.getElementById('stream-controls').style.display = 'none'
    document.getElementById('video-streams').innerHTML = ''
    document.getElementById('dataTable').style.display = 'block'
}

let toggleMic = async (e) => {
    if (localTracks[0].muted) {
        await localTracks[0].setMuted(false)
        e.target.innerText = 'Mic on'
        e.target.style.backgroundColor = 'cadetblue'
    } else {
        await localTracks[0].setMuted(true)
        e.target.innerText = 'Mic off'
        e.target.style.backgroundColor = '#EE4B2B'
    }
}

let toggleCamera = async (e) => {
    if (localTracks[1].muted) {
        await localTracks[1].setMuted(false)
        e.target.innerText = 'Camera on'
        e.target.style.backgroundColor = 'cadetblue'
    } else {
        await localTracks[1].setMuted(true)
        e.target.innerText = 'Camera off'
        e.target.style.backgroundColor = '#EE4B2B'
    }
}

let togglerecording = async (e) => {
    if ($('#rec_user').val() == 0) {
        await (startRecording());
        e.target.innerText = 'Rec on'
        e.target.style.backgroundColor = 'cadetblue'
        $('#rec_user').val(1);
    } else {
        var meetingId = $('#user_meeting').val();
        if (meetingId == 0) {
            alert('Please Wait while we are fetching recording id....');
            return;
        }
        await (stopRecordingFromResource());
        e.target.innerText = 'Rec off'
        e.target.style.backgroundColor = '#EE4B2B'
        $('#rec_user').val(0);
    }
}

let inItScreen = async () => {
    let isSharingEnabled  = false;
    let channelParameters = {
        screenTrack: null,
        localVideoTrack: null
    };
    if (isSharingEnabled == false) {
         // Create a screen track for screen sharing.
         channelParameters.screenTrack = await AgoraRTC.createScreenVideoTrack();
         // Replace the video track with the screen track.
         await channelParameters.localVideoTrack.replaceTrack(channelParameters.screenTrack, true);
         // Update the button text.
         document.getElementById(`inItScreen`).innerHTML = "Stop Sharing";
         // Update the screen sharing state.
         isSharingEnabled = true;
    } else {
        // Replace the screen track with the local video track.
        await channelParameters.screenTrack.replaceTrack(channelParameters.localVideoTrack, true);
        // Update the button text.
        document.getElementById(`inItScreen`).innerHTML = "Share Screen";
        // Update the screen sharing state.
        isSharingEnabled = false;
    }
}

// document.getElementById('join-btn').addEventListener('click', joinStream)
// document.getElementById('leave-btn').addEventListener('click', leaveAndRemoveLocalStream)
// document.getElementById('mic-btn').addEventListener('click', toggleMic)
// document.getElementById('camera-btn').addEventListener('click', toggleCamera)
// document.getElementById('rec-btn').addEventListener('click', togglerecording)
