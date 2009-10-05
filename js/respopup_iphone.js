/**
 * rep2expack - iPhone�p���X�|�b�v�A�b�v
 *
 * iphone.js�̌�ɓǂݍ���
 */

// {{{ globals

var _IRESPOPG = {
	'hash': {},
	'serial': 0,
	'callbacks': []
};

var ipoputil = {};

// }}}
// {{{ ipoputil.getZ()

/**
 * z-index�ɐݒ肷��l��Ԃ�
 *
 * css/ic2_iphone.css �� div#ic2-info �� z-index �� 999 ��
 * �Œ肳��Ă���̂Ń|�b�v�A�b�v���J��Ԃ��ƕs�������B
 * �|�b�v�A�b�v�I�u�W�F�N�g�� z-index ���W���Ǘ�����K�v����B
 *
 * @param {Element} obj
 * @return {String}
 */
ipoputil.getZ = function(obj) {
	return (10 + _IRESPOPG.serial).toString();
};

// }}}
// {{{ getActivator()

/**
 * �I�u�W�F�N�g���őO�ʂɈړ�����֐���Ԃ�
 *
 * @param {Element} obj
 * @return void
 */
ipoputil.getActivator = function(obj) {
	return (function(){
		_IRESPOPG.serial++;
		obj.style.zIndex = ipoputil.getZ();
	});
};

// }}}
// {{{ getDeactivator()

/**
 * DOM�c���[����I�u�W�F�N�g����菜���֐���Ԃ�
 *
 * @param {Element} obj
 * @param {String} key
 * @return void
 */
ipoputil.getDeactivator = function(obj, key) {
	return (function(){
		delete _IRESPOPG.hash[key];
		obj.parentNode.removeChild(obj);
		delete obj;
	});
};

// }}}
// {{{ iResPopUp()

/**
 * iPhone�p���X�|�b�v�A�b�v
 *
 * @param {String} url
 * @param {Event} evt
 * @return {Boolean}
 * @todo use asynchronous request
 */
var iResPopUp = function(url, evt) {
	var yOffset = Math.max(10, iutil.getPageY(evt) - 20);

	if (_IRESPOPG.hash[url]) {
		_IRESPOPG.serial++;
		_IRESPOPG.hash[url].style.top = yOffset.toString() + 'px';
		_IRESPOPG.hash[url].style.zIndex = ipoputil.getZ();
		return false;
	}

	_IRESPOPG.serial++
	var popnum = _IRESPOPG.serial;
	var popid = '_respop' + popnum;
	var req = new XMLHttpRequest();
	req.open('GET', url + '&ajax=true&respop_id=' + popnum, false);
	req.send(null);

	if (req.readyState == 4) {
		if (req.status == 200) {
			var container = document.createElement('div');
			var closer = document.createElement('img');

			container.id = popid;
			container.className = 'respop';
			container.innerHTML = req.responseText;
			/*
			var rx = req.responseXML;
			while (rx.hasChildNodes()) {
				container.appendChild(document.importNode(rx.removeChild(rx.firstChild), true));
			}
			*/
			container.style.top = yOffset.toString() + 'px';
			container.style.zIndex = ipoputil.getZ();
			//container.onclick = ipoputil.getActivator(container);

			closer.className = 'close-button';
			closer.setAttribute('src', 'img/iphone/close.png');
			closer.onclick = ipoputil.getDeactivator(container, url);

			container.appendChild(closer);
			document.body.appendChild(container);

			//iutil.modifyInternalLink(container);
			iutil.modifyExternalLink(container);

			_IRESPOPG.hash[url] = container;

			var lastres = document.evaluate('./div[@class="res" and position() = last()]',
			                                container,
			                                null,
			                                XPathResult.ANY_UNORDERED_NODE_TYPE,
			                                null
			                                ).singleNodeValue;

			if (lastres) {
				var back = document.createElement('div');
				back.className = 'respop-back';
				var anchor = document.createElement('a');
				anchor.setAttribute('href', '#' + popid);
				anchor.onclick = (function(){
					scrollTo(0, yOffset - 10);
					return false;
				});
				anchor.appendChild(document.createTextNode('��'));
				back.appendChild(anchor);
				lastres.appendChild(back);
			}

			for (var i = 0; i < _IRESPOPG.callbacks.length; i++) {
				_IRESPOPG.callbacks[i](container);
			}

			return false;
		}
	}

	return true;
};

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
