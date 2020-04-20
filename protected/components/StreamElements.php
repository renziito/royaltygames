<?php

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Created by PhpStorm.
 * User: Lx
 * Date: 19.06.2018
 * Time: 18:00
 */
class StreamElements
{
    /**
     * URL to SE API
     * @var string
     */
    protected $url = 'https://api.streamelements.com/kappa/v2';

    /**
     * oAuth2 handler
     * @var string
     */
    protected $provider;

    /**
     * Auth type
     * @var string
     */
    protected $auth;

    /**
     * Token
     * @var string
     */
    protected $token;


    /**
     * API limits
     * @var string
     */
    protected $apiLimits;
    /**
     * CURL Options array
     * @var array
     */
    protected $options;
    /**
     * @var \GuzzleHttp\Client
     */
    protected $api;
    /**
     *
     * @var string
     */
    public $channelName;
    /**
     * Profile information received on handshake
     * @var object
     */
    public $profile;
    /**
     * Channel id received on handshake
     * @var int
     */
    public $channelId;
    /**
     * bot data
     * @var array
     */
    public $botInfo;
    /**
     * List of bot commands
     * @var array
     */
    public $botCommands;
    /**
     * List of bot modules
     * @var array
     */
    public $botModules;
    /**
     * Array of channel overlays
     * @var array
     */
    public $overlays;
    /**
     * array of uploaded assets
     * @var array
     */
    public $uploadedFiles;
    /**
     * @var
     */
    public $files;
    /**
     * tips data
     * @var array
     */
    public $tips;

    /**
     * StreamElements constructor.
     * @param $token - OAuth/JWT Token
     * @param $auth - Authorization method (Bearer,OAuth)
     * @return boolean
     * @throws Exception
     */
    public function __construct($token, $auth)
    {
        $this->api = new GuzzleHttp\Client();
        $this->auth = $auth;
        $this->options = array(
            'headers' => array(
                'Accept' => 'application/json',
                "Authorization" => $auth . " " . $token
            ),
            "debug" => false,
            'verify' => false);
        if (!$this->getInfo()) {
            throw new Exception("Invalid credentials");
        }
    }

    /**
     *
     */
    public function __destruct()
    {

    }

    /**
     * @param $method
     * @param $endpoint
     * @param array $params
     * @param bool $isUpload
     * @return bool|mixed
     */
    protected function sendRequest($method, $endpoint, $params = array(), $isUpload = false)
    {

        if (is_array($this->apiLimits['global'])) {
            if ($this->apiLimits['global']['reset'] > new DateTime() && !$this->apiLimits['global']['remaining']) return false;
        }

        $url = $this->url . '/' . $endpoint;
        $options = $this->options;
        if (is_array($params) && $method != 'GET') {
            $options['json'] = $params;
        } else if (is_array($params)) {
            foreach ($params as $param => $value) {
                $query = parse_url($url, PHP_URL_QUERY);
                if (is_array($value)) {
                    foreach ($value as $id => $nestedVal) {
                        if ($query) {
                            $url .= '&' . $param . '=' . urldecode($nestedVal);
                        } else {
                            $url .= '?' . $param . '=' . urldecode($nestedVal);
                        }
                    }
                } else {
                    if ($query) {
                        $url .= '&' . $param . '=' . urldecode($value);
                    } else {
                        $url .= '?' . $param . '=' . urldecode($value);
                    }
                }
            }

        }
        if ($isUpload) {
            $options['multipart'][] = $params;
            unset($options['json']);
        }

        $res = array();
        try {
            if ($method == 'GET') {
                $res = $this->api->get($url, $this->options);
            } else if ($method == 'POST') {

                $res = $this->api->post($url, $options);

            } else if ($method == 'PUT') {
                $res = $this->api->put($url, $options);
            } else if ($method == 'DELETE') {
                $res = $this->api->delete($url, $options);
            }
            $headers = $res->getHeaders();
            $scope = $headers['x-ratelimit-bucket'][0];
            $this->apiLimits[$scope] = [
                'limit' => $headers['x-ratelimit-limit'][0],
                'remaining' => $headers['x-ratelimit-remaining'][0],
                'reset' => new DateTime()
            ];
            $this->apiLimits[$scope]['reset']->setTimestamp((int)($headers['x-ratelimit-reset'][0] / 1000));

        } catch (GuzzleHttp\Exception\ClientException $e) {

            if ($e->hasResponse()) {

                return (json_decode($e->getResponse()->getBody()->getContents(), 1));
            }


            return false;
        }
        //var_dump($res);
        //die();
        $response = json_decode($res->getBody()->getContents(), true);

        return $response;
    }

