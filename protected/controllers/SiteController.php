<?php

class SiteController extends Controller {

    public function actionLogin() {
        $url = 'https://id.twitch.tv/oauth2/authorize';
        $url .= '?client_id=06c0gb66eru1mxcecox3wpwfrf2g5e';
        $url .= '&redirect_uri=' . Yii::app()->createAbsoluteUrl('site/index');
        $url .= '&response_type=code';
        $url .= '&scope=user_read';

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($curl);
        echo $response;
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        /*
        $client_id = '06c0gb66eru1mxcecox3wpwfrf2g5e';
        $token_url = 'https://id.twitch.tv/oauth2/token';
        $params = array(
            'client_id' => $client_id,
            'client_secret' => 'jsxzn8a209fyzrbt1n0ykrezo2sq8a',
            'grant_type' => 'authorization_code',
            'redirect_uri' => Yii::app()->createAbsoluteUrl('site/token'),
            'code' => Yii::app()->request->getQuery('code')
        );

        $curl = curl_init($token_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // Disables SSL verification

        $response = json_decode(curl_exec($curl), true);

        if (isset($response['message'])) {
            $this->redirect('login');
        }

        $curl2 = curl_init('https://api.twitch.tv/helix/users');
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, FALSE); // Disables SSL verification
        curl_setopt($curl2, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v3+json',
            'Client-ID: ' . $client_id,
            'Authorization: Bearer ' . $response['access_token']
        ));
        $user = json_decode(curl_exec($curl2), true);
        $data = [];
*/

        $user['data'][0] = [
            'id' => 173653284,
            'login' => 'renziito',
            'display_name' => 'Renziito',
            'type' => '',
            'broadcaster_type' => 'affiliate',
            'description' => '🇵🇪 Games : R6 |Arma3 | CoD Mobile',
            'profile_image_url' => 'https://static-cdn.jtvnw.net/jtv_user_pictures/2b73ee0f-3397-492f-bc96-26c1ffd526c3-profile_image-300x300.png',
            'offline_image_url' => 'https://static-cdn.jtvnw.net/jtv_user_pictures/84526628-6e31-45d1-a5f1-a1b9b7d0cd78-channel_offline_image-1920x1080.png',
            'view_count' => 7229
        ];

        if (isset($user['data'][0])) {
            $data = $user['data'][0];
        } else {
            $this->redirect('site/login');
        }

        $this->render('games', ['data' => $data]);
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                Utils::show($error);
        }
    }

}
