/*vumaps2.js [optional]
* this file is part of the VIETUNI typing tool 
* by Tran Anh Tuan [tuan@physik.hu-berlin.de]
* Copyright (c) 2001, 2002 AVYS e.V.. All Rights Reserved.
*/

/* This file demonstrate how to add more encoding table to VietUni
* All you have to do is to define the table and register it.
*
* map.vietchars: array defining the encoding scheme
*  - The first element is the name of the charset.
*    The charset can be refered to later by its name: setCharMap("VietWare-F");
*  - each VietChar (dd, vowels, vowels+tonemark) has a defined index in the table
*    (see CVietViqrMap for mapping info)
*  - The value of each array element is the code of the VietChar asigned to 
*    this element by the related charset. "Code" can be expressed using
*    integer (for single-code charset) or "string" (for multi-byte charset)
*             
* map.pattern: code for checking patterns which only appear in texts using this charset.
* Optional: If available the charset can be auto detected
*/

if (typeof(maps) != 'undefined') { 
  maps.register("CVietWareFMap()");
  maps.register("CVietWareXMap()");
  maps.register("CVietBKHCM1Map()");
  maps.register("CVietBKHCM2Map()");
  maps.register("CVietVnuMap()");
}

function CVietWareFMap() { var map = new CVietCharMap();
map.vietchars = new Array(
 "VietWare-F",
 97, 161, 159, 101, 163, 105, 111, 164, 165, 117, 167, 121,
 65, 129, 127, 69, 131, 73, 79, 132, 133, 85, 135, 89,
 192, 202, 197, 207, 213, 219, 226, 231, 236, 242, 247, 252, 
 160, 0, 0, 175, 181, 187, 0, 0, 0, 0, 215, 0, 
 170, 199, 194, 204, 210, 216, 223, 228, 233, 238, 244, 249, 
 138, 0, 0, 172, 178, 184, 191, 0, 0, 0, 0, 0, 
 193, 203, 198, 209, 214, 220, 227, 232, 237, 243, 248, 255, 
 0, 171, 166, 177, 0, 188, 0, 0, 0, 0, 0, 0, 
 182, 200, 195, 205, 211, 217, 224, 229, 234, 239, 245, 250, 
 150, 168, 0, 173, 179, 185, 0, 0, 0, 0, 0, 0, 
 186, 201, 196, 206, 212, 218, 225, 230, 235, 241, 246, 251, 
 154, 169, 0, 174, 180, 0, 0, 0, 0, 0, 0, 0,
 100, 162, 68, 152);

 map.pattern=
"\x5c\x77\xa7[\xa5\xec\xe9\xed\xea\xeb]|\xa2[\xc0\xaa\xc1\xb6\xba\xca"+
"\xdb\xc2\xc6\xc3\xc4\xcc\xd1\xcd\xce\xa3\xd5\xd2\xd6\xd3\xd4\xdb\xd8"+
"\xdc\xd9\xda\xe2\xdf\xe3\xe0\xe1\xa4\xe7\xe4\xe8\xe5\xe6\xa5\xec\xe9"+
"\xed\xea\xeb\xf2\xee\xf3\xef\xf1\xf7\xf4\xf8\xf5\x5d";
 return map;
}


