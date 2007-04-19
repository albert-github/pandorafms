<?php
// Pandora - the Free monitoring system
// ====================================
// Copyright (c) 2004-2006 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2005-2006 Artica Soluciones Tecnologicas S.L, info@artica.es
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com
// Javascript Active Console code.
// Copyright (c) 2006 Jose Navarro <contacto@indiseg.net>
// Additions to Pandora FMS 1.2 graph code and new XML reporting template management

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Global & session management
include ("../include/config.php");
session_start();

include ("../include/functions.php");
include("../include/functions_db.php");
include("../include/languages/language_".$language_code.".php");

// Access control
if (comprueba_login() != 0) {
	$REMOTE_ADDR = getenv ("REMOTE_ADDR");
	audit_db("Unknown",$REMOTE_ADDR, "ACL Violation","Trying to access graph window without auth");
	require ("general/noaccess.php");
	exit;
}
	
// Parsing the refresh before sending any header
if (isset($_GET['refresh']) and is_numeric($_GET['refresh']) and $_GET['refresh']>0) {
	header( 'refresh: ' . $_GET['refresh'] );
	}
// Read styles
echo '<style>';
include("../include/styles/pandora.css");
echo '</style>';

// Get input parameters

if (isset($_GET["label"]))
	$label = entrada_limpia($_GET["label"]);
	
if (!isset($_GET["period"]) OR (!isset($_GET["id"]))) {
	echo "<h3 class='error'>".$lang_label["graf_error"]."</h3>";
	exit;
}
if (isset($_GET["draw_events"]))
	$draw_events = entrada_limpia($_GET["draw_events"]);
else
	$draw_events = 0;

if (isset($_GET["period"]))
	$period = entrada_limpia($_GET["period"]);
else
	$period = 3600; // 1 hour (the most fast query possible)

switch ($period) {
	case 3600: 	$period_label = "Hour";
			break;
	case 21600: 	$period_label = "6 Hours";
			break;
	case 43200: 	$period_label = "12 Hours";
			break;
	case 86400: 	$period_label = "Day";
			break;
	case 172800: 	$period_label = "Two days";
			break;
	case 604800: 	$period_label = "Last Week";
			break;
	case 1296000: 	$period_label = "15 Days";
			break;
	case 2592000: 	$period_label = "Last Month";
			break;
	case 5184000: 	$period_label = "Two Month";
			break;
	case 15552000: 	$period_label = "Six Months";
			break;
	default: 	$period_label = "--";
}
	
if (isset($_GET["draw_alerts"]))
	$draw_alerts = entrada_limpia($_GET["draw_alerts"]);
else
	$draw_alerts = 0;
if (isset($_GET["refresh"]))
	$refresh = entrada_limpia($_GET["refresh"]);
else
	$refresh = 0;
if (isset($_GET["period"]))
	$period = entrada_limpia($_GET["period"]);
else
	$period = 86400; // 1 day default period
if (isset($_GET["id"]))
	$id = entrada_limpia($_GET["id"]);
else
	$id = 0;
if (isset($_GET["width"]))
	$width = entrada_limpia($_GET["width"]);
else
	$width = 525;
if (isset($_GET["height"]))
	$height = entrada_limpia ($_GET["height"]);
else
	$height = 220;

if (isset($_GET["label"]))
	$label = entrada_limpia ($_GET["label"]);
else
	$label = "";

if (isset($_GET["zoom"])){
	$zoom = entrada_limpia ($_GET["zoom"]);
	$height=$height*$zoom;
	$width=$width*$zoom;
}
else
	$zoom = "1";

echo "<img src='fgraph.php?tipo=sparse&draw_alerts=$draw_alerts&draw_events=$draw_events&id=$id&zoom=$zoom&label=$label&height=$height&width=$width&period=$period' border=0 alt=''>";

?>

<script type='text/javascript' src='../operation/active_console/scripts/x_core.js'></script>
<script type='text/javascript' src='../operation/active_console/scripts/x_event.js'></script>
<script type='text/javascript' src='../operation/active_console/scripts/x_slide.js'></script>
<style type='text/css'><!--

