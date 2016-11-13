<?php
/**
|*  Ideal Chat Pro v1.3.0
|*  Created: July 10th, 2011
|*  Last Modified: October 29th, 2011
|*  Author: Ideal Web Technologies (www.idealwebtech.com)
|*
|*  Copyright (c) 2011 Ideal Web Technologies
|*  This file is only to be used with the consent of Ideal Web Technologies 
|*  and may not be redistributed in whole or significant part!  By using
|*  this file, you agree to the Ideal Web Technologies' Terms of Service
|*  at www.idealwebtech.com/documents/tos.html
**/

function execute_shutdown_processes()
{
	global $vbulletin;

	// Setup and Process vBulletin shutdown information so we can keep users online for the long polling
	$vbulletin->db->unlock_tables();

	if (is_object($vbulletin->session))
	{
		$vbulletin->session->set('badlocation', (!empty($vbulletin->userinfo['badlocation']) ? $vbulletin->userinfo['badlocation'] : ''));
		if ($vbulletin->session->vars['loggedin'] == 1 AND !$vbulletin->session->created)
		{
			$vbulletin->session->set('loggedin', 2);
			if (!empty($vbulletin->profilefield['required']))
			{
				foreach ($vbulletin->profilefield['required'] AS $fieldname => $value)
				{
					if (!isset($vbulletin->userinfo["$fieldname"]) OR $vbulletin->userinfo["$fieldname"] === '')
					{
						$vbulletin->session->set('profileupdate', 1);
						break;
					}
				}
			}
		}
		$vbulletin->session->save();
	}

	if (is_array($vbulletin->db->shutdownqueries))
	{
		$vbulletin->db->hide_errors();
		foreach($vbulletin->db->shutdownqueries AS $name => $query)
		{
			if (!empty($query) AND ($name !== 'pmpopup' OR !defined('NOPMPOPUP')))
			{
				$vbulletin->db->query_write($query);
			}
		}
		$vbulletin->db->show_errors();
	}

	// Trigger shutdown event
	$vbulletin->shutdown->shutdown();
	exec_mail_queue();
	$vbulletin->db->close();
	$vbulletin->db->shutdownqueries = array();

	// broken if zlib.output_compression is on with Apache 2
	if (SAPI_NAME != 'apache2handler' AND SAPI_NAME != 'apache2filter')
	{
		flush();
	}
}

function output_ajax_error($error)
{
	// Run the shutdown
	execute_shutdown_processes();

	// Send the headers
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header('Content-Type: text/xml');

	// Send the output and exit
	exit('<?xml version="1.0" encoding="' . vB_Template_Runtime::fetchStyleVar('charset') . '"?>' . "\r\n<error>$error</error>");
}

function output_ajax_notice($notice)
{
	// Run the shutdown
	execute_shutdown_processes();

	// Send the headers
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header('Content-Type: text/xml');

	// Send the output and exit
	exit('<?xml version="1.0" encoding="' . vB_Template_Runtime::fetchStyleVar('charset') . '"?>' . "\r\n<notice>$notice</notice>");
}

