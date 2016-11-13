<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.9 - Free Licence
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

// display the credits table for use in admin/mod control panels

print_form_header('index', 'home');
print_table_header($vbphrase['vbulletin_developers_and_contributors']);
print_column_style_code(array('white-space: nowrap', ''));
print_label_row('<b>' . $vbphrase['software_developed_by'] . '</b>', '
	vBulletin Solutions, Inc.,
	Internet Brands, Inc.
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['business_product_development'] . '</b>', '
	Alan Chiu,
	Michael Anders,
	Neal Sainani,
	Lawrence Cole
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['engineering'] . '</b>', '
	Kevin Sours,
	Freddie Bingham,
	Edwin Brown,
	David Grove,
	Zoltan Szalay,
	Jorge Tiznado,
	Alan Orduno,
	Michael Lavaveshkul,
	Xiaoyu Huang,
	Kyle Furlong,
	Fernando Varesi,
	Glenn Vergara,
	Paul Marsden,
	Olga Mandrosov
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['qa'] . '</b>', '
	Allen Lin,
	Meghan Sensenbach,
	Joanna W.H.,
	Reshmi Rajesh,
	Ruth Navaneetha,
	Sebastiano Vassellatti,
	Lamonda Steele
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['support'] . '</b>', '
	Wayne Luke,
	George Liu,
	Zachery Woods,
	Lynne Sands,
	Trevor Hannant,
	Kay Alley,
	Danny Morlette,
	Zuzanna Grande,
	Dody,
	Rene Jimenez,
	Joe DiBiasi,
	Troy Roberts,
	Riasat Al Jamil
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['special_thanks_and_contributions'] . '</b>', '
	Ace Shattock,
	Adrian Harris,
	Adrian Sacchi,
	Ahmed,
	Ajinkya Apte,
	Andreas Kirbach,
	Andrew Elkins,
	Andy Huang,
	Aston Jay,
	Billy Golightly,
	bjornstrom,
	Bob Pankala,
	Brad Wright,
	Brian Swearingen,
	Brian Gunter,
	Carrie Anderson,
	Chen Avinadav,
	Chevy Revata,
	Chris Holland,
	Christopher Riley,
	Colin Frei,
	Daniel Clements,
	Darren Gordon,
	David Bonilla,
	David Webb,
	David Yancy,
	digitalpoint,
	Dominic Schlatter,
	Don Kuramura,
	Don T. Romrell,
	Doron Rosenberg,
	Elmer Hernandez,
	Eric Johney,
	Eric Sizemore (SecondV),
	Fabian Schonholz,
	Fei Leung,
	Fernando Munoz,
	Floris Fiedeldij Dop,
	Harry Scanlan,
	Gavin Robert Clarke,
	Geoff Carew,
	Giovanni Martinez,
	Green Cat,
	Hanafi Jamil,
	Hani Saad,
	Hanson Wong,
	Hartmut Voss,
	Ivan Anfimov,
	Ivan Milanez,
	Jacquii Cooke,
	Jake Bunce,
	Jan Allan Zischke,
	Jasper Aguila,
	Jaume L&oacute;pez,
	Jelle Van Loo,
	Jen Rundell,
	Jeremy Dentel,
	Jerry Hutchings,
	Joan Gauna,
	Joe Rosenblum,
	Joe Velez,
	Joel Young,
	John Jakubowski,
	John McGanty,
	John Percival,
	Jonathan Javier Coletta,
	Joseph DeTomaso,
	Justin Turner,
	Kevin Connery,
	Kevin Schumacher,
	Kevin Wilkinson,
	Kier Darby,
	Kira Lerner,
	Kolby Bothe,
	Lisa Swift,
	Marco Mamdouh Fahem,
	Mark James,
	Marlena Machol,
	Martin Meredith,
	Matthew Gordon,
	Merjawy,
	Mert Gokceimam,
	Michael Biddle,
	Michael Fara,
	Michael Henretty,
	Michael Kellogg,
	Michael \'Mystics\' K&ouml;nig,
	Michael Pierce,
	Michlerish,
	Mike Sullivan,
	Milad Kawas Cale,
	miner,
	Nathan Wingate,
	nickadeemus2002,
	Ole Vik,
	Oscar Ulloa,
	Overgrow,
	Peggy Lynn Gurney,
	Prince Shah,
	Pritesh Shah,
	Priyanka Porwal,
	Pieter Verhaeghe,
	Rob (Boofo) Hindal,
	Robert Beavan White,
	Roms,
	Ryan Ashbrook,
	Ryan Royal,
	Sal Colascione III,
	Scott MacVicar,
	Scott Molinari,
	Scott William,
	Scott Zachow,
	Shawn Vowell,
	Sophie Xie,
	Stefano Acerbetti,
	Stephan \'pogo\' Pogodalla,
	Steve Machol,
	Sven "cellarius" Keller,
	Tariq Bafageer,
	The Vegan Forum,
	ThorstenA,
	Tom Murphy,
	Tony Phoenix,
	Torstein H&oslash;nsi,
	Tully Rankin,
	Vinayak Gupta,
	Yves Rigaud
	', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['copyright_enforcement_by'] . '</b>', '
	vBulletin Solutions, Inc.
', '', 'top', NULL, false);
print_table_footer();

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 54780 $
|| ####################################################################
\*======================================================================*/
?>
