<?php

/*
 * Anewt, Almost No Effort Web Toolkit, page module
 *
 * This code is copyrighted and distributed under the terms of the GNU LGPL.
 * See the README file for more information.
 */


anewt_include('xhtml');


/**
 * Class for building XHTML pages.
 */
class AnewtPage extends AnewtContainer implements ArrayAccess
{
	/** \private Page content */
	private $_content;

	/** \private Associative array of blocks */
	private $_blocks = array();

	/** \private List of link nodes */
	private $_links = array();

	/** \private List of JavaScript nodes */
	private $_javascripts = array();


	/**
	 * Create a new AnewtPage instance.
	 */
	public function __construct()
	{
		/* Figure out the content type based on the HTTP_ACCEPT header of the
		 * current request. */
		if (array_key_exists('HTTP_ACCEPT', $_SERVER)
				&& str_contains($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))
		{
			$content_type = 'application/xhtml+xml';
		} else {
			$content_type = 'text/html';
		}


		/* Set default values */

		$this->_seed(array(
			'language' => 'en',
			'encoding' => 'UTF-8',
			'content-type' => $content_type,
			'document-type' => DTD_XHTML_1_0_STRICT,

			'base-uri' => null,

			'title' => null,

			'favicon' => null,

			'show-dublin-core' => true,

			'generator' => 'Almost No Effort Web Toolkit (Anewt)',

			'robots' => null,

			'blocks' => array(),
			'default-block' => null,

			'use-wrapper-div' => true,
			'wrapper-div-id' => 'wrapper',
		));
	}


	/** \{
	 * \name Stylesheet methods
	 *
	 * These methods allow you to add stylesheets to the page.
	 */

	/**
	 * Add a stylesheet node to the page.
	 *
	 * This function accepts either a AnewtXHTMLStyle or an
	 * AnewtXHTMLLink element, e.g. created by ax_stylesheet() or
	 * ax_stylesheet_href(). Usually you should use add_stylesheet_href() or
	 * add_stylesheet_href_media() instead.
	 *
	 * \param $node
	 *   A AnewtXHTMLStyle or AnewtXHTMLLink (referring to a stylesheet) node.
	 *
	 * \see add_stylesheet_href
	 * \see add_stylesheet_href_media
	 */
	public function add_stylesheet($node)
	{
		assert('$node instanceof AnewtXHTMLLink || $node instanceof AnewtXHTMLStyle');
		$this->_links[] = $node;
	}

	/**
	 * Add an external stylesheet reference to the page.
	 *
	 * \param $href
	 *   The location of the external stylesheet
	 *
	 * \see add_stylesheet
	 * \see add_stylesheet_href_media
	 * \see ax_stylesheet_href
	 */
	public function add_stylesheet_href($href)
	{
		$this->add_stylesheet(ax_stylesheet_href($href));
	}

	/**
	 * Add an external stylesheet reference to the page.
	 *
	 * \param $href
	 *   The location of the external stylesheet
	 *
	 * \param $media
	 *   The media type for which this stylesheet is intended, e.g. \c print or
	 *   \c screen.
	 *
	 * \see add_stylesheet
	 * \see add_stylesheet_href
	 * \see ax_stylesheet_href_media
	 */
	public function add_stylesheet_href_media($href, $media)
	{
		$this->add_stylesheet(ax_stylesheet_href_media($href, $media));
	}

	/** \} */


	/** \{
	 * \name JavaScript methods
	 *
	 * These methods allow you to add JavaScript to the page.
	 */

	/**
	 * Add JavaScript to this page.
	 *
	 * This function accepts a AnewtXHTMLScript node, e.g. one created using
	 * ax_javascript(). Use the add_javascript_src() and
	 * add_javascript_content() methods to easily add stylesheets to the page
	 * without creating the node yourself.
	 *
	 * \param $node
	 *   A AnewtXHTMLScript node.
	 *
	 * \see add_javascript_src
	 * \see add_javascript_content
	 */
	public function add_javascript($node)
	{
		assert('$node instanceof AnewtXHTMLScript');
		$this->_javascripts[] = $node;
	}

	/**
	 * Add a JavaScript reference to this page for the given source file.
	 *
	 * \param $src
	 *   Source of the JavaScript file
	 */
	public function add_javascript_src($src)
	{
		$this->add_javascript(ax_javascript_src($src));
	}

