<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Dailymotion;
use App\Services\DailymotionApiException;

class DmApiController extends Controller
{
    private $dmApiKey;
    private $dmApiSecret;
    private $dmUser;
    private $dmPassword;

    public function __construct() {
        $this->dmApiKey = env('DM_API_KEY_PRODUCTION', '');
        $this->dmApiSecret = env('DM_API_SECRET_PRODUCTION', '');
        $this->dmUser = env('DM_USER', '');
        $this->dmPassword = env('DM_PASSWORD', '');
    }

    public function get($endpoint, Request $request) {

        // Scopes needed
        $scopes = array(
            'userinfo',
            'feed',
            'manage_videos',
            'userinfo'
        );

        // Dailymotion object
        $api = new Dailymotion();

        // Setup credential
        $api->setGrantType(
            Dailymotion::GRANT_TYPE_PASSWORD,
            $this->dmApiKey,
            $this->dmApiSecret,
            $scopes,
            array(
                'username' => $this->dmUser,
                'password' => $this->dmPassword,
            )
        );

        // Get url parameters
        $query = $request->all();

        $params = array(
            'fields' => array('id','private','private_id', 'title', 'description', 'owner.avatar_190_url'),
            'sort' => array_key_exists('sort', $query) ? $query['sort'] : 'recent',
            'limit' => 1,
            'flags' => 'no_live,exportable',
            'private' => 1,
        );

        // Optional parameters
        if (array_key_exists('search', $query)) $params['search'] = $query['search'];
        if (array_key_exists('channel', $query)) $params['channel'] = $query['channel'];
        if (array_key_exists('exclude_ids', $query)) $params['exclude_ids'] = $query['exclude_ids'];
        if (array_key_exists('language', $query)) $params['language'] = $query['language'];

        try
        {
            $results = $api->get(
                '/user/'. $query['owners'] . '/videos',
                $params
            );
            return response()->json($results, 200);
        }
        catch ( DailymotionApiException $e)
        {
            // Handle the situation when the user refused to authorize and came back here.
            // <YOUR CODE>
            return response()->json($e, 500);
        }

    }
}
