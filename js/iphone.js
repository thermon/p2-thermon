/**
 * rep2expack - DOM�𑀍삵��iPhone�ɍœK������
 */

// {{{ globals

var iutil = {
	/**
	 * �N���C�A���g��iPhone���ǂ���
	 * @type {Boolean}
	 */
	'iphone': (/iP(hone|od)/).test(navigator.userAgent),
	/**
	 * ���������N�̐��K�\��
	 * @type {RegExp}
	 */
	'internalLinkPattern': /^([a-z]\w+)\.php\?/,
	/**
	 * �O�������N�̐��K�\��
	 * @type {RegExp}
	 */
	'externalLinkPattern': /^https?:\/\/([^\/]+?@)?([^:\/]+)/,
	/**
	 * �����N�X���C�f�B���O�̂��߂̕ϐ��R���e�i
	 * @type {Object}
	 */
	'sliding': {
		'start': -1,
		'startX': -1,
		'startY': -1,
		'endX': -1,
		'endY': -1,
		'target': null,
		'callbacks': {},
		'dialogs': {},
		'dialog': null,
		'anchor': null,
		'query': null,
		'href': null,
		'uri': null
	},
	/**
	 * ���x���N���b�N�̃R�[���o�b�N�֐��R���e�i
	 * @type {Object}
	 */
	'labelActions': {}
};

// }}}
// {{{ modifyExternalLink()

/**
 * �O�������N���m�F���Ă���V�����^�u�ŊJ���悤�ɕύX����
 *
 * @param {Node|String} contextNode
 * @return void
 */