	/**
	 * Add a JavaScript to this page for the given content.
	 *
	 * \param $content
	 *   Scripts contents.
	 */
	public function add_javascript_content($content)
	{
		$this->add_javascript(ax_javascript($content));
	}

	/** \} */


	/** \{
	 * \name Linking methods
	 *
	 * These methods should be used to link other resources to this page.
	 */

	/**
	 * Add a link element to this page.
	 *
	 * \param $node
	 *   A AnewtXHTMLLink node.
	 *
	 * \see AnewtPage::add_link_rss
	 * \see AnewtXHTMLLink
	 * \see ax_link
	 */
	public function add_link($node)
	{
		assert('$node instanceof AnewtXHTMLLink');
		$this->_links[] = $node;
	}

	/**
	 * Add a link to a RSS feed to this page.
	 *
	 * \param $href
	 *   The location of the RSS feed
	 * \param $title
	 *   The title used to refer to the RSS feed
	 *
	 * \see AnewtPage::add_link
	 * \see AnewtXHTMLLink
	 * \see ax_link_rss
	 */
	public function add_link_rss($href, $title)
	{
		$this->add_link(ax_link_rss($href, $title));
	}

	/** \} */
	
	
	/** \{
	 * \name Content methods
	 *
	 * These methods should be used to add content to the page.
	 */

	/**
	 * Append new content to this page.
	 *
	 * For simple pages this method just adds some content to the page. For
	 * block-based pages, this method adds content to the default block.
	 *
	 * \param $new_child
	 *   The content to add. This is most likely an Anewt XHTML element
	 *   instance, e.g. created by the <code>ax_*</code> functions from the
	 *   XHTML module. Numerical arrays are also accepted.
	 *
	 * \see append_to
	 */
	public function append($new_child)
	{
		if ($this->_get('blocks'))
		{
			/* This is a block-based page */

			$default_block_name = $this->_get('default-block');

			if (is_null($default_block_name))
				throw new AnewtException('AnewtPage::append() can only be used on block-based pages if a default block is set.');

			$this->append_to($default_block_name, $new_child);

		} else
		{
			/* This is a simple page */

			if (!$this->_content)
				$this->_content = new AnewtXMLDomDocumentFragment();

			if (is_numeric_array($new_child))
				$this->_content->append_children($new_child);
			else
				$this->_content->append_child($new_child);
		}
	}

	/**
	 * Append new content to a block on this page.
	 *
	 * This method can only be used for block-based pages.
	 *
	 * Note that the specified block is not required to be specified in the
	 * <code>blocks</code> property, though in most cases it will be (if you
	 * want it to be rendered at least). In some custom situations where you
	 * build blocks by other means, i.e. using <code>build_*</code> methods on
	 * your page subclass, it might be helpful to use custom block names to act
	 * as temporary containers for the page content. The <code>build_*</code>
	 * methods can then retrieve the value and do something smart with it.
	 *
	 * \param $name
	 *   The name of the block to which the content should be added.
	 *
	 * \param $new_child
	 *   The content to add. This is most likely an Anewt XHTML element
	 *   instance, e.g. created by the <code>ax_*</code> functions from the
	 *   XHTML module. Numerical arrays are also accepted.
	 *
	 * \see append
	 */
	public function append_to($name, $new_child)
	{
		assert('is_string($name);');

		/* Initialize when needed */
		if (!array_key_exists($name, $this->_blocks))
			$this->_init_block($name);

		if (is_numeric_array($new_child))
			$this->_blocks[$name]->append_children($new_child);
		else
			$this->_blocks[$name]->append_child($new_child);
	}

	/**
	 * Get the contents of a block.
	 *
	 * This method returns the content of the block as an AnewtXMLDomNode
	 * instance, or null if no content was set for the requested block.
	 *
	 * \param $name
	 *   The name of the block to retrieve.
	 *
	 * \return
	 *   An AnewtXMLDomNode instance or null if the block did not contain any
	 *   content.
	 */
	protected function get_block($name)
	{
		assert('is_string($name);');

		$block = null;

		if (array_key_exists($name, $this->_blocks)) 
		{
			$block = $this->_blocks[$name];

		} else {
			/* Block contents are not available. Perhaps there's
			 * a special method to build this block instead. */
			$block_build_method = 'build_' . str_replace('-', '_', $name);

			if (method_exists($this, $block_build_method))
				$block = $this->$block_build_method();
		}
		
		return $block;
	}

