// {{{ ic2_count()

/**
 * �摜������ݒ肷��
 *
 * @param {String} key          �������擾���郁���l(rawurlencode�ς�)
 * @param {String|Array} elem   ������ݒ肷��v�f��
 */
function ic2_setcount(key, elem)
{
    var ic2_getcount = function (key) {
        var url, req, res, err;

        req = getXmlHttp();
        if (!req) {
            return null;
        }

        url = 'ic2_getcount.php?key=' + key;
        try {
            res = getResponseTextHttp(req, url, 'nc');
        } catch (err) {
            return null;
        }

        return res;
    }(key);

    if (elem.constructor === Array) {
        for (var i = 0; i < elem.length; i++) {
            var e = document.getElementById(elem[i]);
            if (e) e.innerHTML = '(' + ic2_getcount + ')';
        }
    } else {
        var e = document.getElementById(elem);
        if (e) e.innerHTML = '(' + ic2_getcount + ')';
    }
}

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
