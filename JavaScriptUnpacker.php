<?php
class JavaScriptUnpacker
{
	private $unbaser;
	private $payload;
	private $symtab;
	private $radix;
	private $count;
	
	function Detect($source)
	{
		$source = preg_replace("/ /","",$source);
		preg_match("/eval\(function\(p,a,c,k,e,[r|d]?/", $source, $res);
		
		Debug::Write($res,"detection result");
		
		return (count($res) > 0);
	}
	
	function Unpack($source)
	{
		preg_match_all("/}\('(.*)', *(\d+), *(\d+), *'(.*?)'\.split\('\|'\)/",$source,$out);
		
		Debug::Write($out,"DOTALL", false);
		
		// Payload
		$this->payload = $out[1][0];
		Debug::Write($this->payload,"payload");
		// Words
		$this->symtab = preg_split("/\|/",$out[4][0]); 
		Debug::Write($this->symtab,"symtab");
		// Radix
		$this->radix = (int)$out[2][0];
		Debug::Write($this->radix,"radix");
		// Words Count
		$this->count = (int)$out[3][0];
		Debug::Write($this->count,"count");
		
		if( $this->count != count($this->symtab)) return; // Malformed p.a.c.k.e.r symtab !
		
		//ToDo: Try catch
		$this->unbaser = new Unbaser($this->radix);
		
		$result = preg_replace_callback(
					'/\b\w+\b/',
						array($this, 'Lookup')
					,
					$this->payload
				);
		$result = str_replace('\\', '', $result);
		Debug::Write($result);
		$this->ReplaceStrings($result);
		return $result;
	}
	
	function Lookup($matches)
	{
		$word = $matches[0];
		$ub = $this->symtab[$this->unbaser->Unbase($word)];
		$ret = !empty($ub) ? $ub : $word;
		return $ret;
	}

	function ReplaceStrings($source)
	{
		preg_match_all("/var *(_\w+)\=\[\"(.*?)\"\];/",$source,$out);
		Debug::Write($out);
	}
	
}

class Unbaser
{
	private $base;
	private $dict;
	private $selector = 52;
	private $ALPHABET = array(
		52 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP',
		54 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQR',
		62 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		95 => ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~'
	);
	
	
	function __construct($base)
	{
		$this->base = $base;
		
		if($this->base > 62) $this->selector = 95;
		else if($this->base > 54) $this->selector = 62;
		else if($this->base > 52) $this->selector = 54;
	}
	
	function Unbase($val)
	{
		if( 2 <= $this->base && $this->base <= 36)
		{
			return intval($val,$this->base);
		}else{
			if(!isset($this->dict)){
				
				$this->dict = array_flip(str_split($this->ALPHABET[$this->selector]));
			}
			$ret = 0;
			$valArray = array_reverse(str_split($val));
			
			for($i = 0; $i < count($valArray) ; $i++)
			{
				$cipher = $valArray[$i];
				$ret += pow($this->base, $i) * $this->dict[$cipher];
			}
			return $ret;
			// UnbaseExtended($x, $base)
		}
	}
	
}


class Debug
{
	public static $debug = false;
	public static function Write($data, $header = "", $mDebug = true)
	{
		if(!self::$debug || !$mDebug) return;
		
		if(!empty($header))
			echo "<h4>".$header."</h4>";
			
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

}
?>