    /**
     * @return bool
     */
    public function getInfo()
    {

        $endpoint = 'channels/me';
        try {
            $res = $this->sendRequest('GET', $endpoint);
        } catch (Exception $e) {
            echo($e->getMessage());
            $this->__destruct();
            return false;
        }
        $this->profile = $res;
        if (!isset($this->profile["username"]) || !isset($this->profile["_id"])) return false;
        $this->channelName = $this->profile["username"];
        $this->channelId = $this->profile["_id"];
        return true;
    }

    /**
     * @param string $channelId
     * @return bool|mixed
     */
    public function channels($channelId = '')
    {
        if (!strlen($channelId)) $channelId = 'me';
        $endpoint = 'channels/' . $channelId;
        return $this->sendRequest('GET', $endpoint, $channelId);
    }

    /**
     * @param string $channelName
     * @return array
     */
    public function chatStats($channelName = '')
    {
        if (!strlen($channelName)) $channelName = $this->channelId;
        $url = 'chatstats/' . $channelName;
        $res = $this->sendRequest('GET', $url);
        return $res;
    }

// BOT

    /**
     * Gather bot information
     * @return bool|mixed
     */
    public function botInit()
    {
        $url = 'bot/' . $this->channelId;
        $res = $this->sendRequest('GET', $url);
        $this->botInfo = $res['bot'];
        return $res;
    }

    /**
     * Make bot join channel
     * @return bool|mixed
     */
    public function botJoin()
    {
        if (!$this->botInfo) $this->botInit();
        if ($this->botInfo['joined']) return true;
        $url = 'bot/' . $this->channelId . '/join';
        $res = $this->sendRequest('POST', $url);
        return $res;
    }

    /**
     * Tells bot to leave channel
     * @return bool|mixed
     */
    public function botPart()
    {
        if (!$this->botInfo) $this->botInit();
        if (!$this->botInfo['joined']) return true;
        $url = 'bot/' . $this->channelId . '/part';
        $res = $this->sendRequest('POST', $url);
        return $res;
    }

    /**
     * Mutes the bot if it is not muted already
     * @return bool|mixed
     */
    public function botMute()
    {
        if (!$this->botInfo) $this->botInit();
        if ($this->botInfo['muted']) return true;
        $url = 'bot/' . $this->channelId . '/mute';
        $res = $this->sendRequest('POST', $url);
        return $res;
    }

    /**
     * Mutes the bot if it is muted
     * @return bool|mixed
     */
    public function botUnmute()
    {
        if (!$this->botInfo) $this->botInit();
        if (!$this->botInfo['muted']) return true;
        $url = 'bot/' . $this->channelId . '/unmute';
        $res = $this->sendRequest('POST', $url);
        return $res;
    }

    /**
     * Sends a message to channel as bot
     * @param $message
     * @return bool|mixed
     */
    public function botSay($message)
    {

        if (!$this->botInfo) $this->botInit();
        if (!$this->botInfo['joined']) return false;
        if ($this->botInfo['muted']) return false;

        $url = 'bot/' . $this->channelId . '/say';
        $params = array('message' => $message);

        $res = $this->sendRequest('POST', $url, $params);

        return $res;
    }

    // BOT Modules

    /**
     * Gather data about bot modules
     * @return bool|array
     */
    public function botGetModules()
    {
        $url = 'bot/modules/' . $this->channelId;
        $res = $this->sendRequest('GET', $url);
        $this->botModules = $res;
        return $res;
    }

    /**
     * Send updated module data
     * @param $module
     * @return bool|array
     */
    public function botUpdateModule($module)
    {
        if (!$this->botModules) $this->botGetModules();
        $url = 'bot/modules/' . $this->channelId . '/' . $module;
        $params = array('message' => $this->botModules[$module]);
        $res = $this->sendRequest('PUT', $url, $params);
        return $res;
    }
    // BOT Commands

    /**
     * Gather data about bot modules
     * @return bool|array
     */
    public function botGetCommands()
    {
        $url = 'bot/commands/' . $this->channelId;
        $res = $this->sendRequest('GET', $url);
        $this->botCommands = $res;
        return $res;
    }

