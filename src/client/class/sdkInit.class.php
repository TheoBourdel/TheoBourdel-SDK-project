<?php
namespace SDK;
class sdkInit
{

    private const CLIENT_ID = '67dc2be521bec2ff862d3ab057de216b';
    private const CLIENT_SECRET = '04054cf433eeb3976252c81b6d657fda';
    // DISCORD (theo)
    private const DISCORD_CLIENT_ID = "998319810356191283";
    private const DISCORD_CLIENT_SECRET = "Cj1npEXaa80qbQKaLFILvNhY8YsF-6rU";
    private const DISCORD_TOKEN_URL = "https://discordapp.com/api/v6/oauth2/token";
    private const DISCORD_API_URL = "https://discord.com/api/users/@me";
    private const DISCORD_REDIRECT_URL = "https://localhost/discord_connection_successful";
    // TWITCH (theo)
    private const TWITCH_CLIENT_ID = "ni1dvf4jnz0e56sov6zhb4tpjtb6va";
    private const TWITCH_CLIENT_SECRET = "q8iukayup4ghl3ko6potpywq2hn7l3";
    private const TWITCH_TOKEN_URL = "https://id.twitch.tv/oauth2/token";
    private const TWITCH_API_URL = "https://api.twitch.tv/helix/users";
    private const TWITCH_REDIRECT_URL = "https://localhost/twitch_oauth_success";
    // FACEBOOK (chems)
    private const FB_CLIENT_ID = '1395140080952179';
    private const FB_CLIENT_SECRET = 'ed57a1e2d4ca847f1bce3ad9e897c6ed';
    private const FB_TOKEN_URL = "https://graph.facebook.com/v13.0/oauth/access_token";
    private const FB_API_URL = "https://graph.facebook.com/v13.0/me?fields=last_name,first_name,email";
    private const FB_REDIRECT_URL = "https://localhost/facebook_oauth_success";
    // GITHUB (theo)
    private const GITHUB_CLIENT_ID = "c9efcd63b808ed04017c";
    private const GITHUB_CLIENT_SECRET = "9bbb1e1b55268d18fad7580a034d3eac884481fd";
    private const GITHUB_TOKEN_URL = "https://github.com/login/oauth/access_token";
    private const GITHUB_API_URL = "https://api.github.com/user";
    private const GITHUB_REDIRECT_URL = "https://localhost/github_oauth_success";



