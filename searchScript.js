var cors_api_url = 'https://cors-anywhere.herokuapp.com/';

function searchFunction() {
    summonerName = "magicwizmc";
    const apiKey = "RGAPI-e5edda1c-73e2-474d-aac1-dcefc7876a64";

    const link = cors_api_url + 'https://na1.api.riotgames.com/lol/summoner/v4/summoners/by-name/' + summonerName + '?api_key=' + apiKey;
    
    fetch(link)
     .then(response => response.json())
     .then(data => console.log(data));
}

searchFunction();