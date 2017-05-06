// This example requires the Visualization library. Include the libraries=visualization
// parameter when you first load the API. For example:
// <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=visualization">

var map, heatmap;
var filter = { 
    "year": 2011, 
    "sex": null, 
    "drunk": null, 
    "fatalities": null 
};

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7,
        center: { lat: 44, lng: -120 },
        mapTypeId: 'roadmap'
    });

    heatmap = new google.maps.visualization.HeatmapLayer({
        data: getPoints(),
        map: map,
        radius: 20
    });
}

function toggleHeatmap() {
    heatmap.setMap(heatmap.getMap() ? null : map);
}

function changeGradient() {
    var gradient = [
        'rgba(0, 255, 255, 0)',
        'rgba(0, 255, 255, 1)',
        'rgba(0, 191, 255, 1)',
        'rgba(0, 127, 255, 1)',
        'rgba(0, 63, 255, 1)',
        'rgba(0, 0, 255, 1)',
        'rgba(0, 0, 223, 1)',
        'rgba(0, 0, 191, 1)',
        'rgba(0, 0, 159, 1)',
        'rgba(0, 0, 127, 1)',
        'rgba(63, 0, 91, 1)',
        'rgba(127, 0, 63, 1)',
        'rgba(191, 0, 31, 1)',
        'rgba(255, 0, 0, 1)'
    ]
    heatmap.set('gradient', heatmap.get('gradient') ? null : gradient);
}

function changeRadius() {
    heatmap.set('radius', heatmap.get('radius') ? null : 20);
}

function changeOpacity() {
    heatmap.set('opacity', heatmap.get('opacity') ? null : 0.2);
}

function getPoints() {
    var arr = [];
    var stcase = [];
    var lat;
    var lon;
    var url = "http://web.engr.oregonstate.edu/~desiletn/cs458/accident.php";
    var queryString = generateQueryString(filter);

    $.ajax({
        async: false,
        url: url + queryString,
        dataType: "json",
        success: function (data) {
            //data is the JSON string
            data.forEach(function (d) {
                st = d["ST_CASE"];
                lat = d["LATITUDE"];
                lon = d["LONGITUD"];

                arr.push(new google.maps.LatLng(lat, lon));
            });
        },
    });

    return arr;
}

// Convert filter object into GET query string
function generateQueryString(obj){
    var string = "?";

    var i = 0;
    for(var key in obj){
        var val = obj[key];
        if(val){
            if(i == 0){
                string += key + "=" + val;
            }else{
                string += "&" + key + "=" + val;
            }
        }
        i++;
    }

    console.log(string);

    return string;
}

function getRadioOption(radioGroup){
    for(var i = 0; i < radioGroup.length; i++){
        if(radioGroup[i].checked){
           return radioGroup[i].value;
        }
    }

    return null;
}

/* --- UI stuff --- */

var yearSlider = document.getElementById("yearSlider");
var yearValue = document.getElementById("yearValue");
var genderGroup = document.getElementsByName("sex");
var drinkingGroup = document.getElementsByName("drinking");
var fatalitiesGroup = document.getElementsByName("fatalities");
var optionsDiv = document.getElementById("options");

function toggleOptions(){
    if(optionsDiv.style.display == "none"){
        optionsDiv.style.display = "block";
    }else{
        optionsDiv.style.display = "none";
    }
}

function updateMap(){
    // Update year text 
    yearValue.textContent = yearSlider.value;

    // Get selections from radio groups
    var gender = getRadioOption(genderGroup);
    var drinking = getRadioOption(drinkingGroup);
    var fatalities = getRadioOption(fatalitiesGroup);

    // Update filter object
    filter = {
        year: yearSlider.value,
        sex: gender,
        drunk: drinking,
        fatalities: fatalities
    };

    // Get new map points with updated filter 
    var points = getPoints();
    
    // Clear existing layer with old points
    heatmap.setMap(null)

    // Add new layer with new points
    heatmap = new google.maps.visualization.HeatmapLayer({
        data: points,
        map: map,
        radius: 20
    });
}

