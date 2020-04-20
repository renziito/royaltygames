<?php

use Firebase\FirebaseLib;

class InitCommand extends CConsoleCommand {

    public function actionIndex() {
        $firebase = new FirebaseLib(
                Globals::FIREBASE_DEFAULT_URL,
                Globals::FIREBASE_DEFAULT_TOKEN
        );

        $firebase->setSSLConnection(false);

        while ($settingID = SettingsQuery::getActive()) {
            echo "[" . (new DateTime('now'))->format("D M d H:i:s.u Y") . "]";
            echo "[" . get_class($this) . "] ";
            echo "Inicio del proceso\n";

            $setting = SettingsModel::model()->findByPk($settingID);

            try {
                $round = new RoundsModel();

                $round->date_created = (new DateTime())->format("Y-m-d H:i:s");
                if (!$round->save()) {
                    throw new Exception("No se pudo crear la ronda", 500);
                }

                echo "[" . (new DateTime('now'))->format("D M d H:i:s.u Y") . "]";
                echo "[" . get_class($this) . "]";
                echo "\t[Round: {$round->id}] ";
                echo "Inicio de la ronda \n";

                $nextRoundIn = (int) Utils::addSecondsToDate($setting->next_round_in);
                $gameStartIn = (int) Utils::addSecondsToDate($setting->game_start_in, $nextRoundIn, true);

                $firebase->set(
                        Globals::FIREBASE_DEFAULT_PATH . "/{$round->id}",
                        [
                            "currentStep" => [
                                "id" => 1,
                                "start" => (int) $nextRoundIn
                            ],
                            "winner" => 0,
                            "players" => false,
                        ]
                );

                $firebase->set(
                        "/currentRound",
                        (int) $round->id
                );

                $sleep = $nextRoundIn - (int) Utils::getTimestamp();
                sleep($sleep);

                $firebase->set(
                        Globals::FIREBASE_DEFAULT_PATH . "/{$round->id}/currentStep",
                        [
                            "id" => 2,
                            "start" => (int) $gameStartIn
                        ]
                );
                $sleep = $gameStartIn - (int) Utils::getTimestamp();
                sleep($sleep);

                $firebase->set(
                        Globals::FIREBASE_DEFAULT_PATH . "/{$round->id}/currentStep",
                        [
                            "id" => 3,
                            "start" => false
                        ]
                );

                $random = Utils::calculateRandom($setting->number_of_spins);
                $angle = Utils::calculateAngle($random);
                $ticket = Utils::calculateTicket($angle);

                $round = RoundsModel::model()->findByPk($round->id);

                $round->random = $random;
                $round->angle = $angle;
                $round->ticket = $ticket;

                if (!$round->save()) {
                    throw new Exception("No se pudo actualizar ronda", 500);
                }

                $rollingStopIn = (int) Utils::addSecondsToDate($setting->spinning_stop_in);

                $firebase->set(
                        Globals::FIREBASE_DEFAULT_PATH . "/{$round->id}/currentStep",
                        [
                            "id" => 4,
                            "start" => (int) $rollingStopIn,
                            "position" => (float) $random,
                            "angle" => (float) $angle
                        ]
                );

                $sleep = $rollingStopIn - (int) Utils::getTimestamp();
                sleep($sleep);

                $firebase->set(
                        Globals::FIREBASE_DEFAULT_PATH . "/{$round->id}/currentStep",
                        [
                            "id" => 5,
                            "start" => false
                        ]
                );

                $winner = RoundsQuery::declareWinner($round->id, $ticket);

                $firebase->set(
                        Globals::FIREBASE_DEFAULT_PATH . "/{$round->id}/winner",
                        [
                            "tickect" => $ticket,
                            "player" => ($winner->roundplayer_id == 0) ? false : (int) $winner->roundplayer_id
                        ]
                );

                echo "[" . (new DateTime('now'))->format("D M d H:i:s.u Y") . "]";
                echo "[" . get_class($this) . "]";
                echo "\t[Round: {$round->id}] ";
                echo "Fin de la ronda \n";
            } catch (Exception $ex) {
                echo "[" . (new DateTime('now'))->format("D M d H:i:s.u Y") . "]";
                echo "[" . get_class($this) . "]";
                echo "[Line: " . __LINE__ . "] ";
                echo "Code: " . $ex->getCode() . ": ";
                echo $ex->getMessage() . "\n";

                $firebase->set(
                        "/error",
                        true
                );
            }

            echo "[" . (new DateTime('now'))->format("D M d H:i:s.u Y") . "]";
            echo "[" . get_class($this) . "] ";
            echo "Fin del proceso \n\n";
        }

        $firebase->set(
                "/stoped",
                true
        );
    }

}
