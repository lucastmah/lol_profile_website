const serverURL = "http://54.187.199.13/test.php";

const urlSearchParams = new URLSearchParams(window.location.search);
const summonerName = urlSearchParams.get("summoner");

var summonerIcon = document.getElementById("summonerIcon");
var displayName = document.getElementById("summonerName");
var soloRank = document.getElementById("soloSummonerRank");
var soloRankEmblem = document.getElementById("soloRankEmblem");
var soloLP = document.getElementById("soloLP");
var soloWins = document.getElementById("soloWins");
var soloLosses = document.getElementById("soloLosses");
var soloWinRatio = document.getElementById("soloWinRatio");

var flexRank = document.getElementById("flexSummonerRank");
var flexRankEmblem = document.getElementById("flexRankEmblem");
var flexLP = document.getElementById("flexLP");
var flexWins = document.getElementById("flexWins");
var flexLosses = document.getElementById("flexLosses");
var flexWinRatio = document.getElementById("flexWinRatio");

var winTag = document.getElementById("winTag");
var gameOption = document.getElementById("gameOption");
var gameMap = document.getElementById("gameMap");
var gameLength = document.getElementById("gameLength");
var matchPlayers = document.getElementById("matchPlayers");

var playerChampImage = document.getElementById("playerChampImage");
var champName = document.getElementById("champName");
var summonerSpell_D = document.getElementById("summonerSpell_D");
var summonerSpell_F = document.getElementById("summonerSpell_F");

var kda = document.getElementById("kda");
var kdaRatio = document.getElementById("kdaRatio");

var playerLevel = document.getElementById("playerLevel");
var playerCS = document.getElementById("playerCS");
var playerKP = document.getElementById("playerKP");

var response;

function httpGetAsync() {
    var serverRequest = new XMLHttpRequest();
    serverRequest.responseType = 'json';

    serverRequest.onreadystatechange = function() { 
        if (serverRequest.readyState == 4 && serverRequest.status == 200) {
            json = serverRequest.response;
            console.log(json);
            summonerIcon.src = "/lol_assets/11.15.1/img/profileicon/" + json.summonerIcon + ".png";
            displayName.innerHTML = json.summonerName;
            //set solo ranked info
            if(json.soloTier == "Unranked") {
                soloRank.innerHTML = "Unranked";
            } else {
                soloRank.innerHTML = json.soloTier + " " + json.soloDivision;
                soloLP.innerHTML = json.soloLP + " LP";
                soloWins.innerHTML = json.soloWins + "W";
                soloLosses.innerHTML = json.soloLosses + "L";
                soloWinRatio.innerHTML = "Win Rate: " + json.soloWinRate;
            }
            soloRankEmblem.src = "/ranked-emblems/Emblem_" + json.soloTier + ".png";

            //set flex ranked info
            if(json.flexTier == "Unranked") {
                flexRank.innerHTML = "Unranked";
            } else {
                flexRank.innerHTML = json.flexTier + " " + json.flexDivision;
                flexLP.innerHTML = json.flexLP + " LP";
                flexWins.innerHTML = json.flexWins + "W";
                flexLosses.innerHTML = json.flexLosses + "L";
                flexWinRatio.innerHTML = "Win Rate: " + json.flexWinRate;
            }
            flexRankEmblem.src = "/ranked-emblems/Emblem_" + json.flexTier + ".png";

            //Match history
            if(json.matches[0].matchResult) {
                winTag.innerHTML = "Win";
            } else {
                winTag.innerHTML = "Loss";
            }
            minutes = Math.floor(json.matches[0].gameDuration / 60000);
            seconds = Math.floor((json.matches[0].gameDuration % 60000) / 1000);
            gameLength.innerHTML = minutes + "m " + seconds + "s";
            gameMap.innerHTML = json.matches[0].gameMap;
            gameOption.innerHTML = json.matches[0].gameMode.replace(' games', '');
            json.matches[0].playerList.forEach(function (item, index) {
                var newPlayer = document.createElement("span");
                newPlayer.setAttribute('id', 'player' + index);
                newPlayer.setAttribute('class', 'player');
                matchPlayers.appendChild(newPlayer);
                championImage = document.createElement("img");
                championImage.src = "/lol_assets/11.15.1/img/champion/" + item.championName + ".png";
                championImage.setAttribute('class', 'playerMiniChampionImage');
                newPlayer.appendChild(championImage);
                newPlayer.append(item.summonerName);
                
                if(item.summonerName == json.summonerName) {
                    summonerSpell_D.src = "/lol_assets/11.15.1/img/spell/Summoner" + item.summonerD + ".png";
                    summonerSpell_F.src = "/lol_assets/11.15.1/img/spell/Summoner" + item.summonerF + ".png";
                    champName.innerHTML = item.championName;
                    playerChampImage.src = "/lol_assets/11.15.1/img/champion/" + item.championName + ".png";
                    kda.innerHTML = item.kills + " / " + item.deaths + " / " + item.assists;
                    kdaRatio.innerHTML = ((item.kills + item.assists) / item.deaths).toFixed(2) + " : 1 KDA";
                    playerLevel.innerHTML = "Level " + item.champLevel;
                    playerCS.innerHTML = item.cs + " CS";
                    if(item.teamId == "100") {
                        playerKP.innerHTML = "KP: " + Math.round(((item.kills + item.assists) / json.matches[0].team100TotalKills) * 100) + "%";
                    } else if(item.teamId == "200") {
                        playerKP.innerHTML = "KP: " + Math.round(((item.kills + item.assists) / json.matches[0].team200TotalKills) * 100) + "%";
                    }
                }
            });
        }
    };

    serverRequest.open("GET", serverURL + "?" + "summoner=" + summonerName, true); // true for asynchronous 
    serverRequest.send(null);
}

httpGetAsync();