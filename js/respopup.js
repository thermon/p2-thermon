/* p2 - 引用レス番をポップアップするためのJavaScript */

/*
document.open;
document.writeln('<style type="text/css" media="all">');
document.writeln('<!--');
document.writeln('.respopup{visibility: hidden;}');
document.writeln('-->');
document.writeln('</style>');
document.close;
*/

delayShowSec = 0.1 * 1000;	// レスポップアップを表示する遅延時間。
delaySec = 0.3 * 1000;	// レスポップアップを非表示にする遅延時間。
zNum = 0;

//==============================================================
// gPOPS -- ResPopUp オブジェクトを格納する配列。
// 配列 gPOPS の要素数が、現在生きている ResPopUp オブジェクトの数となる。
//==============================================================
gPOPS = new Array();

gResPopCtl = new ResPopCtl();

gShowTimerIds = new Object();

isIE = /*@cc_on!@*/false;

importedRes=new Array();	// レスが展開されたフラグの配列
resPosition=new Array();	// 展開されたレスの親レスとそこからの相対y座標の配列

// レスポップアップや被参照レスのブロック表示内の、それを呼び出したレスに対するアンカーを修飾する
resCallerDecorate=true;
importedClass="imported";

function getElement(id) {
//	// alert(id);
	if (typeof(id) == "string") {
		if (isIE) { // IE用
			return document.all[id];
		} else if (document.getElementById) { // DOM対応用（Mozilla）
			return document.getElementById(id);
		}
	} else {
		return id;
	}
}

function toggleResBlk(evt, res) {
	if (!getElement('footer')) return null;	// htmlソースがすべて読み込まれていない場合は作動させない
	var evt = (evt) ? evt : ((window.event) ? window.event : null);
	var target = evt.target ? evt.target :
		(evt.srcElement ? evt.srcElement : null);
	if(typeof(res.ondblclick) !== 'function')
		res.ondblclick = res.onclick;

	// イベント発動チェック
	if (target.className == 'respopup') return;
	var resblock = _findChildByClassName(res, 'resblock');
	if (evt == null || res == null || target == null || resblock == null)
		return;
	var button = _findChildByClassName(res, 'buttonblock');
	if (!button) return;
	if (target != res && target != resblock && target != button) {
/*
		// レスリストのクリックかどうか
		var isdescend = function (check) {
			if (!check) return false;
			var test = target;
			do {
				if (test == check) return true;
				test = test.parentNode;
			} while (test && test != res);
		};
		if ( !isdescend(_findChildByClassName(res, 'reslist')) &&
			!isdescend(_findChildByClassName(res, 'v_reslist')))
*/
			return;
	}

	var anchors = _findAnchorComment(res);
	if (anchors == null) return;

	if (_findChildByClassName(resblock, 'resblock_inner') !== null &&
			evt.type != 'dblclick') {
		if (result_pair=removeRes(res, button)) {
//			console.log(result_pair);
			var div_class=result_pair[0].unique();
			if (div_class) {
				// クラス名で要素を探す
				var el=document.getElementsByTagName('div');
				var re=new RegExp('\\b('+div_class.join('|')+')\\b');
				
				i=el.length;
				while(i--) {
					if(el[i].className.match(re)){
						el[i].className=el[i].className.remove(importedClass,' ');
					}
				}
			}
		
			result1=result_pair[1].unique();
//			console.log("close result1="+result1);

			var a_class=new Array();
			var idx=result1.length;
			while(idx--) {
				if (importedRes[result1[idx]]==0) {a_class.push("T"+result1[idx]);}
			}
//			console.log("close result2="+a_class);
			if (a_class.length) {
				// クラス名で要素を探す
				var el=document.getElementsByTagName('a');
				var re=new RegExp('\\b('+a_class.join('|')+')\\b');
			
				// 検索条件にマッチしたら、クラス追加／削除
				i=el.length;
				while(i--) {
					if(el[i].className.match(re)){
						el[i].className=el[i].className.remove(importedClass,' ');
					}
				}
			}
		}
	} else {
		if (result_pair=insertRes(evt, res, anchors)) {
			(function(origId) 
			{
				var oidx=origId.length;
				while(oidx--) {

					var orig = (document.all) ?  document.all[origId[oidx]]
					: ((document.getElementById) ? document.getElementById(origId[oidx])
					   : null);
					if (orig) {
						orig.className+=' '+importedClass;
					}
				}
			}
		 	)(result_pair[0].unique());
		
			result1=result_pair[1].unique();
//			console.log("open result1="+result1);

			var a_class=new Array();
			var idx=result1.length;
			while(idx--) {
				if (importedRes[result1[idx]]==1) {a_class.push("T"+result1[idx]);}
			}
//			console.log("open result2="+a_class);
			if (a_class.length) {
				// クラス名で要素を探す
				var el=document.getElementsByTagName('a');
				var re=new RegExp('\\b('+a_class.join('|')+')\\b');
			
				// 検索条件にマッチしたら、クラス追加／削除
				i=el.length;
				while(i--) {
					if(el[i].className.match(re)){
						el[i].className+=' '+importedClass;
					}
				}
			}
		}
	}
}

