/*
 * rep2expack - �X���ꗗ�pJavaScript
 * menu_i.js �̃T�u�Z�b�g
 */

(function(){
	// {{{ createPop()

	/**
	 * �����N�X���C�h���Ƀ����N�̉��ɕ\�������v�f�𐶐�����
	 *
	 * @param {String} type
	 * @return {Element}
	 */
	var createPop = function(type) {
		var pop, div, button, table;

		pop = document.createElement('li');
		pop.className = 'info-pop';

		// �{�^����
		div = pop.appendChild(document.createElement('div'));
		div.className = 'info-pop-buttons';

		button = div.appendChild(document.createElement('input'));
		button.setAttribute('type', 'button');
		button.value = type + '���J��';
		button.onclick = window.iutil.sliding.openUri;

		div.appendChild(document.createTextNode('\u3000'));

		button = div.appendChild(document.createElement('input'));
		button.setAttribute('type', 'button');
		button.value = '�^�u�ŊJ��';
		button.onclick = window.iutil.sliding.openUriInTab;

		div.appendChild(document.createTextNode('\u3000'));

		button = div.appendChild(document.createElement('input'));
		button.setAttribute('type', 'button');
		button.value = '����';
		button.onclick = window.iutil.sliding.hideDialog;

		// ���C�ɓ���̓o�^�E����
		table = pop.appendChild(document.createElement('table'));
		table.className = 'info-pop-fav';
		table.setAttribute('cellspacing', '0');
		//table.appendChild(document.createElement('caption'))
		//     .appendChild(document.createTextNode('���C��' + type));
		table.appendChild(document.createElement('tbody'));

		return pop;
	};

	// }}}
	// {{{ createFavRow()

	/**
	 * ���C�ɔE���C�ɃX���̓o�^�E�����X�C�b�`�𐶐�����
	 *
	 * @param {String} label
	 * @param {String} klass
	 * @param {Boolean} toggled
	 * @param {function} onclick
	 * @return {Element}
	 */
	var createFavRow = function(label, klass, toggled, onclick) {
		var tr, div, span;

		/*
		<tr>
			<td>{label}</td>
			<td>
				<div class="toggle {klass}" onclick="{onclick}" toggled="{toggled}">
					<span class="thumb"></span>
					<span class="toggleOn">&#x2713;</span>
					<span class="toggleOff">-</span>
				</div>
			</td>
		</tr>
		*/

		tr = document.createElement('tr');
		tr.appendChild(document.createElement('td'))
		  .appendChild(document.createTextNode(label));

		div = tr.appendChild(document.createElement('td'))
		        .appendChild(document.createElement('div'));
		div.className = 'toggle ' + klass;
		div.setAttribute('toggled', toggled ? 'true' : 'false');
		div.addEventListener('click', onclick, false);

		span = div.appendChild(document.createElement('span'));
		span.className = 'thumb';

		span = div.appendChild(document.createElement('span'));
		span.className = 'toggleOn';
		span.appendChild(document.createTextNode('\u2713')); // U+2713 CHECK MARK

		span = div.appendChild(document.createElement('span'));
		span.className = 'toggleOff';
		span.appendChild(document.createTextNode('-'));

		return tr;
	};

	// }}}
	// {{{ updateFavRow()

	/**
	 * ���C�ɔE���C�ɃX���̓o�^�E�����X�C�b�`���X�V����
	 *
	 * @param {Element} tr
	 * @param {String} label
	 * @param {Boolean} toggled
	 * @return void
	 */
	var updateFavRow = function(tr, label, toggled) {
		tr.childNodes[0].firstChild.nodeValue = label;
		tr.childNodes[1].firstChild.setAttribute('toggled', toggled ? 'true' : 'false');
	};

	// }}}
	// {{{ setFav

	/**
	 * ���C�ɃX���̓o�^�E�������s��
	 *
	 * @param {Element} div
	 * @return void
	 */
	var setFav = function(div) {
		var toggled, req, uri, setnum;

		toggled = div.getAttribute('toggled') === 'true';
		setnum = parseInt(div.className.substring(div.className.indexOf('fav') + 3));
		uri = 'httpcmd.php?cmd=setfav&' + window.iutil.sliding.query + '&setnum=' + setnum;
		// menu_i.js�Ƌt
		if (!toggled) {
			uri += '&setfav=1';
		} else {
			uri += '&setfav=0';
		}

		req = new XMLHttpRequest();
		req.open('GET', uri, true);
		req.onreadystatechange = generateOnToggle(req, div, toggled);
		req.send(null);
	};

	// }}}
	// {{{ setPal

	/**
	 * �a������̓o�^�E�������s��
	 *
	 * @param {Element} div
	 * @return void
	 */
	var setPal = function(div) {
		var toggled, req, uri;

		toggled = div.getAttribute('toggled') === 'true';
		uri = 'httpcmd.php?cmd=setpal&' + window.iutil.sliding.query;
		// menu_i.js�Ƌt
		if (!toggled) {
			uri += '&setpal=1';
		} else {
			uri += '&setpal=0';
		}

		req = new XMLHttpRequest();
		req.open('GET', uri, true);
		req.onreadystatechange = generateOnToggle(req, div, toggled);
		req.send(null);
	};

	// }}}
	// {{{ onFavToggled

	/**
	 * ���C�ɃX���X�C�b�`���g�O�����ꂽ�Ƃ��Ɏ��s�����R�[���o�b�N�֐�
	 *
	 * @param {Event} event
	 * @return void
	 */
	var onFavToggled = function(event) {
		setFav(this);
	};

	// }}}
	// {{{ onPalToggled

	/**
	 * �a������X�C�b�`���g�O�����ꂽ�Ƃ��Ɏ��s�����R�[���o�b�N�֐�
	 *
	 * @param {Event} event
	 * @return void
	 */
	var onPalToggled = function(event) {
		setPal(this);
	};

	// }}}
	// {{{ showFavList

	/**
	 * ���C�ɃX���̓o�^�󋵃��X�g���X�V�E�\������
	 *
	 * @param {Element} table
	 * @param {Array} favs
	 * @param {Boolean} palace
	 * @return void
	 */
	var showFavList = function(table, favs, palace) {
		var bodies, tbody, tr, i, l;

		bodies = table.getElementsByTagName('tbody');
		tbody = bodies[0];
		l = favs.length;

		// ���������܂��͎擾�������C�ɃX���̃Z�b�g���Ɗ����̍s�����قȂ�ꍇ
		if (tbody.childNodes.length != l) {
			// �]���ȍs���폜
			while (tbody.childNodes.length > l) {
				tr = tbody.childNodes[tbody.childNodes.length - 1];
				tr.childNodes[1].firstChild.removeEventListener('click', onFavToggled, false);
				tbody.removeChild(tr);
			}

			// �����̍s���X�V
			l = tbody.childNodes.length;
			for (i = 0; i < l; i++) {
				updateFavRow(tbody.childNodes[i], favs[i].title, favs[i].set);
			}

			// �s��ǉ�
			l = favs.length;
			for (; i < l; i++) {
				tr = createFavRow(favs[i].title, 'fav' + i, favs[i].set, onFavToggled);
				tbody.appendChild(tr);
			}
		} else {
			// �����̍s���X�V
			for (i = 0; i < l; i++) {
				updateFavRow(tbody.childNodes[i], favs[i].title, favs[i].set);
			}
		}

		// �a������
		if (table.getElementsByTagName('tbody').length === 1) {
			tr = createFavRow('�a������', 'palace', palace, onPalToggled);
			table.appendChild(document.createElement('tbody')).appendChild(tr);
		} else {
			updateFavRow(bodies[1].firstChild, '�a������', palace);
		}

		table.style.display = 'table';
	};

	// }}}
	// {{{ generateOnThreadInfoGet()

	/**
	 * �񓯊����N�G�X�g�ŃX���b�h�����擾�����ۂ�
	 * ���s�����R�[���o�b�N�֐��𐶐�����
	 *
	 * @param {XMLHttpRequest} req
	 * @param {Function} parse
	 * @param {Element} pop
	 * @param {Element} table
	 * @return void
	 */
	var generateOnThreadInfoGet = function(req, parse, pop, table) {
		return function() {
			var data, err;

			if (req.readyState == 4) {
				if (req.status == 200) {
					try {
						data = parse(req.responseText);
						showFavList(table, data.favs, data.palace);
					} catch (err) {
						window.alert(err.toString());
					}
				} else {
					window.alert('HTTP Error: ' + req.status);
				}
			}
		};
	};

	// }}}
	// {{{ generateOnToggle()

	/**
	 * �񓯊����N�G�X�g�ł��C�ɃX���E�a������̑���������ۂ�
	 * ���s�����R�[���o�b�N�֐��𐶐�����
	 *
	 * �܂�iui����toggle�������ݒ肳���menu_i.js�̂��̂Ƃ͈قȂ�A
	 * �X�V�������ɂ����ŏ��߂�toggle������؂�ւ���B
	 *
	 * @param {XMLHttpRequest} req
	 * @param {Element} toggle
	 * @param {Boolean} toggled
	 * @return void
	 */
	var generateOnToggle = function(req, toggle, toggled) {
		return function() {
			if (req.readyState == 4) {
				if (req.status == 200) {
					if (req.responseText === '1') {
						// toggle�������Z�b�g����
						toggle.setAttribute('toggled', (toggled) ? 'false' : 'true');
					} else if (req.responseText === '0') {
						window.alert('�X�V�Ɏ��s���܂���');
					} else {
						window.alert('�\�����Ȃ����X�|���X');
					}
				} else {
					window.alert('HTTP Error: ' + req.status);
				}
			}
		};
	};

	// }}}
	// {{{ setup()

	/**
	 * �X���b�h�����N�X���C�h���̃A�N�V������ݒ肷��
	 *
	 * @param {Object} iutil
	 * @param {Object} JSON
	 * @return void
	 */
	var setup = function(iutil, JSON) {
		var sliding, i, l, s;

		sliding = iutil.sliding;

		// ���݁Aiphone.js�Ŏ���iutil.modifyInternalLink()�𖳌��ɂ��Ă���̂�
		// ������iutil.sliding.bind()����B
		s = document.evaluate('.//ul[@class = "subject"]/li/a[starts-with(@href, "read.php?")]',
							  document.body,
							  null,
							  XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
							  null);
		l = s.snapshotLength;
		for (i = 0; i < l; i++) {
			sliding.bind(s.snapshotItem(i));
		}

		delete i, l, s;

		// {{{ override sliding.callbacks.read()

		/**
		 * �X���b�h�����N�X���C�h���Ɏ��s�����֐�
		 *
		 * @param {Element} anchor
		 * @param {Event} event
		 * @return void
		 */
		sliding.callbacks.read = function(anchor, event) {
			var pop, div, ul, li, m, req, table;

			// �v�f���擾
			if (typeof sliding.dialogs.menuRead === 'undefined') {
				pop = createPop('�X��');

				div = pop.appendChild(document.createElement('div'));
				div.className = 'info-pop-buttons';

				button = div.appendChild(document.createElement('input'));
				button.setAttribute('type', 'button');
				button.value = '�X���b�h���';
				button.onclick = function() {
					window.open('info.php?' + sliding.query, null);
				};
			} else {
				pop = sliding.dialogs.menuRead;
				pop = pop.parentNode.removeChild(pop);
			}
			sliding.dialogs.menuRead = pop;
			sliding.setActiveDialog(pop);

			// ���C�ɃX���̓o�^�󋵂��擾
			table = pop.childNodes[1];
			table.style.display = 'none';
			req = new XMLHttpRequest();
			req.open('GET', 'info_js.php?' + sliding.query, true);
			req.onreadystatechange = generateOnThreadInfoGet(req, JSON.parse, pop, table);
			req.send(null);

			// �v�f���X���C�h���ꂽ�����N�̌�ɑ}��
			li = anchor.parentNode;
			ul = li.parentNode;
			if (li.nextSibling) {
				ul.insertBefore(pop, li.nextSibling);
			} else {
				ul.appendChild(pop);
			}
			pop.style.display = 'list-item';
		};

		// }}}
	};

	// }}}
	// {{{ on DOMContentLoaded

	window.addEventListener('DOMContentLoaded', function(event) {
		// iutil/JSON�����p�\�ɂȂ�܂ő҂�
		if (typeof window.iutil === 'undefined' ||
			typeof window.JSON  === 'undefined')
		{
			window.setTimeout(arguments.callee, 50);
		} else {
			setup(window.iutil, window.JSON);
			window.removeEventListener('DOMContentLoaded', arguments.callee, false);
		}
	}, false);

	// }}}
})();

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
