<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\AgoraDynamicKey\RtcTokenBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMeeting;
use Illuminate\Support\Facades\Session;
use App\Models\MeetingEntry;
use App\Models\User;
use Laravel\Ui\Presets\React;

class MeetingController extends Controller
{
    public function meetingUser()
    {
        return view('createMeetingUser');
    }

    // public function createMeeting()
    // {
    //     $user = Auth::user();
    //     $meeting = UserMeeting::where('user_id', $user->id)->first();
    //     if (!isset($meeting->id)) {
    //         $data = $this->createAgora();
    //         $data = json_decode($data);
    //         if ($data->projects[0]->id) {
    //             $meeting = new UserMeeting();
    //             $meeting->user_id = Auth::id();
    //             $meeting->app_id = $data->projects[0]->vendor_key;
    //             $meeting->appCertificate = $data->projects[0]->sign_key;
    //             $meeting->channel = $data->projects[0]->name;
    //             $meeting->uid = rand(11111, 99999);
    //             $meeting->save();
    //         }
    //     }
    //     $meeting = UserMeeting::where('user_id', $user->id)->first();
    //     $token = $this->token($meeting->app_id, $meeting->appCertificate, $meeting->channel);
    //     $meeting->token =  $token;
    //     $meeting->url = $this->randomUrl();
    //     $meeting->save();
    //     if (Auth::id() == $meeting->user_id) {
    //         Session::put('meeting', $meeting->url);
    //     }
    //     return redirect('joinMeeting/' . $meeting->url);
    // }

    public function createAgora() {

        // HTTP basic authentication example in PHP using the <Vg k="VSDK" /> Server RESTful API
        // Customer ID
        $customerKey = env('AGORA_KEY');
        // Customer secret
        $customerSecret = env('AGORA_SECRET');
        // Concatenate customer key and customer secret
        $credentials = $customerKey . ":" . $customerSecret;

        // Encode with base64
        $base64Credentials = base64_encode($credentials);
        // Create authorization header
        $arr_header = "Authorization: Basic " . $base64Credentials;

        $curl = curl_init();
        // Send HTTP request
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.agora.io/dev/v1/projects',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYPEER => false,

        CURLOPT_HTTPHEADER => array(
            $arr_header,
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);


        if($response === false) {
            echo "Error in cURL : " . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }


    public function randomUrl()
    {
        $length = 7;
        $randomString = bin2hex(random_bytes($length));
        $randomString = substr($randomString, 0, $length);
        return $randomString;
    }

    // public function joinMeeting($url = '')
    // {
    //     $meeting = UserMeeting::where('url', $url)->first();
    //     if (isset($meeting->id)) {
    //         // Meeting exist
    //         $meeting->app_id = trim($meeting->app_id);
    //         $meeting->appCertificate = trim($meeting->appCertificate);
    //         $meeting->channel = trim($meeting->channel);
    //         $meeting->token = trim($meeting->token);
    //         if (Auth::id() == $meeting->user_id) {
    //             // Meeting create
    //             // dd($meeting);
    //         } else {
    //             if (!Auth::id()) {
    //                 $random_user = rand(1111, 9999);
    //                 $this->createEntry($meeting->user_id, $random_user, $meeting->url);
    //             } else {
    //                 $this->createEntry($meeting->user_id, Auth::id(), $meeting->url);
    //             }

    //         }
    //         return view('joinMeeting', get_defined_vars());
    //     } else {
    //         // .....
    //     }
    // }

    public function createEntry($user_id, $random_user, $url)
    {
        $entry = new MeetingEntry();
        $entry->user_id = $user_id;
        $entry->random_user = $random_user;
        $entry->url = $url;
        $entry->status = 0;
        $entry->save();
    }

    public function token($appID, $appCertificate, $channelName, $uid, $role)
    {
        $expireTimeInSeconds = 3600;
        $currentTimestamp = now()->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        return $token;
    }

    public function createRoom (Request $request) {
        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $channelName = 'Agora'.rand(11111, 99999);
        $idPublisher = Auth::id();
        $tokenPublisher = $this->token($appID, $appCertificate, $channelName, $idPublisher, RtcTokenBuilder::RolePublisher);

        $url =  url()->to('/publisher') . '?channel=' . $channelName. '&token='. $tokenPublisher. '&u_id='. $request->uid;
        return response()->json($url);
    }

    public function joinPublisher(Request $request) {
        $data = $request->only('channel', 'token');
        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $token = $this->token($appID, $appCertificate, $data['channel'], $request->u_id, RtcTokenBuilder::RoleSubscriber);
        $data['url'] = url()->to('/subscriber') . '?channel=' . $data['channel'] . '&token=' . $token . '&u_id=' . $request->u_id;
        return view('Publisher', ['data' => $data]);
    }

    public function joinSubscriber(Request $request) {
        $data = $request->only('channel', 'token', 'u_id');
        return view('joinSubscriber', ['data' => $data]);
    }

}
