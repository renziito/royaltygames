var Jackpot = function (options) {
    var date = new Date();
    var defaults = {
        chartContainer: document.getElementById('jackpot-canvas'),
        chartHeight: 500,
        roundStartIn: new Date(date.setSeconds(date.getSeconds() + 20)),
        gameStartIn: 5000,
        roundInfoContainer: document.getElementById('jackpot-round'),
        url: ''
    };

    this.options = this.extend(defaults, options);
    this.chartContainer = this.options.chartContainer;
    this.roundInfoContainer = this.options.roundInfoContainer;
    this.canvas = document.createElement('canvas');
    this.ctx = this.canvas.getContext("2d");

    this.colors = [];
    this.data = [];
    this.degrees = [];

    this.chartContainer.insertAdjacentHTML('beforeend', this.templateChart());
    this.roundInfoContainer.insertAdjacentHTML('beforeend', this.templateRoundInfo());
};

Jackpot.prototype.templateRoundInfo = function () {
    var html = '<div class="jackpot-round-info-container">';
    html += '<div class="jackpot-round-info">';
    html += '</div>';
    html += '</div>';

    return html;
}

Jackpot.prototype.updateTotal = function () {
    var totalContainer = this.options.chartContainer.getElementsByClassName("jackpot-pie-overlay-content");
    var total = 0;
    for (var key in this.data) {
        total += this.data[key].total;
    }

    totalContainer[0].innerHTML = total;
}

Jackpot.prototype.gameStart = function () {
    var _this = this;
    var countDownContainer = this.options.chartContainer.getElementsByClassName("jackpot-state-countdown");
    var countDownTitle = this.options.chartContainer.getElementsByClassName("jackpot-state-title");
    var seconds = this.options.gameStartIn / 1000;
    countDownTitle[0].innerHTML = "Next Round In";
    var init = setInterval(function () {
        if (seconds >= 0) {
            countDownContainer[0].innerHTML = seconds;
        } else {
            clearInterval(init);
            _this.roundStart();
        }
        seconds--;
    }, 1000);
}

Jackpot.prototype.roundStart = function () {
    var _this = this;
    var countDown = this.options.chartContainer.getElementsByClassName("jackpot-state-countdown");
    var countDownTitle = this.options.chartContainer.getElementsByClassName("jackpot-state-title");
    var now = new Date();
    var diff = parseInt(((this.options.roundStartIn.getTime() - now.getTime()) / 1000) + 1);
    countDownTitle[0].innerHTML = "Game Starting In";
    countDown[0].innerHTML = diff;

    var init = setInterval(function () {
        if (diff >= 0) {
            countDown[0].innerHTML = diff;
            diff--;
        } else {
            clearInterval(init);
            _this.girar();
        }
    }, 1000)
}

Jackpot.prototype.templateWinner = function (key) {
    var data = this.data[key];
    var color = this.colors[key];
    var html = '<div class="jackpot-winner" style="background: linear-gradient(90deg, rgba(' + color + ', 0.5) 0%, rgba(' + color + ', 0.4) 40%, rgba(' + color + ', 0.3) 70%, rgba(' + color + ', 0.2) 80%, rgba(' + color + ', 0.1) 100%);">';
    html += '<div class="row">';
    html += '<div class="col-5">';
    html += '<div class="jackpot-winner-round-info">';
    html += '<span>' + data.name + '</span>';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-7">';
    html += '</div>';
    html += '</div>';

    return html;
}

Jackpot.prototype.templatePlayer = function (data) {
    var color = this.colors[(this.colors.length - 1)];
    var html = '<div class="jackpot-player" style="background: linear-gradient(90deg, rgba(' + color + ', 0.22) 0%, rgba(' + color + ', 0.1) 10%, rgba(' + color + ', 0.05) 20%, rgba(0, 0, 0, 0.1) 35%, rgba(0, 0, 0, 0.1) 100%);">';
    html += '<div class="row">';
    html += '<div class="col-6">';
    html += '<div class="player-name">' + data.name + '</div>';
    html += '<div class="player-win-range" style="color:rgb(' + color + ')">0 - 83.3332</div>';
    html += '</div>';
    html += '<div class="col-6 text-right">';
    html += '<div class="deposit-count">Deposited 0</div>';
    html += '<div class="deposit-value">Total value ' + data.total + '</div>';
    html += '</div>';
    html += '</div>';
    html += '<div class="player-highlight" style="background: rgb(' + color + ');"></div>';
    html += '</div>';

    return html;
}

Jackpot.prototype.winner = function (value) {
    var players = this.roundInfoContainer.querySelector(".jackpot-round-info");

    for (var key in this.degrees) {
        if (value >= this.degrees[key].start && value <= this.degrees[key].end) {
            players.insertAdjacentHTML('afterbegin', this.templateWinner(key));
            break;
        }
    }
}

