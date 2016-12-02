<?php

class Colours {
	public function __construct(){}
	public static function BLACK		() { return chr(27) . "[0;30m"; }
	public static function DARK_GRAY	() { return chr(27) . "[1;30m"; }
	public static function BLUE			() { return chr(27) . "[0;34m"; }
	public static function LIGHT_BLUE	() { return chr(27) . "[1;34m"; }
	public static function GREEN		() { return chr(27) . "[0;32m"; }
	public static function LIGHT_GREEN	() { return chr(27) . "[1;32m"; }
	public static function CYAN			() { return chr(27) . "[0;36m"; }
	public static function LIGHT_CYAN	() { return chr(27) . "[1;36m"; }
	public static function RED			() { return chr(27) . "[0;31m"; }
	public static function LIGHT_RED	() { return chr(27) . "[1;31m"; }
	public static function PURPLE		() { return chr(27) . "[0;35m"; }
	public static function LIGHT_PURPLE	() { return chr(27) . "[1;35m"; }
	public static function BROWN		() { return chr(27) . "[0;33m"; }
	public static function YELLOW		() { return chr(27) . "[1;33m"; }
	public static function LIGHT_GRAY	() { return chr(27) . "[0;37m"; }
	public static function WHITE		() { return chr(27) . "[1;37m"; }
	public static function RESET		() { return chr(27) . "[0m"   ; }

	public static function LOGGING		() { return Colours::LIGHT_RED(); }
}
?>
