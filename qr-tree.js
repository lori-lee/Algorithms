/**
 *
 * @Author: lori@flashbay.com
 *
 * @WARNING: NEVER change below codes unless you are clear what you are doing.
 *
 **/
var SyntaxTreeVisualizer = null;
+function($, window) {
    'use strict';
    SyntaxTreeVisualizer = function(canvasDOM, levelOrderNodesInfo) {
        this.canvasDOM = canvasDOM;
        this.canvasCtx = canvasDOM.getContext('2d');
        this.levelOrderNodesInfo = levelOrderNodesInfo;
        this.fontSize = 12;
        this.fontColor= '#000';
        this.fontFamily = 'serif';
        this.bgColor  = '#DDE';
        this.borderColor = '#000';
        this.gapHeight   = 35;
        this.gapWidth    = 5;
        this.padding     = 5;
        this.boxHeight   = 22;
        this.maxCharsNum = 80;
        this.radius      = 15;
        this.unit = 'px';
        this.dirty= true;
        this.nodesInfoLayout = [];
        this.maxX = 0;
        this.maxY = 0;
    };

    SyntaxTreeVisualizer.prototype.setFontSize = function(size) {
        var _size = parseInt(size);
        var _padding = parseInt(this.padding);
        if(!isNaN(_size) && !isNaN(_padding)) {
            this.fontSize = _size;
            this.boxHeight= ((_padding << 1) + _size);
        }
        return this;
    };

    SyntaxTreeVisualizer.prototype.truncate = function(str) {
        if(str.length > this.maxCharsNum) {
            str = str.substr(0, this.maxCharsNum - 3) + '...';
        }
        return str;
    };

    SyntaxTreeVisualizer.prototype.evaluateMetrics = function(str, addPadding) {
        var font = this.fontSize + this.unit + ' ' + this.fontFamily;
        this.canvasCtx.font = font;
        var _metrics = this.canvasCtx.measureText(str);
        var metrics = {};
        metrics.height0= this.fontSize;
        metrics.width0 = _metrics.width;
        if(!addPadding) {
            addPadding = 0;
        }
        addPadding *= 2;
        metrics.width  = metrics.width0 + addPadding;
        metrics.height = metrics.height0 + addPadding;
        return metrics;
    };

    SyntaxTreeVisualizer.prototype.clone = function(target) {
        var cloned = JSON.parse(JSON.stringify(target));
        return cloned;
    };

    SyntaxTreeVisualizer.prototype.adjustNodeOffseX = function(currentNode, prevNext, direction) {
        var dx = 0;
        if(currentNode && prevNext) {
            var geometry = currentNode['geometry'];
            var _geometry= prevNext['geometry'];
            var type = this.isLeafNode(currentNode);
            var _type= this.isLeafNode(prevNext);
            //
            if(!_type) {
                _geometry['x']  += geometry['dx'];
                _geometry['rx'] += geometry['dx'];
            }
            _geometry['dx'] = geometry['dx'];
            var _x = geometry['x'] + geometry['w'] + this.gapWidth;
            var _dx= _x - _geometry['x'];
            if(direction <= 0) {
                _x = _geometry['x'] + _geometry['w'] + this.gapWidth;
                _dx= _x - geometry['x'];
            }
            var flag = (direction <= 0 ? -1 : 1);
            if(_dx > 0) {
                _geometry['x']  += _dx * flag;
                _geometry['rx'] += _dx * flag;
                if(!_type) {
                    _geometry['dx'] += _dx * flag;
                }
            } else if(_dx < 0) {
                if(_type) {
                    _geometry['x']  += _dx * flag;
                    _geometry['rx'] += _dx * flag;
                }
            }
        }
        return dx;
    };

    SyntaxTreeVisualizer.prototype.isLeafNode = function(nodeInfo) {
        return !(nodeInfo && nodeInfo['left'] && nodeInfo['right']);
    };

    SyntaxTreeVisualizer.prototype.calcParentXLayout = function(left, right) {
        var m = null;
        if(left && right) {
            var xl = left['geometry']['x'];
            var yl = left['geometry']['y'];
            var wl = left['geometry']['w'];
            var xr = right['geometry']['x'];
            var x  = 0.5 * (xl + xr + wl);
            var y  = yl - this.gapHeight;
            m = {
                x: x,
                y: y,
            };
        }
        return m;
    };

    SyntaxTreeVisualizer.prototype.calcLayout = function() {
        if(this.dirty) {
            this.dirty = false;
            var nodesInfoLayout = [];
            for(var i in this.levelOrderNodesInfo) {
                var nodeInfo = this.levelOrderNodesInfo[i];
                var level = nodeInfo['level'];
                var idx   = nodeInfo['index'];
                if(!nodesInfoLayout[level]) {
                    nodesInfoLayout[level] = [];
                }
                nodesInfoLayout[level][idx] = this.clone(this.levelOrderNodesInfo[i]);
                var m = this.evaluateMetrics(this.truncate(nodeInfo['valueTxt']), this.padding);
                nodesInfoLayout[level][idx]['geometry'] = {
                    x: 0, y: 0, w: m.width, h: m.height, w0: m.width0, h0: m.height0,
                    rx: 0, ry: 0, r: this.radius, dx: 0
                };
            }
            var height = nodesInfoLayout.length - 1;
            var deltaHeight = this.boxHeight + (this.radius * 2) + this.gapHeight;
            var offsetX = 0x7FFFFFFF, offsetY = 0x0;
            var d = 2 * this.radius;
            var dh= this.gapHeight + d + this.fontSize + 2 * this.padding;
            var dy = 0;
            var stack = [];
            if(height >= 0) {
                stack.push(nodesInfoLayout[height][0]);
            }
            while(stack.length) {
                var current = stack.pop();
                var level   = current['level'];
                var currentLevelNodes = nodesInfoLayout[level];
                for(var i = current['index']; i >= 0; --i) {
                    this.adjustNodeOffseX(currentLevelNodes[i], currentLevelNodes[i - 1], -1);
                    currentLevelNodes[i]['geometry']['y'] = dy;
                }
                for(var i = current['index']; i < currentLevelNodes.length; ++i) {
                    this.adjustNodeOffseX(currentLevelNodes[i], currentLevelNodes[i + 1], 1);
                    currentLevelNodes[i]['geometry']['y'] = dy;
                }
                if(level > 0) {
                    var parent = nodesInfoLayout[level - 1][current['idxParent']];
                    stack.push(parent);
                    for(var i = 0; i < currentLevelNodes.length; i += 2) {
                        var left  = currentLevelNodes[i];
                        var right = currentLevelNodes[i + 1];
                        var m = this.calcParentXLayout(left, right);
                        var idxParent = left['idxParent'];
                        var parent    = nodesInfoLayout[level - 1][idxParent];
                        var geometry  = parent['geometry'];
                        var x = m['x'] - 0.5 * geometry['w'];
                        var y = m['y'] - this.boxHeight - d;
                        geometry['x'] = x;
                        geometry['y'] = y;
                        geometry['rx']= m['x'];
                        geometry['ry']= m['y'] - this.radius;
                    }
                }
                dy -= dh;
            }
            for(var level = 0; level <= height; ++level) {
                var currentLevelNodes = nodesInfoLayout[level];
                for(var i = 0; i < currentLevelNodes.length; ++i) {
                    var current = currentLevelNodes[i];
                    var idxParent = current['idxParent'];
                    var geometry  = current['geometry'];
                    if(level) {
                        var parent    = nodesInfoLayout[level - 1][idxParent];
                        var pGeometry = parent['geometry'];
                        geometry['dx'] += pGeometry['dx'];
                        geometry['x']  += pGeometry['dx'];
                        geometry['rx'] += pGeometry['dx'];
                    }
                    offsetX = Math.min(offsetX, geometry['x']);
                }
            }
            if(height >= 0) {
                offsetX = -offsetX + this.padding;
                offsetY = -nodesInfoLayout[0][0]['geometry']['y'] + this.padding;
                if(offsetX || offsetY) {
                    for(var level = 0; level <= height; ++level) {
                        var currentLevelNodes = nodesInfoLayout[level];
                        for(var i = 0; i < currentLevelNodes.length; ++i) {
                            var current = currentLevelNodes[i];
                            var geometry= current['geometry'];
                            geometry['x'] += offsetX;
                            geometry['y'] += offsetY;
                            geometry['rx'] += offsetX;
                            geometry['ry'] += offsetY;
                            var x = geometry['x'] + geometry['w'];
                            var y = geometry['y'] + geometry['h'];
                            this.maxX = Math.max(this.maxX, x);
                            this.maxY = Math.max(this.maxY, y);
                        }
                    }
                }
            }
            this.nodesInfoLayout = nodesInfoLayout;
        }
        return this.nodesInfoLayout;
    };

    SyntaxTreeVisualizer.prototype.crossPoint = function(x, y, rx, ry, r) {
        var vector = [x - rx, y - ry];
        var len    = Math.sqrt(Math.pow(vector[0], 2) + Math.pow(vector[1], 2));
        return {
            x: rx + r * vector[0] / len,
            y: ry + r * vector[1] / len
        };
    };

    SyntaxTreeVisualizer.prototype.render = function() {
        var nodesInfoLayout = this.calcLayout();
        this.canvasDOM.setAttribute('width', this.maxX + this.padding);
        this.canvasDOM.setAttribute('height', this.maxY + this.padding);
        var fontStyle = this.fontSize + this.unit + ' ' + this.fontFamily;
        var boldFontStyle = 'bold ' + this.fontSize + this.unit + ' ' + this.fontFamily;
        for(var i = nodesInfoLayout.length - 1; i >= 0; --i) {
            var currentLevelNodes = nodesInfoLayout[i];
            for(var j = 0; j < currentLevelNodes.length; ++j) {
                var currentNode = currentLevelNodes[j];
                var geometry = currentNode['geometry'];
                //
                this.canvasCtx.fillStyle = this.bgColor;
                this.canvasCtx.fillRect(geometry['x'], geometry['y'], geometry['w'], this.boxHeight);
                this.canvasCtx.fillStyle= this.fontColor;
                this.canvasCtx.font = fontStyle;
                this.canvasCtx.fillText(this.truncate(currentNode['valueTxt']), geometry['x'] + this.padding, geometry['y'] + this.boxHeight - this.padding);
                if(currentNode['left'] && currentNode['right']) {
                    var rx = geometry['rx'], ry = geometry['ry'];
                    this.canvasCtx.beginPath();
                    this.canvasCtx.fillStyle = this.bgColor;
                    this.canvasCtx.arc(rx, ry, geometry['r'], 0, 2 * Math.PI);
                    this.canvasCtx.fill();
                    this.canvasCtx.fillStyle= this.fontColor;
                    var left = nodesInfoLayout[i + 1][currentNode['idxLeft']];
                    var right= nodesInfoLayout[i + 1][currentNode['idxRight']];
                    var lxc = left['geometry']['x'] + 0.5 * left['geometry']['w'];
                    var lyc = left['geometry']['y'];
                    var rxc = right['geometry']['x'] + 0.5 * right['geometry']['w'];
                    var ryc = right['geometry']['y'];
                    var crossPointL = this.crossPoint(lxc, lyc, rx, ry, geometry['r']);
                    var crossPointR = this.crossPoint(rxc, ryc, rx, ry, geometry['r']);
                    this.canvasCtx.lineWidth = 0.5;
                    this.canvasCtx.beginPath();
                    this.canvasCtx.moveTo(crossPointL['x'], crossPointL['y']);
                    this.canvasCtx.lineTo(lxc, lyc);
                    this.canvasCtx.stroke();
                    this.canvasCtx.beginPath();
                    this.canvasCtx.moveTo(crossPointR['x'], crossPointR['y']);
                    this.canvasCtx.lineTo(rxc, ryc);
                    this.canvasCtx.stroke();
                    //
                    var m = this.evaluateMetrics(this.truncate(currentNode['operatorTxt']), 0);
                    var x = rx - m['width'] * 0.5;
                    var y = ry + this.fontSize * 0.5;
                    this.canvasCtx.fillStyle= '#F00';
                    this.canvasCtx.font = boldFontStyle;
                    this.canvasCtx.fillText(currentNode['operatorTxt'], x, y);
                }
            }
        }
        return this;
    };
}(window.jQuery, window);
