<?php
class filter_guillemet extends moodle_text_filter {
	public function filter($text, array $options = array()) {
		return str_replace('{{', '«', str_replace('}}', '»', $text));
	}
}
?>