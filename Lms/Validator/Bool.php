<?php
class Lms_Validator_Bool {
	
	public static function isOdd($value)
	{
		return (bool)($value % 2);
	}
	
	public static function isEven($value)
	{
		return !(bool)($value % 2);
	}
	
	public static function isGreat($value, $number)
	{
		return $value > $number;
	}
}
?>