.menu {
	color:#000; background:#ccc;
	margin-left: 10px;
	padding-left: 10px; padding-top: 10px;
	font-family:arial,sans-serif,verdana; font-size:10px;
	border:1px solid #000;
	position:absolute;
	margin:0; width:325px; height:220px;
	visibility:hidden;
	filter:alpha(opacity=95);
	-moz-opacity: 0.95;
	opacity: 0.95;
}

--></style>


<script type='text/javascript'><!--
	var defOffset = 2;
	var defSlideTime = 200;
	var tnActive = 0;
	var visibleMargin = 3;
	var menuW = 325;
	var menuH = 220;
	window.onload = function() {
		var d;
		d = xGetElementById('divmenu');
		d.termNumber = 1;
		xMoveTo(d, visibleMargin - menuW, 0);
		xShow(d);
		xAddEventListener(document, 'mousemove', docOnMousemove, false);
	}
	
	function docOnMousemove(evt) {
		var e = new xEvent(evt);
		var d = getTermEle(e.target);
		if (!tnActive) { // no def is active
			if (d) { // mouse is over a term, activate its def
				xSlideTo('divmenu', 0, xPageY(d), defSlideTime);
				tnActive = 1;
			}
		}
		else { // a def is active
			if (!d) { // mouse is not over a term, deactivate active def
				xSlideTo('divmenu', visibleMargin - menuW, xPageY(d), defSlideTime);
				tnActive = 0;
			}
		}
	}
	
	function getTermEle(ele) {
		//window.status = ele;
  		while(ele && !ele.termNumber) {
    			if (ele == document) return null;
    			ele = xParent(ele);
  		}
  		return ele;
	}
//-->
</script>
</head>
<body>

<div id='divmenu' class='menu'>
	<b>Pandora FMS Graph configuration menu</b><br>Please, make your changes and apply with <i>Reload</i> button

	<form method='get' action='stat_win.php'>
	<?php
	echo "<input type='hidden' name='id' value='$id'>";
	echo "<input type='hidden' name='label' value='$label'>";
	?>
		<TABLE class='databox_frame' cellspacing=5>
		</td><td>
			<?php
			echo "<tr><td>";
			echo "Refresh time (sec)";
			echo "<td>";
			echo "<input type='text' size=5 name='refresh' value='" . $refresh . "'>";
			echo "<tr><td>";
			echo "Zoom factor (1x)";
			echo "<td>";
			echo "<select name=zoom>";
			echo "<option value='$zoom'>"."x".$zoom;
			echo "<option value='1'>"."x1";
			echo "<option value='2'>"."x2";
			echo "<option value='3'>"."x3";
			echo "<option value='4'>"."x4";
			echo "</select>";

			echo "<tr><td>";
			echo "Range of time";
			echo "<td>";
			echo "<select name='period'>";
			echo "<option value=$period>".$period_label;
			echo "<option value=3600>"."Hour";
			echo "<option value=21600>"."6 Hours";
			echo "<option value=43200>"."12 Hours";
			echo "<option value=86400>"."Last day";
			echo "<option value=172800>"."Two days";
			echo "<option value=604800>"."Last Week";
			echo "<option value=1296000>"."15 days";
			echo "<option value=2592000>"."Last Month";
			echo "<option value=5184000>"."Two Month";
			echo "<option value=15552000>"."Six Months";
			echo "</select>";
			
			echo "<tr><td>";
			echo "Show events ";
			echo "<td>";
			if ($draw_events == 1)
				echo "<input type='checkbox' name='draw_events' CHECKED  value=1>";
			else
				echo "<input type='checkbox' name='draw_events'  value=1>";
			
			echo "<tr><td>";
			echo "Show alert ";
			echo "<td>";
			if ($draw_alerts == 1)
				echo "<input type='checkbox' name='draw_alerts' value=1  CHECKED>";
			else
				echo "<input type='checkbox' name='draw_alerts' value=1>";
		
			echo "<td>";
			echo "<input type='submit' class='sub next' value='GO'>";
			?>
		</td></tr>
		</table>
	</form>
	
</div>