    function callback()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            ["username"=> $username, "password" => $password] = $_POST;
            $specifParams = [
                "grant_type" => "password",
                "username" => $username,
                "password" => $password,
            ];
        } else {
            ["code"=> $code, "state" => $state] = $_GET;
            $specifParams = [
                "grant_type" => "authorization_code",
                "code" => $code
            ];
        }
        $queryParams = http_build_query(array_merge(
            $specifParams,
            [
                "redirect_uri" => "http://localhost:8081/oauth_success",
                "client_id" => self::CLIENT_ID,
                "client_secret" => self::CLIENT_SECRET,
            ]
        ));
        $response = file_get_contents("http://server:8080/token?{$queryParams}");
        if (!$response) {
            echo $http_response_header;
            return;
        }
        ["access_token" => $token] = json_decode($response, true);


        $context = stream_context_create([
            "http"=>[
                "header"=>"Authorization: Bearer {$token}"
            ]
        ]);
        $response = file_get_contents("http://server:8080/me", false, $context);
        if (!$response) {
            echo $http_response_header;
            return;
        }
        var_dump(json_decode($response, true));
    }

    public function login(): void
    {
        
        $queryParams = http_build_query([
            "state"=>bin2hex(random_bytes(16)),
            "client_id"=> self::CLIENT_ID,
            "scope"=>"profile",
            "response_type"=>"code",
            "redirect_uri"=>"http://localhost:8081/oauth_success",
        ]);
        echo "
        <form method=\"POST\" action=\"/oauth_success\">
            <input type=\"text\" name=\"username\"/>
            <input type=\"password\" name=\"password\"/>
            <input type=\"submit\" value=\"Login\"/>
        </form>
    ";
        $fbQueryParams = http_build_query([
            "state"=>bin2hex(random_bytes(16)),
            "client_id"=> self::FB_CLIENT_ID,
            "scope"=>"public_profile,email",
            "redirect_uri"=> self::FB_REDIRECT_URL,
        ]);

        $discordQueryParams = http_build_query([
            "state"=>bin2hex(random_bytes(16)),
            "client_id"=> self::DISCORD_CLIENT_ID,
            "scope"=>"identify",
            "response_type" => "code",
            "redirect_uri"=> self::DISCORD_REDIRECT_URL,
        ]);

        $twitchQueryParams = http_build_query([
            "state"=>bin2hex(random_bytes(16)),
            "client_id"=> self::TWITCH_CLIENT_ID,
            "scope"=>"user:read:email",
            "response_type" => "code",
            "redirect_uri"=> self::TWITCH_REDIRECT_URL,
        ]);

        $githubQueryParams = http_build_query([
            "state"=>bin2hex(random_bytes(16)),
            "client_id"=> self::GITHUB_CLIENT_ID,
            "scope"=>"user",
            "redirect_uri"=> self::GITHUB_REDIRECT_URL,
        ]);

        echo "<a href=\"http://localhost:8080/auth?$queryParams\">Login with Oauth-Server</a><br>";
        echo "<a href=\"https://www.facebook.com/v13.0/dialog/oauth?$fbQueryParams\">Login with Facebook</a><br>";
        echo "<a href=\"https://discord.com/api/oauth2/authorize?$discordQueryParams\">Login with Discord</a><br>";
        echo "<a href=\"https://id.twitch.tv/oauth2/authorize?{$twitchQueryParams}\">Login with Twitch</a><br>";
        echo "<a href=\"https://github.com/login/oauth/authorize?{$githubQueryParams}\">Login with Github</a><br>";

    }

    public function app_callback($app)
    {
        switch($app) {
            case "fb":
                $token = $this->getFbToken(self::FB_TOKEN_URL, self::FB_CLIENT_ID, self::FB_CLIENT_SECRET);
                $apiURL = self::FB_API_URL;
                $headers = [
                    "Authorization: Bearer $token",
                ];
                break;
            case "discord":
                $token = $this->getDiscordToken(self::DISCORD_TOKEN_URL, self::DISCORD_CLIENT_ID, self::DISCORD_CLIENT_SECRET);
                $apiURL = self::DISCORD_API_URL;
                $headers = [
                    "Authorization: Bearer $token",
                ];
                break;
            case "twitch":
                $token = $this->getTwitchToken(self::TWITCH_TOKEN_URL, self::TWITCH_CLIENT_ID, self::TWITCH_CLIENT_SECRET);
                $apiURL = self::TWITCH_API_URL;
                $headers = [
                    "Authorization: Bearer $token",
                    "Client-ID: " . self::TWITCH_CLIENT_ID
                ];
                break;
            case "github":
                $token = $this->getGithubToken(self::GITHUB_TOKEN_URL, self::GITHUB_CLIENT_ID, self::GITHUB_CLIENT_SECRET);
                $apiURL = self::GITHUB_API_URL;
                $headers = [
                    "Authorization: token $token",
                    "User-Agent: benjaminli7"
                ];
                break;
            default:
                return;
        }

        $user = $this->getUser($apiURL, $headers);
        var_dump($user);
    }

    public function getUser($apiURL, $headers)
    {
        $context = stream_context_create([
            "http"=>[
                "header"=>$headers,
            ]
        ]);

        $response = file_get_contents($apiURL, false, $context);
        if (!$response) {
            var_dump($http_response_header);
            return;
        }

        return json_decode($response, true);
    }

    public function getFbToken($baseUrl, $clientId, $clientSecret)
    {
        ["code"=> $code, "state" => $state] = $_GET;
        $queryParams = http_build_query([
            "client_id"=> $clientId,
            "client_secret"=> $clientSecret,
            "redirect_uri"=> self::FB_REDIRECT_URL,
            "code"=> $code,
            "grant_type"=>"authorization_code",
        ]);

        $url = $baseUrl . "?{$queryParams}";
        $response = file_get_contents($url);

        if (!$response) {
            var_dump($http_response_header);
            return;
        }
        ["access_token" => $token] = json_decode($response, true);

        return $token;
    }

    public function getDiscordToken($baseUrl, $clientId, $clientSecret)
    {
        ["code"=> $code, "state" => $state] = $_GET;

        $postData = http_build_query(
            [
                "client_id"=> $clientId,
                "client_secret"=> $clientSecret,
                "redirect_uri"=> self::DISCORD_REDIRECT_URL,
                "code"=> $code,
                "grant_type"=>"authorization_code",
            ]
        );

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData,
            ]
        ];

        $context  = stream_context_create($opts);
        $response = file_get_contents($baseUrl, false, $context);

        if (!$response) {
            var_dump($http_response_header);
            return;
        }

        ["access_token" => $token] = json_decode($response, true);
        return $token;
    }

    function getTwitchToken($baseUrl, $clientId, $clientSecret)
    {
        ["code"=> $code, "state" => $state] = $_GET;
        $postData = http_build_query(
            [
                "client_id"=> $clientId,
                "client_secret"=> $clientSecret,
                "redirect_uri"=> self::TWITCH_REDIRECT_URL,
                "code"=> $code,
                "grant_type"=>"authorization_code"
        ]);

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];

        $context  = stream_context_create($opts);
        $response = file_get_contents($baseUrl, false, $context);

        if (!$response) {

            var_dump($http_response_header);
            return;
        }

        ["access_token" => $token] = json_decode($response, true);
        return $token;
    }

    public function getGithubToken($baseUrl, $clientId, $clientSecret)
    {
        ["code"=> $code, "state" => $state] = $_GET;
        $postData = http_build_query(
            [
                "client_id"=> $clientId,
                "client_secret"=> $clientSecret,
                "redirect_uri"=> self::GITHUB_REDIRECT_URL,
                "code"=> $code,
                "grant_type"=>"authorization_code",
            ]
        );

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                'content' => $postData,
            ]
        ];

        $context  = stream_context_create($opts);
        $response = file_get_contents($baseUrl, false, $context);

        if (!$response) {
            var_dump($http_response_header);
            return;
        }


        parse_str( $response, $output );
        $result = json_encode($output);
        ["access_token" => $token] = json_decode($result, true);
        return $token;
    }
}
