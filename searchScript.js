const serverURL = "http://54.187.199.13/test.php";

const urlSearchParams = new URLSearchParams(window.location.search);
const summonerName = urlSearchParams.get("summoner");

var display = document.getElementById("displayName");
var rankEmblem = document.getElementById("rankEmblem");
var summonerIcon = document.getElementById("summonerIcon");

var response;

function httpGetAsync() {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.responseType = 'json';

    xmlHttp.onreadystatechange = function() { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            json = xmlHttp.response;
            console.log(json);
            rankEmblem.src = "/ranked-emblems/Emblem_" + json.soloTier + ".png";
            summonerIcon.src = "/lol_assets/11.15.1/img/profileicon" + json.summonerIcon + ".png";
            display.innerHTML = json.soloTier + " " + json.soloDivision;
        }
    };

    xmlHttp.open("GET", serverURL + "?" + "summoner=" + summonerName, true); // true for asynchronous 
    xmlHttp.send(null);
}

httpGetAsync();