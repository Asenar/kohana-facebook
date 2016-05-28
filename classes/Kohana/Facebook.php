<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Facebook{
	protected $config;

    private static $_instance = null;

    public $facebook = null;

    private $helper = null;

    public function __construct()
    {
        $this->config = Kohana::$config->load('facebook')->as_array();

        $app_id     = $this->config['default']['app_id'];
        $app_secret = $this->config['default']['app_secret'];
        $default_graph_version = $this->config['default']['default_graph_version'];

        $this->facebook = new Facebook\Facebook([
            'app_id'     => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => $default_graph_version,
        ]);

        if (isset($_SESSION['facebook_access_token'])) {
            $this->facebook->setDefaultAccessToken($_SESSION['facebook_access_token']);
        } else {
            $_SESSION['facebook_access_token'] = null;
        }
    }

    public function getLoginUrl($callback_link) {
        $helper = $this->get_helper();

        $scope = [
            'email',
            'user_likes',
            'user_birthday',
            'user_hometown',
            //'friends_likes',
            //'scope' => 'email,user_likes,user_birthday,user_hometown',
        ];

        $login_url =  $helper->getLoginUrl($callback_link, $scope);

        return $login_url;
    }

    public function get_helper($fct = null) {
        if (!$this->helper) {
            if (!$fct) {
                $fct = 'getRedirectLoginHelper';
            }
            $this->helper = $this->facebook->$fct();
        }
        return $this->helper;
    }

    public function api() {
        return $this->facebook;
    }

    static public function instance($returnFB = false)
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }
        if ($returnFB)
            return self::$_instance->facebook;

        return self::$_instance;

    }

    public function getUser() {
        if (!($accessToken = $_SESSION['facebook_access_token'])) {
            $helper =  $this->get_helper();
            try {
                $accessToken = $helper->getAccessToken();
                if (!empty($accessToken)) {
                    $_SESSION['facebook_access_token'] = (string)$accessToken;
                }
                //$this->facebook->setDefaultAccessToken($accessToken);
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                throw new Exception(' Graph returned an error: ' . $e->getMessage());
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                throw new Exception('Facebook SDK returned an error: ' . $e->getMessage());
            }
        }

        if (!empty($accessToken)) {
            $response = $this->facebook->get('/me', $accessToken);
            $body = $response->getBody();
            $user  = json_decode($body);
            return $user;
        }
        return false;
    }
}