/**
|*	@func	fetch_chat_commands
|*	@desc	Returns an array of chat commands for User-to-User chats.
**/
function fetch_chat_commands()
{
	$you = $vbphrase['iwt_idealchat_you'];
	$you_lower = strtolower($you);

	return array(
		array("command" => "agree", "outputS" => "{$you} agree with *RECIEVER*.", "outputR" => "*SENDER* agrees with {$you_lower}."),
		array("command" => "angry", "outputS" => "{$you} are angry at *RECIEVER*!", "outputR" => "*SENDER* is angry at {$you_lower}!"),
		array("command" => "apologize", "outputS" => "{$you} apologize!", "outputR" => "*SENDER* apologizes!"),
		array("command" => "sorry", "outputS" => "* See /apologize *", "outputR" => "* See /apologize *"),
		array("command" => "applaud", "outputS" => "{$you} applaud!", "outputR" => "*SENDER* applauds!"),
		array("command" => "applause", "outputS" => "* See /applaud *", "outputR" => "* See /applaud *"),
		array("command" => "bravo", "outputS" => "* See /applaud *", "outputR" => "* See /applaud *"),

		array("command" => "bark", "outputS" => "{$you} bark at *RECIEVER*!", "outputR" => "*SENDER* barks at {$you_lower}!"),
		array("command" => "beckon", "outputS" => "{$you} beckon *RECIEVER*.", "outputR" => "*SENDER* beckons {$you_lower}."),
		array("command" => "bed", "outputS" => "{$you} go to bed.", "outputR" => "*SENDER* goes to bed."),
		array("command" => "beg", "outputS" => "{$you} beg *RECIEVER*.", "outputR" => "*SENDER* begs {$you_lower}."),
		array("command" => "belch", "outputS" => "{$you} belch!", "outputR" => "*SENDER* belches!"),
		array("command" => "bite", "outputS" => "{$you} bite *RECIEVER*!", "outputR" => "*SENDER* bites {$you_lower}!"),
		array("command" => "blush", "outputS" => "{$you} blush!", "outputR" => "*SENDER* blushes!"),
		array("command" => "bored", "outputS" => "{$you} are bored!", "outputR" => "*SENDER* is bored!"),
		array("command" => "bow", "outputS" => "{$you} bow.", "outputR" => "*SENDER* bows."),
		array("command" => "brb", "outputS" => "{$you} will be right back.", "outputR" => "*SENDER* will be right back."),
		array("command" => "burp", "outputS" => "{$you} burp.", "outputR" => "*SENDER* burps."),
		array("command" => "bye", "outputS" => "{$you} say bye!", "outputR" => "*SENDER* says bye!"),

		array("command" => "calm", "outputS" => "{$you} attempt to calm *RECIEVER*!", "outputR" => "*SENDER* attemps to calm {$you_lower}!"),
		array("command" => "cheer", "outputS" => "{$you} cheer!", "outputR" => "*SENDER* cheers!"),
		array("command" => "chicken", "outputS" => "{$you} strut around like a chicken! Bwak, Bwak!", "outputR" => "*SENDER* struts around like a chicken! Bwak, Bwak!"),
		array("command" => "chuckle", "outputS" => "{$you} chuckle.", "outputR" => "*SENDER* chuckles."),
		array("command" => "clap", "outputS" => "{$you} clap!", "outputR" => "*SENDER* claps!"),
		array("command" => "cold", "outputS" => "{$you} are cold!", "outputR" => "*SENDER* is cold!"),
		array("command" => "comfort", "outputS" => "{$you} comfort *RECIEVER*.", "outputR" => "*SENDER* comforts {$you_lower}."),
		array("command" => "confused", "outputS" => "{$you} are confused!", "outputR" => "*SENDER* is confused!"),
		array("command" => "congratulate", "outputS" => "{$you} congratulate *RECIEVER*!", "outputR" => "*SENDER* congratulates {$you_lower}!"),
		array("command" => "congrats", "outputS" => "* See /congratulate *", "outputR" => "* See /congratulate *"),
		array("command" => "grats", "outputS" => "* See /congratulate *", "outputR" => "* See /congratulate *"),
		array("command" => "cough", "outputS" => "{$you} cough.", "outputR" => "*SENDER* coughs."),
		array("command" => "cower", "outputS" => "{$you} cower in fear!", "outputR" => "*SENDER* cowers in fear!"),
		array("command" => "fear", "outputS" => "* See /cower *", "outputR" => "* See /cower *"),
		array("command" => "cuddle", "outputS" => "{$you} cuddle with *RECIEVER*.", "outputR" => "*SENDER* cuddles with {$you_lower}."),
		array("command" => "curious", "outputS" => "{$you} are curious!", "outputR" => "*SENDER* is curious!"),
		array("command" => "curtsey", "outputS" => "{$you} curtsey.", "outputR" => "*SENDER* curtseys."),
		array("command" => "cringe", "outputS" => "{$you} cringe!", "outputR" => "*SENDER* cringes!"),
		array("command" => "cry", "outputS" => "{$you} cry!", "outputR" => "*SENDER* cries!"),
		array("command" => "cya", "outputS" => "{$you} say cya!", "outputR" => "*SENDER* says cya!"),

		array("command" => "dance", "outputS" => "{$you} burst into dance!", "outputR" => "*SENDER* bursts into dance!"),
		array("command" => "disappointed", "outputS" => "{$you} are disappointed!", "outputR" => "*SENDER* is disappointed!"),
		array("command" => "duck", "outputS" => "{$you} duck.", "outputR" => "*SENDER* ducks."),
		array("command" => "drool", "outputS" => "{$you} drool.", "outputR" => "*SENDER* drools."),

		array("command" => "excited", "outputS" => "{$you} are excited!", "outputR" => "*SENDER* is excited!"),

		array("command" => "fart", "outputS" => "{$you} fart.", "outputR" => "*SENDER* farts."),
		array("command" => "fidget", "outputS" => "{$you} fidget.", "outputR" => "*SENDER* fidgets."),
		array("command" => "flatter", "outputS" => "{$you} attempt to flatter *RECIEVER*!", "outputR" => "*SENDER* attemps to flatter {$you_lower}!"),
		array("command" => "flex", "outputS" => "{$you} flex!", "outputR" => "*SENDER* flexes!"),
		array("command" => "flirt", "outputS" => "{$you} flirt with *RECIEVER*.", "outputR" => "*SENDER* flirts with {$you_lower}."),
		array("command" => "frown", "outputS" => "{$you} frown.", "outputR" => "*SENDER* frowns."),

		array("command" => "gasp", "outputS" => "{$you} gasp!", "outputR" => "*SENDER* gasps!"),
		array("command" => "gaze", "outputS" => "{$you} gaze at *RECIEVER*!", "outputR" => "*SENDER* gazes at {$you_lower}!"),
		array("command" => "giggle", "outputS" => "{$you} giggle.", "outputR" => "*SENDER* giggles."),
		array("command" => "glad", "outputS" => "{$you} are glad!", "outputR" => "*SENDER* is glad!"),
		array("command" => "glares", "outputS" => "{$you} glares at *RECIEVER*!", "outputR" => "*SENDER* glaress at {$you_lower}!"),
		array("command" => "gloat", "outputS" => "{$you} gloat.", "outputR" => "*SENDER* gloats."),
		array("command" => "goodbye", "outputS" => "{$you} say goodbye!", "outputR" => "*SENDER* says goodbye!"),
		array("command" => "greet", "outputS" => "{$you} greet *RECIEVER*.", "outputR" => "*SENDER* greets {$you_lower}."),
		array("command" => "greetings", "outputS" => "* See /greet *", "outputR" => "* See /greet *"),
		array("command" => "hello", "outputS" => "* See /greet *", "outputR" => "* See /greet *"),
		array("command" => "hi", "outputS" => "* See /greet *", "outputR" => "* See /greet *"),
		array("command" => "grin", "outputS" => "{$you} grin.", "outputR" => "*SENDER* grins."),
		array("command" => "groan", "outputS" => "{$you} groan at *RECIEVER*!", "outputR" => "*SENDER* groans at {$you_lower}!"),
		array("command" => "grovel", "outputS" => "{$you} grovel!", "outputR" => "*SENDER* grovels!"),
		array("command" => "growl", "outputS" => "{$you} growl at *RECIEVER*!", "outputR" => "*SENDER* growls at {$you_lower}!"),

		array("command" => "hail", "outputS" => "{$you} hail *RECIEVER*.", "outputR" => "*SENDER* hails {$you_lower}."),
		array("command" => "happy", "outputS" => "{$you} are happy!", "outputR" => "*SENDER* is happy!"),
		array("command" => "helpme", "outputS" => "{$you} are in need of help.", "outputR" => "*SENDER* is in need of help."),
		array("command" => "hide", "outputS" => "{$you} hide.", "outputR" => "*SENDER* hides."),
		array("command" => "hit", "outputS" => "{$you} hit *RECIEVER*!", "outputR" => "*SENDER* hits {$you_lower}!"),
		array("command" => "hit *MESSAGE*", "outputS" => "{$you} hit *RECIEVER* *MESSAGE*!", "outputR" => "*SENDER* hits {$you_lower} *MESSAGE*!"),
		array("command" => "hug", "outputS" => "{$you} hug *RECIEVER*.", "outputR" => "*SENDER* hugs {$you_lower}."),
		array("command" => "hungry", "outputS" => "{$you} are hungry!", "outputR" => "*SENDER* is hungry!"),

		array("command" => "impatient", "outputS" => "{$you} are impatient!", "outputR" => "*SENDER* is impatient!"),
		array("command" => "insult", "outputS" => "{$you} insult *RECIEVER*.", "outputR" => "*SENDER* insults {$you_lower}."),
		array("command" => "insulted", "outputS" => "{$you} are insulted!", "outputR" => "*SENDER* is insulted!"),

		array("command" => "jk", "outputS" => "{$you} are just kidding.", "outputR" => "*SENDER* is just kidding!"),
		array("command" => "joke", "outputS" => "{$you} joke.", "outputR" => "*SENDER* jokes."),

		array("command" => "kick", "outputS" => "{$you} kick *RECIEVER*!", "outputR" => "*SENDER* kicks {$you_lower}!"),
		array("command" => "kiss", "outputS" => "{$you} kiss *RECIEVER*!", "outputR" => "*SENDER* kisses {$you_lower}!"),
		array("command" => "kneel", "outputS" => "{$you} kneel.", "outputR" => "*SENDER* kneels."),

		array("command" => "laugh", "outputS" => "{$you} laugh.", "outputR" => "*SENDER* laughs."),
		array("command" => "laughat", "outputS" => "{$you} laugh at *RECIEVER*!", "outputR" => "*SENDER* laughs at {$you_lower}!"),
		array("command" => "lick", "outputS" => "{$you} lick *RECIEVER*.", "outputR" => "*SENDER* licks {$you_lower}."),
		array("command" => "listen", "outputS" => "{$you} listen.", "outputR" => "*SENDER* listens."),
		array("command" => "lol", "outputS" => "{$you} laugh out loud!", "outputR" => "*SENDER* laughs out loud!"),
		array("command" => "lost", "outputS" => "{$you} are lost!", "outputR" => "*SENDER* is lost!"),
		array("command" => "love", "outputS" => "{$you} love *RECIEVER*!", "outputR" => "*SENDER* loves {$you_lower}!"),

		array("command" => "mad", "outputS" => "{$you} are mad at *RECIEVER*!", "outputR" => "*SENDER* is mad at {$you_lower}!"),
		array("command" => "massage", "outputS" => "{$you} massage *RECIEVER*.", "outputR" => "*SENDER* massages {$you_lower}."),
		array("command" => "me", "outputS" => "{$you} want attention!", "outputR" => "*SENDER* wants attention!"),
		array("command" => "me *MESSAGE*", "outputS" => "*SENDER* *MESSAGE*", "outputR" => "*SENDER* *MESSAGE*"),
		array("command" => "moan", "outputS" => "{$you} moan at *RECIEVER*!", "outputR" => "*SENDER* moans at {$you_lower}!"),
		array("command" => "mock", "outputS" => "{$you} mock *RECIEVER*!", "outputR" => "*SENDER* mocks {$you_lower}!"),
		array("command" => "moo", "outputS" => "{$you} moo at *RECIEVER*!", "outputR" => "*SENDER* moos at {$you_lower}!"),
		array("command" => "moon", "outputS" => "{$you} moon *RECIEVER*!", "outputR" => "*SENDER* moons {$you_lower}!"),
		array("command" => "mourn", "outputS" => "{$you} mourn *RECIEVER*.", "outputR" => "*SENDER* mourns {$you_lower}."),

		array("command" => "no", "outputS" => "{$you} say no!", "outputR" => "*SENDER* says no!"),
		array("command" => "nod", "outputS" => "{$you} nod.", "outputR" => "*SENDER* nods."),

		array("command" => "panic", "outputS" => "{$you} panic!", "outputR" => "*SENDER* panics!"),
		array("command" => "pester", "outputS" => "{$you} pester *RECIEVER*.", "outputR" => "*SENDER* pesters {$you_lower}."),
		array("command" => "pity", "outputS" => "{$you} pity *RECIEVER*.", "outputR" => "*SENDER* pities {$you_lower}."),
		array("command" => "plead", "outputS" => "{$you} plead with *RECIEVER*.", "outputR" => "*SENDER* pleads with {$you_lower}."),
		array("command" => "poke", "outputS" => "{$you} poke *RECIEVER*!", "outputR" => "*SENDER* pokes {$you_lower}!"),
		array("command" => "poke *MESSAGE*", "outputS" => "{$you} poke *RECIEVER* *MESSAGE*!", "outputR" => "*SENDER* pokes {$you_lower} *MESSAGE*!"),
		array("command" => "ponder", "outputS" => "{$you} ponder.", "outputR" => "*SENDER* ponders."),
		array("command" => "pounce", "outputS" => "{$you} pounce *RECIEVER*.", "outputR" => "*SENDER* pounces {$you_lower}."),
		array("command" => "purr", "outputS" => "{$you} purr at *RECIEVER*!", "outputR" => "*SENDER* purrs at {$you_lower}!"),
		array("command" => "puzzled", "outputS" => "{$you} are puzzled!", "outputR" => "*SENDER* is puzzled!"),
		array("command" => "praise", "outputS" => "{$you} praise *RECIEVER*.", "outputR" => "*SENDER* praises {$you_lower}."),
		array("command" => "prey", "outputS" => "{$you} prey.", "outputR" => "*SENDER* preys."),

		array("command" => "question", "outputS" => "{$you} question *RECIEVER*.", "outputR" => "*SENDER* questions {$you_lower}."),

		array("command" => "ready", "outputS" => "{$you} are ready!", "outputR" => "*SENDER* is ready!"),
		array("command" => "rdy", "outputS" => "* See /ready *", "outputR" => "* See /ready *"),
		array("command" => "roar", "outputS" => "{$you} roar at *RECIEVER*!", "outputR" => "*SENDER* roars at {$you_lower}!"),
		array("command" => "rofl", "outputS" => "{$you} roll on the floor laughing!", "outputR" => "*SENDER* rolls on the floor laughing!"),
		array("command" => "roll", "outputS" => "{$you} roll a number (0-100) and get a *RANDOM*.", "outputR" => "*SENDER* rolls a number (0-100) and gets a *RANDOM*."),
		array("command" => "roll *MIN*-*MAX*", "outputS" => "{$you} roll a number (*MIN*-*MAX*) and get a *RANDOM*.", "outputR" => "*SENDER* rolls a number (*MIN*-*MAX*) and gets a *RANDOM*."),

		array("command" => "salute", "outputS" => "{$you} salute *RECIEVER*.", "outputR" => "*SENDER* salutes {$you_lower}."),
		array("command" => "scared", "outputS" => "{$you} are scared!", "outputR" => "*SENDER* is scared!"),
		array("command" => "scratch", "outputS" => "{$you} scratch *RECIEVER*!", "outputR" => "*SENDER* scratches {$you_lower}!"),
		array("command" => "shake", "outputS" => "{$you} shake *RECIEVER*.", "outputR" => "*SENDER* shakes {$you_lower}."),
		array("command" => "shiver", "outputS" => "{$you} shiver.", "outputR" => "*SENDER* shivers."),
		array("command" => "shoo", "outputS" => "{$you} shoo *RECIEVER*.", "outputR" => "*SENDER* shoos {$you_lower}."),
		array("command" => "shout", "outputS" => "{$you} shout!", "outputR" => "*SENDER* shouts!"),
		array("command" => "shrug", "outputS" => "{$you} shrug.", "outputR" => "*SENDER* shrugs."),
		array("command" => "shy", "outputS" => "{$you} are shy!", "outputR" => "*SENDER* is shy!"),
		array("command" => "slap", "outputS" => "{$you} slap *RECIEVER*!", "outputR" => "*SENDER* slaps {$you_lower}!"),
		array("command" => "slap *MESSAGE*", "outputS" => "{$you} slap *RECIEVER* *MESSAGE*!", "outputR" => "*SENDER* slaps {$you_lower} *MESSAGE*!"),
		array("command" => "sleep", "outputS" => "{$you} go to sleep.", "outputR" => "*SENDER* goes to sleep."),
		array("command" => "smell", "outputS" => "{$you} smell *RECIEVER*.", "outputR" => "*SENDER* smells {$you_lower}."),
		array("command" => "sigh", "outputS" => "{$you} sigh.", "outputR" => "*SENDER* sighs."),
		array("command" => "smile", "outputS" => "{$you} smile at *RECIEVER*.", "outputR" => "*SENDER* smiles at {$you_lower}."),
		array("command" => "smirk", "outputS" => "{$you} smirk at *RECIEVER*!", "outputR" => "*SENDER* smirks at {$you_lower}!"),
		array("command" => "snarl", "outputS" => "{$you} snarl at *RECIEVER*!", "outputR" => "*SENDER* snarls at {$you_lower}!"),
		array("command" => "snicker", "outputS" => "{$you} snicker.", "outputR" => "*SENDER* snickers."),
		array("command" => "sniff", "outputS" => "{$you} sniff *RECIEVER*.", "outputR" => "*SENDER* sniffs {$you_lower}."),
		array("command" => "sob", "outputS" => "{$you} sob!", "outputR" => "*SENDER* sobs!"),
		array("command" => "soothe", "outputS" => "{$you} soothe *RECIEVER*.", "outputR" => "*SENDER* soothes {$you_lower}."),
		array("command" => "spit", "outputS" => "{$you} spit at *RECIEVER*!", "outputR" => "*SENDER* spits at {$you_lower}!"),
		array("command" => "spoon", "outputS" => "{$you} spoon with *RECIEVER*.", "outputR" => "*SENDER* spoons with {$you_lower}."),
		array("command" => "stare", "outputS" => "{$you} stare at *RECIEVER*!", "outputR" => "*SENDER* stares at {$you_lower}!"),
		array("command" => "surprised", "outputS" => "{$you} are surprised!", "outputR" => "*SENDER* is surprised!"),
		array("command" => "surrender", "outputS" => "{$you} surrender!", "outputR" => "*SENDER* surrenders!"),

		array("command" => "taunt", "outputS" => "{$you} taunt *RECIEVER*!", "outputR" => "*SENDER* taunts {$you_lower}!"),
		array("command" => "tease", "outputS" => "{$you} tease *RECIEVER*!", "outputR" => "*SENDER* teases {$you_lower}!"),
		array("command" => "thank", "outputS" => "{$you} thank *RECIEVER*.", "outputR" => "*SENDER* thanks {$you_lower}."),
		array("command" => "thanks", "outputS" => "* See /thank *", "outputR" => "* See /thank *"),
		array("command" => "ty", "outputS" => "* See /thank *", "outputR" => "* See /thank *"),
		array("command" => "thirsty", "outputS" => "{$you} are thirsty!", "outputR" => "*SENDER* is thirsty!"),
		array("command" => "tired", "outputS" => "{$you} are tired!", "outputR" => "*SENDER* is tired!"),
		array("command" => "tickle", "outputS" => "{$you} tickle *RECIEVER*.", "outputR" => "*SENDER* tickles {$you_lower}."),

		array("command" => "violin", "outputS" => "{$you} play the world\"s smallest violin for *RECIEVER*!", "outputR" => "*SENDER* plays the world\"s smallest violin for {$you_lower}!"),
		array("command" => "tinyviolin", "outputS" => "* See /violin *", "outputR" => "* See /violin *"),
		array("command" => "smallviolin", "outputS" => "* See /violin *", "outputR" => "* See /violin *"),

		array("command" => "wait", "outputS" => "{$you} wait.", "outputR" => "*SENDER* waits."),
		array("command" => "wave", "outputS" => "{$you} wave at *RECIEVER*.", "outputR" => "*SENDER* waves at {$you_lower}."),
		array("command" => "weep", "outputS" => "{$you} weep!", "outputR" => "*SENDER* weeps!"),
		array("command" => "welcome", "outputS" => "{$you} welcome *RECIEVER*.", "outputR" => "*SENDER* welcomes {$you_lower}."),
		array("command" => "whine", "outputS" => "{$you} whine.", "outputR" => "*SENDER* whines."),
		array("command" => "whistle", "outputS" => "{$you} whistle.", "outputR" => "*SENDER* whistles."),
		array("command" => "wink", "outputS" => "{$you} wink.", "outputR" => "*SENDER* winks."),
		array("command" => "work", "outputS" => "{$you} go to work.", "outputR" => "*SENDER* goes to work."),

		array("command" => "yawn", "outputS" => "{$you} yawn.", "outputR" => "*SENDER* yawns."),
		array("command" => "yes", "outputS" => "{$you} say yes!", "outputR" => "*SENDER* says yes!")
	);
}

