/**
 *
 * @Author: lori@flashbay.com
 *
 **/
/**
用法：
<script src="qr-clock.js"></script>
<style>
.svg-clock {
    height: 100px;
    width: 400px;
}
</style>
<div class="svg-clock" id="svg-clock-1"></div>
<div class="svg-clock" id="svg-clock-2"></div>
<div class="svg-clock" id="svg-clock-3"></div>
<div class="svg-clock" id="svg-clock-4"></div>
<script type="text/javascript">
var targetDOM1 = document.querySelector('#svg-clock-1');
var targetDOM2 = document.querySelector('#svg-clock-2');
var targetDOM3 = document.querySelector('#svg-clock-3');
var targetDOM4 = document.querySelector('#svg-clock-4');
var clock1 = new QRLCDClock(targetDOM1, 0x7);
var clock2 = new QRLCDClock(targetDOM2, 0x7, 13, 42);
var clock3 = new QRLCDClock(targetDOM3, 0x7, 0, 30, 05);
var clock4 = new QRLCDClock(targetDOM4, 0x6);
clock1.run();
clock2.run();
clock3.set24HourMode(false)
      .setCountdown(true);
clock3.run();
clock4.run();
</script>
   **/
var QRLCDClock = null;
+function($, window) {
    'use strict';
    var gDotDelimiter = [
        [3, 3,  4, 4],
        [3, 11, 4, 4]
    ];
    //12/20
    var gSegPolygons = [
        [[1, 1],  [2, 0],  [8, 0],   [9, 1],  [8, 2],  [2, 2]],//A
        [[9, 1],  [10, 2], [10, 8],  [9, 9],  [8, 8],  [8, 2]],//B
        [[9, 9],  [10,10], [10, 16], [9, 17], [8,16],  [8,10]],//C
        [[9, 17], [8, 18], [2, 18],  [1, 17], [2, 16], [8,16]],//D
        [[1, 17], [0, 16], [0, 10],  [1, 9],  [2,10],  [2,16]],//E
        [[1, 9],  [0, 8],  [0, 2],   [1, 1],  [2, 2],  [2, 8]],//F
        [[1, 9],  [2, 8],  [8, 8],   [9, 9],  [8, 10], [2, 10]]//G
    ];
    var gLcdDigits = [
        [0, 1, 2, 3, 4, 5],
        [1, 2],
        [0, 1, 6, 4, 3],
        [0, 1, 6, 2, 3],
        [5, 6, 1, 2],
        [0, 5, 6, 2, 3],
        [0, 5, 4, 3, 2, 6],
        [0, 1, 2],
        [0, 1, 2, 3, 4, 5, 6],
        [6, 5, 0, 1, 2, 3],
    ];
    QRLCDClock = function(targetDOM, style, h, m, s) {
        if(!(targetDOM instanceof Element)) {
            throw 'Invalid parameter 1';
        }
        this.targetDOM = targetDOM;
        this.updateByDelta = false;
        if('undefined' != typeof h 
           || 'undefined' != typeof m
           || 'undefined' != typeof s) {
           this.updateByDelta = true;
        }
        this.hour = isNaN(parseInt(h)) ? 0 : parseInt(h);
        this.min  = isNaN(parseInt(m)) ? 0 : parseInt(m);;
        this.sec  = isNaN(parseInt(s)) ? 0 : parseInt(s);;
        if('undefined' == typeof style) {
            style = QRLCDClock.HOUR | QRLCDClock.MIN | QRLCDClock.SEC;
        } else {
            style = style & 0x7;
        }
        this.style = style;
        this.highlight = '#F00';
        this.greyed    = '#EEE';
        this.use24HourMode = true;
        this.running = false;
        this.updateRid = null;
        this.countdown = false;
        this.ticksNum  = 0;
    };

    QRLCDClock.UNIT_ROWS = 20;
    QRLCDClock.UNIT_COLS = 12;
    QRLCDClock.HOUR = 0x4;
    QRLCDClock.MIN  = 0x2;
    QRLCDClock.SEC  = 0x1;
    //
    QRLCDClock.SVG_G_HOUR_ID_0 = 'svg_hour_0';
    QRLCDClock.SVG_G_HOUR_ID_1 = 'svg_hour_1';
    //
    QRLCDClock.SVG_G_HMD_ID    = 'svg_hour_min_d';
    //
    QRLCDClock.SVG_G_MIN_ID_0  = 'svg_min_0';
    QRLCDClock.SVG_G_MIN_ID_1  = 'svg_min_1';
    //
    QRLCDClock.SVG_G_MSD_ID    = 'svg_min_sec_d';
    //
    QRLCDClock.SVG_G_SEC_ID_0  = 'svg_sec_0';
    QRLCDClock.SVG_G_SEC_ID_1  = 'svg_sec_1';

    QRLCDClock.prototype.set24HourMode = function(use24HourMode) {
        this.use24HourMode = !!use24HourMode;
        return this;
    };

    QRLCDClock.prototype.setCountdown = function(flag) {
        this.countdown = !!flag;
        return this;
    };

    QRLCDClock.prototype.isAllZero = function() {
        return !this.hour && !this.min && !this.sec;
    };

    QRLCDClock.prototype.run = function() {
        if(!this.running) {
            this.running = true;
            var updateCallback = function(event) {
                this.eraseDotDelimiter(event);
                if(!(this.ticksNum++ % this.periodNum)) {
                    if(this.countdown) {
                        this.hour = Math.max(0, this.hour) % 100;
                        this.min  = Math.max(0, this.min) % 100;
                        this.sec  = Math.max(0, this.sec) % 100;
                        if(this.isAllZero()) {
                            return this;
                        }
                        if(--this.sec < 0) {
                            this.sec = 59;
                            if(--this.min < 0) {
                                this.min = 59;
                                --this.hour;
                            }
                        }
                    } else {
                        if(this.updateByDelta) {
                            if(60 == ++this.sec) {
                                ++this.min;
                                this.sec = 0;
                                if(60 == ++this.min) {
                                    ++this.hour;
                                    this.min = 0;
                                    if(this.use24HourMode) {
                                        this.hour %= 24;
                                    } else {
                                        this.hour %= 12;
                                    }
                                }
                            }
                        } else {
                            var currentDate = new Date;
                            this.hour = currentDate.getHours();
                            this.min  = currentDate.getMinutes();
                            this.sec  = currentDate.getSeconds();
                        }
                    }
                    this.updateUI();
                }
                return this;
            };
            updateCallback = updateCallback.bind(this);
            updateCallback();
            if(!(this.style & QRLCDClock.SEC)) {
                this.periodNum = 2;
            } else {
                this.periodNum = 1;
            }
            this.updateRid = setInterval(updateCallback, 1000 / this.periodNum);
        }
        return this;
    };

    QRLCDClock.prototype.stop = function() {
        if(this.running) {
            if(null !== this.updateRid) {
                clearInterval(this.updateRid);
                this.updateRid = null;
            }
            this.running = false;
        }
        return this;
    };

    QRLCDClock.prototype.eraseDotDelimiter = function(event) {
        if(!(this.style & QRLCDClock.SEC) && this._getLCDColumnsNum() > 3) {
            var offset = QRLCDClock.UNIT_COLS << 1;
            this._updateDotDelimiter(
                QRLCDClock.SVG_G_HMD_ID,
                offset, this.greyed
            );
        }
        return this;
    };

    QRLCDClock.prototype.getPolygonPointsOfSegs = function(segIndexSet, offsetX, offsetY) {
        var points = [];
        for(var i = 0; i < segIndexSet.length; ++i) {
            var segIndex = segIndexSet[i];
            var segPoints= [];
            for(var j = 0; j < gSegPolygons[segIndex].length; ++j) {
                var point = gSegPolygons[segIndex][j];
                var x = offsetX + point[0];
                var y = offsetY + point[1];
                segPoints.push(x + ',' + y);
            }
            points.push(segPoints);
        }
        return points;
    };

    QRLCDClock.prototype.getPolygonPointsOfDigit = function(digit, offsetX, offsetY) {
        var highlightPoints = this.getPolygonPointsOfSegs(gLcdDigits[digit], offsetX, offsetY);
        var greyedPoints    = [];
        var greyedSegIndex  = [0, 1, 2, 3, 4, 5, 6];
        for(var i = 0; i < gLcdDigits[digit].length; ++i) {
            var segIndex = gLcdDigits[digit][i];
            var j = greyedSegIndex.indexOf(segIndex);
            greyedSegIndex.splice(j, 1);
        }
        greyedPoints = this.getPolygonPointsOfSegs(greyedSegIndex, offsetX, offsetY);
        return [highlightPoints, greyedPoints];
    };

    QRLCDClock.prototype.setHighlightColor = function(highlightColor) {
        this.highlight = highlightColor;
        return this;
    };

    QRLCDClock.prototype.getHighlightColor = function() {
        return this.highlight;
    };

    QRLCDClock.prototype.getGreyedColor = function() {
        return this.greyed;
    };

    QRLCDClock.prototype.updateUI = function() {
        var offset = 0;
        var step;
        if(this.style & QRLCDClock.HOUR) {
            step = QRLCDClock.UNIT_COLS;
            this._updateDigit(
                QRLCDClock.SVG_G_HOUR_ID_0,
                offset, parseInt(this.hour / 10),
                this.highlight, this.greyed
            );
            offset += step;
            this._updateDigit(
                QRLCDClock.SVG_G_HOUR_ID_1,
                offset, this.hour % 10,
                this.highlight, this.greyed
            );
            offset += step;
        }
        if(offset > 0
           && ((this.style & QRLCDClock.MIN)
                || (this.style & QRLCDClock.SEC))) {
            this._updateDotDelimiter(
                QRLCDClock.SVG_G_HMD_ID,
                offset, this.highlight
            );
            offset += QRLCDClock.UNIT_COLS;
        }
        if(this.style & QRLCDClock.MIN) {
            step = QRLCDClock.UNIT_COLS;
            this._updateDigit(
                QRLCDClock.SVG_G_MIN_ID_0,
                offset, parseInt(this.min / 10),
                this.highlight, this.greyed
            );
            offset += step;
            this._updateDigit(
                QRLCDClock.SVG_G_MIN_ID_1,
                offset, this.min % 10,
                this.highlight, this.greyed
            );
            offset += step;
        }
        if(offset > 0
            && (this.style & QRLCDClock.SEC)) {
            this._updateDotDelimiter(
                QRLCDClock.SVG_G_MSD_ID,
                offset, this.highlight
            );
            offset += QRLCDClock.UNIT_COLS;
        }
        if(this.style & QRLCDClock.SEC) {
            step = QRLCDClock.UNIT_COLS;
            this._updateDigit(
                QRLCDClock.SVG_G_SEC_ID_0,
                offset, parseInt(this.sec / 10),
                this.highlight, this.greyed
            );
            offset += step;
            this._updateDigit(
                QRLCDClock.SVG_G_SEC_ID_1,
                offset, this.sec % 10,
                this.highlight, this.greyed
            );
        }
        return this;
    };

    QRLCDClock.prototype._getDotDelimterSVGHtml = function(offset, highlight) {
        var points = [];
        var dotDelimterHtml = [];
        for(var i = 0; i < gDotDelimiter.length; ++i) {
            var xywh = gDotDelimiter[i];
            var x = offset + xywh[0];
            var rectHtml = '<rect id="" x="' + x + '" y="' + xywh[1] + '" width="' + xywh[2] + '" height="' + xywh[3] + '" fill="' + highlight + '"/>';
            dotDelimterHtml.push(rectHtml); 
        }
        return dotDelimterHtml.join('');
    };

    QRLCDClock.prototype._updateDotDelimiter = function(groupId, offset, highlight) {
        var SVGGroupDOM = this._getSVGGroup(groupId);
        var dotDelimterHtml = this._getDotDelimterSVGHtml(offset, highlight);
        SVGGroupDOM.innerHTML = dotDelimterHtml;
        return this;
    };

    QRLCDClock.prototype._getDigitSVGHtml = function(offset, digit, highlight, greyed) {
        var points = this.getPolygonPointsOfDigit(digit, offset, 0);
        var digitSVGHtml = [];
        var colors = [highlight, greyed];
        for(var i = 0; i < points.length; ++i) {
            for(var j = 0; j < points[i].length; ++j) {
                var polygonHtml = '<polygon xmlns="http://www.w3.org/2000/svg" id="" points="' + points[i][j].join(' ') + '" fill="' + colors[i] + '"/>';
                digitSVGHtml.push(polygonHtml);
            }
        }
        return digitSVGHtml.join('');
    };

    QRLCDClock.prototype._updateDigit = function(groupId, offset, digit, highlight, greyed) {
        digit &= 0xF;
        if(isNaN(digit) || digit > 9 || digit < 0) {
            throw 'Invalid digit: ' + digit + ', expect 0 ~ 9';
        }
        var SVGGroupDOM = this._getSVGGroup(groupId);
        var digitSVGHtml= this._getDigitSVGHtml(offset, digit, highlight, greyed);
        SVGGroupDOM.innerHTML = digitSVGHtml;
        return this;
    };

    QRLCDClock.prototype._getLCDColumnsNum = function() {
        var columns = 0;
        if(this.style & QRLCDClock.HOUR) {
            columns += 2;
        }
        if(this.style & QRLCDClock.MIN) {
            if(columns) {
                ++columns;
            }
            columns += 2;
        }
        if(this.style & QRLCDClock.SEC) {
            if(columns) {
                ++columns;
            }
            columns += 2;
        }
        return columns;
    };

    QRLCDClock.prototype._getSVGContainer = function() {
        var columnNum = this._getLCDColumnsNum();
        var SVGDOM    = this.targetDOM.querySelector('svg');
        if(!SVGDOM) {
            var svgHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 ' + (QRLCDClock.UNIT_COLS * columnNum) + ' ' + QRLCDClock.UNIT_ROWS + '"></svg>';
            this.targetDOM.innerHTML = svgHtml;
            SVGDOM = this.targetDOM.querySelector('svg');
        }
        return SVGDOM;
    };

    QRLCDClock.prototype._getSVGGroup = function(groupId) {
        var SVGDOM  = this._getSVGContainer();
        var selector= 'g[id="' + groupId + '"]';
        var groupDOM = SVGDOM.querySelector(selector);
        if(!groupDOM) {
            var svgGroupHtml = '<g xmlns="http://www.w3.org/2000/svg" id="' + groupId + '" style="fill-rule:evenodd;stroke:#FFFFFF;stroke-width:0.2;stroke-opacity:1;stroke-linecap:butt;stroke-linejoin:miter;"></g>';
            SVGDOM.innerHTML += svgGroupHtml;
            groupDOM = SVGDOM.querySelector(selector);
        }
        groupDOM.innerHTML = '';
        return groupDOM;
    };
}(window.jQuery, window);
