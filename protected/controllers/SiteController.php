<?php

use Firebase\FirebaseLib;

class SiteController extends Controller {

    public function actionLogin() {
        $this->render('login');
    }

    public function actionToken() {
        $code = Yii::app()->request->getQuery('code', false);
        if ($code) {
            $token_url = 'https://id.twitch.tv/oauth2/token';
            $params = array(
                'client_id' => Globals::TWITCH_ID,
                'client_secret' => Globals::TWITCH_SECRET,
                'grant_type' => 'authorization_code',
                'redirect_uri' => Yii::app()->createAbsoluteUrl('site/token'),
                'code' => $code
            );

            $curl = curl_init($token_url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = json_decode(curl_exec($curl), true);

            if (isset($response['message'])) {
                Yii::app()->request->redirect('login');
            }

            $cookie = new CHttpCookie('cookieRes', $response['access_token']);
            Yii::app()->request->cookies['cookieRes'] = $cookie;

            Yii::app()->request->redirect('index');
        }
        Yii::app()->request->redirect('login');
    }

    public function getPoints() {
        $cookie = Yii::app()->request->cookies['cookieRes'];
        if ($cookie != NULL) {
            $curl = curl_init('https://api.twitch.tv/helix/users');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // Disables SSL verification
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Accept: application/vnd.twitchtv.v3+json',
                'Client-ID: ' . Globals::TWITCH_ID,
                'Authorization: Bearer ' . $cookie->value
            ));
            $user = json_decode(curl_exec($curl), true);
            $data = [];
            if (isset($user['data'][0])) {
                $se = new StreamElements(Utils::getSEToken(), 'Bearer');
                $data = array_merge($user['data'][0], $se->getPoints($user['data'][0]['login']));
                $id = new CHttpCookie('uuid', $data['id']);
                Yii::app()->request->cookies['uuid'] = $id;
                $name = new CHttpCookie('name', $data['display_name']);
                Yii::app()->request->cookies['name'] = $name;

                if (isset($data['points'])) {
                    $number = $data['points'] / 1000;
                    $data['points'] = number_format($number, 3, '.', ',');
                }
                return $data;
            }
        } else {
            return [];
        }
    }

    public function actionGetPoints() {
        echo json_encode($this->getPoints());
    }

    public function actionLogout() {
        Yii::app()->request->cookies->clear();
        Yii::app()->request->redirect('login');
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        $cookie = Yii::app()->request->cookies['cookieRes'];
        if ($cookie == null) {
            Yii::app()->request->redirect('login');
        }

        $this->render('games', ['data' => $this->getPoints()]);
    }

    public function actionAddPlayer() {
        $firebase = new FirebaseLib(
                Globals::FIREBASE_DEFAULT_URL,
                Globals::FIREBASE_DEFAULT_TOKEN
        );

        try {
            if (!$post = Yii::app()->request->getPost("player")) {
                throw new Exception("Metodo no permitido", 403);
            }

            $roundID = $post["rid"];
            $search = RoundPlayersModel::model()->find('round_id = ' . $roundID . ' AND user_id = ' . $post['uid']);
            if ($search) {
                throw new Exception("Already on this Round", 500);
            }

            $model = new RoundPlayersModel();
            $model->round_id = $roundID;
            $model->user_id = $post["uid"];
            $model->user_name = $post["name"];
            $model->color = implode(",", Utils::hex2rgb(Utils::randomColor()));
            $model->bet = $post["bet"];
            $model->date_created = (new DateTime())->format("Y-m-d H:i:s");

            $se = new StreamElements(Utils::getSEToken(), 'Bearer');

            $se->addPoints($post["name"], ($post["bet"] * -1000));

            if (!$model->save()) {
                throw new Exception("Error", 500);
            }

            $players = RoundPlayersModel::model()->findAll(
                    "round_id = :rid and status = 1 order by date_created",
                    [
                        ":rid" => $roundID
                    ]
            );

            $percentStart = 0;
            $totalBet = 0;

            foreach ($players as $player) {
                $totalBet += $player->bet;
            }

            $data = [];

            foreach ($players as $player) {
                $percentEnd = ($player->bet / $totalBet);
                $player->percent_start = $percentStart;
                $player->percent_end = $percentStart + $percentEnd;
                $player->chance = $player->percent_end - $percentStart;

                $player->save();

                if ($model->id === $player->id) {
                    $data = [
                        "info" => [
                            "id" => (int) $player->user_id,
                            "total" => (float) $player->bet,
                            "name" => $post["name"]
                        ],
                        "color" => $player->color,
                        "degrees" => false,
                        "percent" => false,
                    ];
                }
                $percentStart += $percentEnd;
            }

            $firebase->set(
                    Globals::FIREBASE_DEFAULT_PATH . "/{$roundID}/players/{$model->id}",
                    $data
            );

            Response::JSON(false, 200, "success", compact("data"));
        } catch (Exception $ex) {
            Response::Error($ex);
        }
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

    public function actionAdmin() {
        $token = Yii::app()->request->getPost('jwtToken');
        $setoken = Utils::getSEToken();
        Utils::show($setoken);

        if ($token) {
            SettingsAdminModel::model()->updateAll(['state' => False], 'state = TRUE');

            $model = new SettingsAdminModel();
            $model->token = $token;
            $model->save();
        }

        $this->render('admin');
    }

}
