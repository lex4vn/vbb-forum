/*vumods.js [optional]
* this file is part of the VIETUNI typing tool 
* by Tran Anh Tuan [tuan@physik.hu-berlin.de]
* Copyright (c) 2001, 2002 AVYS e.V.. All Rights Reserved.
*/                                             

/*Note:
* this file provides interfaces to extended features of VietUni
* It must be loaded after (not before) vietuni.js
* All other script files will be loaded automatically when needed
*/

// modules: make sure the URIs are correct!
// The following settings have been made for off-line users.
// For web applications it is recommended to use full URI instead,
// e.g. SCRIPT_PATH= "http://www.mysite.net/js/";
//
if (typeof(SCRIPT_PATH)=='undefined') {
  SCRIPT_PATH = "js/";
}
var vuspellaURI= "vuspella.js";
var vuspellbURI= "vuspellb.js";
var vumapsURI  = "vumaps.js";
var vuspella = 0;
var vuspellb = 0;
var vumaps = 0;

function loadModule(mURI,idstr) {
  if (!document.all) { return alert(ermsg1); }
  var ls="&nbsp;<script defer type='text/javascript'"
  ls += "src='"+SCRIPT_PATH+mURI+"'></script>";
  document.body.insertAdjacentHTML('beforeEnd', ls);
  if (!eval(idstr)) alert( ermsg2);
}          
var ermsg1="Sorry, Browser cu?a ba.n kho^ng cho phe'p na.p the^m modul.";
var ermsg2="Module chu+a na.p xong, co' the^? ke^'t no^'i cha^.m...\n"+
"Ba.n ha~y thu+.c hie^.n thao ta'c mo^.t la^`n nu+~a!";

function updateArea(area, newvalue) {
  area.value= newvalue;
  if(area.onchange) area.onchange();
  if(!document.all) area.focus();
}

function parseMapID(param) {
  if (typeof(param)=='number') return (param+1);
  else if (/^\d+$/g.test(param)) return parseInt(param,10);
  else return param;
}


function convertAtOnce(txtarea) {
  if(!txtarea) return;
  if(!theTyper) theTyper = new CVietString("");
  if(theTyper.keymode.off) {
    var msg = "Bo^. go~ ddang o+? tra.ng tha'i ta('t.\n Ba.n pha?i ddu+a ve^` "+
    "kie^?u dda~ du`ng dde^? vie^'t ba`i truo+'c khi soa't da^'u";
    return alert(msg);      
  }
  updateArea(txtarea, theTyper.doConvertIt(txtarea.value));
}

function loadSpellA (lflag) {
  if (!lflag) return (vuspella= 0);
  if (theTyper && theTyper.checkSpell) return (vuspella=1);
  loadModule(vuspellaURI, "vuspella");
}

function loadSpellB (txtarea, search) {
  if (!txtarea) return;
  if(!vuspellb) {
     loadModule(vuspellbURI, "vuspellb");
     if(vuspellb) document.body.insertAdjacentHTML('beforeEnd', vuspellb);
  }
  if (!theTyper) return; else theTyper.txtarea = txtarea; 
  if (theTyper.theSChecker) theTyper.theSChecker.startCS(search);
}

function setCharMap(mapID) { 
  charmapid = parseMapID(mapID);
  if (!vumaps) loadModule(vumapsURI, "vumaps");
  if (theTyper) theTyper.charmap = initCharMap();
} 

function convertTo(txtarea, destID) {
  if (!txtarea) return 0;
  if (!vumaps) loadModule(vumapsURI, "vumaps");
  if (!vumaps) return 0;
  var srcmap = initCharMap();                        
  var destmap = initCharMap(parseMapID(destID));
  var txt= srcmap.convertTxtTo(txtarea.value, destmap);
  updateArea(txtarea, txt);
  return 1;
}

function detectMap(txtarea) { 
  if (!txtarea) return 0;
  if (!vumaps) loadModule(vumapsURI, "vumaps");
  if (!vumaps) return 0;      
  var cm = detectFormat(txtarea.value, 1);
  if (cm) setCharMap(cm-1);
  return cm;
} 

function convertArea(txtarea, toID) {
  if (!txtarea) return 0;
  var srcid = detectFormat(txtarea.value);
  if (!srcid) return 0;
  var srcmap = initCharMap(srcid);
  var destmap = initCharMap(parseMapID(toID));
  updateArea(txtarea, srcmap.convertTxtTo(txtarea.value,destmap));
  return 1;
}          

function autoConvert(form, toID) {
  if (!form || !vumaps) return;
  var objs = form.elements;
  for (var i=0; i<objs.length; i++) {    
    if(!objs[i].vietarea) continue;
    else convertArea(objs[i], toID);
  }
}

function correctArea(txtarea) {
  if (!txtarea) return;
  if (!vumaps) loadModule(vumapsURI, "vumaps");
  if (!vumaps) return 0;
  var txt= correctTxt(txtarea.value);
  if (txt) updateArea(txtarea, txt);
}

function correctTxt(text, map) {
  var id= map? map: detectFormat(text);
  if (!id) { id = "UNICODE"; }
  var txt= text.replace(/([:;,"!=>\)\]\}\-\.\?]+\s)/g, " :::$1")+' ';
  txt= initCharMap(id).convertTxtTo(txt, initCharMap("VIQR"));
  txt= txt.replace(/(\w)([\^\+\*\(])(\S+)(\s)/g, "$1$3$2$4");
  txt= txt.replace(/(\w)([\xB4\/'\u2019`\.\?~])(\S+)(\s)/g, "$1$3$2$4");
  if(!theTyper) theTyper = new CVietString("");
  theTyper.keymode = new CViqrKeys();
  txt = theTyper.doConvertIt(txt).replace(/ :::/g, "");
  theTyper.keymode = initKeys();
  return txt;
}

CVietString.prototype.doConvertIt = function(txt) {
  var i = 1, len = txt.length, line=1;
  this.value = txt.charAt(0);
  while (i < len) {
    this.ctrlchar = txt.charAt(i++);
    if (this.ctrlchar=='\x0D') window.status= "processing line "+ line++;
    this.changed = 0;
    this.keymode.getAction(this);
    if (!this.changed) this.value+= this.ctrlchar;
  }
  window.status= "";
  return this.value;
}