	private function _init_block($name)
	{
		$this->_blocks[$name] = new AnewtXMLDomDocumentFragment();
	}

	/** \} */


	/** \{
	 * \name Output methods
	 */

	/**
	 * Render this page into XHTML.
	 *
	 * This methods renders the whole page into a complete XHTML page. Usually
	 * you want to use flush() to output the page to the browser.
	 *
	 * \return
	 *   The rendered page as a string.
	 *
	 * \see AnewtPage::flush
	 */
	public function render()
	{
		/* Create basic element nodes */

		$head = new AnewtXMLDomElement('head');
		$body = new AnewtXMLDomElement('body');
		$head->always_render_closing_tag = true;
		$body->always_render_closing_tag = true;

		/* Content-type in meta tag. This must be the first element inside the
		 * <head>...</head> element. */

		$head->append_child(ax_meta(array(
			'http-equiv' => 'Content-type',
			'content'    => $this->build_content_type_charset(),
		)));


		/* Base URI */

		$base_uri = $this->_get('base-uri');
		if (!is_null($base_uri))
		{
			assert('is_string($base_uri); // base-uri must be a simple string');

			/* Employ a simple heuristic to make sure we use an absolute URI, as
			 * is required by the HTML specification. */
			if (!str_contains($base_uri, '://'))
				$base_uri = AnewtRequest::canonical_base_url() . $base_uri;

			$base = new AnewtXHTMLBase();
			$base->set_attribute('href', $base_uri);
			$head->append_child($base);
		}


		/* Page title (always include, even if empty, since this is required by
		 * the HTML specification) */

		$title = $this->_get('title');
		$head->append_child(ax_title($title));


		/* Dublin Core metadata. See http://dublincore.org/documents/dcq-html/ * */

		if ($this->_get('show-dublin-core'))
		{
			$head->append_child(ax_link(array(
				'rel'  => 'schema.DC',
				'href' => 'http://purl.org/dc/elements/1.1/')));

			$head->append_child(ax_meta_name_content('DC.language', $this->_get('language')));

			if (!is_null($title))
				$head->append_child(ax_meta_name_content('DC.title', $title));

			if ($this->_isset('creator'))
				$head->append_child(ax_meta_name_content('DC.creator', $this->_get('creator')));

			if ($this->_isset('description'))
				$head->append_child(ax_meta_name_content('DC.description', $this->_get('description')));

			if ($this->_isset('date'))
				$date = $this->get('date');
			else
				$date = AnewtDateTime::now();

			$head->append_child(ax_meta_name_content('DC.date', AnewtDateTime::date($date)));
		}


		/* Powered by Anewt! */

		$generator = $this->_get('generator');
		if (!is_null($generator))
		{
			assert('is_string($generator);');
			$head->append_child(ax_meta_name_content('generator', $generator));
		}


		/* Robots */

		$robots = $this->_get('robots');
		if (!is_null($robots))
		{
			assert('is_string($robots);');
			$head->append_child(ax_meta(array(
				'name'    => 'robots',
				'content' => $robots,
			)));
		}


		/* Links (including stylesheets) and JavaScripts */

		$head->append_children($this->_links);
		$head->append_children($this->_javascripts);


		/* Favicon */

		$favicon = $this->_get('favicon');
		if (!is_null($favicon))
		{
			assert('is_string($favicon);');
			$head->append_child(ax_link_favicon($favicon));
		}


		/* Body content */

		if ($this->_get('blocks'))
		{
			/* This is a page using div blocks */

			if ($this->_content && $this->_content->has_child_nodes())
				throw new AnewtException('Pages using blocks should not have content outside blocks.');

			/* The buffer holding all content is either a wrapper div or an
			 * invisible fragment (both XML nodes, so the API is the same). */

			if ($this->_get('use-wrapper-div'))
				$buffer = ax_div_id(null, $this->_get('wrapper-div-id'));
			else
				$buffer = ax_fragment();


			/* Add the content */

			foreach ($this->_get('blocks') as $block_name)
			{
				$block = $this->get_block($block_name);
				$buffer->append_child(ax_div_id($block, $block_name));

				unset ($block);
				unset ($div);
			}

			$body->append_child($buffer);

		}
		else
		{
			/* This page has no blocks, so we use the nodes in _content instead
			 * (if any) */

			if ($this->_blocks)
				throw new AnewtException('Pages not using blocks should not have content in blocks');

			if ($this->_content)
				$body->append_child($this->_content);
		}


		/* Assemble the top level elements */

		$document = new AnewtXMLDomDocument();
		$document->set_document_type($this->_get('document-type'));
		$document->set_content_type($this->_get('content-type'));
		$document->set_encoding($this->_get('encoding'));

		$html = new AnewtXMLDomElement('html', array(
			'xmlns'    => 'http://www.w3.org/1999/xhtml',
			'xml:lang' => $this->_get('language'),
			'lang'     => $this->_get('language'),
			));

		$html->append_child($head);
		$html->append_child($body);
		$document->append_child($html);

		return $document;
	}

