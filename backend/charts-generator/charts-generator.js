const ChartJsImage = require('chartjs-to-image');

const chart = new ChartJsImage();
const axios = require('axios');

var data = []
axios.get('https://app.greenavicash.ru/api/chart/list-item')
  .then(function (response) {
    data = response.data
    if(data.length > 0) {
      generateChart()
    }
  })
  .catch(function (error) {
    console.log("error");
  })

function generateChart() {
  let item = data[0];
  data.shift()

  let symbol = item.symbol == "USDT" ? "USDCUSDT" : (item.symbol + "USDT")
  axios.get('https://api.binance.com/api/v3/klines?symbol=' + symbol + '&interval=1d&limit=50')
    .then(function (response) {

      var config = {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            data: [],
            pointStyle: 'cross',
            borderColor: 'black',
            fill: false
          }]
        },
        options: {
          bezierCurve : true,
          legend: {
            display: false,
          },
          scales: {
            xAxes: [{
              gridLines: {
                display: false,
                drawBorder: false,
              },
              ticks: {
                display: false,
              }
            }],
            yAxes: [{
              gridLines: {
                  display: false,
                  drawBorder: false,
              },
              ticks: {
                display: false,
                beginAtZero: true,
              }
            }]
          },
          elements: {
            point:{
                radius: 0
            }
          }
        }
      }
      response.data.forEach((item, i) => {
        config.data.labels.push(item[0])
        config.data.datasets[0].data.push(item[4])
      });

      try {
          chart.setConfig(config);
          chart.setWidth(200).setHeight(200).setBackgroundColor('transparent');
          chart.toFile('/var/www/greenavicash-app/web/charts/' + item.symbol + '.png');
      } catch (e) {
          console.log(e)
      }

      if(data.length > 0) {
        generateChart()
      }
    })
    .catch(function (error) {
      console.log("error");

      if(data.length > 0) {
        generateChart()
      }
    })
}
