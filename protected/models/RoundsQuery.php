<?php

class RoundsQuery {

    public static function declareWinner($roundID, $ticket) {
        $players = self::getAllPlayers($roundID);
        $winnerID = 0;
        $username = "";

        foreach ($players as $player) {
            $start = ($player["percent_start"] * 100);
            $end = ($player["percent_end"] * 100);
            if ($ticket >= $start && $ticket <= $end) {
                $winnerID = $player["id"];
                $username = $player["user_name"];
                break;
            }
        }
        $total = self::sumTotalByRound($roundID);
        $se = new StreamElements(Utils::getSEToken(), 'Bearer');
        $se->addPoints($username, $total * 1000);

        $model = new RoundWinnersModel();

        $model->round_id = $roundID;
        $model->roundplayer_id = $winnerID;
        $model->date_created = (new DateTime())->format("Y-m-d H:i:s");

        if (!$model->save()) {
            throw new Exception("No se pudo declarar al ganador", 500);
        }

        return $model;
    }

    public static function sumTotalByRound($roundID) {
        $sql = "SELECT Round(SUM(bet),2) FROM round_players where";
        $sql .= " round_id = " . $roundID . " and status = 1 and bet is not null";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }

    public static function getAllPlayers($roundID) {
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from("round_players")
                        ->where("round_id = :rid and status = 1", [
                            ":rid" => $roundID
                        ])
                        ->order("date_created")
                        ->queryAll();
    }

    public static function getHistory($currentRound, $rowLimint) {
        $sql = "call sp_rounds_details(:current_round, :row_limit);";

        $command = Yii::app()->db->createCommand($sql);
        $command->bind_param(":current_round", $currentRound, PDO::PARAM_INT);
        $command->bind_param(":row_limit", $rowLimint, PDO::PARAM_INT);

        return $command->execute();
    }

}