function resIdCompare(a,b){
	var ax=a.match(/\d+$/);
	var bx=b.match(/\d+$/);
	return ax-bx;
}

function insertRes(evt, res, anchors) {
	var cascade_flag=false;	// カスケードか、平面展開か
//	console.log('insertRes');
//	console.log(res);
	//		console.log(anchors);
	var markRead=new Array();		// インポートされたレスの番号を格納
	var openedAnchors=new Array();	// 開くことができたレスのアンカーを格納
	var resblock = _findChildByClassName(res, 'resblock');
	if (!resblock) return new Array();
	var button = _findChildByClassName(res, 'buttonblock');
	var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
	/*
	// 既に開いていた場合
	if (resblock_inner) {
		if (evt.type != 'dblclick') return new Array();
		// ダブルクリックならカスケード
		var r=(function (nodes) {
			var result=new Array();
			for  (var i=0;i<nodes.length;i++) {
				if (nodes[i].className != 'folding_container') continue;
				var anchor = _findAnchorComment(nodes[i]);
				if (anchor != null) {
					result_pair=insertRes(evt, nodes[i], _findAnchorComment(nodes[i]));
					if (result_pair.length) {
						markRead=markRead.concat(result_pair[0]);
						openedAnchors=openedAnchors.concat(result_pair[1]);
					}
				}
				
			}
			console.log("result="+result);
			return result;
		   })(resblock_inner.childNodes);
		console.log("r="+r);
		return;
	}
	*/

	// reslistがあれば非表示に
	var reslistP = _findChildByClassName(res, 'reslist');
	//	if (reslistP) reslistP.style.display = 'none';
	
	var anchorsArray=anchors.split("/");
	var resblock_inner = document.createElement('div');
	var count=0;

	// ダブルクリックなら平面展開
	var allAnchors=new Array();
	if (evt.type == 'dblclick' && !cascade_flag) {
		while (anchorsArray.length) {
			var resid=anchorsArray.shift();
//		console.log(resid);
			var resElement=getElementForCopy(""+resid);
			var resAnchor = _findAnchorComment(resElement);
			if (resAnchor) {
				anchorsArray=anchorsArray.concat(resAnchor.split("/"));
			}
			allAnchors.push(resid);
		}
		allAnchors=allAnchors.unique().sort(resIdCompare);
	} else {
		allAnchors=anchorsArray;
	}
//	console.log(allAnchors);
	
	var i=allAnchors.length;
	var children=new Array();

	while(i--) {	// 重複展開を禁止
		if (!importedRes[allAnchors[i]] || !cascade_flag) {
			children.unshift(allAnchors[i]);
			importedRes[allAnchors[i]]=1;
		}
	}
	var HTMLAnchorList=document.getElementsByName('linkfrom');

	for (i=0;i<children.length;i++) {
		var importId=children[i];
		//	console.log(importId);

		/*ハイパーリンクのURLを被参照ブロック内に変更する*/
		var id_r=importId.replace(/qr/,'r');
		var id_rx=importId.replace(/qr/,'rx');
		var HTMLAnchorTo=document.getElementsByName(id_rx);

		if (!HTMLAnchorTo.length){
			var href=location.href.replace(/#.*$/,"#"+id_r);
			for (ix=0;ix<HTMLAnchorList.length;ix++) {
				if (HTMLAnchorList.item(ix).href == href) {
					HTMLAnchorList.item(ix).href="#"+id_rx;
				}
			}
		}
	}
	
	for (i=0;i<children.length;i++) {
		var importId=children[i];

//		var importElement=getElementForCopy(""+importId);
		var importElement2=getElement(importId);
		

		//参照先レス情報をコピー
		var container=document.createElement('blockquote');
		var importedAnchor=createNamedElement('a',importId.replace(/qr/,'rx'));
		container.appendChild(importedAnchor);
		for (var c=0;c<importElement2.childNodes.length;c++) {
			container.appendChild(importElement2.childNodes[c].cloneNode(true));
		}

		var msgBlock = _findChildByClassName(container, 'message');
		msgBlock.removeAttribute('id');
//		container.innerHTML=importElement.innerHTML.replace(/id=\"[^\"]+\"/g,"");
		container.className='folding_container ';
		if (evt.type == 'dblclick' && !cascade_flag) {
			container.className+='single_layer ';
		}
		container.className+=importId.replace(/qr/,"r");

		var anchor = _findAnchorComment(importElement2);
		if (anchor) {
			container.onclick = function (evt) {
				toggleResBlk(evt, this);
			};
/*			var c_buttonblock=document.createElement('div');
			c_buttonblock.className = 'buttonblock';
			if (button)
				c_buttonblock.appendChild(button.cloneNode(false));*/

			var reslistC = _findChildByClassName(container, 'reslist');
			container.insertBefore(button.cloneNode(false), reslistC);
			
			var c_resblock=document.createElement('div');
			c_resblock.className = 'resblock';
			container.appendChild(c_resblock);
			
			// ダブルクリックならカスケード
			if (evt.type == 'dblclick' && cascade_flag) {
				result_pair=insertRes(evt, container, anchor);
				if (result_pair.length) {
					markRead=markRead.concat(result_pair[0]);
					openedAnchors=openedAnchors.concat(result_pair[1]);
				}
			}
		}
		resblock_inner.appendChild(container);	// 展開処理
		
		if (reslistP) {
			var linkstr=_findChildByClassName(reslistP, importId);
			if (linkstr) {
//				linkstr.innerHTML=linkstr.innerHTML.replace(/(【.+】)/,"<!--$1-->");
				linkstr.style.display = 'none';
			}
		}

		openedAnchors.push(importId);
		markRead.push(importId.replace(/qr/,'qm'));	// 子レスのポップアップを既読処理
		var anchor = _findAnchorComment(importElement2);
		//		if (!anchor) {
		markRead.push(importId.replace(/qr/,'r'));	// 孫がいないときは子レスの本体を既読処理
		//		}
		//		console.log('markRead='+markRead);

		//		console.log("open "+importId+":"+importedRes[importId]);
		count++;
				var parent=res.id;
		if (!parent) {
			var x=res.className.match(/\br\d+\b/);
			parent=x[0];
		}
		/*
		var y0=getElement(parent).offsetTop;
		var y1=getElement(container).offsetTop;
		console.log("parent y="+y0);
		console.log("child y="+y1);
		resPosition[importId]=new Array(parent,y1-y0);
		*/
	}

	if (count) {
		resblock_inner.className='resblock_inner';
		resblock.appendChild(resblock_inner);
		
		importId=res.id.replace(/r/,"qr");
//		console.log(importId);
		if (importId && !importedRes[importId]) {
			importedRes[importId]=1;
			openedAnchors.push(importId);
			// オリジナルのレスがあれば見た目変更
			var resClass=res.className.match(/(^| )(r\d+)/);
			if (!res.id) {markRead.push(resClass[2]);}// 親レスの本体を既読処理
			markRead.push(resClass[2].replace(/r/,'qm'));	// 親レスのポップアップを既読処理
		}
	}

	if (button && count) button.src=button.src.replace(/plus/,'minus');
	return new Array(markRead,openedAnchors);
}

function appendChildBackword(parent,child) {
		if (parent.childNodes.length) {
			parent.insertBefore(child,parent.firstChild);
		} else {
			parent.appendChild(child);
		}
}

function removeRes(res, button) {
//	console.log('removeRes');
	if (button) button.src=button.src.replace(/minus/,'plus');
	var closedAnchors=new Array();
	var markRead=new Array();		// インポートされたレスの番号を格納
	var children2=new Array();
	var resblock = _findChildByClassName(res, 'resblock');
	if (resblock) {
		var resblock_inner = _findChildByClassName(resblock, 'resblock_inner');
		if (resblock_inner) 
		{
			for (var c=0;c<resblock_inner.childNodes.length;c++) {
				result=removeRes(resblock_inner.childNodes[c]);
				closedAnchors=closedAnchors.concat(result[1]);
				markRead=markRead.concat(result[0]);
				var id=resblock_inner.childNodes[c].className.match(/(t\d+)?r\d+/);
				var id_r=id[0];
				var id_qr=id_r.replace(/r/,"qr");
				if (--importedRes[id_qr]<=0) {
					importedRes[id_qr]=0;
					closedAnchors.push(id_qr);
					markRead.push(id_qr.replace(/qr/,'qm'));	// 子レスのポップアップを既読解除
					var child=_findChildByClassName(resblock_inner, id_r);
					//			if (!_findChildByClassName(child, 'resblock')) {
					markRead.push(id_r);	// 孫がいなければ子レスの本体を既読解除
					//			}
				}
			}
			resblock.removeChild(resblock_inner);
			
			var HTMLAnchorList=document.getElementsByName('linkfrom');
			/*ハイパーリンクのURLを元に戻す*/
			for (var c=0;c<resblock_inner.childNodes.length;c++) {
				var id=resblock_inner.childNodes[c].className.match(/(t\d+)?r\d+/);
				var id_r=id[0];
				var id_rx=id_r.replace(/r/,"rx");	
				var HTMLAnchorTo=document.getElementsByName(id_rx);

				if (!HTMLAnchorTo.length){
					var href=location.href.replace(/#.*/,"#"+id_rx);
					for (ix=0;ix<HTMLAnchorList.length;ix++) {
						if (HTMLAnchorList.item(ix).href == href) {
							HTMLAnchorList.item(ix).href="#"+id_r;
						}
					}
				}
			
			}
		}


	}
	
	// reslistがあれば表示
	var reslistP = _findChildByClassName(res, 'reslist');
//	if (reslistP) reslistP.style.display = 'block';
	if (reslistP) {
		for (var i=0;i<reslistP.childNodes.length;i++) {
//			reslistP.childNodes[i].innerHTML=reslistP.childNodes[i].innerHTML.replace(/<!\-\-(.+)\-\->/,"$1");
			reslistP.childNodes[i].style.display = 'block';

		}
	}
	id=res.id.replace(/q?r/,"qr");
	if (--importedRes[id]<=0) {
		importedRes[id]=0;
		closedAnchors.push(id);
		// オリジナルのレスがあれば見た目変更
		var resClass=res.className.match(/(^| )(r\d+)/);
		if (!res.id) {markRead.unshift(resClass[2]);}// 親レスの本体を既読解除
		markRead.unshift(resClass[2].replace(/r/,'qm'));	// 親レスのポップアップを既読解除
	}

	return new Array(markRead,closedAnchors);
}

function getElementForCopy(qresID) {
	if (qresID.indexOf("-") != -1) { return null; } // 連番 (>>1-100) は非対応なので抜ける
	
	if (document.all) { // IE用
		aResPopUp = document.all[qresID];
	} else if (document.getElementById) { // DOM対応用（Mozilla）
		aResPopUp = document.getElementById(qresID);
	}

	return aResPopUp ? aResPopUp : null;
}

function _findChildByClassName(p, kls) {
	if (p != null) {
		for (var i=0;i<p.childNodes.length;i++) {
			if (!p.childNodes[i].className) {continue;}
			if (p.childNodes[i].className.split(' ').find(kls)) {
				return p.childNodes[i];
			}
		}
	}
	return null;
}

function _findAnchorComment(res) {
	if (res != null) {
		var i=res.childNodes.length;
		while(i--) {
//		for (var i=0;i<res.childNodes.length;i++) {
			if (res.childNodes[i].nodeName.toLowerCase().indexOf('comment') != -1) {
				var nv = res.childNodes[i].nodeValue.replace(/^\s+|\s+$/g, '');
				if (nv.indexOf('backlinks:') == 0) {
					return nv.substr('backlinks:'.length);
				}
			}
		}
	}
	return null;
}

/**
 * レスポップアップを表示タイマーする
 *
 * 引用レス番に onMouseover で呼び出される
 */
function showResPopUp(divID, ev,anchor) {
	if (!getElement('footer')) return null;	// htmlソースがすべて読み込まれていない場合は作動させない
	if (divID.indexOf("-") != -1) { return; } // 連番 (>>1-100) は非対応なので抜ける

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // 非表示タイマーを解除
	} else {
		// doShowResPopUp(divID, ev);

		x = getPageX(ev);
		y = getPageY(ev);

		var from='';
		if (anchor) {
			var matched=anchor.className.match(/F((t\d+)?q?r\d+)/);
			if (matched) {from=matched[1].replace(/q?r/,'qr');}
		}
		aShowTimer = new Object();
		aShowTimer.timerID = setTimeout("doShowResPopUp('" + divID + "','"+from+"')", delayShowSec); // 一定時間したら表示する

		aShowTimer.x = x;
		aShowTimer.y = y;

		gShowTimerIds[divID] = aShowTimer;
		//alert(gShowTimerIds[divID].timerID);
	}

}

/**
 * レスポップアップを表示する
 */
function doShowResPopUp(divID,from) {
	x = gShowTimerIds[divID].x;
	y = gShowTimerIds[divID].y;
	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // 非表示タイマーを解除

		/*
		// 再表示時の zIndex 処理 ------------------------
		// しかしなぜか期待通りの動作をしてくれない。
		// IEとMozillaで挙動も違う。よって非アクティブ。
		aResPopUp.zNum = zNum;
		aResPopUp.popOBJ.style.zIndex = aResPopUp.zNum;
		//----------------------------------------
		*/

	} else {
		zNum++;
		aResPopUp = gResPopCtl.addResPopUp(divID,from); // 新しいポップアップを追加
	}

	aResPopUp.showResPopUp(x, y);

	(function (divID) {	// ポップアップの元があればハイライト
		var orig;
		if (document.all) { // IE用
			orig = document.all[divID.replace(/qr/,'r')];
		} else if (document.getElementById) { // DOM対応用（Mozilla）
			orig = document.getElementById(divID.replace(/qr/,'r'));
		}

		 if (orig) {
		 	orig.className+=' highlight';
		}
	})(divID);
	
}