iutil.modifyExternalLink = function(contextNode) {
	var anchors, anchor, re, i, l, m;

	switch (typeof contextNode) {
		case 'string':
			contextNode = document.getElementById(contextNode);
			break;
		case 'undefined':
			contextNode = document.body;
			break;
	}
	if (!contextNode) {
		return;
	}

	anchors = document.evaluate('.//a[starts-with(@href, "http")]',
	                            contextNode, null,
	                            XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
	l = anchors.snapshotLength;
	re = iutil.externalLinkPattern;

	for (i = 0; i < l; i++) {
		anchor = anchors.snapshotItem(i);
		m = re.exec(anchor.getAttribute('href'));

		if (m !== null && m[2] != location.host) {
			if (typeof anchor.onclick !== 'function') {
				anchor.onclick = iutil.confirmOpenExternalLink;
			}

			if (!anchor.hasAttribute('target')) {
				anchor.setAttribute('target', '_blank');
			}
		}
	}
};

// }}}
// {{{ confirmOpenExternalLink()

/**
 * �O���T�C�g���J�����ǂ������m�F����
 *
 * @param void
 * @return {Boolean}
 */
iutil.confirmOpenExternalLink = function() {
	var url, title;

	url = this.href;

	if (this.hasAttribute('title')) {
		title = this.getAttribute(title);
	} else if (this.hasChildNodes() &&
		this.firstChild.nodeType == 3 &&
		this.firstChild.nodeValue.search(/^h?t?tps?:\/\/[^\/]/) != -1)
	{
		title = this.firstChild.nodeValue;
		switch (title.indexOf('tp')) {
			case 0:
				title = 'ht' + title;
				break;
			case 1:
				title = 'h' + title;
				break;
		}
	} else {
		title = '';
	}

	if (!title.length || title == url) {
		return window.confirm('�O���T�C�g���J���܂���?\nURL: ' + url);
	} else {
		return window.confirm('�O���T�C�g���J���܂���?\nURL: ' + url + '\n(' + title + ')');
	}
};


// }}}
// {{{ toggleChekcbox()

/**
 * �`�F�b�N�{�b�N�X���g�O������
 *
 * @param {Node} node
 * @param {Event} evt
 * @return void
 */
iutil.toggleChekcbox = function(node, evt) {
	if (node && node.nodeType === 1 && typeof node.checked != 'undefined') {
		node.checked = !node.checked;
		if (typeof node.onclick == 'function') {
			node.onclick(evt);
		}
		if (typeof node.onchange == 'function') {
			node.onchange(evt);
		}
	}
};

// }}}
// {{{ checkPrev()

/**
 * �O�̃`�F�b�N�{�b�N�X���g�O������B�^��label����
 *
 * @param {Element|String} elem
 * @param {Event} evt
 * @return void
 */
iutil.checkPrev = function(elem, evt) {
	elem = (typeof elem == 'string') ? document.getElementById(elem) : elem;
	iutil.toggleChekcbox(elem.previousSibling, evt);
};

// }}}
// {{{ checkNext()

/**
 * ���̃`�F�b�N�{�b�N�X���g�O������B�^��label����
 *
 * @param {Element|String} elem
 * @param {Event} evt
 * @return void
 */
iutil.checkNext = function(elem, evt) {
	elem = (typeof elem == 'string') ? document.getElementById(elem) : elem;
	iutil.toggleChekcbox(elem.nextSibling, evt);
};

// }}}
// {{{ setLabelAction()

/**
 * for��������label�v�f�ɃN���b�N���̃C�x���g�n���h����o�^����
 *
 * iPhone��Safari��label�v�f���T�|�[�g����΂��̊֐��͕s�v�ɂȂ�
 *
 * @param {Node|String} contextNode
 * @return void
 */
iutil.setLabelAction = function(contextNode) {
	var labels, label, targetId, targetElement, i, l;

	switch (typeof contextNode) {
		case 'string':
			contextNode = document.getElementById(contextNode);
			break;
		case 'undefined':
			contextNode = document.body;
			break;
	}
	if (!contextNode) {
		return;
	}

	labels = document.evaluate('.//label[@for]',
	                           contextNode, null,
	                           XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
	l = labels.snapshotLength;

	for (i = 0; i < l; i++) {
		label = labels.snapshotItem(i);
		targetId = label.getAttribute('for');
		targetElement = document.getElementById(targetId);
		if (!targetElement) {
			continue;
		}

		if (typeof iutil.labelActions[targetId] != 'function') {
			if (targetElement.nodeName.toLowerCase() == 'input' &&
				targetElement.hasAttribute('type') &&
				targetElement.getAttribute('type').toLowerCase() == 'checkbox')
			{
				iutil.labelActions[targetId] = (function(element) {
					return function(event){
						event = event || window.event;
						iutil.toggleChekcbox(element, event);
						return false;
					};
				})(targetElement);
			} else {
				iutil.labelActions[targetId] = (function(element) {
					return function(){
						element.forcus();
						return false;
					};
				})(targetElement);
			}
		}

		label.onclick = iutil.labelActions[targetId];
	}
};

// }}}
// {{{ adjustTextareaSize()

/**
 * textarea�̕����ő剻����
 *
 * @return void
 */
iutil.adjustTextareaSize = function() {
	var areas, width, i, l;

	areas = document.body.getElementsByTagName('textarea');
	l = (areas) ? areas.length : 0;

	for (i = 0; i < l; i++) {
		width = areas[i].parentNode.clientWidth;
		if (width > 100) {
			width -= 12; // (borderWidth + padding) * 2
			if (width > 480) {
				width = 480; // maxWidth
			}
			areas[i].style.width = width + 'px';
		}
	}
};

// }}}
// {{{ shrinkTextarea()

/**
 * textarea�̍���������������
 *
 * @param {Element|String} elem
 * @return void
 */
iutil.shrinkTextarea = function(elem) {
	var rows;

	elem = (typeof elem == 'string') ? document.getElementById(elem) : elem;
	if (!elem) {
		return;
	}

	//var y = elem.clientHeight;
	rows = elem.hasAttribute('rows') ? parseInt(elem.getAttribute('rows'), 10) : 3;
	rows = Math.max(rows - 1, 3);
	elem.setAttribute('rows', rows.toString());
	//window.scrollBy(0, elem.clientHeight - y);
};

// }}}
// {{{ expandTextarea()

/**
 * textarea�̍�����傫������
 *
 * @param {Element|String} elem
 * @return void
 */
iutil.expandTextarea = function(elem) {
	var rows;

	elem = (typeof elem == 'string') ? document.getElementById(elem) : elem;
	if (!elem) {
		return;
	}

	//var y = elem.clientHeight;
	rows = elem.hasAttribute('rows') ? parseInt(elem.getAttribute('rows'), 10) : 3;
	rows = Math.max(rows + 1, 3);
	elem.setAttribute('rows', rows.toString());
	//window.scrollBy(0, elem.clientHeight - y);
};

// }}}
// {{{ toggleAutocorrect()

/**
 * �t�H�[����autocorrect�̗L���E������؂�ւ���
 *
 * @param {Element|String} elem
 * @param {Boolean} toggle
 * @return void
 */
iutil.toggleAutocorrect = function(elem, toggle) {
	elem = (typeof elem == 'string') ? document.getElementById(elem) : elem;
	if (!elem) {
		return;
	}

	elem.setAttribute('autocorrect', (toggle ? 'on' : 'off'));
};

// }}}
// {{{ changeLinkTarget()

/**
 * �����N�^�[�Q�b�g��؂�ւ���
 *
 * @param {String|Array} expr
 * @param {Boolean} toggle
 * @param {Node|String} contextNode
 * @param {String} target
 * @return void
 */
iutil.changeLinkTarget = function(expr, toggle, contextNode) {
	var anchors, args, i, l;

	switch (typeof contextNode) {
		case 'string':
			contextNode = document.getElementById(contextNode);
			break;
		case 'undefined':
			contextNode = document.body;
			break;
	}

	if (typeof expr != 'string') {
		args = [toggle, contextNode];
		if (arguments.length > 3) {
			args.push(arguments[3]);
		}
		l = expr.length;
		for (i = 0; i < l; i++) {
			args.unshift(expr[i]);
			iutil.changeLinkTarget.apply(this, args);
			args.shift();
		}
		return;
	}

	anchors = document.evaluate(expr,
	                            contextNode,
	                            null,
	                            XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
	                            null);

	l = anchors.snapshotLength;

	if (toggle) {
		for (i = 0; i < l; i++) {
			anchors.snapshotItem(i).setAttribute('target', '_blank');
		}
	} else if (arguments.length > 3) {
		for (i = 0; i < l; i++) {
			anchors.snapshotItem(i).setAttribute('target', arguments[3]);
		}
	} else {
		for (i = 0; i < l; i++) {
			anchors.snapshotItem(i).removeAttribute('target');
		}
	}
};

// }}}
// {{{ getTextNodes()

/**
 * �w�肳�ꂽ�m�[�h�Ɋ܂܂�Ă���e�L�X�g�m�[�h�̃��X�g���擾����
 *
 * @param {Node} node
 * @param {Boolean} needsValue
 * @param {Array} texts
 * @return {Array}
 */
iutil.getTextNodes = function(node, needsValue, texts) {
	var i, l;

	if (typeof texts == 'undefined') {
		texts = [];
	}

	switch (node.nodeType) {
		case 1:
			l = node.childNodes.length;
			for (i = 0; i < l; i++) {
				iutil.getTextNodes(node.childNodes[i], needsValue, texts);
			}
			break;
		case 3:
			texts.push((needsValue) ? node.nodeValue : node);
			break;
	}

	return texts;
};

// }}}
// {{{ httpGetText()

/**
 * GET���N�G�X�g�̌��ʂ��e�L�X�g�Ƃ��Ď擾����
 *
 * @param {String} uri
 * @return {String|null}
 */
iutil.httpGetText = function(uri) {
	var req, err;
	try {
		var req = new XMLHttpRequest();
		req.open('GET', uri, false);
		req.send(null);

		if (req.readyState == 4) {
			if (req.status == 200) {
				return req.responseText;
			}
		}
	} catch (err) {
		// pass
	}
	return null;
};

// }}}
// {{{ stopEvent()

/**
 * �f�t�H���g�C�x���g�̔����ƃC�x���g�̓`�d��}������
 *
 * @param {Event} event
 * @return {false}
 */
iutil.stopEvent = function(event) {
	event.preventDefault();
	event.stopPropagation();
	return false;
};

// }}}
// {{{ sliding.onTouchStart()

/**
 * �����N�X���C�h�E�^�b�`/�}�E�X�����������̃C�x���g�n���h��
 * �_�C�A���O�\���^�C�}�[���Z�b�g����
 *
 * @param {Event} event
 * @param {Element} target
 * @return void
 */
iutil.sliding.onTouchStart = function(event, target) {
	var x, y;

	event = event || window.event;

	if (event.targetTouches) {
		if (!event.targetTouches.length) {
			return;
		}
		x = event.targetTouches[0].pageX;
		y = event.targetTouches[0].pageY;
	} else {
		x = iutil.getPageX(event);
		y = iutil.getPageY(event);
	}

	if (!target) {
		if (event.currentTarget) {
			target = event.currentTarget;
		} else {
			if (event.target) {
				target = event.target;
			} else {
				target = event.srcElement;
			}
			while (target && (target.nodeType != 1 || target.nodeName.toLowerCase() !== 'a')) {
				target = target.parentNode;
			}
			if (!target) {
				return;
			}
		}
	}

	iutil.sliding.start = event.timeStamp;
	iutil.sliding.startX = x;
	iutil.sliding.startY = y;
	iutil.sliding.target = target;
};

// }}}
// {{{ sliding.onTouchMove()

/**
 * �����N�X���C�h�E���[�u/�h���b�O���̃C�x���g�n���h��
 * �^�b�`/�J�[�\�����ړ������Ȃ�_�C�A���O�\���^�C�}�[���L�����Z������
 *
 * @param {Event} event
 * @return void
 */
iutil.sliding.onTouchMove = function(event) {
	var x, y;

	event = event || window.event;

	//iutil.stopEvent(event);

	if (event.targetTouches) {
		if (!event.targetTouches.length) {
			return;
		}
		x = event.targetTouches[0].pageX;
		y = event.targetTouches[0].pageY;
	} else {
		x = iutil.getPageX(event);
		y = iutil.getPageY(event);
	}

	iutil.sliding.endX = x;
	iutil.sliding.endY = y;
};

// }}}
// {{{ sliding.onTouchEnd()

/**
 * �����N�X���C�h�E�����[�X���̃C�x���g�n���h��
 * �_�C�A���O���\�����ꂽ�Ȃ�N���b�N���L�����Z������
 *
 * @param {Event} event
 * @return void
 */
iutil.sliding.onTouchEnd = function(event) {
	event = event || window.event;

	if (Math.abs(iutil.sliding.endX - iutil.sliding.startX) > 160 &&
		Math.abs(iutil.sliding.endY - iutil.sliding.startY) < 16 &&
		event.timeStamp < iutil.sliding.start + 1000)
	{
		iutil.sliding.showDialog(iutil.sliding.target, iutil.sliding.startX, iutil.sliding.startY);
		iutil.stopEvent(event);
		return false;
	} else {
		return true;
	}
};

// }}}
// {{{ sliding.showDialog()

/**
 * �_�C�A���O��\������
 *
 * @param {Element} anchor
 * @param {Number} x
 * @param {Number} y
 * @return void
 */
iutil.sliding.showDialog = function(anchor, x, y) {
	var sliding, dialog, div, text, button, m, p, left;

	sliding = iutil.sliding;
	sliding.timeoutId = -1;
	sliding.anchor = anchor;
	sliding.href = anchor.getAttribute('href');
	sliding.uri = anchor.href;

	sliding.hideDialog();

	m = iutil.internalLinkPattern.exec(anchor.getAttribute('href'));
	if (m === null) {
		return;
	}

	sliding.query = sliding.href.substring(m[0].length);
	p = sliding.query.indexOf('#');
	if (p !== -1) {
		sliding.query = sliding.query.substring(0, p);
	}

	// ����̃R�[���o�b�N�֐�������Ƃ�
	if (typeof sliding.callbacks[m[1]] === 'function') {
		sliding.callbacks[m[1]](anchor, event);
		return;
	}

	// �f�t�H���g�̃_�C�A���O��\������
	if (typeof sliding.dialogs._default === 'undefined') {
		dialog = document.createElement('div');
		dialog.className = 'popup-dialog';

		// �����N�e�L�X�g
		div = dialog.appendChild(document.createElement('div'));
		div.className = 'popup-dialog-text';
		div.appendChild(document.createTextNode('-'));

		// �{�^����
		div = dialog.appendChild(document.createElement('div'));
		div.className = 'popup-dialog-buttons';

		button = div.appendChild(document.createElement('input'));
		button.setAttribute('type', 'button');
		button.value = '�����N���J��';
		button.onclick = sliding.openUri;

		div.appendChild(document.createTextNode('\u3000'));

		button = div.appendChild(document.createElement('input'));
		button.setAttribute('type', 'button');
		button.value = '�^�u�ŊJ��';
		button.onclick = sliding.openUriInTab;

		// �u����v�{�^��
		button = dialog.appendChild(document.createElement('img'));
		button.className = 'close-button';
		button.setAttribute('src', 'img/iphone/close.png');
		button.onclick = sliding.hideDialog;

		sliding.dialogs._default = document.body.appendChild(dialog);
	} else {
		dialog = sliding.dialogs._default;
	}
	sliding.setActiveDialog(dialog);

	text = dialog.firstChild.firstChild;
	text.nodeValue = iutil.getTextNodes(anchor, true).join('').replace(/\s+/g, ' ')
	               + ' (' + m[1] + '.php)';

	dialog.style.display = 'block';
	left = iutil.getWindowWidth() - iutil.parsePixels(iutil.getCurrentStyle(dialog).width) - 10;
	dialog.style.top = (y + 5) + 'px';
	dialog.style.left = Math.min(x, Math.max(0, left)) + 'px';
};

// }}}
// {{{ sliding.hideDialog()

/**
 * �A�N�e�B�u�ȃ_�C�A���O���B��
 *
 * @param void
 * @return void
 */
iutil.sliding.hideDialog = function() {
	if (iutil.sliding.dialog) {
		iutil.sliding.dialog.style.display = 'none';
		iutil.sliding.setActiveDialog(null);
	}
};

// }}}
// {{{ sliding.setActiveDialog()

/**
 * �A�N�e�B�u�ȃ_�C�A���O��ݒ肷��
 *
 * @param {Element|null} element
 * @return void
 */
iutil.sliding.setActiveDialog = function(element) {
	iutil.sliding.dialog = element;
};

// }}}
// {{{ sliding.openUri()

/**
 * �����N���J��
 *
 * @param void
 * @return void
 */
iutil.sliding.openUri = function() {
	window.location.href = iutil.sliding.uri;
};

// }}}
// {{{ sliding.openUriInTab()

/**
 * �V�����^�u�Ń����N���J��
 *
 * @param void
 * @return void
 */
iutil.sliding.openUriInTab = function() {
	window.open(iutil.sliding.uri, null);
};

// }}}
// {{{ sliding.bind()

/**
 * �����N�ɃX���C�h�C�x���g�n���h����o�^����
 *
 * @param {Element} anchor
 * @return void
 */
if (iutil.iphone) {
	iutil.sliding.bind = function(anchor) {
		anchor.addEventListener('touchstart',   iutil.sliding.onTouchStart, false);
		anchor.addEventListener('touchmove',    iutil.sliding.onTouchMove, false);
		anchor.addEventListener('touchend',     iutil.sliding.onTouchEnd, false);
		anchor.addEventListener('touchcancel',  iutil.sliding.onTouchEnd, false);
	};
} else {
	iutil.sliding.bind = function(anchor) {
		anchor.addEventListener('mousedown',    iutil.sliding.onTouchStart, false);
		anchor.addEventListener('drag',         iutil.sliding.onTouchMove, false);
		anchor.addEventListener('mosueup',      iutil.sliding.onTouchEnd, false);
	};
}

// }}}
// {{{ parsePixels(), isStaticLayout()

iutil.parsePixels = function(value) {
	var n = 0;

	switch (typeof value) {
		case 'number':
			n = Math.floor(value);
			break;
		case 'string':
			if (value.length > 2 && value.indexOf('px') === value.length - 2) {
				n = parseInt(value);
				if (!isFinite(n)) {
					n = 0;
				}
			}
			break;
	}

	return n;
};

iutil.isStaticLayout = function(element) {
	switch (iutil.getCurrentStyle(element).position) {
		case 'absolute':
		case 'relative':
		case 'fixed':
			return false;
		default:
			return true;
	}
};

// }}}
// {{{ getCurrentStyle(), getTargetNode(), getScrollXY()

if (document.all && !window.opera) {
	// {{{ IE

	iutil.getCurrentStyle = function(element) {
		return element.currentStyle;
	};

	iutil.getTargetNode = function(event) {
		var target = event.srcElement;
		while (target.nodeType !== 1) {
			target = target.parentNode;
		}
		return target;
	};

	if (document.compatMode === 'BackCompat') {
		iutil.getScrollX = function() { return document.body.scrollLeft; };
		iutil.getScrollY = function() { return document.body.scrollTop; };
		iutil.getScrollXY = function() {
			return [document.body.scrollLeft, document.body.scrollTop];
		};
	} else {
		iutil.getScrollX = function() { return document.documentElement.scrollLeft; };
		iutil.getScrollY = function() { return document.documentElement.scrollTop; };
		iutil.getScrollXY = function() {
			return [document.documentElement.scrollLeft, document.documentElement.scrollTop];
		};
	}

	// }}}
} else {
	// {{{ Others

	iutil.getCurrentStyle = function(element) {
		return document.defaultView.getComputedStyle(element, '');
	};

	iutil.getTargetNode = function(event) {
		var target = event.target;
		while (target.nodeType !== 1) {
			target = target.parentNode;
		}
		return target;
	};

	if (typeof window.scrollX === 'number') {
		iutil.getScrollX = function() { return window.scrollX; };
		iutil.getScrollY = function() { return window.scrollY; };
		iutil.getScrollXY = function() {
			return [window.scrollX, window.scrollY];
		};
	} else {
		iutil.getScrollX = function() { return window.pageXOffset; };
		iutil.getScrollY = function() { return window.pageYOffset; };
		iutil.getScrollXY = function() {
			return [window.pageXOffset, window.pageYOffset];
		};
	}

	// }}}
}

// }}}
// {{{ getWindowWidth(), getWindowHeight(), getWindowSize()

if (typeof document.compatMode === 'undefined') {
	// Safari <= 2.x, etc.
	iutil.getWindowWidth  = function() { return window.innerWidth; };
	iutil.getWindowHeight = function() { return window.innerHeight; };
	iutil.getWindowSize = function() {
		return [window.innerWidth, window.innerHeight, document.width, document.height];
	};
} else if (document.compatMode === 'BackCompat') {
	// Backward Compatibility Mode
	iutil.getWindowWidth  = function() { return document.body.clientWidth; };
	iutil.getWindowHeight = function() { return document.body.clientHeight; };
	iutil.getWindowSize = function() {
		return [document.body.clientWidth, document.body.clientHeight,
		        document.body.scrollWidth, document.body.scrollHeight];
	};
} else {
	// Standard Mode
	iutil.getWindowWidth  = function() { return document.documentElement.clientWidth; };
	iutil.getWindowHeight = function() { return document.documentElement.clientHeight; };
	iutil.getWindowSize = function() {
		return [document.documentElement.clientWidth, document.documentElement.clientHeight,
		        document.documentElement.scrollWidth, document.documentElement.scrollHeight];
	};
}

// }}}
// {{{ getOffsetXY(), getLayerXY(), getPageXY()

// Common
iutil.getOffsetX = function(event) { return event.offsetX; };
iutil.getOffsetY = function(event) { return event.offsetY; };
iutil.getOffsetXY = function(event) {
	return [event.offsetX, event.offsetY];
};

iutil.getLayerX = function(event) { return event.layerX; };
iutil.getLayerY = function(event) { return event.layerY; };
iutil.getLayerXY = function(event) {
	return [event.layerX, event.layerY];
};

iutil.getPageX = function(event) { return event.pageX; };
iutil.getPageY = function(event) { return event.pageY; };
iutil.getPageXY = function(event) {
	return [event.pageX, event.pageY];
};

if (window.opera) {
	// {{{ Opera

	iutil.getOffsetX = function(event) { return iutil.getOffsetXY(event)[0]; };
	iutil.getOffsetY = function(event) { return iutil.getOffsetXY(event)[1]; };
	iutil.getOffsetXY = function(event) {
		var style = iutil.getCurrentStyle(iutil.getTargetNode(event));
		return [event.offsetX + iutil.parsePixels(style.borderLeftWidth) + iutil.parsePixels(style.paddingLeft),
		        event.offsetY + iutil.parsePixels(style.borderTopWidth)  + iutil.parsePixels(style.paddingTop)];
	};

	iutil.getLayerX = function(event) { return iutil.getLayerXY(event)[0]; };
	iutil.getLayerY = function(event) { return iutil.getLayerXY(event)[1]; };
	iutil.getLayerXY = function(event) {
		var target = iutil.getTargetNode(event);
		var offset = iutil.getOffsetXY(event);
		if (iutil.isStaticLayout(target) && target.offsetParent) {
			offset[0] += target.offsetLeft;
			offset[1] += target.offsetTop;
		}
		return offset;
	};

	// }}}
} else if (document.all) {
	// {{{ IE

	iutil.getOffsetX = function(event) { return iutil.getOffsetXY(event)[0]; };
	iutil.getOffsetY = function(event) { return iutil.getOffsetXY(event)[1]; };
	iutil.getOffsetXY = function(event) {
		var style = iutil.getCurrentStyle(iutil.getTargetNode(event));
		return [event.offsetX + iutil.parsePixels(style.borderLeftWidth),
		        event.offsetY + iutil.parsePixels(style.borderTopWidth)];
	};

	iutil.getLayerX = function(event) { return iutil.getLayerXY(event)[0]; };
	iutil.getLayerY = function(event) { return iutil.getLayerXY(event)[1]; };
	iutil.getLayerXY = function(event) {
		var target = iutil.getTargetNode(event);
		var offset = iutil.getOffsetXY(event);
		if (iutil.isStaticLayout(target) && target.offsetParent) {
			offset[0] += target.offsetLeft;
			offset[1] += target.offsetTop;
		}
		return offset;
	};

	iutil.getPageX = function(event) {
		return event.clientX + iutil.getScrollX();
	};
	iutil.getPageY = function(event) {
		return event.clientY + iutil.getScrollY();
	};
	iutil.getPageXY = function(event) {
		return [event.clientX + iutil.getScrollX(), event.clientY + iutil.getScrollY()];
	};

	// }}}
} else if (navigator.userAgent.indexOf('AppleWebKit') === -1) {
	// {{{ Firefox and other non WebKit browsers

	iutil.getOffsetX = function(event) { return iutil.getOffsetXY(event)[0]; };
	iutil.getOffsetY = function(event) { return iutil.getOffsetXY(event)[1]; };
	iutil.getOffsetXY = function(event) {
		var target = iutil.getTargetNode(event);
		var offsetX = event.layerX;
		var offsetY = event.layerY;
		if (iutil.isStaticLayout(target) && target.offsetParent) {
			var style = iutil.getCurrentStyle(target.offsetParent);
			offsetX -= target.offsetLeft + iutil.parsePixels(style.borderLeftWidth);
			offsetY -= target.offsetTop  + iutil.parsePixels(style.borderTopWidth);
		}
		return [offsetX, offsetY];
	};

	// }}}
}

// }}}

// }}}
// {{{ DOMContentLoaded

window.addEventListener('DOMContentLoaded', function(event) {
	window.removeEventListener('DOMContentLoaded', arguments.callee, false);

	if (typeof window.iphone_js_no_modification === 'undefined' || !window.iphone_js_no_modification) {
		// �����N�ɃC�x���g�n���h����o�^����
		iutil.modifyExternalLink(document.body);

		// label�ɃC�x���g�n���h����o�^����
		if (iutil.iphone) {
			iutil.setLabelAction(document.body);
		}

		// textarea�̕��𒲐�
		iutil.adjustTextareaSize();

		// ��]���̃C�x���g�n���h����ݒ�
		document.body.addEventListener('orientationchange', iutil.adjustTextareaSize, false);
	}

	// ���P�[�V�����o�[���B��
	if (typeof window.iui !== 'undefined') {
		window.scrollTo(0, 1);
	} else if (!window.location.hash.length && iutil.getScrollX() < 1) {
		window.scrollTo(0, 1);
	}
}, false);

// }}}

/*
 * Local Variables:
 * mode: javascript
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
/* vim: set syn=javascript fenc=cp932 ai noet ts=4 sw=4 sts=4 fdm=marker: */
