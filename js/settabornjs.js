// ���X���b�h���ځ[��Z�b�g�֐�
// setTAbornJs('host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$sid_q}', '{$tabdo}',{$STYLE['info_pop_size']}, 'read|subject|info', this);
//
function setTAbornJs(tquery, tabdo, info_pop_width, info_pop_height, page, obj)
{
	// read.php�ł́A�y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
	// �iread.php �͓ǂݍ��݊�������idx�L�^����������邽�߁j
	if ((page == 'read') && !gIsPageLoaded) {
		return false;
	}
	
	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
		// XMLHTTP�i��innerHTML�j �ɖ��Ή��Ȃ珬����
		infourl = 'info.php?' + tquery + '&taborn=' + tabdo + '&popup=2';
		return OpenSubWin(infourl,info_pop_width,info_pop_height,0,0);
	}

	url = 'httpcmd.php?' + tquery + '&taborn=' + tabdo + '&cmd=taborn'; // �X�N���v�g�ƁA�R�}���h�w��

	var res = getResponseTextHttp(objHTTP, url, 'nc');
	var rmsg = "";
	if (res) {
		if (res == '1') {
			rmsg = '����';
		}
		if (rmsg) {
			var ta_num = document.getElementById('ta_num');
			var ta_int = (ta_num) ? parseInt(ta_num.innerHTML, 10) : 0;
			if (tabdo == '1') {
				ta_int++;
				nextset = '0';
				tabmark = '�~';
				tabtitle = '���ځ[�����';
			} else {
				ta_int--;
				nextset = '1';
				tabmark = '�|';
				tabtitle = '���ځ[�񂷂�';
			}
			if (ta_num) {
				ta_num.innerHTML = ta_int.toString();
			}
			if (obj.className) {
				objClass = ' class="' + obj.className + '"';
			} else {
				objClass = '';
			}
			if (page != 'subject') {
				tabstr = '���ځ[��';
			} else {
				tabstr = '';
			}
			var tabhtm = '<a' + objClass + ' href="info.php?' + tquery + '&amp;taborn=' + nextset + '" target="info" onClick="return setTAbornJs(\'' + tquery + '\', \''+nextset+'\', '+info_pop_width+', '+info_pop_height+', \'' + page + '\', this);" title="' + tabtitle + '">' + tabstr + tabmark + '</a>';
			if (page != 'read') {
				var cBox = document.getElementById('tabcb_removenow');
				if (nextset == '0' && cBox && cBox.checked) {
					var tr = obj.parentNode;
					while (tr.tagName.toUpperCase() != 'TR') {
						tr = tr.parentNode;
					}
					/*var suicide = function() {
						tr.parentNode.removeChild(tr);
					}
					setTimeout(suicide, 300);*/
					tr.parentNode.removeChild(tr);
				} else {
					obj.parentNode.innerHTML = tabhtm;
				}
			} else {
				var span = document.getElementsByTagName('span');
				for (var i = 0; i < span.length; i++) {
					if (span[i].className == 'tabdo') {
						span[i].innerHTML = tabhtm;
					}
				}
			}
		}
	}
	return false;
}

//�X���b�h���ځ[��̃X�C�b�`��\������
function showTAborn(i, tabdo, info_pop_width, info_pop_height, page, obj)
{
	var th, th2, to, tx, closure, cBox, nObj;
	
	tx = '1em';
	
	if (th = document.getElementById('sb_th_no')) {
		th2 = document.createElement(th.tagName);
		th2.className = th.className;
		th2.style.width = tx;
		th2.appendChild(document.createTextNode('�w'));
		if (th.nextSibling) {
			th.parentNode.insertBefore(th2, th.nextSibling);
		} else {
			th.parentNode.appendChild(th2);
		}
	}
	
	while (to = document.getElementById('to' + i.toString())) {
		closure = function() {
			var td, td2, tparam, tquery, cObj, pObj;
			
			tparam = '&taborn=' + tabdo;
			tquery = to.href.substring(to.href.indexOf('?') + 1, to.href.length) + tparam;
			
			cObj = document.createElement('a');
			cObj.href = to.href + tparam;
			if (to.target) {
				cObj.target = to.target;
			}
			if (to.className) {
				cObj.className = to.className;
			}
			cObj.onclick = function() {
				return setTAbornJs(tquery, tabdo, info_pop_width, info_pop_height, page, cObj);
			}
			
			if (tabdo == '1') {
				cObj.appendChild(document.createTextNode('�|'));
			} else {
				cObj.appendChild(document.createTextNode('�~'));
			}
			
			pObj = document.createElement('span');
			pObj.appendChild(cObj);
			
			td = to.parentNode;
			while (td.tagName.toUpperCase() != 'TD') {
				td = td.parentNode;
			}
			td2 = document.createElement('td');
			td2.className = td.className;
			td2.style.width = tx;
			td2.appendChild(pObj);
			if (td.nextSibling) {
				td.parentNode.insertBefore(td2, td.nextSibling);
			} else {
				td.parentNode.appendChild(td2);
			}
		}
		closure();
		i++;
	}
	
	cBox = document.createElement('input');
	cBox.id = 'tabcb_removenow';
	cBox.type = 'checkbox';
	cBox.checked = true;
	cBox.defaultChecked = true;
	
	nObj = document.createElement('label');
	nObj.appendChild(cBox);
	nObj.appendChild(document.createTextNode('���ځ[�񂵂��X���b�h���ꗗ�������'));
	obj.parentNode.replaceChild(nObj, obj);
	
	return false;
}
