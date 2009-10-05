/*
   p2 - HTML���|�b�v�A�b�v���邽�߂�JavaScript
   
   @thanks http://www.yui-ext.com/deploy/yui-ext/docs/
*/

//showHtmlDelaySec = 0.2 * 1000; // HTML�\���f�B���C�^�C���B�}�C�N���b�B

gShowHtmlTimerID = null;
gNodePopup = null;	// iframe���i�[����div�v�f
//gNodeClose = null; // �~���i�[����div�v�f
tUrl = ""; // URL�e���|�����ϐ�
gUrl = ""; // URL�O���[�o���ϐ�

// �u���E�U��ʁi�X�N���[����j�̃}�E�X�� X, Y���W
gMouseX = 0;
gMouseY = 0;

iResizable = null;
stophide = false;

/**
 * HTML�v�A�b�v��\������
 * �����̈��p���X�Ԃ�(p)�� onMouseover �ŌĂяo�����
 * [memo] ��������event�I�u�W�F�N�g�ɂ��������悢���낤���B
 *
 * @access public
 */
function showHtmlPopUp(url, ev, showHtmlDelaySec)
{
	if (!document.createElement) { return; } // DOM��Ή��Ȃ甲����
	
	// �܂� onLoad ����Ă��Ȃ��A�R���e�i���Ȃ���΁A������
	if (!gIsPageLoaded && !document.getElementById('popUpContainer')) {
		return;
	}
	
	showHtmlDelaySec = showHtmlDelaySec * 1000;

	if (!gNodePopup || url != gUrl) {
		tUrl = url;

		var pointer = getPageXY(ev);
		gMouseX = pointer[0];
		gMouseY = pointer[1];
		
		// HTML�\���f�B���C�^�C�}�[
		gShowHtmlTimerID = setTimeout("showHtmlPopUpDo()", showHtmlDelaySec);
	}
}

/**
 * showHtmlPopUpDo() ���痘�p�����
 *
 * @return integer
 */
function getCloseTop(win_bottom)
{
	var close_top_adjust = 16;

	close_top = Math.min(win_bottom - close_top_adjust, gMouseY + close_top_adjust);
	if (close_top >= win_bottom - close_top_adjust) {
		close_top = gMouseY - close_top_adjust - 12;
	}
	return close_top;
}

/**
 * HTML�|�b�v�A�b�v�̎��s
 */
