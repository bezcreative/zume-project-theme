
'use strict'
jQuery(document).ready(function($) {

  let map = function () {
    console.log("map");
    google.charts.load('current', {
       'packages': ['geochart'],
       // Note: you will need to get a mapsApiKey for your project.
       // See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
       'mapsApiKey': 'AIzaSyAUbmUVr7LSMCygtgzz1gIVACvSE-teDgs'
     });
     google.charts.setOnLoadCallback(drawMarkersMap);

    function drawMarkersMap() {
      // var data = google.visualization.arrayToDataTable(
      //     wpApiSettings.groups
      // );
      var data = new google.visualization.DataTable();
      data.addColumn('number', 'Latitude');
      data.addColumn('number', 'Longitude')
      data.addRows(
        wpApiSettings.locations
      )


      var options = {
        sizeAxis: { minValue: 0, maxValue: 1 },
        region: 'US',
        title: "Location of each group",
        displayMode: 'markers',
        defaultColor: '#000ef5',
        backgroundColor: "#caf8ff"
      };

      var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
  }
  map()

  let group_sizes = function () {
    google.charts.load("current", {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
      var data = google.visualization.arrayToDataTable(wpApiSettings.sizes);

      var view = new google.visualization.DataView(data);

      var options = {
        bars: 'horizontal',
        title: "Number of groups according to their member count",
        legend: {position: "none"},
      };
      var chart = new google.visualization.BarChart(document.getElementById("sizes"));
      chart.draw(view, options)
    }
  }
  group_sizes()

  let group_progress = function () {
    google.charts.load("current", {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
      var data = google.visualization.arrayToDataTable(wpApiSettings.steps);

      var view = new google.visualization.DataView(data);

      var options = {
        bars: 'horizontal',
        title: "Number of groups completing each session",
        legend: {position: "none"},
      };
      var chart = new google.visualization.BarChart(document.getElementById("sessions"));
      chart.draw(view, options)
    }
  }
  group_progress()
})