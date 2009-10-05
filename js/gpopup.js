/* vim: set fileencoding=cp932 ai noet ts=4 sw=4 sts=4: */
/* mi: charset=Shift_JIS */

/* expack - Google�����̗v����|�b�v�A�b�v���邽�߂�JavaScript */
/* respopup.js�̃T�u�Z�b�g */

var zNum = 0;

//==============================================================
// gShowPopUp -- �v��|�b�v�A�b�v��\������֐�
//==============================================================

function gShowPopUp(divID, ev)
{
	zNum++;

	var popOBJ = document.getElementById(divID);
	var x_adjust = 10; //x���ʒu����
	var y_adjust = 10; //y���ʒu����

	if (popOBJ && popOBJ.style.visibility != "visible") {
		popOBJ.style.zIndex = zNum;
		var x = getPageX(ev);
		var y = getPageY(ev);
		var scrollY = getScrollY();
		var windowHeight = getWindowHeight();
		popOBJ.style.left = x + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
		popOBJ.style.top = y + y_adjust + "px";

		if ((popOBJ.offsetTop + popOBJ.offsetHeight) > (scrollY + windowHeight)) {
			popOBJ.style.top = (scrollY + windowHeight - popOBJ.offsetHeight - 20) + "px";
		}
		if (popOBJ.offsetTop < scrollY) {
			popOBJ.style.top = (scrollY - 2) + "px";
		}
		popOBJ.style.visibility = "visible"; //���X�|�b�v�A�b�v�\��
	}
}

//==============================================================
// gHidePopUp -- �v��|�b�v�A�b�v���\���ɂ���֐�
//==============================================================

function gHidePopUp(divID)
{
	var popOBJ = document.getElementById(divID);
	if (popOBJ) {
		popOBJ.style.visibility = "hidden"; //���X�|�b�v�A�b�v��\��
	}
}
