/**
|*	Copyright (c) 2010-2011 Ideal Web Technologies
|*	This file is only to be used with the consent of Ideal Web Technologies 
|*	and may not be redistributed in whole or significant part!  By using
|*	this file, you agree to the Ideal Web Technologies' Terms of Service
|*	at www.idealwebtech.com/documents/tos.html
**/
(function(window,undefined){var document=window.document;var iwt=window.iwt;var idealChat_menuControls=function(){this.menus=new Array();this.openMenu=null;return this;};idealChat_menuControls.prototype.closeAll=function(){for(var i=0;i<this.menus.length;i++)
{iwt.hideElement(this.menus[i]+'_popup');iwt.removeClass(this.menus[i],'active');}
var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData&&cookieData.openMenu)
{delete(cookieData.openMenu);this.openMenu=null;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}};idealChat_menuControls.prototype.close=function(menu){iwt.hideElement(menu+'_popup');iwt.removeClass(menu,'active');var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData&&cookieData.openMenu)
{delete(cookieData.openMenu);this.openMenu=null;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}};idealChat_menuControls.prototype.open=function(menu){this.closeAll();iwt.showElement(menu+'_popup');iwt.addClass(menu,'active');var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData)
{cookieData={};}
cookieData.openMenu=menu;this.openMenu=menu;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));};idealChat_menuControls.prototype.toggle=function(menu){if(iwt.hasClass(menu,'active'))
{return this.close(menu);}
else
{return this.open(menu);}};idealChat_menuControls.prototype.create=function(menu){var link=iwt.getById(menu);var popup=iwt.getById(menu+'_popup');var tab=iwt.getById(menu+'_tab');var heading=iwt.getByClassName('heading',iwt.getById(menu));if(!link||!popup){return;}
iwt.hideElement(popup);iwt.addClass(popup,'popup');if(!tab)
{iwt.addEvent(link,"click",function(e){window.idealChat.menu.toggle(this.id);e.preventDefualt;});}
else
{iwt.addEvent(tab,"click",function(e){window.idealChat.menu.toggle(this.parentNode.id);e.preventDefualt;});}
if(heading)
{iwt.addEvent(heading,"click",function(e){window.idealChat.menu.toggle(this.parentNode.parentNode.id);e.preventDefualt;});}
this.menus.push(menu);return{tab:tab,link:link,popup:popup};};idealChat_menuControls.prototype.remove=function(menu){for(var i=0;i<this.menus.length;i++)
{if(this.menus[i]==menu)
{this.menus.splice(i,1);}}};var idealChat_scrollControls=function(){this.position=-1;this.tabWidth=0;this.openTabDifference=0;this.scrollTabWidth=0;this.rightBarEmptyWidth=0;this.barWidth=0;this.maxTabs=0;this.tabsToShow=0;this.elements=new Array();this.initalized=false;return this;};idealChat_scrollControls.prototype.init=function(){if(!this.initalized&&this.elements.length>0)
{iwt.addEvent('icbb_chat_scroll_left',"click",function(){window.idealChat.scroll.left();});iwt.addEvent('icbb_chat_scroll_right',"click",function(){window.idealChat.scroll.right();});if(iwt.hasClass(this.elements[0],'active'))
{this.openTabDifference=this.elements[0].offsetWidth;iwt.removeClass(this.elements[0],'active');this.tabWidth=this.elements[0].offsetWidth;iwt.addClass(this.elements[0],'active');}
else
{this.tabWidth=this.elements[0].offsetWidth;iwt.addClass(this.elements[0],'active');this.openTabDifference=this.elements[0].offsetWidth;iwt.removeClass(this.elements[0],'active');}
iwt.hideElement(this.elements[0]);this.rightBarEmptyWidth=iwt.getById('bottom_bar_right').offsetWidth;iwt.showElement(this.elements[0]);this.openTabDifference-=this.tabWidth;this.tabWidth+=2;iwt.showElement('icbb_chat_scroll_left');this.scrollTabWidth=iwt.getById('icbb_chat_scroll_left').offsetWidth;iwt.hideElement('icbb_chat_scroll_left');this.resize();iwt.addEvent(window,"resize",function(){window.idealChat.scroll.resize();});this.initalized=true;}};idealChat_scrollControls.prototype.resize=function(){this.barWidth=iwt.getById('bottom_bar').clientWidth-iwt.getById('bottom_bar_left').offsetWidth-this.rightBarEmptyWidth-this.openTabDifference;this.tabsToShow=Math.floor((this.barWidth-(this.scrollTabWidth*2))/this.tabWidth);this.maxTabs=Math.floor(this.barWidth/this.tabWidth);if(this.position-this.tabsToShow<-1)
{this.position=this.tabsToShow-1;}
this.update();};idealChat_scrollControls.prototype.elementOpened=function(element){this.elements=iwt.getByClassName('icbb_chat_tab',iwt.getById('bottom_bar_right'));for(var i=0;i<this.elements.length;i++)
{if(this.elements[i].id==element)
{this.position=i;break;}}
if(this.position-this.tabsToShow<-1)
{this.position=this.tabsToShow-1;}
this.update();};idealChat_scrollControls.prototype.elementAdded=function(){this.elements=iwt.getByClassName('icbb_chat_tab',iwt.getById('bottom_bar_right'));if(!this.initalized){this.init();}
if(this.elements.length>this.maxTabs)
{this.position=this.elements.length-1;}
this.update();};idealChat_scrollControls.prototype.elementRemoved=function(){this.elements=iwt.getByClassName('icbb_chat_tab',iwt.getById('bottom_bar_right'));if(this.position>=this.elements.length)
{this.position=this.elements.length-1;}
this.update();};idealChat_scrollControls.prototype.update=function(){if(this.elements.length>this.maxTabs)
{iwt.each(this.elements,function(i){iwt.hideElement(i);});for(var i=this.position;i>this.position-this.tabsToShow;i--)
{iwt.showElement(this.elements[i]);}
if(this.position!=this.elements.length-1)
{iwt.showElement('icbb_chat_scroll_left');}
else
{iwt.hideElement('icbb_chat_scroll_left');}
if(this.position!=this.tabsToShow-1)
{iwt.showElement('icbb_chat_scroll_right');}
else
{iwt.hideElement('icbb_chat_scroll_right');}
var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData)
{cookieData={};}
cookieData.scrollPos=this.position;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}
else if(this.position!=-1)
{this.position=-1;iwt.hideElement('icbb_chat_scroll_left');iwt.hideElement('icbb_chat_scroll_right');iwt.each(this.elements,function(i){iwt.showElement(i);});var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData&&cookieData.scrollPos)
{delete(cookieData.scrollPos);iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}}
this.updateNewFlags();};idealChat_scrollControls.prototype.updateNewFlags=function(){if(this.position!=-1)
{iwt.addClass(iwt.getByClassName('new_messages',iwt.getById('icbb_chat_scroll_right'))[0],'empty');for(var i=this.position-this.tabsToShow;i>=0;i--)
{if(!iwt.hasClass(iwt.getByClassName('new_messages',this.elements[i])[0],'empty'))
{iwt.removeClass(iwt.getByClassName('new_messages',iwt.getById('icbb_chat_scroll_right'))[0],'empty');break;}}
iwt.addClass(iwt.getByClassName('new_messages',iwt.getById('icbb_chat_scroll_left'))[0],'empty');for(var i=this.position+1;i<this.elements.length;i++)
{if(!iwt.hasClass(iwt.getByClassName('new_messages',this.elements[i])[0],'empty'))
{iwt.removeClass(iwt.getByClassName('new_messages',iwt.getById('icbb_chat_scroll_left'))[0],'empty');break;}}}};idealChat_scrollControls.prototype.left=function(){if(this.position!=-1&&this.position!=this.elements.length-1)
{this.position++;iwt.hideElement(this.elements[this.position-this.tabsToShow]);iwt.showElement(this.elements[this.position]);iwt.showElement('icbb_chat_scroll_right');if(this.position==this.elements.length-1)
{iwt.hideElement('icbb_chat_scroll_left');}
var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData)
{cookieData={};}
cookieData.scrollPos=this.position;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}
this.updateNewFlags();};idealChat_scrollControls.prototype.right=function(){if(this.position!=-1&&this.position!=this.tabsToShow-1)
{iwt.hideElement(this.elements[this.position]);iwt.showElement(this.elements[this.position-this.tabsToShow]);this.position--;iwt.showElement('icbb_chat_scroll_left');if(this.position==this.tabsToShow-1)
{iwt.hideElement('icbb_chat_scroll_right');}
var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData)
{cookieData={};}
cookieData.scrollPos=this.position;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}
this.updateNewFlags();};var idealChat=function(){this.menu=new idealChat_menuControls();this.scroll=new idealChat_scrollControls();this.ajaxPush=false;this.ajaxPushTimeout=300000;this.consecutiveFailedPushes=0;this.cookieInterval=null;this.openChats={};this.openChatRooms={};this.lastMessageId=-1;this.lastChatMessageId=-1;this.friendsOnline=0;this.documentTitle=document.title;this.titleInterval=null;this.soundLastPlayed=0;this.panicCount=0;this.popupHelp=null;this.phrases={};this.settings={};this.initialized=false;return this;};idealChat.prototype.init=function(){if(!this.initialized)
{if(!navigator.cookieEnabled)
{iwt.getById('bottom_bar_right').innerHTML=window.idealChat.inlineErrorWrapper.replace('{ERROR}',this.phrases.needcookies);iwt.hideElement('bottom_bar_left');return;}
iwt.showElement('bottom_bar');iwt.getByTagName('html')[0].style.paddingBottom=iwt.getById('bottom_bar').offsetHeight+'px';this.setupOnlineFriendsTab();this.setupU2UChatPopupLinks();this.setupChatRoomPopupLinks();if(iwt.getById('icbb_branding'))
{if(iwt.getByTagName('a',iwt.getById('icbb_branding'))[0])
{iwt.getByTagName('a',iwt.getById('icbb_branding'))[0].target="_blank";}}
iwt.addEvent(iwt.getByTagName('html')[0],"click",function(){if(window.idealChat.titleInterval)
{clearInterval(window.idealChat.titleInterval);window.idealChat.titleInterval=null;document.title=window.idealChat.documentTitle;}});iwt.addEvent('icbb_help',"click",function(){window.idealChat.help();});iwt.addEvent('icbb_settings',"click",function(){window.idealChat.editSettings();});iwt.addEvent('icbb_chatrooms',"click",function(){window.idealChat.showChatRoomList();});this.resize();iwt.addEvent(window,"resize",window.idealChat.resize);this.cookieInterval=setInterval("idealChat.updateFromCookie();",2500);var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData)
{if(cookieData.lastMessageId)
{this.lastMessageId=cookieData.lastMessageId;}
if(cookieData.lastChatMessageId)
{this.lastChatMessageId=cookieData.lastChatMessageId;}
if(cookieData.scrollPos)
{this.scroll.positionTemp=cookieData.scrollPos;}
var tran=null;if(cookieData.openChats||cookieData.openChatRooms)
{var userids=new Array();if(cookieData.openChats)
{for(var i in cookieData.openChats)
{if(parseInt(i)!=i){continue;}
userids.push(cookieData.openChats[i]);}}
userids=userids.join(',');tran=iwt.ajaxPOST("ideal_chat_ajax.php",{success:function(o){this.update(o);this.init_loadCookieData();this.openUpdateStream(false);this.initialized=true;},failure:function(o){this.ajaxFailure(o);this.init_loadCookieData();this.openUpdateStream(false);this.initialized=true;},scope:this},"do=openchats&userids="+userids+'&lastmsgid='+this.lastMessageId+'&lastchatmsgid='+this.lastChatMessageId);}
if(!tran)
{this.init_loadCookieData(cookieData);this.openUpdateStream(true);}}
else
{this.openUpdateStream(true);}
this.initialized=true;}
return;};idealChat.prototype.init_loadCookieData=function(cookieData){if(!cookieData)
{cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));}
if(cookieData)
{if(this.scroll.positionTemp)
{if(this.scroll.positionTemp<this.scroll.elements.length)
{this.scroll.position=this.scroll.positionTemp;this.scroll.update();}
delete this.scroll.positionTemp;}
else if(cookieData.scrollPos)
{this.scroll.position=cookieData.scrollPos;this.scroll.update();}
if(cookieData.openMenu)
{this.menu.open(cookieData.openMenu);if(cookieData.openMenu=='icbb_online_friends')
{iwt.ajaxPOST("ideal_chat_ajax.php",{success:this.openOnlineFriends_ajax,failure:this.ajaxFailure,scope:this},"do=openfriendslist");}
else if(iwt.getByClassName('messagearea',iwt.getById(cookieData.openMenu))[0])
{iwt.getByClassName('messagearea',iwt.getById(cookieData.openMenu))[0].scrollTop=iwt.getByClassName('messagearea',iwt.getById(cookieData.openMenu))[0].scrollHeight;}}}
return null;};idealChat.prototype.resize=function(){var popup=iwt.getById('icbb_online_friends_popup');if(popup)
{if(typeof iwt.windowHeight!=="number")
{iwt.setWindowSize();}
var friendsList=iwt.getById('icbb_ofp_friendscontainer');var staffList=iwt.getById('icbb_ofp_staffcontainer');var maxHeight=iwt.windowHeight-10;if(!friendsList)
{friendsList={offsetHeight:0};}
if(!staffList)
{staffList={offsetHeight:0};}
if(popup.style.display=='none')
{iwt.showElement(popup);maxHeight-=(popup.offsetHeight-friendsList.offsetHeight-staffList.offsetHeight);iwt.hideElement(popup);}
else
{maxHeight-=(popup.offsetHeight-friendsList.offsetHeight-staffList.offsetHeight);}
maxHeight+='px';if(friendsList.style)
{friendsList.style.maxHeight=maxHeight;}
if(staffList.style)
{staffList.style.maxHeight=maxHeight;}}};idealChat.prototype.updateFromCookie=function(){var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData||!cookieData.openChatRooms)
{for(var i in this.openChatRooms)
{this.closeChatRoom(i,true);}}
if(!cookieData||!cookieData.openChats)
{for(var i in this.openChats)
{this.closeU2UChat(i);}}
if(cookieData)
{for(var i in this.openChatRooms)
{if(cookieData.openChatRooms[i]!=i)
{this.closeChatRoom(i,true);}}
for(var i in this.openChats)
{if(cookieData.openChats[i]!=i)
{this.closeU2UChat(i);}}
for(var i in cookieData.openChatRooms)
{if(this.openChatRooms[i]!=i)
{this.openChatRoom(i,'',1);}}
for(var i in cookieData.openChats)
{if(this.openChats[i]!=i)
{this.openU2UChat(i);}}
if(cookieData.scrollPos!=this.scroll.position)
{this.scroll.position=cookieData.scrollPos;this.scroll.update();}
if(cookieData.openMenu!=this.menu.openMenu)
{this.menu.open(cookieData.openMenu);if(cookieData.openMenu=='icbb_online_friends')
{iwt.ajaxPOST("ideal_chat_ajax.php",{success:this.openOnlineFriends_ajax,failure:this.ajaxFailure,scope:this},"do=openfriendslist");}
else if(iwt.getByClassName('messagearea',iwt.getById(cookieData.openMenu))[0])
{iwt.getByClassName('messagearea',iwt.getById(cookieData.openMenu))[0].scrollTop=iwt.getByClassName('messagearea',iwt.getById(cookieData.openMenu))[0].scrollHeight;}}}};idealChat.prototype.openUpdateStream=function(firstPost){this.ajaxPush=iwt.ajaxPOST("ideal_chat_update_stream.php",{success:function(o){if(o&&o.responseXML&&o.responseXML.documentElement)
{this.update(o);}
this.consecutiveFailedPushes=0;this.openUpdateStream(false);},failure:function(o){if(o&&o.status&&o.status==-1)
{this.openUpdateStream(false);}
else
{this.consecutiveFailedPushes++;var timeout=3000;if(this.consecutiveFailedPushes>=3)
{timeout=60000;}
if(this.consecutiveFailedPushes<5)
{setTimeout("window.idealChat.openUpdateStream(false);",timeout);}
else
{iwt.getById('bottom_bar_right').innerHTML=window.idealChat.inlineErrorWrapper.replace('{ERROR}',this.phrases.pleasereload);}}},scope:this,timeout:(firstPost&&(typeof window.chrome==="object")?1:this.ajaxPushTimeout)},"lastmsgid="+this.lastMessageId+'&lastchatmsgid='+this.lastChatMessageId+'&friendsonline='+this.friendsOnline);};idealChat.prototype.update=function(ajax){if(!ajax||!ajax.responseXML)
{return this.ajaxFailure(ajax);}
var xml=ajax.responseXML.documentElement;if(xml.tagName!='ajaxresponse')
{return this.ajaxFailure(ajax);}
var tabUpdates=iwt.getByTagName('tabupdates',xml);var roomTabUpdates=iwt.getByTagName('roomtabupdates',xml);var lastmsgid=iwt.getByTagName('lastmsgid',xml)[0];var lastchatmsgid=iwt.getByTagName('lastchatmsgid',xml)[0];var friendsOnline=iwt.getByTagName('friendsonline',xml)[0];var friendsOnlineText=iwt.getByTagName('friendsonlinetext',xml)[0];var chatTabs=iwt.getByTagName('chattab',xml);var chatRoomTabs=iwt.getByTagName('chatroomtab',xml);var needOpened={users:new Array(),flashTabs:new Array()};if(lastmsgid||lastchatmsgid)
{var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData){cookieData={};}
if(lastmsgid)
{this.lastMessageId=iwt.getNodeValue(lastmsgid);cookieData.lastMessageId=this.lastMessageId;}
if(lastchatmsgid)
{this.lastChatMessageId=iwt.getNodeValue(lastchatmsgid);cookieData.lastChatMessageId=this.lastChatMessageId;}
iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}
for(var i=0;i<tabUpdates.length;i++)
{var userid=iwt.getNodeValue(iwt.getByTagName('userid',tabUpdates[i])[0]);var messages=iwt.getNodeValue(iwt.getByTagName('messages',tabUpdates[i])[0]);var flashTab=(iwt.getNodeValue(iwt.getByTagName('flashtab',tabUpdates[i])[0])==1);if(iwt.getById('icbb_user_chat_'+userid))
{var messageArea=iwt.getByClassName('messagearea',iwt.getById('icbb_user_chat_'+userid))[0];if(messageArea)
{var scrollArea=(messageArea.scrollTop+messageArea.offsetHeight>=messageArea.scrollHeight);messageArea.innerHTML+=messages;if(scrollArea)
{messageArea.scrollTop=messageArea.scrollHeight}
if(flashTab)
{this.newU2UMessage(userid);}}}
else
{needOpened.users.push(userid);if(flashTab){needOpened.flashTabs.push(userid);}}}
if(needOpened.users.length>0)
{iwt.ajaxPOST("ideal_chat_ajax.php",{success:function(o){var temp=this.scroll.initalized;if(temp)
{temp=this.scroll.position;if(temp==-1)
{if((this.scroll.elements.length+needOpened.users.length)>this.scroll.maxTabs)
{temp=this.scroll.tabsToShow-1;}
else
{temp=false;}}}
if(this.update(o))
{if(o&&o.argument&&o.argument.flashTabs)
{for(var i=0;i<o.argument.flashTabs.length;i++)
{this.newU2UMessage(o.argument.flashTabs[i]);}}
if(temp)
{this.scroll.position=temp;this.scroll.update();}}},failure:this.ajaxFailure,scope:this,argument:{flashTabs:needOpened.flashTabs}},"do=openchats&userids="+needOpened.users.join(',')+'&lastmsgid='+this.lastMessageId+'&lastchatmsgid='+this.lastChatMessageId+'skiprooms=1');}
for(var i=0;i<roomTabUpdates.length;i++)
{var roomid=iwt.getNodeValue(iwt.getByTagName('roomid',roomTabUpdates[i])[0]);var messages=iwt.getNodeValue(iwt.getByTagName('messages',roomTabUpdates[i])[0]);var flashTab=(iwt.getNodeValue(iwt.getByTagName('flashtab',roomTabUpdates[i])[0])==1);if(iwt.getById('icbb_chatroom_'+roomid))
{var messageArea=iwt.getByClassName('messagearea',iwt.getById('icbb_chatroom_'+roomid))[0];if(messageArea)
{var scrollArea=(messageArea.scrollTop+messageArea.offsetHeight>=messageArea.scrollHeight);messageArea.innerHTML+=messages;if(scrollArea)
{messageArea.scrollTop=messageArea.scrollHeight}
if(flashTab)
{this.newChatRoomMessage(roomid);}}}}
if(friendsOnline)
{this.friendsOnline=iwt.getNodeValue(friendsOnline);}
if(friendsOnlineText)
{if(iwt.getById('icbb_online_friends_tab')&&iwt.getByClassName('wrapper',iwt.getById('icbb_online_friends_tab'))[0])
{iwt.getByClassName('wrapper',iwt.getById('icbb_online_friends_tab'))[0].innerHTML=iwt.getNodeValue(friendsOnlineText);if(iwt.getById('icbb_online_friends_popup').style.display!='none')
{iwt.ajaxPOST("ideal_chat_ajax.php",{success:window.idealChat.openOnlineFriends_ajax,failure:window.idealChat.ajaxFailure,scope:window.idealChat},"do=openfriendslist");}}}
for(var i=0;i<chatTabs.length;i++)
{this.openU2UChat_ajax(iwt.getNodeValue(iwt.getByTagName('userid',chatTabs[i])[0]),iwt.getNodeValue(iwt.getByTagName('html',chatTabs[i])[0]));}
for(var i=0;i<chatRoomTabs.length;i++)
{this.openChatRoom_ajax(iwt.getNodeValue(iwt.getByTagName('roomid',chatRoomTabs[i])[0]),iwt.getNodeValue(iwt.getByTagName('html',chatRoomTabs[i])[0]));}
return true;};idealChat.prototype.ajaxFailure=function(ajax){if(ajax&&ajax.responseXML)
{var xml=ajax.responseXML.documentElement;if(xml.tagName=='error')
{alert("Error:\n\n"+iwt.getNodeValue(xml));}
else if(xml.tagName=='notice')
{alert(iwt.getNodeValue(xml));}}};idealChat.prototype.openU2UChat_ajax=function(userid,html){if(!iwt.getById('icbb_user_chat_'+userid))
{var chatTab=iwt.str2HTML(html)[0];iwt.each(iwt.getByClassName('close',chatTab),function(j){iwt.addEvent(j,"click",function(e){var tab=this;while(!iwt.hasClass(tab,'icbb_chat_tab'))
{tab=tab.parentNode;}
var userid=tab.id.slice(15);window.idealChat.closeU2UChat(userid);});});var messageForm=iwt.getByTagName('form',chatTab)[0];iwt.addEvent(messageForm,"submit",function(e){if(this.message.value&&!window.idealChat.parseSlashCommand(this.message.value))
{iwt.ajaxPOSTform(messageForm,{success:window.idealChat.ajaxFailure,failure:window.idealChat.ajaxFailure,scope:window.idealChat});if(window.idealChat.titleInterval)
{clearInterval(window.idealChat.titleInterval);window.idealChat.titleInterval=null;document.title=window.idealChat.documentTitle;}}
messageForm.reset();iwt.preventDefault(e);});iwt.getById('bottom_bar_right').insertBefore(chatTab,iwt.getById('icbb_chat_scroll_left'));var menu=this.menu.create('icbb_user_chat_'+userid);iwt.addEvent(menu.tab,"click",function(e){window.idealChat.scroll.elementOpened(this.id);iwt.getByTagName('form',this.parentNode)[0].message.focus();});iwt.addEvent(chatTab,"click",function(){var userid=this.id.slice(15);var messageCount=iwt.getByClassName('new_messages',this)[0];if(messageCount&&!iwt.hasClass(messageCount,'empty'))
{iwt.addClass(messageCount,'empty');}
iwt.getByClassName('messagearea',this)[0].scrollTop=iwt.getByClassName('messagearea',this)[0].scrollHeight;});this.scroll.elementAdded();var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData)
{cookieData={openChats:{}};}
else if(!cookieData.openChats)
{cookieData.openChats={};}
cookieData.openChats[userid]=userid;this.openChats[userid]=userid;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}};idealChat.prototype.openU2UChat=function(userid){if(!iwt.getById('icbb_user_chat_'+userid))
{iwt.ajaxPOST("ideal_chat_ajax.php",{success:function(o){if(this.update(o))
{var userid=iwt.getNodeValue(iwt.getByTagName('userid',iwt.getByTagName('chattab',o.responseXML.documentElement)[0])[0]);this.menu.open('icbb_user_chat_'+userid);iwt.getByClassName('messagearea',iwt.getById('icbb_user_chat_'+userid))[0].scrollTop=iwt.getByClassName('messagearea',iwt.getById('icbb_user_chat_'+userid))[0].scrollHeight;}},failure:this.ajaxFailure,scope:this},"do=openchat&userid="+userid);}
else
{this.scroll.elementOpened('icbb_user_chat_'+userid);this.menu.open('icbb_user_chat_'+userid);iwt.getByClassName('messagearea',iwt.getById('icbb_user_chat_'+userid))[0].scrollTop=iwt.getByClassName('messagearea',iwt.getById('icbb_user_chat_'+userid))[0].scrollHeight;}};idealChat.prototype.closeU2UChat=function(userid){if(iwt.getById('icbb_user_chat_'+userid))
{this.menu.remove('icbb_user_chat_'+userid);iwt.getById('bottom_bar_right').removeChild(iwt.getById('icbb_user_chat_'+userid));var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData&&cookieData.openChats)
{delete(cookieData.openChats[userid]);}
delete(this.openChats[userid]);iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));this.scroll.elementRemoved();}};idealChat.prototype.newU2UMessage=function(userid){var chatTab=iwt.getById('icbb_user_chat_'+userid);if(chatTab)
{if(this.settings.chatnotifs==2||(this.settings.chatnotifs==1&&!iwt.hasClass(chatTab,'active')))
{if(this.settings.chatsound!=''&&(iwt.timenow()-this.soundLastPlayed)>2)
{iwt.playSound('iwt/idealchat/audio/'+this.settings.chatsound);this.soundLastPlayed=iwt.timenow();}
if(!this.titleInterval)
{this.titleInterval=iwt.flashTitle(this.documentTitle,this.phrases.newmsg,2000);}}
if(!iwt.hasClass(chatTab,'active'))
{var messageCount=iwt.getByClassName('new_messages',chatTab)[0];if(messageCount)
{iwt.removeClass(messageCount,'empty');this.scroll.updateNewFlags();}}}
return;};idealChat.prototype.setupU2UChatPopupLinks=function(scope){var links=iwt.getByClassName('icbb_chat_popup',iwt.fetchElement(scope));for(var i=0;i<links.length;i++)
{iwt.addEvent(links[i],'click',function(e){var start=this.className.indexOf('icbb_uid_')+9;var end=this.className.indexOf(' ',start);if(end!=-1)
{var userid=this.className.slice(start,end);}
else
{var userid=this.className.slice(start);}
window.idealChat.openU2UChat(userid);iwt.preventDefault(e);});}};idealChat.prototype.openChatRoom_ajax=function(roomid,html){if(!iwt.getById('icbb_chatroom_'+roomid))
{var chatTab=iwt.str2HTML(html)[0];iwt.each(iwt.getByClassName('close',chatTab),function(j){iwt.addEvent(j,"click",function(e){var tab=this;while(!iwt.hasClass(tab,'icbb_chat_tab'))
{tab=tab.parentNode;}
var roomid=tab.id.slice(14);window.idealChat.closeChatRoom(roomid);});});var messageForm=iwt.getByTagName('form',chatTab)[0];iwt.addEvent(messageForm,"submit",function(e){if(this.message.value&&!window.idealChat.parseSlashCommand(this.message.value))
{iwt.ajaxPOSTform(messageForm,{success:window.idealChat.ajaxFailure,failure:window.idealChat.ajaxFailure,scope:window.idealChat});if(window.idealChat.titleInterval)
{clearInterval(window.idealChat.titleInterval);window.idealChat.titleInterval=null;document.title=window.idealChat.documentTitle;}}
messageForm.reset();iwt.preventDefault(e);});iwt.getById('bottom_bar_right').insertBefore(chatTab,iwt.getById('icbb_chat_scroll_left'));var menu=this.menu.create('icbb_chatroom_'+roomid);iwt.addEvent(menu.tab,"click",function(e){window.idealChat.scroll.elementOpened(this.id);iwt.getByTagName('form',this.parentNode)[0].message.focus();});iwt.addEvent(chatTab,"click",function(){var roomid=this.id.slice(14);var messageCount=iwt.getByClassName('new_messages',this)[0];if(messageCount&&!iwt.hasClass(messageCount,'empty'))
{iwt.addClass(messageCount,'empty');}
iwt.getByClassName('messagearea',this)[0].scrollTop=iwt.getByClassName('messagearea',this)[0].scrollHeight;});this.scroll.elementAdded();var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(!cookieData)
{cookieData={openChatRooms:{}};}
else if(!cookieData.openChatRooms)
{cookieData.openChatRooms={};}
cookieData.openChatRooms[roomid]=roomid;this.openChatRooms[roomid]=roomid;iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}};idealChat.prototype.openChatRoom=function(roomid,password,skipIfWrongPassword){if(window.iwt.openPopupOverlay&&window.iwt.openPopupOverlay.popup.id=='icbb_chatrooms_list_popup')
{new iwt.popup('icbb_chatrooms_list').close();}
if(!iwt.getById('icbb_chatroom_'+roomid))
{iwt.ajaxPOST("ideal_chat_ajax.php",{success:function(o){if(!o||!o.responseXML){return this.ajaxFailure(o);}
var password_protected=iwt.getByTagName('password_protected',o.responseXML.documentElement)[0];var rooms_disabled=iwt.getByTagName('rooms_disabled',o.responseXML.documentElement)[0];if(rooms_disabled&&iwt.getNodeValue(rooms_disabled)=='true')
{var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData&&cookieData.openChatRooms)
{cookieData.openChatRooms={};iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));}}
else if(password_protected&&iwt.getNodeValue(password_protected)=='true')
{var password=prompt(window.idealChat.phrases.providepassword);if(password!==null)
{var roomid=iwt.getNodeValue(iwt.getByTagName('roomid',o.responseXML.documentElement)[0]);window.idealChat.openChatRoom(roomid,password);}}
else
{if(this.update(o))
{var roomid=iwt.getNodeValue(iwt.getByTagName('roomid',iwt.getByTagName('chatroomtab',o.responseXML.documentElement)[0])[0]);this.menu.open('icbb_chatroom_'+roomid);iwt.getByClassName('messagearea',iwt.getById('icbb_chatroom_'+roomid))[0].scrollTop=iwt.getByClassName('messagearea',iwt.getById('icbb_chatroom_'+roomid))[0].scrollHeight;}}},failure:this.ajaxFailure,scope:this},"do=openchatroom&roomid="+roomid+'&password='+password+'&skipifwrongpassword='+skipIfWrongPassword);}
else
{this.scroll.elementOpened('icbb_chatroom_'+roomid);this.menu.open('icbb_chatroom_'+roomid);iwt.getByClassName('messagearea',iwt.getById('icbb_chatroom_'+roomid))[0].scrollTop=iwt.getByClassName('messagearea',iwt.getById('icbb_chatroom_'+roomid))[0].scrollHeight;}};idealChat.prototype.closeChatRoom=function(roomid,skipPost){if(iwt.getById('icbb_chatroom_'+roomid))
{if(!skipPost)
{iwt.ajaxPOST("ideal_chat_ajax.php",{},"do=closechatroom&roomid="+roomid);}
this.menu.remove('icbb_chatroom_'+roomid);iwt.getById('bottom_bar_right').removeChild(iwt.getById('icbb_chatroom_'+roomid));var cookieData=JSON.parse(iwt.getCookie('iwt_idealChatData'));if(cookieData&&cookieData.openChatRooms)
{delete(cookieData.openChatRooms[roomid]);}
delete(this.openChatRooms[roomid])
iwt.setCookie('iwt_idealChatData',JSON.stringify(cookieData));this.scroll.elementRemoved();}};idealChat.prototype.newChatRoomMessage=function(roomid){var chatTab=iwt.getById('icbb_chatroom_'+roomid);if(chatTab)
{if(this.settings.chatnotifs==2||(this.settings.chatnotifs==1&&!iwt.hasClass(chatTab,'active')))
{if(this.settings.chatsound!=''&&(iwt.timenow()-this.soundLastPlayed)>2)
{iwt.playSound('iwt/idealchat/audio/'+this.settings.chatsound);this.soundLastPlayed=iwt.timenow();}
if(!this.titleInterval)
{this.titleInterval=iwt.flashTitle(this.documentTitle,this.phrases.newmsg,2000);}}
if(!iwt.hasClass(chatTab,'active'))
{var messageCount=iwt.getByClassName('new_messages',chatTab)[0];if(messageCount)
{iwt.removeClass(messageCount,'empty');this.scroll.updateNewFlags();}}}
return;};idealChat.prototype.setupChatRoomPopupLinks=function(scope){var links=iwt.getByClassName('icbb_chatroom_popup',iwt.fetchElement(scope));for(var i=0;i<links.length;i++)
{iwt.addEvent(links[i],'click',function(e){var start=this.className.indexOf('icbb_room_')+10;var end=this.className.indexOf(' ',start);if(end!=-1)
{var roomid=this.className.slice(start,end);}
else
{var roomid=this.className.slice(start);}
window.idealChat.openChatRoom(roomid);iwt.preventDefault(e);});}};idealChat.prototype.openOnlineFriends_ajax=function(ajax){if(!ajax||!ajax.responseXML)
{return this.ajaxFailure(ajax);}
var xml=ajax.responseXML.documentElement;if(xml.tagName!='ajaxresponse')
{return this.ajaxFailure(ajax);}
var friendsList=iwt.getByTagName('friendlist',iwt.getByTagName('userlists',xml)[0])[0];var staffList=iwt.getByTagName('stafflist',iwt.getByTagName('userlists',xml)[0])[0];if(friendsList&&iwt.getById('icbb_ofp_friendscontainer'))
{iwt.getById('icbb_ofp_friendscontainer').innerHTML=iwt.getNodeValue(friendsList);this.setupU2UChatPopupLinks('icbb_ofp_friendscontainer');}
if(staffList&&iwt.getById('icbb_ofp_staffcontainer'))
{iwt.getById('icbb_ofp_staffcontainer').innerHTML=iwt.getNodeValue(staffList);this.setupU2UChatPopupLinks('icbb_ofp_staffcontainer');}};idealChat.prototype.setupOnlineFriendsTab=function(){var menu=this.menu.create('icbb_online_friends');iwt.addEvent(menu.tab,"click",function(e){iwt.ajaxPOST("ideal_chat_ajax.php",{success:window.idealChat.openOnlineFriends_ajax,failure:window.idealChat.ajaxFailure,scope:window.idealChat},"do=openfriendslist");});iwt.addEvent('icbb_ofp_friendslink','click',function(){iwt.removeClass('icbb_ofp_stafflink','active');iwt.hideElement('icbb_ofp_staffcontainer');iwt.hideElement('icbb_ofp_staffheading');iwt.addClass('icbb_ofp_friendslink','active');iwt.showElement('icbb_ofp_friendscontainer');iwt.showElement('icbb_ofp_friendsheading');});iwt.addEvent('icbb_ofp_stafflink','click',function(){iwt.removeClass('icbb_ofp_friendslink','active');iwt.hideElement('icbb_ofp_friendscontainer');iwt.hideElement('icbb_ofp_friendsheading');iwt.addClass('icbb_ofp_stafflink','active');iwt.showElement('icbb_ofp_staffcontainer');iwt.showElement('icbb_ofp_staffheading');});};idealChat.prototype.parseSlashCommand=function(message){if(message.slice(0,1)=='/')
{var slashCommand=message.slice(1);switch(slashCommand)
{case'iwt':iwt.newWindow('http://idealwebtech.com');return true;case'about':case'credit':case'credits':this.about();return true;case'help':this.help();return true;case'settings':this.editSettings();return true;case'panic':this.panic();return false;}}
return false;};idealChat.prototype.about=function(){alert("Powered By Ideal Chat\n\n"+"Ideal Web Technologies\n"+"http://www.idealwebtech.com\n\n"+"Copyright (c) 2010-2011 Ideal Web Technologies");};idealChat.prototype.showChatRoomList=function(){iwt.ajaxPOST('ideal_chat_ajax.php',{success:function(o)
{new iwt.popup('icbb_chatrooms_list',iwt.getNodeValue(iwt.getByTagName('roomlistshtml',o.responseXML.documentElement)[0])).show();window.idealChat.setupChatRoomPopupLinks(iwt.getById('icbb_chatrooms_list_popup'));},failure:function(o)
{alert(window.idealChat.phrases.roomslistfailed);}},'do=openroomlist');};idealChat.prototype.help=function(){if(this.popupHelp)
{this.popupHelp.show();}
else
{iwt.ajaxPOST('ideal_chat_ajax.php',{success:function(o)
{window.idealChat.popupHelp=new iwt.popup('icbb_help',iwt.getNodeValue(iwt.getByTagName('helphtml',o.responseXML.documentElement)[0])).show();},failure:function(o)
{alert(window.idealChat.phrases.helpfailed);}},'do=showhelp');}};idealChat.prototype.editSettings=function(){iwt.ajaxPOST('ideal_chat_ajax.php',{success:function(o)
{new iwt.popup('icbb_settings',iwt.getNodeValue(iwt.getByTagName('settingshtml',o.responseXML.documentElement)[0])).show();},failure:function(o)
{alert(window.idealChat.phrases.settingsfailed);}},'do=showsettings');};idealChat.prototype.saveSettings=function(){iwt.ajaxPOSTform(iwt.getById('icbb_settings_form'),{success:function(o)
{iwt.getById('icbb_settings_inner').innerHTML=iwt.getNodeValue(iwt.getByTagName('settingshtml',o.responseXML.documentElement)[0]);window.iwt.each(window.iwt.getByClassName('close_popup',iwt.getById('icbb_settings_inner')),function(i){window.iwt.addEvent(i,"click",function(e){if(window.iwt.openPopupOverlay)
{window.iwt.openPopupOverlay.close();}});});this.settings=JSON.parse(iwt.getNodeValue(iwt.getByTagName('settingsjson',o.responseXML.documentElement)[0]));},failure:this.ajaxFailure,scope:this});return false;};idealChat.prototype.panic=function(){this.panicCount++;if(this.panicCount==5)
{alert(this.phrases.calmdown);}
else if(this.panicCount==10)
{alert(this.phrases.calmdownalready);}};window.idealChat=new idealChat();iwt.addEvent(window,"load",function(){if(!iwt.isMobileBrowser)
{window.idealChat.init();}});})(window);