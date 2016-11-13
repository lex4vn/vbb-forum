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
vB_XHTML_Ready.subscribe(init_posticons);function init_posticons(){var C=YAHOO.util.Dom.get("posticons");var A=YAHOO.util.Dom.get("posticon_preview");var B=new Array();if(C&&A){containers=YAHOO.util.Dom.get("posticons").getElementsByTagName("li");for(i=0;i<containers.length;i++){B[i]=new PostIconPreview(containers[i],A);if(B[i].control.checked){B[i].choose()}}}}function PostIconPreview(B,A){this.posticon_preview=A;this.control=B.getElementsByTagName("input")[0];this.icon=B.getElementsByTagName("img")[0];YAHOO.util.Event.on(this.control,"click",this.choose,this,true)}PostIconPreview.prototype.choose=function(A){this.clear_preview();this.set_preview()};PostIconPreview.prototype.clear_preview=function(A){this.posticon_preview.src="images/clear.gif"};PostIconPreview.prototype.set_preview=function(A){if(this.control.value!=0){this.posticon_preview.src="images/icons/icon"+this.control.value+".gif"}};vB_XHTML_Ready.subscribe(function(A){return handle_dep(document.body)});var DepCtrls=new Object();function handle_dep(B){var A=YAHOO.util.Dom.getElementsByClassName("dep_ctrl","input",B);var D=null;for(var C=0;C<A.length;C++){D=new DepCtrl(A[C]);if(!D.fail){console.log("Dep Ctrl: %s",A[C].id);DepCtrls[A[C].id]=D}}}function DepCtrl(C){this.ctrl=YAHOO.util.Dom.get(C);if(!this.ctrl){console.log("Dep Ctrl (ctrl) FAIL: %s",C.id);this.fail=true;return false}this.deps=YAHOO.util.Dom.get(C.id+"_deps");if(!this.deps){console.log("Dep Ctrl (deps_x) FAIL: %s",C.id);this.fail=true;return false}this.set_disabled_state(this.deps,true);if(this.ctrl.type=="checkbox"){console.log("Checkbox %s",this.ctrl.id);this.add_click_event(this.ctrl)}else{if(this.ctrl.type=="radio"){console.log("Radio %s (%s)",this.ctrl.id,this.ctrl.name);var A=document.getElementsByName(this.ctrl.name);for(var B=0;B<A.length;B++){this.add_click_event(A[B])}}}}DepCtrl.prototype.add_click_event=function(A){YAHOO.util.Event.on(A,"click",this.check_state,this,true)};DepCtrl.prototype.check_state=function(A){this.set_disabled_state(this.deps,true);this.set_focus()};DepCtrl.prototype.set_disabled_state=function(B,C){if(B.tagName&&(B.tagName!="DD"||C)){B.disabled=!this.ctrl.checked||this.ctrl.disabled;if(B.tagName=="INPUT"&&YAHOO.util.Dom.hasClass(B,"dep_ctrl")&&DepCtrls[B.id]){DepCtrls[B.id].set_disabled_state(DepCtrls[B.id].deps,true)}if(B.hasChildNodes()){for(var A=0;A<B.childNodes.length;A++){this.set_disabled_state(B.childNodes[A])}}}};DepCtrl.prototype.is_form_element=function(A){switch(A.tagName){case"INPUT":case"SELECT":case"TEXTAREA":return true;default:return false}};DepCtrl.prototype.set_focus=function(B){var A=YAHOO.util.Dom.getElementsBy(this.is_form_element,"*",this.deps);try{try{A[0].focus()}catch(B){A[0].focus()}}catch(B){}};