function CVietWareXMap() { var map = new CVietMultibyteMap();
map.maxchrlen = 2;
map.vietchars = new Array(
 "VietWare-X",
 "a","\xe1","\xe0","e","\xe3","i","o","\xe4","\xe5","u","\xe6","y",
 "A","\xc1","\xc0","E","\xc3","I","O","\xc4","\xc5","U","\xc6","Y",
 "a\xef","\xe1\xfa","\xe0\xf5","e\xef","\xe3\xfa","\xea","o\xef","\xe4\xfa","\xe5\xef","u\xef","\xe6\xef","y\xef",
 "A\xcf","\xc1\xda","\xc0\xd5","E\xcf","\xc3\xda","\xca","O\xcf","\xc4\xda","\xc5\xcf","U\xcf","\xc6\xcf","Y\xcf",
 "a\xec","\xe1\xf6","\xe0\xf2","e\xec","\xe3\xf6","\xe7","o\xec","\xe4\xf6","\xe5\xec","u\xec","\xe6\xec","y\xec",
 "A\xcc","\xc1\xd6","\xc0\xd2","E\xcc","\xc3\xd6","\xc7","O\xcc","\xc4\xd6","\xc5\xcc","U\xcc","\xc6\xcc","Y\xcc",
 "a\xfb","\xe1\xfb","\xe0\xfb","e\xfb","\xe3\xfb","\xeb","o\xfc","\xe4\xfc","\xe5\xfc","u\xfb","\xe6\xfb","y\xf1",
 "A\xdb","\xc1\xdb","\xc0\xdb","E\xdb","\xc3\xdb","\xcb","O\xdc","\xc4\xdc","\xc5\xdc","U\xdb","\xc6\xdb","Y\xd1",
 "a\xed","\xe1\xf8","\xe0\xf3","e\xed","\xe3\xf8","\xe8","o\xed","\xe4\xf8","\xe5\xed","u\xed","\xe6\xed","y\xed",
 "A\xcd","\xc1\xd8","\xc0\xd3","E\xcd","\xc3\xd8","\xc8","O\xcd","\xc4\xd8","\xc5\xcd","U\xcd","\xc6\xcd","Y\xcd",
 "a\xee","\xe1\xf9","\xe0\xf4","e\xee","\xe3\xf9","\xe9","o\xee","\xe4\xf9","\xe5\xee","u\xee","\xe6\xee","y\xee",
 "A\xce","\xc1\xd9","\xc0\xd4","E\xce","\xc3\xd9","\xc9","O\xce","\xc4\xd9","\xc5\xce","U\xce","\xc6\xce","Y\xce",
 "d","\xe2","D","\xc2");

 map.pattern=
"\x5b\xe1\xe3\xe4\x5d\x5b\xfa\xf6\xfb\xf8\xf9\x5d\x7c\xe0\x5b\xf5\xf2"+
"\xfb\xf3\xf4\x5d\x7c\x5b\xe5\xe6\x5d\x5b\xef\xec\xfc\xed\xee\x5d";
 return map;
}


function CVietBKHCM1Map() { var map = new CVietCharMap();
map.vietchars = new Array(
 "B.K. HCM 1",
 97, 221, 215, 101, 227, 105, 111, 233, 239, 117, 245, 121,
 65, 159, 153, 69, 165, 73, 79, 171, 177, 85, 183, 89,
 190, 222, 216, 195, 228, 200, 205, 234, 240, 210, 246, 251, 
 128, 126, 154, 133, 166, 138, 143, 172, 178, 148, 184, 123, 
 191, 223, 217, 196, 229, 201, 206, 235, 241, 211, 247, 252, 
 129, 161, 155, 134, 167, 139, 144, 173, 179, 149, 185, 94, 
 194, 226, 220, 199, 232, 204, 209, 238, 244, 214, 250, 255, 
 132, 164, 158, 137, 170, 142, 147, 176, 182, 152, 188, 0, 
 192, 224, 218, 197, 230, 202, 207, 236, 242, 212, 248, 253, 
 130, 162, 156, 135, 168, 140, 145, 174, 180, 150, 186, 96, 
 193, 225, 219, 198, 231, 203, 208, 237, 243, 213, 249, 254, 
 131, 163, 157, 136, 169, 141, 146, 175, 181, 151, 187, 124,
 100, 189, 68, 125);

 map.pattern=
"\x5c\x77\xf5\x5b\xef\xf0\xf1\xf4\xf2\xf3\x5d\x7c\x5c\x73\xbd\x5b\xda"+
"\xdb\xc3\xc4\xc7\xc8\xc9\xca\xd1\xd0\xed\xf4\xf3\xd2\xd3\xd4\xd5\x5d";
 return map;
}


