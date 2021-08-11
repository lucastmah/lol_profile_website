<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

    $NALOL = "https://na1.api.riotgames.com/lol/";
    $apiKey = "?api_key=RGAPI-21397016-f1ca-495a-86dc-5311b4451b84";

    //get ID's for given summoner
    if(isset($_GET['summoner'])) {
        $summonerName = $_GET['summoner'];

        $getPlayerIDS = $NALOL . "summoner/v4/summoners/by-name/" . $summonerName . $apiKey;

        $encodedPlayerIDS = file_get_contents($getPlayerIDS);

        if(is_null($encodedPlayerIDS)) {
            return http_response_code(404);
        }

        //echo "player ID's: ";
        //var_dump($encodedPlayerIDS);

        $playerIDS = json_decode($encodedPlayerIDS);

        //Get Rank
        if($playerIDS->id != null) {
            $encryptedSummonerID = $playerIDS->id;
            $summonerIcon = $playerIDS->profileIconId;

            //echo "encrypted ID: " . $encryptedSummonerID . "\n";

            $getPlayerRank = $NALOL . "league/v4/entries/by-summoner/" . $encryptedSummonerID . $apiKey;

            $encodedPlayerRank = file_get_contents($getPlayerRank);

            if(is_null($encodedPlayerRank)) {
                return http_response_code(404);
            }

            //echo "player ranks: ";
            //var_dump($encodedPlayerRank);

            $playerRank = json_decode($encodedPlayerRank);
            
            if($playerRank != null) {
                if($playerRank[0]->queueType == "RANKED_SOLO_5x5") {
                    $soloTier = ucwords(strtolower($playerRank[0]->tier));
                    $soloDivision = $playerRank[0]->rank;
                    $soloRank = $soloTier . " " . $soloDivision;
                    $soloLP = $playerRank[0]->leaguePoints;
                    $soloWins = $playerRank[0]->wins;
                    $soloLosses = $playerRank[0]->losses;
                    $soloWinRate = round($soloWins * 100 / ($soloWins + $soloLosses), 1) . "%";
                    $soloPrint = $soloRank . " " . $soloLP . "LP " . $soloWinRate . " Win Rate " . $soloWins . " Wins " . $soloLosses . " Losses";
                    if(isset($playerRank[0]->miniSeries)) {
                        $soloPrint = $soloPrint . "\n-> Promotion: " . $playerRank[0]->miniSeries->wins . " Wins " . $playerRank[0]->miniSeries->losses . " Losses";
                    }
                } else {
                    $soloPrint = "Unranked";
                }

                if($playerRank[0]->queueType == "RANKED_FLEX_SR") {
                    $flexTier = ucwords(strtolower($playerRank[0]->tier));
                    $flexDivision = $playerRank[0]->rank;
                    $flexRank = $flexTier . " " . $flexDivision;
                    $flexLP = $playerRank[0]->leaguePoints;
                    $flexWins = $playerRank[0]->wins;
                    $flexLosses = $playerRank[0]->losses;
                    $flexWinRate = round($flexWins * 100 / ($flexWins + $flexLosses), 1) . "%";
                    $flexPrint = $flexRank . " " . $flexLP . "LP " . $flexWinRate . " Win Rate " . $flexWins . " Wins " . $flexLosses . " Losses";
                    if(isset($playerRank[0]->miniSeries)) {
                        $flexPrint = $flexPrint . "\n-> Promotion: " . $playerRank[0]->miniSeries->wins . " Wins " . $playerRank[0]->miniSeries->losses . " Losses";
                    }
                } 
                else if($playerRank[1]->queueType == "RANKED_FLEX_SR") {
                    $flexTier = ucwords(strtolower($playerRank[1]->tier));
                    $flexDivision = $playerRank[1]->rank;
                    $flexRank = $flexTier . " " . $flexDivision;
                    $flexLP = $playerRank[1]->leaguePoints;
                    $flexWins = $playerRank[1]->wins;
                    $flexLosses = $playerRank[1]->losses;
                    $flexWinRate = round($flexWins * 100 / ($flexWins + $flexLosses), 1) . "%";
                    $flexPrint = $flexRank . " " . $flexLP . "LP " . $flexWinRate . " Win Rate " . $flexWins . " Wins " . $flexLosses . " Losses";
                    if(isset($playerRank[1]->miniSeries)) {
                        $flexPrint = $flexPrint . "\n-> Promotion: " . $playerRank[1]->miniSeries->wins . " Wins " . $playerRank[1]->miniSeries->losses . " Losses";
                    }
                } else {
                    $flexPrint = "Unranked";
                }
            } else {
                $soloPrint = "Unranked";
                $flexPrint = "Unranked";
            }
            //echo "Solo Queue Rank: " . $soloPrint . "\n";
            //echo "Flex Queue Rank: " . $flexPrint . "\n";
        } else {
            return http_response_code(404);
        }

        
        //Get Match History
        if($playerIDS->accountId != null) {
            $getPlayerMatchHistory = $NALOL . "match/v4/matchlists/by-account/" . $playerIDS->accountId . $apiKey;

            $encodedPlayerMatchHistory = file_get_contents($getPlayerMatchHistory);

            if(is_null($encodedPlayerMatchHistory)) {
                return http_response_code(404);
            }

            $playerMatchHistory = json_decode($encodedPlayerMatchHistory);

            if($playerMatchHistory != null) {
                if($playerMatchHistory->matches != null) {
                    $matchHistory = $playerMatchHistory->matches;
                }
            }
        }
    } else {
        return http_response_code(404);
    }

    $file = array(
        'summonerName' => $summonerName,
        'summonerIcon' => $summonerIcon,
        'soloLP' => $soloLP,
        'soloTier' => $soloTier,
        'soloDivision' => $soloDivision,
        'soloWins' => $soloWins,
        'soloLosses' => $soloLosses,
        'flexTier' => $flexTier,
        'flexDivision' => $flexDivision,
        'flexLP' => $flexLP,
        'flexWins' => $flexWins,
        'flexLosses' => $flexLosses,
        'matches' => $matchHistory
    );

    echo json_encode($file);