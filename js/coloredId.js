(function(){

var b64chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
var b64decs = function(){
    var ret = {};
    for (var i = 0; i < b64chars.length; i++) ret[b64chars.charAt(i)] = i;
    return ret;
}();

var halfid2num = function(idstr) {
    idstr = idstr.replace(/\./g, '+');
        var n = (b64decs[ idstr.charAt(0) ] << 18)
            |   (b64decs[ idstr.charAt(1) ] << 12)
            |   (b64decs[ idstr.charAt(2) ] <<  6)
            |   (b64decs[ idstr.charAt(3) ]);
    return n;
};

var colorFromId = function(idstr, count, mode) {
    if (idstr.length != 8) return;
    // �F��H�F�l��0�`360�i�p�x�j
    var n1 = halfid2num(idstr.substr(0, 4));
    var n2 = halfid2num(idstr.substr(4, 4));
    var h1 = n1 / 360 * 360;
    var h2 = n2 / 360 * 360;

    if (mode == null) mode = 'L*C*h';
    var ret = (function() {
        switch (mode) {
            case 'HSV':     // HSV�F���
                // �ʓxS(HSV)�F�l��0�i�W���j�`1�i�Z��)
                var S = count * 0.05;
                if (S > 1) S = 1;
                // ���xV(HSV)�F�l��0�i�Â��j�`1�i���邢�j
                var V = 1 - count * 0.025;
                if (V < 0.1) V = 0.1;
                return {label : ColorLib.HSV2RGB([h2, 1, 0.6]),
                        body  : ColorLib.HSV2RGB([h1, S, V]) };
                return [ColorLib.HSV2RGB([h1, S, V]), ColorLib.HSV2RGB([h2, 1, 0.6])];
                break;
            case 'HLS':     // HLS�F���
                // �P�xL(HLS)�F�l��0�i���j�`0.5�i���F�j�`1�i���j
                var L = 0.95 - count * 0.025;
                if (L < 0.1) L = 0.1;
                // �ʓxS(HLS)�F�l��0�i�D�F�j�`1�i���F�j
                var S = count * 0.05;
                if (S > 1) S = 1;
                return {label : ColorLib.HLS2RGB([h2, 0.6, 0.5]),
                        body  : ColorLib.HLS2RGB([h1, L, S]) };
                break;
            case 'L*C*h':   // L*C*h�F���
                // ���xL*(L*C*h)�F�l��0�i���j�`50�i���F�j�`100�i���j
                var L = 100 - count * 2.5;
                if (L < 10) L = 10;
                // �ʓxC*(L*C*h)�F�l��0�i�D�F�j�`100�i���F�j
                var C = Math.floor(40 * Math.sin((count * 180 / 50) * Math.PI / 180) + 8);
                if (C < 0) C = 0;
                C += (30 - L) > 0 ? 30 - L : 0;
                return {label : ColorLib.LCh2RGB([50, 60, h2]),
                        body  : ColorLib.LCh2RGB([L, C, h1]) };
                break;
        }
    })();
    ret.nums = [n1, n2];
    return ret;
};

var styleFromId = function(idstr, count, hissi, mode) {
    if (mode == null) mode = 'L*C*h';
    idstr = idstr.substr(0, 8);
    var colors = colorFromId(idstr, count, mode);
    var f = function (c) {
        var light = c.type == 'L*C*h' ? c.LCh[0]
            : (RGB2LCh([c.r, c.g, c.b]))[0];
        var ret = {backgroundColor : c.color, color : light > 60 ? '#000' : '#fff'};
        if (hissi && hissi > 0 && count >= hissi) ret.textDecoration = 'blink';  // �K���`�F�b�J�[����
        return ret;
    };
    return {label : f(colors.label), body : f(colors.body),
            klass : cssClassFromNum(colors.nums[0], colors.nums[1]) };
};

var cssClassFromNum = function(n1, n2) {
    return 'idcss-'
        + ('000000' + n1.toString(16)).slice(-6)
        + ('000000' + n2.toString(16)).slice(-6);
};

var cssClassFromId = function(idstr) {
    var n1 = halfid2num(idstr.substr(0, 4));
    var n2 = halfid2num(idstr.substr(4, 4));
    return cssClassFromNum(n1, n2);
};

var toggle = function(idstr, cnt, colorStyle, hissi) {
    var styles = styleFromId(idstr, cnt, hissi);
    var d0 = delrule(styles.klass + '-l');
    var d1 = delrule(styles.klass + '-b');
    var n  = delrule(styles.klass);
    if (!d0 && !d1) {
        for (var i in colorStyle) {
            styles.label[i] = (styles.label[i] ? styles.label[i] + ' ' : '')
                + colorStyle[i];
            styles.body[i] = (styles.body[i] ? styles.body[i] + ' ' : '')
                + colorStyle[i];
        }
        insrule(styles.label, styles.klass + '-l');
        insrule(styles.body, styles.klass + '-b');
        return true;;
    }
    return false;
};

var STYLEID = 0;    // �W�I�ɂ���X�^�C���V�[�g��ID

var getRule = function(kls, f) {
    var i = STYLEID;
    var rules = document.styleSheets[i].cssRules
        ? document.styleSheets[i].cssRules
        : document.styleSheets[i].rules;
    for(var j = 0; j < rules.length; j++) {
        if (rules[j].selectorText && rules[j].selectorText == ('.' + kls)) {
            return rules[j];
        }
    }
    return null;
};

var delrule = function(kls) {
//    for(var i=0; i<document.styleSheets.length; i++) {
    var i = STYLEID;
    var rules = document.styleSheets[i].cssRules
        ? document.styleSheets[i].cssRules
        : document.styleSheets[i].rules;
    for(var j = 0; j < rules.length; j++) {
        if (rules[j].selectorText && rules[j].selectorText == ('.' + kls)) {
            if (document.all) document.styleSheets[i].removeRule(j)
            else document.styleSheets[i].deleteRule(j);
            return true;
        }
    }
//    }
    return false;
};

var clearrule = function() {
    var i = STYLEID;
    var rules = document.styleSheets[i].cssRules
        ? document.styleSheets[i].cssRules
        : document.styleSheets[i].rules;
    var f = function() {
        var hit = 0;
        for(var j = 0; j < rules.length; j++) {
            if (rules[j].selectorText && rules[j].selectorText.substr(0, '.idcss-'.length) == '.idcss-') {
                if (document.all) document.styleSheets[i].removeRule(j)
                else document.styleSheets[i].deleteRule(j);
                hit++;
            }
        }
        return hit;
    };
    while (f() > 0);
};

var insrule = function(styles, kls) {
    var ss = document.styleSheets[STYLEID];
    var style = '';
    for (var i in styles) style += i.replace(/([A-Z])/g, "-$1").toLowerCase() + ':' + styles[i] + ';';
    if (document.all) ss.addRule('.' + kls, style)
    else ss.insertRule('.' + kls + '{' + style + '}', ss.cssRules.length);
};


var makeColor = function(idstr, cnt, colorStyle, hissi) {
    var styles = styleFromId(idstr, cnt, hissi);
    for (var i in colorStyle) {
        styles.label[i] = (styles.label[i] ? styles.label[i] + ' ' : '')
            + colorStyle[i];
        styles.body[i] = (styles.body[i] ? styles.body[i] + ' ' : '')
            + colorStyle[i];
    }
    insrule(styles.label, styles.klass + '-l');
    insrule(styles.body, styles.klass + '-b');
};

var addStyles = function(idstr, cnt, hissi, addStyle) {
    var styles = styleFromId(idstr, cnt, hissi);
    for (var i in addStyle) {
        styles.label[i] = addStyle[i];
        styles.body[i] = addStyle[i];
    }
    var l = getRule(styles.klass + '-l');
    if (l) for (var i in styles.label) l.style[i] = styles.label[i];
    else insrule(styles.label, styles.klass + '-l');
    var b = getRule(styles.klass + '-b');
    if (b) for (var i in styles.body) b.style[i] = styles.body[i];
    else insrule(styles.body, styles.klass + '-b');
    var n = getRule(styles.klass);
    if (n) b.style.color = addStyle.color;
    else insrule({color : addStyle.color}, styles.klass);
};


if (!this['ColoredIDLib']) ColoredIDLib = {
    makeColor : makeColor,
    toggle : toggle,
    addStyles : addStyles,
    clearrule : clearrule
};

})();