/**
 * レスポップアップを非表示タイマーする
 *
 * 引用レス番から onMouseout で呼び出される
 */
function hideResPopUp(divID,anchor) {
	if (divID.indexOf("-") != -1) { return; } // 連番 (>>1-100) は非対応なので抜ける

	// 表示タイマーを解除
	if (gShowTimerIds[divID] && gShowTimerIds[divID].timerID) {
		clearTimeout(gShowTimerIds[divID].timerID);
	}

	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
				var from='';
		if (anchor && anchor.className) {
			var matched=anchor.className.match(/F((t\d+)?q?r\d+)/);
			if (matched) {from=matched[1].replace(/q?r/,'qr');}
		}
		aResPopUp.hideResPopUp(from);
	}
}

/**
 * レスポップアップを非表示にする
 */
function doHideResPopUp(divID,from) {
	var aResPopUp = gResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		aResPopUp.doHideResPopUp();
	}
	
	(function (divID) {	// ポップアップ元のハイライトを戻し
		if (document.all) { // IE用
			 var orig = document.all[divID.replace(/qr/,'r')];
		} else if (document.getElementById) { // DOM対応用（Mozilla）
			 var orig = document.getElementById(divID.replace(/qr/,'r'));
		}
		 if (orig) {
		 	orig.className=orig.className.remove('highlight',' ');
		}
	})(divID);
}