function showHtmlPopUpDo()
{
	
	// ���炩���ߊ�����HTML�|�b�v�A�b�v����Ă���
	hideHtmlPopUp(null, true);

	gUrl = tUrl;
	var popup_x_adjust = 7;			// popup(iframe)��x���ʒu����
	var closebox_width = 18;		// �~�̉���
	var adjust_for_scrollbar = 22;	// 22 �X�N���[���o�[���l�����ď��������ڂɔ�����
	
	if (gUrl.indexOf("kanban.php?") != -1) { popup_x_adjust += 23; }

	if (!gNodePopup) {
		gNodePopup = document.createElement('iframe');
		gNodePopup.setAttribute('id', 'iframespace');
		gNodePopup.style.backgroundColor = "#ffffff";
		
		/*
		gNodeClose = document.createElement('div');
		gNodeClose.setAttribute('id', "closebox");
		gNodeClose.setAttribute('onMouseover', "hideHtmlPopUp(ev)");
		*/
		
		var closeX = gMouseX + popup_x_adjust - closebox_width;
		
		// IE�p
		if (document.all) {
			var body = getDocumentBodyIE();
			
			var iframeX = gMouseX + popup_x_adjust;
			gNodePopup.style.pixelLeft  = iframeX;			// �|�b�v�A�b�v�ʒu iframe��X���W
			gNodePopup.style.pixelTop  = body.scrollTop;	// �|�b�v�A�b�v�ʒu iframe��Y���W
			// document.body.scrollTop �� DOCTIYE�� document.documentElement.scrollTop �ɂȂ�炵��
			
			/*
			gNodeClose.style.pixelLeft  = closeX; 		// �|�b�v�A�b�v�ʒu �~��X���W
			// �|�b�v�A�b�v�ʒu �~��Y���W
			var close_top = getCloseTop(body.scrollTop + body.clientHeight);
			gNodeClose.style.pixelTop = close_top;
			*/
			
			var iframe_width = body.clientWidth - gNodePopup.style.pixelLeft - adjust_for_scrollbar;
			var iframe_height = body.clientHeight - adjust_for_scrollbar;
			
			widthRatio = 0.6;
			if (iframe_width < body.clientWidth * widthRatio) {
				addIframeWidth = (body.clientWidth * widthRatio) - iframe_width;
				iframe_width += addIframeWidth;
				gNodePopup.style.pixelLeft = iframeX - addIframeWidth;
			}
		
		// DOM�Ή��p�iMozilla�j
		} else if (document.getElementById) {
			
			var iframeX = gMouseX + popup_x_adjust;
			gNodePopup.style.left = iframeX + "px"; 			// �|�b�v�A�b�v�ʒu iframe��X���W
			gNodePopup.style.top  = window.pageYOffset + "px";	// �|�b�v�A�b�v�ʒu iframe��Y���W
			
			/*
			gNodeClose.style.left = closeX + "px"; 			// �|�b�v�A�b�v�ʒu �~��X���W
			// �|�b�v�A�b�v�ʒu �~��Y���W
			var close_top = getCloseTop(window.pageYOffset + window.innerHeight);
			gNodeClose.style.top = close_top + "px";
			*/
			
			var iframe_width = window.innerWidth - iframeX - adjust_for_scrollbar;
			var iframe_height = window.innerHeight - adjust_for_scrollbar;
			
			widthRatio = 0.6;
			if (iframe_width < window.innerWidth * widthRatio) {
				addIframeWidth = (window.innerWidth * widthRatio) - iframe_width;
				iframe_width += addIframeWidth;
				var iframe_left = iframeX - addIframeWidth;
				gNodePopup.style.left = iframe_left + 'px';
			}
		}

		gNodePopup.src = gUrl;
		gNodePopup.frameborder = 0;
		gNodePopup.width = iframe_width;
		gNodePopup.height = iframe_height;
		
		pageMargin_at = "";
		// �摜�̏ꍇ�̓}�[�W�����[���ɂ���
		if (gUrl.match(/(jpg|jpeg|gif|png)$/)) {
			//pageMargin_at = ' marginheight="0" marginwidth="0" hspace="0" vspace="0"';
			
			// ���̐ݒ�͌����Ă��Ȃ��HinnerHTML�ł͌����Ă����C������
			gNodePopup.marginheight = 0;
			gNodePopup.marginwidth = 0;
			gNodePopup.hspace = 0;
			gNodePopup.vspace = 0;
		}
		
		// 2006/11/30 ����܂�div����innerHTML�ɂ��Ă����̂́A�������R���������C�����邪�Y�ꂽ�B
		// IE�ł̃|�b�v�A�b�v���|�b�v�A�b�v�͂ǂ���ɂ���ł��Ă��Ȃ��悤���B
		//gNodePopup.innerHTML = "<iframe id=\"iframepop\" src=\""+gUrl+"\" frameborder=\"1\" border=\"1\" style=\"background-color:#fff;margin-right:8px;margin-bottom:8px;\" width=" + iframe_width + " height=" + iframe_height + pageMargin_at +">&nbsp;</iframe>";
		
		//gNodeClose.innerHTML = "<b onMouseover=\"hideHtmlPopUp(ev)\">�~</b>";
		
		var popUpContainer = document.getElementById("popUpContainer");
		
		var headerEI = document.getElementById("header"); //read�p
		if (headerEI) {
			popUpContainer = headerEI;
		} else {
			var Ntd1EI = document.getElementById("ntd1"); // read_new�p
			if (Ntd1EI) {
				popUpContainer = Ntd1EI;
			}
		}
		// popUpContainer ��body�ǂݍ��݂���������O���痘�p�ł���悤�ɗp�ӂ��Ă���B
		// popUpContainer �ł́AYAHOO.ext.Resizable �̕\����������ɁAIE�ŋ󔒃X�y�[�X�������Ă��܂��i�H�j�̂ŁA
		// header �����鎞�́Aheader�𗘗p���Ă���
		if (popUpContainer) {
			popUpContainer.appendChild(gNodePopup);
			//popUpContainer.appendChild(gNodeClose);
		} else {
			document.body.appendChild(gNodePopup);
			//document.body.appendChild(gNodeClose);
		}
		
		if (gIsPageLoaded) {
			setIframeResizable();
		} else {
			var setIframeResizableOnLoad = function(){ setIframeResizable(); }
			YAHOO.util.Event.addListener(window, 'load', setIframeResizableOnLoad);
		}
	}
}

