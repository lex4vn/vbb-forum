<?php
/**
* Fetches a list of allowed BBCode tags
*
* @param	array	The complete list of BBCode tags
* @param	array	The permissions array
*
* @return	array	A list of the allowed tags
*/
function vbshout_fetch_tag_list($tag_list, $permarray)
{
	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_QUOTE))
	{			
		// [QUOTE]
		unset($tag_list['no_option']['quote']);

		// [QUOTE=XXX]
		unset($tag_list['option']['quote']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_BASIC))
	{
		// [B]
		unset($tag_list['no_option']['b']);

		// [I]
		unset($tag_list['no_option']['i']);

		// [U]
		unset($tag_list['no_option']['u']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_COLOR))
	{
		// [COLOR=XXX]
		unset($tag_list['option']['color']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_SIZE))
	{
		// [SIZE=XXX]
		unset($tag_list['option']['size']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_FONT))
	{
		// [FONT=XXX]
		unset($tag_list['option']['font']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_ALIGN))
	{
		// [LEFT]
		unset($tag_list['no_option']['left']);

		// [CENTER]
		unset($tag_list['no_option']['center']);

		// [RIGHT]
		unset($tag_list['no_option']['right']);

		// [INDENT]
		unset($tag_list['no_option']['indent']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_LIST))
	{
		// [LIST]
		unset($tag_list['no_option']['list']);

		// [LIST=XXX]
		unset($tag_list['option']['list']);

		// [INDENT]
		unset($tag_list['no_option']['indent']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_URL))
	{
		// [EMAIL]
		unset($tag_list['no_option']['email']);

		// [EMAIL=XXX]
		unset($tag_list['option']['email']);

		// [URL]
		unset($tag_list['no_option']['url']);

		// [URL=XXX]
		unset($tag_list['option']['url']);

		// [THREAD]
		unset($tag_list['no_option']['thread']);

		// [THREAD=XXX]
		unset($tag_list['option']['thread']);

		// [POST]
		unset($tag_list['no_option']['post']);

		// [POST=XXX]
		unset($tag_list['option']['post']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_PHP))
	{
		// [PHP]
		unset($tag_list['no_option']['php']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_CODE))
	{
		//[CODE]
		unset($tag_list['no_option']['code']);
	}

	if (!($permarray['bbcodepermissions_parsed']['bit'] & ALLOW_BBCODE_HTML))
	{
		// [HTML]
		unset($tag_list['no_option']['html']);
	}

	return $tag_list;
}