	/**
	 * Flush this page to the browser.
	 *
	 * This method renders the page into a XHTML string and outputs it to the
	 * browser along with the correct HTTP headers.
	 *
	 * \see AnewtPage::render
	 */
	public function flush()
	{
		header(sprintf('Content-type: %s', $this->build_content_type_charset()));
		echo to_string($this->render()), NL;
	}

	/**
	 * Build a content type and character set string.
	 */
	private function build_content_type_charset()
	{
		return sprintf(
			'%s;charset=%s',
			$this->_get('content-type'),
			$this->_get('encoding')
		);
	}

	/** \} */

	/** \{
	 * \name Array access methods
	 *
	 * AnewtPage offers some syntactic sugar to reduce the amount of code
	 * required to append content to the page. This functionality is for
	 * convenience only and does not offer any additional features over using
	 * AnewtPage::append() and AnewtPage::append_to().
	 *
	 * The array append operator on the AnewtPage instance itself (<code>$page[]
	 * = ...</code>) can be used to append content to the default block. This
	 * means the following two lines of code are equivalent:
	 *
	 * \code
	 * $page->append(ax_p('Some text.'));
	 * $page[] = ax_p('Some text.');
	 * \endcode
	 *
	 * To append content to a specific block (other than the default block), you
	 * may specify the block to append to by using two array operators: the
	 * first to specify the block, and the second to append to it. Those two
	 * lines do exactly the same:
	 *
	 * \code
	 * $page->append_to('some-block', ax_p('Some text.'));
	 * $page['some-block'][] = ax_p('Some text.');
	 * \endcode
	 *
	 * Note that AnewtPage does not offer full array access: only the append
	 * operator can be used! This mean you can only use the array operators
	 * exactly like the examples above! In particular, never use or keep
	 * a reference to the variable obtained from
	 * <code>$page['some-block']</code>; you may only use it for appending
	 * content directly using <code>$page['some-block'][] = ...</code>.
	 */

	/**
	 * \private
	 *
	 * Return the named block so that one can append to it using teh ArrayAccess
	 * interface.
	 *
	 * For internal use only: do not invoke this method directly.
	 *
	 * \param $name
	 */
	public function offsetGet($name)
	{
		if (!array_key_exists($name, $this->_blocks))
			$this->_init_block($name);

		return $this->_blocks[$name];
	}

	/**
	 * \private
	 *
	 * Add content to a block using the ArrayAccess interface.
	 *
	 * For internal use only: do not invoke this method directly.
	 *
	 * \param $name
	 *   The name of the block or null if the <code>[]</code> append operator
	 *   was used.
	 * \param $value
	 *   The content to add.
	 */
	public function offsetSet($name, $value)
	{
		if (!is_null($name))
			throw new AnewtException('Array operators on AnewtPage can only be used to append to blocks.');

		if (is_null($name))
			$this->append($value);
	}

	/**
	 * \private
	 *
	 * Throws an error. (Only needed for ArrayAccess interface.)
	 *
	 * \param $name
	 */
	public function offsetExists($name)
	{
		throw new AnewtException('Array operators on AnewtPage can only be used to append to blocks.');
	}

	/**
	 * \private
	 *
	 * Throws an error. (Only needed for ArrayAccess interface.)
	 *
	 * \param $name
	 */
	public function offsetUnset($name)
	{
		throw new AnewtException('Array operators on AnewtPage can only be used to append to blocks.');
	}

	/** \} */
}

?>