Jackpot.prototype.girar = function () {
    var _this = this;
    var spinnercontent = document.querySelector(".jackpot-spinner");
    var rand = Math.random() * 7200;
    var value = rand / 360;
    value = (value - parseInt(value.toString().split('.')[0])) * 360;
    setTimeout(function () {
        var countDown = document.querySelector(".jackpot-state-countdown");
        var countDownTitle = document.querySelector(".jackpot-state-title");
        countDownTitle.innerHTML = "Running";
        countDown.innerHTML = "";
        spinnercontent.style.cssText += "transform:rotate(" + rand + "deg)";
        setTimeout(function () {
            _this.winner(value);
        }, 5000);
    }, 5000);
}

Jackpot.prototype.calculateSizeDiv = function (percent) {
    var width = (this.options.chartHeight * percent);
    var height = (this.options.chartHeight * percent);
    var mWidth = (width / 2);
    var mHeight = (height / 2);

    return {
        width: width,
        height: height,
        minWidth: mWidth,
        minHeight: mHeight,
    };
};

Jackpot.prototype.templateChart = function () {
    var infoDiv = this.calculateSizeDiv(0.6);
    var spinnerDiv = this.calculateSizeDiv(0.7);

    var html = '<div class="jackpot-container">';
    html += '<div class="jackpot-content">';
    html += '<div class="jackpot-chart"></div>';
    html += '<div class="jackpot-spinner" style="width: ' + spinnerDiv.width + 'px;height: ' + spinnerDiv.height + 'px; margin-left:-' + spinnerDiv.minHeight + 'px;margin-top:-' + spinnerDiv.minWidth + 'px;">';
    html += '<img src="' + this.options.url + '/css/arrow.png">';
    html += '</div>';
    html += '<div class="jackpot-state-info" style="padding-top: 70px;width: ' + infoDiv.width + 'px;height: ' + infoDiv.height + 'px; margin-left:-' + infoDiv.minHeight + 'px;margin-top:-' + infoDiv.minWidth + 'px;">';
    html += '<div class="jackpot-row">';
    html += '<span class="jackpot-pie-overlay-title" style="padding-top:10px">Current Pot</span>';
    html += '<h2 class="jackpot-pie-overlay-content" style="padding-top:10px">0</h2>';
    html += '</div>';
    html += '<div>';
    html += '<span class="jackpot-state-title" style="padding-top:10px">Next Round In</span>';
    html += '<h2 class="jackpot-state-countdown" style="padding-top:10px">0</h2>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div > ';

    return html;
}

Jackpot.prototype.extend = function (src, obj) {
    for (var key in src) {
        if (obj.hasOwnProperty(key))
            src[key] = obj[key];
    }
    return src;
};

Jackpot.prototype.drawPieSlice = function (name, ctx, centerX, centerY, radius, startAngle, endAngle, color) {
    ctx.fillStyle = "rgb(" + color + ")";
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    ctx.closePath();
    ctx.fill();

    this.degrees.push({
        start: ((startAngle) - (1.5 * Math.PI)) * (180 / Math.PI),
        end: ((endAngle) - (1.5 * Math.PI)) * (180 / Math.PI)
    });
};

Jackpot.prototype.randomRGB = function () {
    var r = Math.floor(Math.random() * 256);
    var g = Math.floor(Math.random() * 256);
    var b = Math.floor(Math.random() * 256);
    return r + "," + g + "," + b;
}

Jackpot.prototype.bidUp = function (player) {
    this.data.push(player);
    this.colors.push(this.randomRGB());
    this.drawChart();
    var players = this.roundInfoContainer.getElementsByClassName("jackpot-round-info");
    players[0].insertAdjacentHTML('afterbegin', this.templatePlayer(player));
    this.updateTotal();
};

Jackpot.prototype.placeHolder = function () {

}

Jackpot.prototype.drawChart = function () {
    this.degrees = [];
    this.canvas.width = this.options.chartHeight;
    this.canvas.height = this.options.chartHeight;

    var total_value = 0;
    var color_index = 0;
    for (var key in this.data) {
        var val = this.data[key].total;
        total_value += val;
    }

    var start_angle = 1.5 * Math.PI;
    for (key in this.data) {
        val = this.data[key].total;
        var slice_angle = 2 * Math.PI * val / total_value;

        this.drawPieSlice(
                this.data[key].name,
                this.ctx,
                this.canvas.width / 2,
                this.canvas.height / 2,
                Math.min(this.canvas.width / 2, this.canvas.height / 2),
                start_angle,
                start_angle + slice_angle,
                this.colors[key]
                );

        start_angle += slice_angle;
        color_index++;
    }

    var draw = this.chartContainer.getElementsByClassName('jackpot-chart');
    draw[0].appendChild(this.canvas);
};