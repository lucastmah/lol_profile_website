<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

    $NALOL = "https://na1.api.riotgames.com/lol/";
    $americaLOL = "https://americas.api.riotgames.com/lol/";
    $apiKey = "api_key=RGAPI-1eb32fe4-938b-45f6-a3ce-27211d066a5b";

    //get ID's for given summoner
    if(isset($_GET['summoner'])) {
        $summonerInput = rawurlencode($_GET['summoner']);

        $getPlayerIDS = $NALOL . "summoner/v4/summoners/by-name/" . $summonerInput . "?" . $apiKey;

        $encodedPlayerIDS = file_get_contents($getPlayerIDS);

        if(is_null($encodedPlayerIDS)) {
            return http_response_code(404);
        }

        //echo "player ID's: ";
        //var_dump($encodedPlayerIDS);

        $playerIDS = json_decode($encodedPlayerIDS);

        //Get Rank
        if($playerIDS->id != null) {
            $summonerName = $playerIDS->name;
            $encryptedSummonerID = $playerIDS->id;
            $summonerIcon = $playerIDS->profileIconId;

            //echo "encrypted ID: " . $encryptedSummonerID . "\n";

            $getPlayerRank = $NALOL . "league/v4/entries/by-summoner/" . $encryptedSummonerID . "?" . $apiKey;

            $encodedPlayerRank = file_get_contents($getPlayerRank);

            if(is_null($encodedPlayerRank)) {
                return http_response_code(404);
            }

            //echo "player ranks: ";
            //var_dump($encodedPlayerRank);

            $playerRank = json_decode($encodedPlayerRank);
            
            if($playerRank != null) {
                foreach($playerRank as $rank) {
                    if($rank->queueType == "RANKED_SOLO_5x5") {
                        $soloTier = ucwords(strtolower($rank->tier));
                        $soloDivision = $rank->rank;
                        $soloRank = $soloTier . " " . $soloDivision;
                        $soloLP = $rank->leaguePoints;
                        $soloWins = $rank->wins;
                        $soloLosses = $rank->losses;
                        $soloWinRate = round($soloWins * 100 / ($soloWins + $soloLosses), 1) . "%";
                        if(isset($rank->miniSeries)) {
                            $soloPrint = $soloPrint . "\n-> Promotion: " . $rank->miniSeries->wins . " Wins " . $rank->miniSeries->losses . " Losses";
                        }
                    }
                    else if($rank->queueType == "RANKED_FLEX_SR") {
                        $flexTier = ucwords(strtolower($rank->tier));
                        $flexDivision = $rank->rank;
                        $flexRank = $flexTier . " " . $flexDivision;
                        $flexLP = $rank->leaguePoints;
                        $flexWins = $rank->wins;
                        $flexLosses = $rank->losses;
                        $flexWinRate = round($flexWins * 100 / ($flexWins + $flexLosses), 1) . "%";
                        if(isset($rank->miniSeries)) {
                            $flexPrint = $flexPrint . "\n-> Promotion: " . $rank->miniSeries->wins . " Wins " . $rank->miniSeries->losses . " Losses";
                        }
                    } 
                }
            }
            if($soloTier == null) {
                $soloTier = "Unranked";
                $soloDivision = "Unranked";
                $soloRank = "Unranked";
                $soloLP = "Unranked";
                $soloWins = "Unranked";
                $soloLosses = "Unranked";
                $soloWinRate = "Unranked";
            }
            if($flexTier == null) {
                $flexTier = "Unranked";
                $flexDivision = "Unranked";
                $flexRank = "Unranked";
                $flexLP = "Unranked";
                $flexWins = "Unranked";
                $flexLosses = "Unranked";
                $flexWinRate = "Unranked";
            }

        } else {
            return http_response_code(404);
        }

        //Get queues into a php variable
        $encodedQueueTypes = file_get_contents("./queues.json");

        if(is_null($encodedQueueTypes)) {
            return http_response_code(404);
        }

        $queueTypes = json_decode($encodedQueueTypes);

        //Get summoners into a php variable
        $encodedSummoners = file_get_contents("./summoner.json");

        if(is_null($encodedSummoners)) {
            return http_response_code(404);
        }

        $summoners = json_decode($encodedSummoners);


        //Get Match History
        if($playerIDS->accountId != null) {
            $encryptedSummonerPUUID = $playerIDS->puuid;
            $startMatchIndex = 0;
            $endMatchIndex = 20;
            $getPlayerMatchHistory = $americaLOL . "match/v5/matches/by-puuid/" . $encryptedSummonerPUUID . "/ids?start=" . $startMatchIndex . "&count=" . $endMatchIndex ."&" . $apiKey;

            $encodedPlayerMatchHistory = file_get_contents($getPlayerMatchHistory);

            if(is_null($encodedPlayerMatchHistory)) {
                return http_response_code(404);
            }

            $playerMatchHistory = json_decode($encodedPlayerMatchHistory);

            if($playerMatchHistory != null) {
                $matchHistory = array();
                foreach($playerMatchHistory as $match) {
                    $getPlayerSingleMatch = $americaLOL . "match/v5/matches/" . $match . "?" . $apiKey;

                    $encodedPlayerSingleMatch = file_get_contents($getPlayerSingleMatch);

                    if(is_null($encodedPlayerSingleMatch)) {
                        return http_response_code(404);
                    }

                    $playerSingleMatch = json_decode($encodedPlayerSingleMatch);

                    //Get individual player information for a match
                    $playerList = array();
                    foreach($playerSingleMatch->info->participants as $participant) {
                        foreach($summoners->data as $summoner) {
                            if($summoner->key == $participant->summoner1Id) {
                                $summonerD = $summoner->name;
                            }
                            if($summoner->key == $participant->summoner2Id) {
                                $summonerF = $summoner->name;
                            }
                        }
                        $player = array(
                            'summonerName' => $participant->summonerName,
                            'championId' => $participant->championId,
                            'championName' => $participant->championName,
                            'kills' => $participant->kills,
                            'deaths' => $participant->deaths,
                            'assists' => $participant->assists,
                            'position' => $participant->individualPosition,
                            'champLevel' => $participant->champLevel,
                            'summonerD' => $summonerD,
                            'summonerF' => $summonerF,
                            'cs' => $participant->totalMinionsKilled,
                            'teamId' => $participant->teamId
                        );

                        if($participant->summonerName == $summonerName) {
                            $gameResult = $participant->win;
                        }

                        array_push($playerList, $player);
                    }

                    //find queue type
                    foreach($queueTypes as $queueType) {
                        if($queueType->queueId == $playerSingleMatch->info->queueId) {
                            $gameMap = $queueType->map;
                            $gameMode = $queueType->description;
                        }
                    }

                    //Get total kills for each side
                    foreach($playerSingleMatch->info->teams as $teamStats) {
                        if($teamStats->teamId == "100") {
                            $team100TotalKills = $teamStats->objectives->champion->kills;
                        }
                        if($teamStats->teamId == "200") {
                            $team200TotalKills = $teamStats->objectives->champion->kills;
                        }
                    }

                    //Set data for a single match
                    $returnMatch = array (
                        'matchId' => $playerSingleMatch->metadata->matchId,
                        'gameMap' => $gameMap,
                        'gameMode' => $gameMode,
                        'gameVersion' => $playerSingleMatch->info->gameVersion,
                        'mapId' => $playerSingleMatch->info->mapId,
                        'gameDuration' => $playerSingleMatch->info->gameDuration,
                        'gameResult' => $gameResult,
                        'team100TotalKills' => $team100TotalKills,
                        'team200TotalKills' => $team200TotalKills,
                        'playerList' => $playerList
                    );

                    array_push($matchHistory, $returnMatch);
                }
            }
        }
    } else {
        return http_response_code(404);
    }

    //JSON data
    $file = array(
        'summonerName' => $summonerName,
        'summonerIcon' => $summonerIcon,
        'summonerID' => $encryptedSummonerID,
        'summonerPUUID' => $encryptedSummonerPUUID,
        'soloLP' => $soloLP,
        'soloTier' => $soloTier,
        'soloDivision' => $soloDivision,
        'soloWins' => $soloWins,
        'soloLosses' => $soloLosses,
        'soloWinRate' => $soloWinRate,
        'flexTier' => $flexTier,
        'flexDivision' => $flexDivision,
        'flexLP' => $flexLP,
        'flexWins' => $flexWins,
        'flexLosses' => $flexLosses,
        'flexWinRate' => $flexWinRate,
        'matches' => $matchHistory
    );

    echo json_encode($file);