/**
|*	@func	add_chattab_output
|*	@desc	Adds a chat tabs output to the xml stream.
|*
|*	@param	Int		userid		The userid of this chat tab's user.
|*	@param	String	username	The username of this chat tab's user.
|*	@param	Array	messages	An array of messages to populate the chat tab with.
**/
function add_chattab_output($userid, $username, $messages)
{
	global $vbulletin, $xml, $vbphrase;

	$xml->add_group('chattab');
	$xml->add_tag('userid', $userid);

	$bits = '';

	foreach ((array) $messages AS $message)
	{
		$messageSlashCommand = parse_slash_command($message['message'], $message['sender'], $message['reciever']);

		if ($messageSlashCommand != $message['message'])
		{
			$templater = vB_Template::create('iwt_idealchat_bb_chat_message_bit_special');
				$templater->register('message', $messageSlashCommand);
				$templater->register('time_sent', vbdate('M j, g:i:s a', $message['timestamp']));
			$bits .= $templater->render(false);
		}
		else
		{
			$templater = vB_Template::create('iwt_idealchat_bb_chat_message_bit');
				$templater->register('sender', (($message['sender']==$vbulletin->userinfo['username']) ? $vbphrase['iwt_idealchat_you'] : $message['sender']));
				$templater->register('message', $message['message']);
				$templater->register('time_sent', vbdate('M j, g:i:s a', $message['timestamp']));
			$bits .= $templater->render(false);
		}
	}

	//spit out tab info
	$templater = vB_Template::create('iwt_idealchat_bb_user_chat_tab');
		$templater->register('userid', $userid);
		$templater->register('username', $username);
		$templater->register('messages', $bits);
	$xml->add_tag('html', $templater->render(false), array(), true);

	$xml->close_group('chattab');
}