/**
 * オブジェクトデータをコントロールするクラス
 */
function ResPopCtl() {

	/**
		* 配列 gPOPS に新規 ResPopUp オブジェクト を追加する
		*/
	ResPopCtl.prototype.addResPopUp = function (divID,from) {
		var aResPopUp = new ResPopUp(divID);
		// gPOPS.push(aResPopUp); Array.push はIE5.5未満未対応なので代替処理
		aResPopUp.from.push(from);
		if (from) {
		var msgblock = _findChildByClassName(aResPopUp.popOBJ, 'message');
		if (msgblock) {
			var anchor=_findChildByClassName(msgblock,'T'+from);
			if (anchor) {
				anchor.className+= " popedFrom";
			}
		}
	}
		return gPOPS[gPOPS.length] = aResPopUp;
	}

	/**
		* 配列 gPOPS から 指定の ResPopUp オブジェクト を削除する
		*/
	ResPopCtl.prototype.rmResPopUp = function (divID) {
		for (i = 0; i < gPOPS.length; i++) {
			if (gPOPS[i].divID == divID) {

				gPOPS = arraySplice(gPOPS, i);

				return true;
			}
		}
		return false;
	}

	/**
		* 配列 gPOPS で指定 divID の ResPopUp オブジェクトを返す
		*/
	ResPopCtl.prototype.getResPopUp = function (divID) {
		for (i = 0; i < gPOPS.length; i++) {
			if (gPOPS[i].divID == divID) {
				return gPOPS[i];
			}
		}
		return false;
	}

	return this;
}

