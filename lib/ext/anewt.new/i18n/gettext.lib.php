<?php

/*
 * Anewt, Almost No Effort Web Toolkit, i18n module
 *
 * Copyright (C) 2006  Wouter Bolsterlee <uws@xs4all.nl>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA+
 */


/**
 * \file
 *
 * i18n utility functions.
 */


/**
 * Marks a string for translation but does return the original (untranslated)
 * string.
 *
 * \param $str
 *   The string to translate.
 *
 * \return
 *   The original string
 *
 * \see N_
 */
function gettext_noop($str) {
	assert('is_string($str)');
	return $str;
}

/**
 * Alias for gettext_noop.
 *
 * \param $str
 *   The string to translate.
 *
 * \return
 *   The original string
 *
 * \see
 *   gettext_noop
 */
function N_($str) {
	assert('is_string($str)');
	return $str;
}

/**
 * Just like gettext(), but strips the context (the part before the first
 * <code>|</code> character) if the translation is the same as the original
 * string. This is like pgettext, but with the context specified in the string,
 * not as a separate argument.
 *
 * \deprecated
 *   This method is deprecated; use pgettext() instead.
 *
 * \param $str
 *   The string to translate.
 *
 * \return
 *   The translated string, or the original string if no translation was
 *   found (with any prefix stripped)
 *
 * \see pgettext
 * \see gettext
 */
function Q_($str) {
	assert('is_string($str)');

	/* Try to translate normally */
	$translated = gettext($str);

	/* Translation succeeded? */
	if ($str != $translated)
		return $translated;

	/* We need to strip the context prefix when the untranslated and translated
	 * string are unique, ie. the part after the first | character. Note that
	 * strstr() returns false if the needle was not found. */
	$translated = strstr($str, '|');
	if (($translated !== false) && (strlen($translated) > 1))
		return substr($translated, 1); /* skip the | character */

	return $str;
}

/**
 * Translate a message with disambiguating context.
 *
 * This function can be used to provide context to the translator.
 *
 * \param $domain
 *   The text domain used for the lookup
 * \param $ctx
 *   The context of the message.
 * \param $msgid
 *   The string to translate.
 *
 * \return
 *   The translated string, or the original string if no translation was
 *   found.
 *
 * \see pgettext
 */
function dpgettext($domain, $ctx, $msgid)
{
	/* The .mo files generated by gettext > 0.15 have context separated from the
	 * message itself using 0x04 as a separator. */

	$msgid_with_ctx = sprintf("%s\004%s", $ctx, $msgid);
	$translated = dgettext($domain, $msgid_with_ctx);

	/* Translation succeeded? */
	if ($msgid_with_ctx != $translated)
		return $translated;

	return $msgid;
}

/**
 * Translate a message with disambiguating context using the default text
 * domain.
 *
 * This function can be used to provide context to the translator.
 *
 * \param $ctx
 *   The context of the message.
 * \param $str
 *   The string to translate.
 *
 * \return
 *   The translated string, or the original string if no translation was
 *   found.
 *
 * \see dpgettext
 */
function pgettext($ctx, $str)
{
	return dpgettext(textdomain(null), $ctx, $str);
}

/**
 * Alias for pgettext.
 *
 * \param $ctx
 * \param $str
 * \see pgettext
 */
function C_($ctx, $str) {
	return pgettext($ctx, $str);
}

/**
 * Translate a plural message with disambiguating context using the default text
 * domain.
 *
 * This function can be used to provide context to the translator.
 *
 * \param $domain
 *   The text domain used for the lookup
 * \param $ctx
 *   The context of the message.
 * \param $msgid1
 *   The singular string to translate.
 * \param $msgid1
 *   The plural string to translate.
 * \param $n
 *   The count
 *
 * \return
 *   The translated string, or the original string if no translation was
 *   found.
 *
 * \see ngettext
 * \see dpgettext
 * \see npgettext
 */
function dnpgettext($domain, $ctx, $msgid1, $msgid2, $n)
{
	/* See dpgettext() implementation above */
	$msgid1_with_ctx = sprintf("%s\004%s", $ctx, $msgid1);
	$msgid2_with_ctx = sprintf("%s\004%s", $ctx, $msgid2);
	$translated = dngettext($domain, $msgid1_with_ctx, $msgid2_with_ctx, $n);
	$prefix = sprintf("%s\004", $ctx);
	$translated = str_strip_prefix($translated, $prefix);
	return $translated;
}

/**
 * Translate a plural message with disambiguating context using the default text
 * domain.
 *
 * This function can be used to provide context to the translator.
 *
 * \param $ctx
 *   The context of the message.
 * \param $msgid1
 *   The singular string to translate.
 * \param $msgid1
 *   The plural string to translate.
 * \param $n
 *   The count
 *
 * \return
 *   The translated string, or the original string if no translation was
 *   found.
 *
 * \see ngettext
 * \see dpgettext
 * \see npgettext
 */
function npgettext($ctx, $msgid1, $msgid2, $n)
{
	return dnpgettext(textdomain(null), $ctx, $msgid1, $msgid2, $n);
}


/* Check for the gettext() function. If it was not found, we include the
 * fallback functions. */

if (!function_exists('gettext')) {
	anewt_include('i18n/gettext-fallback');
}


?>