function CVietBKHCM2Map() { var map = new CVietMultibyteMap();
map.maxchrlen = 2;
map.vietchars = new Array(
 "B.K. HCM 2",
 "a","\xea","\xf9","e","\xef","i","o","\xf6","\xfa","u","\xfb","y",
 "A","\xca","\xd9","E","\xcf","I","O","\xd6","\xda","U","\xdb","Y",
 "a\xe1","\xea\xeb","\xf9\xe6","e\xe1","\xef\xeb","\xf1","o\xe1","\xf6\xeb","\xfa\xe1","u\xe1","\xfb\xe1","y\xe1", 
 "A\xc1","\xca\xcb","\xd9\xc6","E\xc1","\xcf\xcb","\xd1","O\xc1","\xd6\xcb","\xda\xc1","U\xc1","\xdb\xc1","Y\xc1", 
 "a\xe2","\xea\xec","\xf9\xe7","e\xe2","\xef\xec","\xf2","o\xe2","\xf6\xec","\xfa\xe2","u\xe2","\xfb\xe2","y\xe2", 
 "A\xc2","\xca\xcc","\xd9\xc7","E\xc2","\xcf\xcc","\xd2","O\xc2","\xd6\xcc","\xda\xc2","U\xc2","\xdb\xc2","Y\xc2", 
 "a\xe5","\xea\xe5","\xf9\xe5","e\xe5","\xef\xe5","\xf5","o\xe5","\xf6\xe5","\xfa\xe5","u\xe5","\xfb\xe5","y\xe5", 
 "A\xc5","\xca\xc5","\xd9\xc5","E\xc5","\xcf\xc5","\xd5","O\xc5","\xd6\xc5","\xda\xc5","U\xc5","\xdb\xc5","Y\xc5", 
 "a\xe3","\xea\xed","\xf9\xe8","e\xe3","\xef\xed","\xf3","o\xe3","\xf6\xed","\xfa\xe3","u\xe3","\xfb\xe3","y\xe3", 
 "A\xc3","\xca\xcd","\xd9\xc8","E\xc3","\xcf\xcd","\xd3","O\xc3","\xd6\xcd","\xda\xc3","U\xc3","\xdb\xc3","Y\xc3", 
 "a\xe4","\xea\xee","\xf9\xe9","e\xe4","\xef\xee","\xf4","o\xe4","\xf6\xee","\xfa\xe4","u\xe4","\xfb\xe4","y\xe4", 
 "A\xc4","\xca\xce","\xd9\xc9","E\xc4","\xcf\xce","\xd4","O\xc4","\xd6\xce","\xda\xc4","U\xc4","\xdb\xc4","Y\xc4",
 "d","\xe0","D","\xc0");

 map.pattern=
"\x5c\x77\x5b\xea\xf6\xef\x5d\x5b\xeb\xec\xe5\xed\xee\x5d\x7c\xfa\xfb"+
"\x5b\xe1\xe2\xe5\xe3\xe4\x5d\x7c\xf9\x5b\xe6\xe7\xe5\xe8\xe9\x5d";
 return map;
}


function CVietVnuMap() { var map = new CVietCharMap();
map.vietchars = new Array(
 "VNU",
 97, 181, 175, 101, 197, 105, 111, 225, 231, 117, 245, 121,
 65, 149, 143, 69, 165, 73, 79, 193, 199, 85, 213, 89,
 159, 182, 176, 190, 198, 207, 220, 226, 232, 238, 246, 251, 
 127, 150, 144, 158, 166, 0, 188, 0, 200, 0, 0, 219, 
 161, 183, 177, 191, 203, 214, 221, 227, 233, 239, 247, 252, 
 129, 151, 145, 0, 171, 0, 0, 195, 201, 0, 0, 0, 
 173, 186, 180, 194, 206, 217, 224, 230, 236, 242, 250, 255, 
 141, 154, 148, 162, 174, 0, 0, 0, 0, 210, 218, 0, 
 168, 184, 178, 192, 204, 215, 222, 228, 234, 240, 248, 253, 
 136, 152, 146, 160, 0, 0, 0, 196, 202, 208, 0, 0, 
 172, 185, 179, 193, 205, 216, 223, 229, 235, 241, 249, 254, 
 140, 153, 147, 0, 0, 0, 0, 0, 0, 209, 0, 0,
 100, 189, 68, 135);

 map.pattern=
"\x5c\x77\xf5\x5b\xe7\xe8\xe9\xec\xea\xeb\x5d\x7c\x5c\x73\xbd\x5b\x3f"+
"\xa1\xad\xa8\xac\xb5\xb6\xb7\xba\xb8\xb9\xaf\xb0\xb1\xb4\xb2\xb3\x5d";
 return map;
}

