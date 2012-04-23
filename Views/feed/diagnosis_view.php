<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->

<?php

  $name = $feed[1];
  $tag = $feed[2];
  
  //colors
  $red = "rgb(255,125,20)";
  $orange = "rgb(240,180,20)";
  $green = "rgb(50,200,50)";

?>

<div class='lightbox' style="margin-bottom:20px; margin-left:3%; margin-right:3%;">
<h2><?php echo $name; ?></h2>
<h3>Day comparison</h3>
<div style='color:
<?php
	if ($diagnosis['day'] > 0)
	{
		if($diagnosis['day'] > 0.20)
			echo $red . ";'>+";
		else echo $orange . ";'>+";
	}
	else echo $green . ";'>+";
?><?php echo number_format ($diagnosis['day'] * 100, 1) . "%"; ?></div>
<h3>Month comparison</h3>
<div style='color:
<?php
	if ($diagnosis['month'] > 0)
	{
		if($diagnosis['month'] > 0.20)
			echo $red . ";'>+";
		else echo $orange . ";'>+";
	}
	else echo $green . ";'>+";
?><?php echo number_format ($diagnosis['month'] * 100, 1) . "%"; ?></div>
<h3>Year comparison</h3>
<div style='color:
<?php
	if ($diagnosis['year'] > 0)
	{
		if($diagnosis['year'] > 0.20)
			echo $red . ";'>+";
		else echo $orange . ";'>+";
	}
	else echo $green . ";'>+";
?><?php echo number_format ($diagnosis['year'] * 100, 1) . "%"; ?></div>
</div>