/**
 * arraySplice
 *
 * anArray.splice(i, 1); Array.splice はIE5.5未満未対応なので代替処理
 * @return array
 */
function arraySplice(anArray, i) {
	var newArray = new Array();
	for (j = 0; j < anArray.length; j++) {
		if (j != i) {
			newArray[newArray.length] = anArray[j];
		}
	}
	return newArray;
}

/**
 * レスポップアップクラス
 */
function ResPopUp(divID) {

	this.divID = divID;
	this.zNum = zNum;
	this.hideTimerID = 0;
	this.linkclass=new Array();
	this.from=new Array();

	if (document.all) { // IE用
		this.popOBJ = document.all[this.divID];
	} else if (document.getElementById) { // DOM対応用（Mozilla）
		this.popOBJ = document.getElementById(this.divID);
	}

	/**
		* レスポップアップを表示する
		*/
	ResPopUp.prototype.showResPopUp = function (x, y) {
		var x_adjust = 10;	// x軸位置調整
		var y_adjust = -10;	// y軸位置調整
		if (this.divID.indexOf('spm_') == 0) {
			y_adjust = -10;
		} else if (this.divID.indexOf('matome_cache_meta') == 0) {
			x_adjust += 10;
			y_adjust += 10;
		}
		if (this.popOBJ.style.visibility != "visible") {
			this.popOBJ.style.zIndex = this.zNum;
			//x = getPageX(ev); // 現在のマウス位置のX座標
			//y = getPageX(ev); // 現在のマウス位置のY座標
			this.popOBJ.style.left = x + x_adjust + "px"; //ポップアップ位置
			this.popOBJ.style.top = y + y_adjust + "px";
			//alert(window.pageYOffset);
			//alert(this.popOBJ.offsetTop);

			var scrollY = getScrollY();
			var windowHeight = getWindowHeight();
			if ((this.popOBJ.offsetTop + this.popOBJ.offsetHeight) > (scrollY + windowHeight)) {
				this.popOBJ.style.top = (scrollY + windowHeight - this.popOBJ.offsetHeight - 20) + "px";
			}
			if (this.popOBJ.offsetTop < scrollY) {
				this.popOBJ.style.top = (scrollY - 2) + "px";
			}

			this.popOBJ.style.visibility = "visible"; // レスポップアップ表示
		}
	}

	/**
		* レスポップアップを非表示タイマーする
		*/
	ResPopUp.prototype.hideResPopUp = function () {
		this.hideTimerID = setTimeout("doHideResPopUp('" + this.divID + "')", delaySec); // 一定時間表示したら消す
	}

	/**
		* レスポップアップを非表示にする
		*/
	ResPopUp.prototype.doHideResPopUp = function () {

		for (i=0; i < gPOPS.length; i++) {

			if (this.zNum < gPOPS[i].zNum) {
				//clearTimeout(this.hideTimerID); // タイマーを解除
				this.hideTimerID = setTimeout("hideResPopUp('" + this.divID + "','"+ from +"')", delaySec); // 一定時間表示したら消す
				return;
			}
		}

		this.popOBJ.style.visibility = "hidden"; // レスポップアップ非表示
		// clearTimeout(this.hideTimerID); // タイマーを解除
		gResPopCtl.rmResPopUp(this.divID);

		var from=this.from.pop();
		
//		console.log("doHideResPopUp "+from+"=>"+this.divID);
		if (from) {
			var msgblock = _findChildByClassName(this.popOBJ, 'message');
			if (msgblock) {
				var anchor=_findChildByClassName(msgblock,'T'+from);

				if (anchor) {
					anchor.className=anchor.className.remove('popedFrom',' ');
				}
			}
		}

	}

	return this;
}