(function(){

var addIdlist = function(addlist) {
    for (var i in addlist) {
        if (this.idlist[i]) this.idlist[i] = this.idlist[i] + addlist[i]
        else this.idlist[i] = addlist[i];
    }
};

var initColor = function(rate, idlist) {
    if (!rate) rate = this.rate;
    if (!idlist) idlist = this.idlist;
    if (rate && idlist) {
        for (var i in idlist) {
            if (idlist[i] >= rate)
                ColoredIDLib.makeColor(i, idlist[i], this.colorStyle, this.hissi);
        }
    }
}

var refreshColor = function(rate) {
    this.clear();
    this.initColor(rate);
};

var toggle = function(idstr) {
    if (this.idlist[idstr])
        ColoredIDLib.toggle(idstr, this.idlist[idstr], this.colorStyle, this.hissi);
};


var getColor = function() {
    color = this.colors.shift();
    this.colors.push(color);
    return color;
};

var mark = function(idstr) {
    var style = {color : this.getColor()};
    var addStyle = (function(p) {
            var F = new Function();
            F.prototype = p;
            var ret = new F();
            ret.color = style.color;
            return ret;
            })(this.highlightStyle);
    ColoredIDLib.addStyles(idstr, this.idlist[idstr], this.hissi, addStyle);
};

var click = function(idstr, evt) {
    if (evt.type == 'click') this.toggle(idstr)
    else if (evt.type == 'dblclick') this.mark(idstr);
};

var createSPMmenu = function (idval) {
    var amenu = document.createElement('div');
    amenu.id = idval;
    amenu.className = 'spm';
    amenu.appendItem = function()
    {
        this.appendChild(SPM.createMenuItem.apply(this, arguments));
    }
    SPM.setOnPopUp(amenu, amenu.id, true);

    var _this = this;
    amenu.appendItem('�S�ăN���A', function() {_this.clear()});
    if (this.tops) amenu.appendItem('�g�b�v10', function() {_this.refreshColor(_this.tops)});
    if (this.average) amenu.appendItem('����(' + idCol.average + ')�ȏ�', function() {_this.refreshColor(_this.average)});
    amenu.appendItem(idCol.rate + '�ȏ�', function() {_this.refreshColor(_this.rate)});
    if (this.rate != 2) amenu.appendItem('2�ȏ�', function() {_this.refreshColor(2)});
    return amenu;
};

var setupSPM = function (objName) {
    var amenu = this.createSPMmenu(objName + '_col');
    document.getElementById(objName + '_spm').appendItem(
            'ID�J���[', null, objName + '_col');
    document.getElementById('popUpContainer').appendChild(amenu);
};


if (!this['IDColorChanger']) {
    IDColorChanger = function(idlist, hissi) {
        this.idlist = idlist;
        this.hissi = hissi;
    };
    IDColorChanger.prototype = {
        idlist : {},
        hissi : null,               // �K���`�F�b�J�[�����l
        rate : null,                // �����J���[�����O臒l
        addIdlist : addIdlist,
        initColor : initColor,
        toggle : toggle,
        mark : mark,
        getColor : getColor,
        click : click,
        clear : ColoredIDLib.clearrule,
        refreshColor : refreshColor,
        colors : [],
        colorStyle : {},
        highlightStyle : {},
        createSPMmenu : createSPMmenu,
        setupSPM : setupSPM
    };
}
})()
