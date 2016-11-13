var androidBranded = false;
var iphoneBranded = false;
var forumName = 'PKL NOI NHIP DAM ME';

function
setCookie (cookieName, cookieValue, expireDays)
{
    var expires = new Date()
    expires.setDate(expires.getDate() + expireDays);
    var val = escape(cookieValue) + ((expires == null) ? '' : ';expires=' + expires.toUTCString());
    document.cookie = cookieName + '=' + val;
}

function
getCookie (cookieName)
{
    var i, x, y, cookiesArray = document.cookie.split(';');
    for (i = 0; i < cookiesArray.length; i++) {
        x = cookiesArray[i].substr(0, cookiesArray[i].indexOf('='));
        y = cookiesArray[i].substr(cookiesArray[i].indexOf('=') + 1);
        x = x.replace(/^\s+|\s+$/g, '');
        if (x == cookieName) {
            return unescape(y);
        }
    }
    return null;
}

function
needForumRunnerPrompt ()
{
    var firstPopup = getCookie('frfdate');
    if (firstPopup == null) {
        var d = new Date();
        setCookie('frfdate', d.toGMTString());
        return true;
    } else {
        var d = new Date(getCookie('frfdate'));
        var now = new Date();
        if (((now - d) / 1000) > 60 * 60 * 24) {
            setCookie('skip_fr_detect', 'true', 9000);
            return true;
        }
    }
    return false;
}

function 
iOSVersion ()
{
    if (/iP(hone|od|ad)/.test(navigator.platform)) {
        var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
        return [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)];
    }
}

function
forumRunnerPrompt (type, opera)
{
    var operaMsg;
    var safariMsg;

    if (type == 'iPad' || type == 'iPhone') {
        // If we are on iOS 6 or later, send the meta tag.
        var ver = iOSVersion();
        if (ver[0] >= 6) {
            var meta = document.createElement('meta');
            meta.name = 'apple-itunes-app';
            meta.content = 'app-id=362527234';
            document.getElementsByTagName('head')[0].appendChild(meta);
            return;
        }

        if (iphoneBranded && forumName != '') {
            operaMsg = 'Get our ' + type + ' app for easier viewing and posting on this forum!  Search for "' + forumName + '" in the App Store.';
            safariMsg = 'Get our ' + type + ' app for easier viewing and posting on this forum, optional push notifications and more!';

        } else {
            operaMsg = 'Get our ' + type + ' app for easier viewing and posting on this forum!  Search for "Forum Runner" in the App Store.';
            safariMsg = 'Get our ' + type + ' app for easier viewing and posting on this forum, optional push notifications and more!';
        }
    } else if (type == 'Android') {
        if (androidBranded && forumName != '') {
            safariMsg = 'Get our Android app for easier viewing and posting on this forum, optional push notifications and more!';
            operaMsg = 'Get our Android app for easier viewing and posting on this forum!  Search for "' + forumName + '" in the Market.  Reload this page to load the normal website.';
        } else {
            safariMsg = 'Get our Android app for easier viewing and posting on this forum, optional push notifications and more!';
            operaMsg = 'Get our Android app for easier viewing and posting on this forum!  Search for "Forum Runner" in the Market.  Reload the page to load the normal website.';
        }
    }

    if (opera) {
        setCookie('skip_fr_detect', 'true', 9000);
	alert(operaMsg);
	return;
    }

    if (needForumRunnerPrompt() && confirm(safariMsg)) {
        setCookie('skip_fr_detect', 'true', 9000);
        if (type == 'iPad' || type == 'iPhone') {
            window.location = 'http://itunes.apple.com/us/app/forum-runner-vbulletin/id362527234?mt=8';
        } else if (type == 'Android') {
            window.location = 'market://details?id=net.endoftime.android.forumrunner';
        }
    }
}

function
forumRunnerDetect ()
{
    if (getCookie('skip_fr_detect') == null) {
	var agent = navigator.userAgent.toLowerCase();
	var type;
	var opera = (agent.indexOf('opera') != -1);

	if (agent.indexOf('iphone') != -1) {
	    type = 'iPhone';
	} else if (agent.indexOf('ipod') != -1) {
	    type = 'iPod Touch';
	} else if (agent.indexOf('ipad') != -1) {
	    type = 'iPad';
	} else if (agent.indexOf('android') != -1) {
            type = 'Android';
	} else {
	    return;
	}
        forumRunnerPrompt(type, opera);
    }
}

forumRunnerDetect();