function add_chatroomtab_output($roomid, $roomname, $messages)
{
	global $vbulletin, $xml;

	$xml->add_group('chatroomtab');
	$xml->add_tag('roomid', $roomid);

	$bits = '';

	foreach ((array) $messages AS $message)
	{
		$messageSlashCommand = parse_slash_command_chatroom($message['message'], $message['sender']);

		if ($messageSlashCommand != $message['message'])
		{
			$templater = vB_Template::create('iwt_idealchat_bb_chat_message_bit_special');
				$templater->register('message', $messageSlashCommand);
				$templater->register('time_sent', vbdate('M j, g:i:s a', $message['timestamp']));
			$bits .= $templater->render(false);
		}
		else
		{
			$templater = vB_Template::create('iwt_idealchat_bb_chat_message_bit');
				$templater->register('sender', (($message['sender']==$vbulletin->userinfo['username']) ? $vbphrase['iwt_idealchat_you'] : $message['sender']));
				$templater->register('message', $message['message']);
				$templater->register('time_sent', vbdate('M j, g:i:s a', $message['timestamp']));
			$bits .= $templater->render(false);
		}
	}

	//spit out tab info
	$templater = vB_Template::create('iwt_idealchat_bb_chatroom_tab');
		$templater->register('roomid', $roomid);
		$templater->register('roomname', $roomname);
		$templater->register('messages', $bits);
	$xml->add_tag('html', $templater->render(false), array(), true);

	$xml->close_group('chatroomtab');
}

/**
|*  This file was downloaded from http://www.idealwebtech.com at 15:46:27, Thursday December 29th, 2011 
|*
|*  This product has been licensed to Brenda Covey.
|*  License Key: 2b01811ed4f45876dbd9392ea3a0a4ad
**/