function setIframeResizable()
{
	if (!gNodePopup) {
		return;
	}
	
    iResizable = new YAHOO.ext.Resizable('iframespace', {
            pinned:true,
            //width: 200,
            //height: 100,
            minWidth:100,
            minHeight:50,
            handles: 'all',
            wrap:true,
            draggable:true,
            dynamic: true
    });
	
	var iframespaceEl = iResizable.getEl();
	
    iframespaceEl.dom.style.backgroundColor = "#ffffff";
	iframespaceEl.dom.ondblclick = hideHtmlPopUp;
	
	var msgClose = '����ɂ́A�|�b�v�A�b�v�O���N���b�N';
	window.status = msgClose;
	
    iframespaceEl.on('resize', function(){
        stophide = true;
		this.dom.title = msgClose;
    });
}

// �y�[�W�g�b�v����̃}�E�X�ʒu��X, Y���W
// @return  array
function getPageXY(ev)
{
	/*
	// Yahoo UI �͎g�������Ŏg���Ȃ��H�������肻���ȋC������񂾂��c
	alert(YAHOO.util.Dom.getClientHeight()); // ��ʂ̍��� // Deprecated Now using getViewportHeight. 
	alert(YAHOO.util.Dom.getViewportHeight()); // ���  (excludes scrollbars)
	alert(YAHOO.util.Dom.getDocumentHeight()); // �h�L�������g�S��
	// YAHOO.util.Event.getPageX(ev)
	var cursor1 = YAHOO.util.Event.getXY(ev); // �y�[�W��
	alert(cursor1);
	*/
	
	// IE�p
	if (document.all) {
		// ���݂̃}�E�X�ʒu��X, Y���W
		var body = getDocumentBodyIE();
		// IE�Ȃ�window.event�ŃO���[�o���ɎQ�Ƃł��邪�A�����ł�IE�ȊO�ɂ����킹�Ďg��Ȃ��ł���
		var pageX = body.scrollLeft + ev.clientX;
		var pageY = body.scrollTop  + ev.clientY;

	} else {
		// pageX, pageY �́AIE�͔�T�|�[�g
		var pageX = ev.pageX;
		var pageY = ev.pageY;
	}
	return [pageX, pageY];
}

/**
 * HTML�|�b�v�A�b�v���\���ɂ���
 *
 * @access public
 */
function hideHtmlPopUp(ev, fast)
{
	if (!gIsPageLoaded) {
		return false;
	}
	
	if (!document.createElement) { return; } // DOM��Ή��Ȃ甲����
	
	if (stophide) {
		stophide = false;
		return;
	}
	
	if (!gFade) {
		fast = true;
	}
	
	if (iResizable) {
		var iframespaceEl = iResizable.getEl();
		
		var iRegion = YAHOO.util.Region.getRegion(iframespaceEl.dom);
		
		if (ev) {
			var pageXY = getPageXY(ev);
			//alert(pageXY);
			var pagePoint = new YAHOO.util.Point(pageXY);
			
			if (iRegion.intersect(pagePoint)) {
				return;
			}
			//alert(iRegion);
		}
		
		if (fast) {
			iframespaceEl.remove();
			iResizable = null;
			hideHtmlPopUpDo();
		} else {
			iframespaceEl.setOpacity(0, true, 0.15, function(){
					this.remove();
					iResizable = null;
					hideHtmlPopUpDo();
				});
		}
	
	} else {
		hideHtmlPopUpDo();
	}

}

function hideHtmlPopUpDo()
{
	if (gShowHtmlTimerID) { clearTimeout(gShowHtmlTimerID); } // HTML�\���f�B���C�^�C�}�[����������
	

	if (gNodePopup) {
		gNodePopup.style.visibility = "hidden";
		gNodePopup.parentNode.removeChild(gNodePopup);
		gNodePopup = null;
	}
	
	/*
	if (gNodeClose) {
		gNodeClose.style.visibility = "hidden";
		gNodeClose.parentNode.removeChild(gNodeClose);
		gNodeClose = null;
	}
	*/
}

/**
 * HTML�\���^�C�}�[����������
 *
 * (p)�� onMouseout �ŌĂяo�����
 */
function offHtmlPopUp()
{
	// HTML�\���f�B���C�^�C�}�[������Ή������Ă���
	if (gShowHtmlTimerID) {
		clearTimeout(gShowHtmlTimerID);
	}
}
