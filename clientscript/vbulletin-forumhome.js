/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.9
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
function toggle_forum_descriptions(){var C=(YAHOO.util.Dom.get("show_descriptions").checked?"":"none");var A=YAHOO.util.Dom.getElementsByClassName("forumdescription","p");for(var B=0;B<A.length;B++){YAHOO.util.Dom.setStyle(A[B],"display",C)}}function toggle_sub_forums(){var A=(YAHOO.util.Dom.get("show_subforums").checked?"":"none");var B=YAHOO.util.Dom.getElementsByClassName("subforums","div");for(var C=0;C<B.length;C++){YAHOO.util.Dom.setStyle(B[C],"display",A)}}function init_collapsers(){var A=YAHOO.util.Dom.getElementsByClassName("collapsegadget","a","forums");for(var B=0;B<A.length;B++){YAHOO.util.Event.on(A[B],"click",toggle_collapse)}}function toggle_collapse(C){YAHOO.util.Event.stopEvent(C);var B=this;while(B.tagName!="LI"&&B.tagName!="HTML"){B=B.parentNode}if(B.tagName=="LI"){var A=YAHOO.util.Dom.getElementsByClassName("subforums","ol",B)[0];if(A.style.display==""){A.style.display="none";YAHOO.util.Dom.setStyle(this,"backgroundImage","url(../images/collapse-expand.png)")}else{A.style.display="";YAHOO.util.Dom.setStyle(this,"backgroundImage","url(../images/collapse-collapse.png)")}}}vB_XHTML_Ready.subscribe(toggle_forum_descriptions);YAHOO.util.Event.on("show_descriptions","click",toggle_forum_descriptions);YAHOO.util.Event.on("show_subforums","click",toggle_sub_forums);vB_XHTML_Ready.subscribe(init_collapsers);