// Arrayクラスにメソッド追加
Array.prototype.remove= function(el){
	var j=this.length;
	while(j--) {	
//	for (var j=this.length-1;j>=0;j--) {
		if (this[j] == el) {
			this.splice(j, 1);
			break;
		}
	}
	return this;
};

Array.prototype.find= function(el){
	var j=this.length;
	while(j--) {
		if (this[j] == el) {
			return true;
		}
	}
	return false;
};

Array.prototype.unique = function(){
	var i = this.length;
	var hashary=new Object();
	var newarray=new Array();
	while(i--){
		if (!hashary[this[i]] && this[i]!='') {
			hashary[this[i]]=1;
			newarray.unshift(this[i]);
		}
   }
   return newarray;
};

function createNamedElement(type, name) {
   var element = null;
   // Try the IE way; this fails on standards-compliant browsers
   try {
      element = document.createElement('<'+type+' name="'+name+'">');
   } catch (e) {
   }
   if (!element || element.nodeName != type.toUpperCase()) {
      // Non-IE browser; use canonical method to create named element
      element = document.createElement(type);
      element.name = name;
   }
   return element;
}

// Stringクラスにメソッド追加
String.prototype.remove= function(str,delimiter){
	return this.split(delimiter).remove(str).join(delimiter);
};

function ShowSize() 
{
     var ua = navigator.userAgent;       // ユーザーエージェント
     var nWidth, nHeight;                   // サイズ
     var nHit = ua.indexOf("MSIE");     // 合致した部分の先頭文字の添え字
     var bIE = (nHit >=  0);                 // IE かどうか
     var bVer6 = (bIE && ua.substr(nHit+5, 1) == "6");  // バージョンが 6 かどうか
     var bStd = (document.compatMode && document.compatMode=="CSS1Compat");
                                                                           // 標準モードかどうか
     if (bIE) {
          if (bVer6 && bStd) {
               nWidth = document.documentElement.clientWidth;
               nHeight = document.documentElement.clientHeight;
          } else {
               nWidth = document.body.clientWidth;
               nHeight = document.body.clientHeight;
          }
     } else {
          nWidth = window.innerWidth;
          nHeight = window.innerHeight;
     }

//     alert("サイズ　：　幅 " + nWidth + " / 高さ " + nHeight);
	return nWidth;
}
width=ShowSize();