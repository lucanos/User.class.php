<?php

// Here's an array containing some data to plot
$test_data=array(1,2,4,8,16,32,64,128,256,512, 1028, 2056, 4112, 8224, 16448);

// Here's where we call the chart, and return the encoded chart data
echo "<img src=http://chart.apis.google.com/chart?chtt=".urlencode("Monthly active users")."&cht=lc&chs=600x400&chd=".chart_data($test_data).">";

// And here's the function
function countGraph($maxValue, $number) { 
	$s = "";
	
	for($i = 1; $i < $maxValue; $i++) {
		if(!is_float($maxValue / $i)){
			$s .= $i . "|";
		}
	}
	
	return $s;
}

echo countGraph(100, 2);

function chart_data($values) {

// Port of JavaScript from http://code.google.com/apis/chart/
// http://james.cridland.net/code

// First, find the maximum value from the values given

$maxValue = max($values);

// A list of encoding characters to help later, as per Google's example
$simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
 
$chartData = "s:";
  for ($i = 0; $i < count($values); $i++) {
    $currentValue = $values[$i];

    if ($currentValue > -1) {
    $chartData.=substr($simpleEncoding,61*($currentValue/$maxValue),1);
    }
      else {
      $chartData.='_';
      }
  }


// Return the chart data - and let the Y axis to show the maximum value
return $chartData."&chxt=y&chxl=0:|0|400|" . count($maxValue, 20);
}

?>