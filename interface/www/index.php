<?php

define("PORT", 8024);

class keys
{
	
}

class profiles
{
	private $location = "/Profiles/";
	private $extention = ".txt";
	public $profiles = "<option selected>None</option>";
	
	public function fetch()
	{
		$p = getcwd() . $this->location;
		
		if ( !empty($p) )
		{
			$files = scandir($p);
			
			foreach ( $files as $i )
			{
				if ( !empty($i) && $i != "." && $i != ".." )
				{
					$f = substr($i, 0, -4);
					$this->profiles .= "<option value=\"" . $i . "\">" . $f . "</option>";
				}
			}
		}
		
		if ( empty($this->profiles) )
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function read($input)
	{
		$p = getcwd() . $this->location;
		$file = $p . $input;
		
		if ( file_exists($file) && filesize($file <= 1665) )
		{
			
		}
		else
		{
			return 1;
		}
	}
	
	public function write()
	{
		
	}
}

class process
{
	public $message;
	public $input;
	public $color;
	private $keys;
	private $toSend = "";
	
	public function execute()
	{
		if ( !$this->getVars() )
		{
			return false;
		}
		
		if ( !$this->prepare() )
		{
			return false;
		}
		
		if ( $this->send() )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function getVars()
	{
		if ( empty($_POST["pos"]) )
		{
			$this->message = "No keys set to update";
			return false;
		}
		else
		{
			$this->input = $_POST["pos"];
		}
		
		if ( empty($_POST["color"]) )
		{
			$this->message = "No color given";
			return false;
		}
		else
		{
			$this->color = $_POST["color"];
			return true;
		}
	}
	
	private function prepare()
	{
		$k = null;
		$c = null;
		
		$k = explode(";", $this->input);
		$c = str_replace("#", "", $this->color);
		
		foreach ( $k as $i )
		{
			if ( !empty($i) )
			{
				$this->toSend .= $i . "#" . $c . ";";
			}
		}
		
		if ( !empty($this->toSend) && $this->toSend != "#;" )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function send()
	{
		if ( !$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) )
		{
			$this->message = "Failed to create socket: " . socket_strerror(socket_last_error());
			return false;
		}
		
		if ( !socket_connect($sock, "127.0.0.1", PORT) )
		{
			$this->message = "Failed to connect: " . socket_strerror(socket_last_error());
			return false;
		}
		
		if ( !socket_write($sock, $this->toSend, strlen($this->toSend)) )
		{
			$this->message = "Failed to write data!";
			return false;
		}
		
		socket_close($sock);
		
		$this->message = "Data successfully processed!" . $this->toSend;
		
		return true;
	}
}

$obj = new process();
$profiles = new profiles();

if ( !empty($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST" )
{
	if ( !empty($_POST["load"]) )
	{
		$profiles->read($_POST["load"]);
	}
	else
	{
		$obj->execute();
	}
}

if ( !empty($_GET["positions"]) && $_GET["positions"] == "show" )
{
	$body = "show";
}
else
{
	$body = "hide";
}

$profiles->fetch();

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title>RGB keyboard control center</title>
		<style>
		html
		{
			width: 100%;
			height: 100%;
			background-color: lightgray;
		}
		
		body
		{
			float: left;
			margin: 0;
			padding: 8px;
			font-family: sans-serif;
			font-size: 18px;
			width: min-content;
			box-sizing: border-box;
		}
		
		body p
		{
			width: 300px;
			margin: 8px 0;
		}
		
		body input
		{
			padding: 5px;
		}
		
		body input[type=color]
		{
			background-color: gray;
			border-radius: 10px;
			width: 50px;
			height: 50px;
			border: 0;
		}
		
		body div.row
		{
			float: left;
			width: 1442px;
		}
		
		body div.row div
		{
			width: 66px;
			height: 66px;
			float: left;
			margin: 3px;
			position: relative;
			border-radius: 5px;
			background-color: black;
		}
		
		body div.row div.select
		{
			background-color: #4a4a4a;
		}
		
		body div div.arrow
		{
			background-color: transparent;
			font-size: 50px;
			padding: 0 10px;
			box-sizing: border-box;
		}
		
		body div.row div span
		{
			font-size: 16px;
			color: white;
			margin: 2px;
			text-transform: uppercase;
		}
		
		body div.row div span.big
		{
			font-size: 20px;
			margin: 0;
			margin-top: 8px;
		}
		
		body div.row div span.name, 
		body div.row div span.extra
		{
			width: 100%;
			float: left;
			font-weight: bold;
			text-align: center;
		}
		
		
		body div.row div span.name
		{
			margin-top: 10px;
		}
		body div.row div span.extra
		{
			margin-bottom: 10px;
			color: #808080;
			font-size: 14px;
		}
		
		body div.row div span.pos
		{
			position: absolute;
			visibility: hidden;
			font-size: 12px;
			bottom: 0;
			right: 0;
		}
		
		body.show div div span.pos
		{
			visibility: visible;
		}
		
		body form label
		{
			width: 33%;
/* <div><span><div style="background-color: #4px; */
			margin-top: 25px;
		}
		
		body form label,
		body form label *
		{
			float: left;
		}
		
		body form label div
		{
			width: 100%;
		}
		
		body form label div span
		{
			background: linear-gradient(#acacac, #959595);
			border: black solid 1px;
			border-radius: 5px;
			padding-right: 3px;
			margin-bottom: 2px;
		}
		
		body form label div div
		{
			width: 18px;
			height: 18px;
			margin: 3px;
			border-radius: 4px;
		}
		
		body form label.positions span
		{
			font-size: 26px;
			font-weight: bold;
		}
		
		#backsp
		{
			width: 138px;
		}
		
		#tab
		{
			width: 98px;
		}
		
		#enter1
		{
			width: 106px;
		}
		
		#enter2
		{
			width: 88px;
			height: 78px;
			margin-top: -10px;
		}
		
		#pad-plus2, #pad-enter2
		{
			height: 78px;
			margin-top: -10px;
		}
		
		#caps
		{
			width: 116px;
		}
		
		#lShift, #lCtrl
		{
			width: 82px;
		}
		
		#rShift, #rCtrl
		{
			width: 121px;
		}
		
		#space
		{
			width: 354px;
		}
		</style>
		<script type="text/javascript">

		function updateKey(key, div)
		{
			var obj = document.getElementById("keyInput");
			var str = obj.value;

			key += ";";

			if ( str.search(key) == -1 )
			{
				obj.value += key;
			}
			else
			{
				obj.value = str.replace(key, "");
			}

			if ( key == "087;" )
			{
				document.getElementById("enter1").classList.toggle("select");
				document.getElementById("enter2").classList.toggle("select");
			}
			else if ( key == "109;" )
			{
				document.getElementById("pad-enter1").classList.toggle("select");
				document.getElementById("pad-enter2").classList.toggle("select");
			}
			else if ( key == "111;" )
			{
				document.getElementById("pad-plus1").classList.toggle("select");
				document.getElementById("pad-plus2").classList.toggle("select");
			}
			else
			{
				div.classList.toggle("select");
			}
		}

		function updateRow(div, row)
		{
			var obj = document.getElementById("keyInput");
			var names = [];
			var keys = [];
			var remove;
			var item;
			
			if ( div.classList.contains("on") )
			{
				div.classList.remove("on");
				remove = true;
			}
			else
			{
				div.classList.add("on");
				remove = false;
			}
			
			switch (row)
			{
				case 0:
					names = ["esc", "f1", "f2", "f3", "f4", "f5", "f6", "f7", "f8", "f9", "f10", "f11", "f12", "insrt", "del", "home", "end", "pgup", "pgdn"];
					keys = ["005", "011", "017", "023", "029", "035", "041", "047", "053", "059", "065", "071", "083", "077", "089", "095", "101", "113", "107"];
					break;
				case 1:
					names = ["tilde", "n1", "n2", "n3", "n4", "n5", "n6", "n7", "n8", "n9", "n0", "minus", "plus", "backsp", "num", "pad-slash", "pad-asterisk", "pad-minus"];
					keys = ["004", "010", "016", "022", "028", "034", "040", "046", "052", "058", "064", "070", "076", "088", "094", "100", "106", "112"];
					break;
				case 2:
					names = ["tab", "Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P", "lSquare", "rSquare", "enter1", "enter2", "p7", "p8", "p9", "pad-plus1", "pad-plus2"];
					keys = ["003", "015", "021", "027", "033", "039", "045", "051", "057", "063", "069", "075", "081", "087", "093", "099", "105", "111"];
					break;
				case 3:
					names = ["caps", "A", "S", "D", "F", "G", "H", "J", "K", "L", "semicolon", "apostrophe", "backslash", "p4", "p5", "p6"];
					keys = ["002", "014", "020", "026", "032", "038", "044", "050", "056", "062", "068", "074", "080", "092", "098", "104"];
					break;
				case 4:
					names = ["lShift", "less-than", "Z", "X", "C", "V", "B", "N", "M", "comma", "period", "question", "rShift", "up", "p1", "p2", "p3", "pad-enter1", "pad-enter2"];
					keys = ["001", "013", "019", "025", "031", "037", "043", "049", "055", "061", "067", "073", "079", "085", "091", "097", "103", "109"];
					break;
				case 5:
					names = ["lCtrl", "fn", "super", "lAlt", "space", "rAlt", "menu", "rCtrl", "left", "down", "right", "p0", "pDel"];
					keys = ["000", "012", "018", "024", "042", "060", "066", "072", "078", "084", "090", "096", "102"];
					break;
				default:
					return;
			}

			for ( x = 0; x < keys.length; x++ )
			{
				item = keys[x] + ";";

				if ( remove )
				{
					if ( obj.value.search(item) != -1 )
					{
						obj.value = obj.value.replace(item, "");
					}
				}
				else
				{
					obj.value += item;
				}
			}
			
			for ( x = 0; x < names.length; x++ )
			{
				item = names[x];

				if ( remove )
				{
					document.getElementById(item).classList.remove("select");
				}
				else
				{
					document.getElementById(item).classList.add("select");
				}
			}
		}

		function updateColor(clr)
		{
			document.getElementById("colorSelect").value = clr;
		}

		function Positions()
		{
			var obj = document.getElementById("keyInput");

			obj.value = "<?php echo $obj->input; ?>";
		}

		function invert(input)
		{
			if ( input == true )
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		</script>
	</head>
	<body class="<?php echo $body; ?>">
		<div class="switch" onclick="positions();">Show positions</div>
		<p>Select key(s): </p>
		<p style="color: red;"><?php if ( !empty($obj->message) ) { echo $obj->message; } ?></p>
		<div id="row1" class="row">
			<?php $p = "005"; ?><div id="esc" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">ESC</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "011"; ?><div id="f1" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F1</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "017"; ?><div id="f2" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F2</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "023"; ?><div id="f3" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F3</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "029"; ?><div id="f4" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F4</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "035"; ?><div id="f5" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F5</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "041"; ?><div id="f6" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F6</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "047"; ?><div id="f7" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F7</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "053"; ?><div id="f8" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F8</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "059"; ?><div id="f9" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F9</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "065"; ?><div id="f10" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F10</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "071"; ?><div id="f11" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F11</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "077"; ?><div id="f12" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F12</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "083"; ?><div id="insrt" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">INSERT</span><span class="extra">PRT&nbsp;SC</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "089"; ?><div id="del" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">DEL</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "095"; ?><div id="home" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">HOME</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "101"; ?><div id="end" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">END</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "107"; ?><div id="pgup" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">PGUP</span><span class="extra">PAUSE</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "113"; ?><div id="pgdn" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">PGDN</span><span class="extra">BREAK</span><span class="pos"><?php echo $p; ?></span></div>
			<div class="arrow" onclick="updateRow(this, 0);">←</div>
		</div>
		<div id="row2" class="row">
			<?php $p = "004"; ?><div id="tilde" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#96;&nbsp;&nbsp;&nbsp;&#126;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "010"; ?><div id="n1" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#49;&nbsp;&nbsp;&nbsp;&#33;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "016"; ?><div id="n2" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#50;&nbsp;&nbsp;&nbsp;&#64;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "022"; ?><div id="n3" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#51;&nbsp;&nbsp;&nbsp;&#35;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "028"; ?><div id="n4" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#52;&nbsp;&nbsp;&nbsp;&#36;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "034"; ?><div id="n5" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#53;&nbsp;&nbsp;&nbsp;&#37;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "040"; ?><div id="n6" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#54;&nbsp;&nbsp;&nbsp;&#94;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "046"; ?><div id="n7" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#55;&nbsp;&nbsp;&nbsp;&#38;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "052"; ?><div id="n8" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#56;&nbsp;&nbsp;&nbsp;&#42;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "058"; ?><div id="n9" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#57;&nbsp;&nbsp;&nbsp;&#40;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "064"; ?><div id="n0" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#48;&nbsp;&nbsp;&nbsp;&#41;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "070"; ?><div id="minus" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#45;&nbsp;&nbsp;&nbsp;&#8213;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "076"; ?><div id="plus" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#61;&nbsp;&nbsp;&nbsp;&#43;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "088"; ?><div id="backsp" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#10229;</span><span class="extra">BACKSPACE</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "094"; ?><div id="num" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name"></span><span class="extra">NUM&nbsp;LK</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "100"; ?><div id="pad-slash" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#47;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "106"; ?><div id="pad-asterisk" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#42;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "112"; ?><div id="pad-minus" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#45;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<div class="arrow" onclick="updateRow(this, 1);">←</div>
		</div>
		<div id="row3" class="row">
			<?php $p = "003"; ?><div id="tab" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#8633;</span><span class="extra">TAB</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "015"; ?><div id="Q" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">Q</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "021"; ?><div id="W" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">W</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "027"; ?><div id="E" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">E</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "033"; ?><div id="R" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">R</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "039"; ?><div id="T" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">T</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "045"; ?><div id="Y" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">Y</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "051"; ?><div id="U" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">U</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "057"; ?><div id="I" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">I</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "063"; ?><div id="O" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">O</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "069"; ?><div id="P" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">P</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "075"; ?><div id="lSquare" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#91;&nbsp;&nbsp;&nbsp;&#123;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "081"; ?><div id="rSquare" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#93;&nbsp;&nbsp;&nbsp;&#125;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "087"; ?><div id="enter1" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&crarr;</span><span class="extra">ENTER</span></div>
			<?php $p = "093"; ?><div id="p7" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#55;</span><span class="extra">HOME</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "099"; ?><div id="p8" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#56;</span><span class="extra">&#11205;</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "105"; ?><div id="p9" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#57;</span><span class="extra">PGUP</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "111"; ?><div id="pad-plus1" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#43;</span><span class="extra"></span></div>
			<div class="arrow" onclick="updateRow(this, 2);">←</div>
		</div>
		<div id="row4" class="row">
			<?php $p = "002"; ?><div id="caps" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name"></span><span class="extra">CAPS&nbsp;LK</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "014"; ?><div id="A" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">A</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "020"; ?><div id="S" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">S</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "026"; ?><div id="D" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">D</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "032"; ?><div id="F" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">F</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "038"; ?><div id="G" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">G</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "044"; ?><div id="H" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">H</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "050"; ?><div id="J" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">J</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "056"; ?><div id="K" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">K</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "062"; ?><div id="L" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">L</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "068"; ?><div id="semicolon" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#59;&nbsp;&nbsp;&nbsp;&#58;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "074"; ?><div id="apostrophe" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#39;&nbsp;&nbsp;&nbsp;&#34;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "080"; ?><div id="backslash" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#92;&nbsp;&nbsp;&nbsp;&#124;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "087"; ?><div id="enter2" onclick="updateKey('<?php echo $p; ?>', this);"><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "092"; ?><div id="p4" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#52;</span><span class="extra">&#11207;</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "098"; ?><div id="p5" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#53;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "104"; ?><div id="p6" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#54;</span><span class="extra">&#11208;</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "111"; ?><div id="pad-plus2" onclick="updateKey('<?php echo $p; ?>', this);"><span class="pos"><?php echo $p; ?></span></div>
			<div class="arrow" onclick="updateRow(this, 3);">←</div>
		</div>
		<div id="row5" class="row">
			<?php $p = "001"; ?><div id="lShift" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&#8679;</span><span class="extra">SHIFT</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "013"; ?><div id="less-than" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#60;&nbsp;&nbsp;&nbsp;&#62;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "019"; ?><div id="Z" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">Z</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "025"; ?><div id="X" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">X</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "031"; ?><div id="C" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">C</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "037"; ?><div id="V" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">V</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "043"; ?><div id="B" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">B</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "049"; ?><div id="N" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">N</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "055"; ?><div id="M" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">M</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "061"; ?><div id="comma" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#44;&nbsp;&nbsp;&nbsp;&#60;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "067"; ?><div id="period" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#46;&nbsp;&nbsp;&nbsp;&#62;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "073"; ?><div id="question" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#47;&nbsp;&nbsp;&nbsp;&#63;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "079"; ?><div id="rShift" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&#8679;</span><span class="extra">SHIFT</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "085"; ?><div id="up" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&#8657;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "091"; ?><div id="p1" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#49;</span><span class="extra">END</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "097"; ?><div id="p2" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#50;</span><span class="extra">&#11206;</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "103"; ?><div id="p3" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#51;</span><span class="extra">PGDN</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "109"; ?><div id="pad-enter1" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">ENTER</span><span class="extra"></span></div>
			<div class="arrow" onclick="updateRow(this, 4);">←</div>
		</div>
		<div id="row6" class="row">
			<?php $p = "000"; ?><div id="lCtrl" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">CTRL</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "012"; ?><div id="fn" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">FN</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "018"; ?><div id="super" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">SUPER</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "024"; ?><div id="lAlt" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">ALT</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "042"; ?><div id="space" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#9472;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "060"; ?><div id="rAlt" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">ALT</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "066"; ?><div id="menu" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">MENU</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "072"; ?><div id="rCtrl" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">CTRL</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "078"; ?><div id="left" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&#8656;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "084"; ?><div id="down" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&#8659;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "090"; ?><div id="right" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name big">&#8658;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "096"; ?><div id="p0" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#48;</span><span class="extra"></span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "102"; ?><div id="pDel" onclick="updateKey('<?php echo $p; ?>', this);"><span class="name">&#46;</span><span class="extra">DEL</span><span class="pos"><?php echo $p; ?></span></div>
			<?php $p = "109"; ?><div id="pad-enter2" onclick="updateKey('<?php echo $p; ?>', this);"><span class="pos"><?php echo $p; ?></span></div>
			<div class="arrow" onclick="updateRow(this, 5);">←</div>
		</div>
		<form method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">
			<label>
				<span>Select preset color:</span>
				<?php $p = "#FF0000"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Red (<?php echo $p; ?>)</span></div>
				<?php $p = "#00FF00"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Green (<?php echo $p; ?>)</span></div>
				<?php $p = "#0000FF"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Blue (<?php echo $p; ?>)</span></div>
				<?php $p = "#00FFFF"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Teal (<?php echo $p; ?>)</span></div>
				<?php $p = "#FF00FF"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Purple (<?php echo $p; ?>)</span></div>
				<?php $p = "#FF0077"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Pink (<?php echo $p; ?>)</span></div>
				<?php $p = "#FF7700"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Yellow (<?php echo $p; ?>)</span></div>
				<?php $p = "#FFFFFF"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>White (<?php echo $p; ?>)</span></div>
				<?php $p = "#FF1C00"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Orange (<?php echo $p; ?>)</span></div>
				<?php $p = "#808000"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Olive (<?php echo $p; ?>)</span></div>
				<?php $p = "#800000"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Maroon (<?php echo $p; ?>)</span></div>
				<?php $p = "#A52A2A"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Brown (<?php echo $p; ?>)</span></div>
				<?php $p = "#808080"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Gray (<?php echo $p; ?>)</span></div>
				<?php $p = "#87CEEB"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Skyblue (<?php echo $p; ?>)</span></div>
				<?php $p = "#000080"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Navy (<?php echo $p; ?>)</span></div>
				<?php $p = "#DC143C"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Crimson (<?php echo $p; ?>)</span></div>
	 			<?php $p = "#006400"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Darkgreen (<?php echo $p; ?>)</span></div>
                <?php $p = "#90EE90"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Lightgreen (<?php echo $p; ?>)</span></div>
                <?php $p = "#FFD700"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Gold (<?php echo $p; ?>)</span></div>
                <?php $p = "#EE82EE"; ?><div><span onclick="updateColor('<?php echo $p; ?>');"><div style="background-color: <?php echo $p; ?>;"></div>Violet (<?php echo $p; ?>)</span></div>
			</label>
			<label class="positions">
				<input id="keyInput" name="pos" type="text">
				<span onclick="Positions();" style="">&#10227;</span>
			</label>
			
			<label style="height: 34px;">
				<span>Save profile</span>
				
			</label>
			<label>
				<span>Load profile</span>
				<select name="load">
					<?php if ( !empty($profiles) && !empty($profiles->profiles) ) { echo $profiles->profiles; } ?>
				</select>
			</label>
			<label>
				<span style="width: 100%;">Select color:</span>
				<input id="colorSelect" name="color" type="color" value="<?php if ( !empty($obj) && !empty($obj->color) ) { echo "#" . $obj->color; } else { echo "#CCCCCC"; } ?>">
			</label>
			<label>
				<input type="submit" value="Submit">
			</label>
		</form>
	</body>
</html>