    /**
     * Sets $amount points to user
     * @param array $command
     * @return boolean
     */
    public function addBotCommand($command)
    {
        $url = 'bot/commands/' . $this->channelId;

        /*
        //Structure of command
        $command=array(
          "accessLevel"=>300, //integer 100 - viewer, 250 - subscriber, 300 - regular, 400 - VIP, 500 - moderator (twitch mods automatically placed at this level), 1000 - super moderator, 1500 - broadcaster
          "type"=>"say", //string say, reply, whisper
          "aliases"=>array("test2","test3"),
          "keywords"=>array("test4","test5"),
            "regex" => "[A-Z]{4}[1-3]{2}", //string
            "reply" => 'This is a test response ${user}', //string
            "enabledOnline" => true, //boolean
            "enabledOffline" => false, //boolean
            "hidden" => false, //boolean
            "cooldown" => array("user"=>15,"global"=>30), //values in seconds
            "command" => "test" //string
        );
        */


        $res = $this->sendRequest('PUT', $url, $command);
        return $res;
    }

    /**
     * Sets $amount points to user
     * @param string $user
     * @param int $amount
     * @return array
     */
    public function addPoints($user, $amount)
    {
//        $amount = (int)$amount;
        $url = 'points/' . $this->channelId . '/' . $user . '/' . $amount;
        $res = $this->sendRequest('PUT', $url);
        return $res;
    }

    /**
     * Sets $amount points to user
     * @param array $users
     * @return array
     */
    public function addPointsBulk($users)
    {
        $url = 'points/' . $this->channelId;
        $params = array('mode' => 'add', 'users' => []);
        foreach ($users as $row => $value) {
            $params['users'][] = [
                'username' => $value['username'],
                'current' => $value['points'],
                'alltime' => $value['points']
            ];
        }
        $res = $this->sendRequest('PUT', $url, $params);
        return $res;
    }

    /**
     * Gets points to user
     * @param string $user
     * @param int $amount
     * @return array
     */
    public function getPoints($user)
    {

        $url = 'points/' . $this->channelId . '/' . $user;
        $res = $this->sendRequest('GET', $url);
        return $res;
    }

    /**
     * Upload a file to StreamElements Cloud
     * @param $filename
     * @param $fileContent
     * @return bool|mixed
     */
    public function upload($filename, $fileContent)
    {
        //return array("uuid" => "image.gif");
        if (isset($this->uploadedFiles[md5($fileContent)])) {
            return $this->uploadedFiles[md5($fileContent)];
        }
        $url = 'uploads/' . $this->channelId;

        $uploadRequest = array(
            "name" => "file",
            "contents" => $fileContent,
            "filename" => $filename,
        );
        $res = $this->sendRequest('POST', $url, $uploadRequest, true);
        $this->uploadedFiles[md5($fileContent)] = $res;
        $this->files[$res['_id']] = $res;
        return $res;
    }

    /**
     * @return bool|mixed
     */
    public function getUploads()
    {
        $url = 'uploads/' . $this->channelId;
        $res = $this->sendRequest('GET', $url);
        foreach ($res as $file) {
            $this->files[$file['_id']] = $file;

        }
        return $res;
    }

    /**
     * @param $asset
     * @return bool|mixed
     */
    public function removeUpload($asset)
    {
        $url = 'uploads/' . $this->channelId . '/' . $asset;
        $res = $this->sendRequest('DELETE', $url);
        unset($this->files[$asset]);
        return $res;
    }


    // OVERLAYS

    /**
     * @return bool|mixed
     */
    public function getOverlays()
    {
        $url = 'overlays/' . $this->channelId;
        $res = $this->sendRequest('GET', $url);
        $this->overlays = $res['docs'];
        return $res;
    }

