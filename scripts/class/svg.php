<?php

class Svg {
	var $dom = null;

	function load($path) {
		return $this->dom->load($path);
	}
	
	function save($path) {
		return $this->dom->save($path);
	}

	function findElementById($parent, $id) {
		for($node = $parent->firstChild; $node; $node = $node->nextSibling) {
			if ($node->nodeType == XML_ELEMENT_NODE) {
				if ($node->getAttribute("id") == $id)
					return $node;

				$result = $this->findElementById($node, $id);
				if ($result)
					return $result; 
			}
		}
		return false;
	}

	function getElementById($id) {
		return $this->findElementById($this->dom->documentElement, $id);
	}

	function __construct() {
		$this->dom = new DOMDocument();
	}
}