    /**
     * @param $name
     * @param string $game
     * @param string $preview
     * @param int $width
     * @param int $height
     * @param array $widgets
     * @return mixed
     */
    public function createOverlay($name, $game = '', $preview = 'https://static-cdn.jtvnw.net/ttv-static/404_boxart-272x380.jpg', $width = 1920, $height = 1080, $widgets = array())
    {

        $url = 'overlays/' . $this->channelId;
        $data = array(
            "settings" => array(
                "name" => "1080p",
                "width" => $width,
                "height" => $height
            ),
            "game" => $game,
            "preview" => $preview,
            "name" => $name,
            "widgets" => $widgets
        );

        $res = $this->sendRequest('POST', $url, $data);

        $this->overlays[] = $res;
        return $res;

    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function getOverlay($id)
    {
        $url = 'overlays/' . $this->channelId . '/' . $id;
        $res = $this->sendRequest('GET', $url);
        return $res;
    }

    /**
     * @param $id
     * @param $widgets
     * @return bool|mixed
     */
    public function updateOverlay($id, $widgets)
    {

        $url = 'overlays/' . $this->channelId . '/' . $id;
        $data = array(
            "widgets" => $widgets,

        );
        $res = $this->sendRequest('PUT', $url, $data);

        return $res;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteOverlay($id)
    {

        $url = 'overlays/' . $this->channelId . '/' . $id;

        $res = $this->sendRequest('DELETE', $url);

        return $res;
    }


    /**
     * @param DateTime|integer $dateStart - dateTime object or 0/false for today
     * @param DateTime|integer $dateEnd - dateTime object or 0/false for -1 week
     * @param integer $limit - number of tips in single return, max 100
     * @param integer $offset - page number
     * @param string $sort - key for sorting
     * @param string $order - sort type (ASC/DESC)
     * @return integer - number of events within selected period
     * @throws Exception
     */
    public function getTips($dateStart = 0, $dateEnd = 0, $limit = 25, $offset = 0, $sort = 'createdAt', $order = 'ASC')
    {
        $now = new DateTime();
        if (!$dateEnd) {

            $dateEnd = $now->getTimestamp() * 1000;
        }
        if (!$dateStart) {
            $dateStart = $now->modify('-1 week')->getTimestamp() * 1000;
        }

        if ($sort != 'ASC') {
            $sort = '-' . $sort;
        }
        $limit = min($limit, 100);

        $url = 'tips/' . $this->channelId . '?after=' . $dateStart . '&before=' . $dateEnd . '&limit=' . $limit . '&offset=' . $offset . '&sort=' . $sort . '&username=';
        $res = $this->sendRequest('GET', $url);
        $this->tips = $res['docs'];
        return $res['total'];
    }

    /**
     * @param double|integer $amount - Tip amount
     * @param string $currency - Currency ISO Code
     * @param string $message - Attached message
     * @param string $username - Tipping user
     * @param string $email - e-mail address of tipper
     * @param string $provider - transaction provider
     * @param array $media - Attached media request ["start"=>integer,"videoId"=>YTVideoId]
     * @param null|string $geo - Geolocation of user
     * @param null|string $ip - IP Address of tipper
     * @param boolean $imported - if it is the tip imported (plays alert if true)
     * @param boolean $payFees - if fees are paid by tipper
     * @return array - details of added tip
     * @throws Exception
     */
    public function addTip($amount, $currency, $message, $username, $email, $provider = "PayPal", $geo = null, $ip = null, $imported = true, $payFees = true, $media = [])
    {
        $url = 'tips/' . $this->channelId;
        $data = array(
            "amount" => $amount,
            "currency" => $currency,
            "message" => $message,
            "provider" => $provider,
            "imported" => $imported,
            "payFees" => $payFees,
            "user" => [
                "username" => $username,
                "email" => $email,
                "geo" => $geo,
                "ip" => $ip
            ],
        );
        if (isset($media['videoId'])) $data["media"] = $media;
        $res = $this->sendRequest('POST', $url, $data);
        return $res;
    }

    public function getActivities($types, $grabAll = 0, $after = 0, $before = 0, $limit = 100, $minCheer = 0, $minHost = 0, $minSub = 0, $minTip = 0, $origin = 'SEApiWrapper')
    {
        $url = 'activities/' . $this->channelId;
        if ($grabAll) $limit = 100;
        if (!$before) $before = time() * 1000;
        if (!$after) $after = $before - 24 * 60 * 60 * 1000;

        $params = [
            'after' => $after,
            'before' => $before,
            'limit' => min(max((int)$limit, 1), 100),
            'mincheer' => (int)$minCheer,
            'mintip' => (int)$minTip,
            'minhost' => (int)$minHost,
            'minsub' => (int)$minSub,
            'origin' => $origin,
            'types' => $types
        ];
        //print_r($params);
        $res = $this->sendRequest('GET', $url, $params);
        if (sizeof($res) == 100 && $grabAll) {
            //print_r($res);
            $before = $res[99]['createdAt'];
            $res = array_merge($res, $this->getActivities($types, $grabAll, $after, $before, $limit, $minCheer, $minHost, $minSub, $minTip));
        }
        return ($res);
    }

    public function addPurchase($platform, $user, $avatar = "https://cdn.streamelements.com/assets/homepage/SE_logo_396x309px_ground_control_page%403x.png", $items)
    {
        $url = 'activities/' . $this->channelId;
        /* structure of $items
          $items =   [
                            [
                                "name" => "Item name",
                                "image" => "https://url.to/image.jpg",
                                "price" => 123,
                                "quantity" => 2
                            ]
                        ]
         */
        $params = [
            "channel" => $this->channelId,
            "provider" => $platform,
            "type" => "purchase",
            "data" => [
                "username" => $user,
                "avatar" => $avatar,
                "items" => $items
            ]
        ];
        $res = $this->sendRequest('POST', $url, $params);
        return ($res);
    }


}
