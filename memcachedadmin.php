<?php
//$m = new Memcached();
//$m->addServer('localhost', 11211);

//$statsdata = $m->getStats();

//var_dump($statsdata);

function getMemcacheKeys($host = '127.0.0.1', $port = 11211){
        $mem = @fsockopen($host, $port);
        if($mem === FALSE) return -1;
 
        // retrieve distinct slab
        $r = @fwrite($mem, 'stats items' . chr(10));
        if($r === FALSE) return -2;
 
        $slab = array();
        while( ($l = @fgets($mem, 1024)) !== FALSE){
                // sortie ?
                $l = trim($l);
                if($l=='END') break;
 
                $m = array();
                // <STAT items:22:evicted_nonzero 0>
                $r = preg_match('/^STAT\sitems\:(\d+)\:/', $l, $m);
                if($r!=1) return -3;
                $a_slab = $m[1];
 
                if(!array_key_exists($a_slab, $slab)) $slab[$a_slab] = array();
        }

	reset($slab);
        foreach($slab AS $a_slab_key => &$a_slab){
                $r = @fwrite($mem, 'stats cachedump ' . '17' . ' 100' . chr(10));
                if($r === FALSE) return -4;
 
                while( ($l = @fgets($mem, 1024)) !== FALSE){
                        // sortie ?
                        $l = trim($l);
                        if($l=='END') break;
 
                        $m = array();
                        // ITEM 42 [118 b; 1354717302 s]
                        $r = preg_match('/^ITEM\s([^\s]+)\s/', $l, $m);
                        if($r!=1) return -5;
                        $a_key = $m[1];
 
                        $a_slab[] = $a_key;
                }
        }

	// close
        @fclose($mem);
        unset($mem);

	// transform it;
        $keys = array();
        reset($slab);
        foreach($slab AS &$a_slab){
                reset($a_slab);
                foreach($a_slab AS &$a_key) $keys[] = $a_key;
        }
        unset($slab);
 
        return $keys;
}

function getMemcacheSlabs($host = '127.0.0.1', $port = 11211){
        $mem = @fsockopen($host, $port);
        if($mem === FALSE) return -1;
 
        // retrieve distinct slab
        $r = @fwrite($mem, 'stats items' . chr(10));
        if($r === FALSE) return -2;
 
        $slab = array();
        while( ($l = @fgets($mem, 1024)) !== FALSE){
                // sortie ?
                $l = trim($l);
                if($l=='END') break;
 
                $m = array();
                // <STAT items:22:evicted_nonzero 0>
                $r = preg_match('/^STAT\sitems\:(\d+)\:/', $l, $m);
                if($r!=1) return -3;
                $a_slab = $m[1];
 
                if(!array_key_exists($a_slab, $slab)) $slab[$a_slab] = $l;
        }

	// close
        @fclose($mem);
        unset($mem);

        return $slab;
}

function getStats($host = '127.0.0.1', $port = 11211){
        $mem = @fsockopen($host, $port);
        if($mem === FALSE) return -1;
 
        // retrieve distinct slab
        $r = @fwrite($mem, 'stats slabs' . chr(10));
        if($r === FALSE) return -2;
 
        $slab = array();
        while( ($l = @fgets($mem, 1024)) !== FALSE){
                // sortie ?
                $l = trim($l);
                if($l=='END') break;
		array_push($slab, $l);
        }

	// close
        @fclose($mem);
        unset($mem);

        return $slab;
}

//$slabs = getMemcacheSlabs();
//var_dump($slabs);

$keys = getMemcacheKeys();
//var_dump($keys);

//$stats = getStats();
//var_dump($stats);

//var_dump($m->get('member2752181'));
?>

<html ng-app="memcachedApp">
<head>
	<title>Memcached Admin</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.7.0/nv.d3.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.0/angular.min.js"></script>
	<script src="http://d3js.org/d3.v3.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.7.0/nv.d3.js"></script>

	<link href='metricsgraphics.css' rel='stylesheet' type='text/css'>
	<script src='metricsgraphics.min.js'></script>
	<link href='tipsy.css' rel='stylesheet' type='text/css'>
	<script src='jquery.tipsy.js'></script>

	<style>
		body {
			background-color: #e7e7e7;
		}
		.content {
			width: 980px;
			margin-right: auto;
			margin-left: auto;
			background-color: #fff;
			margin-top: -20px;
			padding: 10px;
		}
		table {
			background-color: #fff;
		}
		.head {
			width: 980px;
			margin-right: auto;
			margin-left: auto;
		}
		.stats {
			max-width: 980px;
		}
		.floatright {
			float: right;
		}
		.floatright_container {
			overflow: auto;
			width: 100%
		}
		.dropdown {
			margin-left: 4px;
		}
		.dropdown button {
			margin-left: 4px;
			margin-right: 4px;
		}
		.charts {
			display: flex;
		}
		.askicon {
			padding: 1px;
			margin-top: -1px;
		}
	</style>
</head>
<body ng-controller="mainCtrl">
	<div class="head">
		<img src="/images/mm_980.jpg"></img>
	</div>
	<div class="content">

	<div class="floatright_container">
	<h2 class="floatright">Memcached Admin</h2>
	</div>

	<div class="floatleft_container">
		<select ng-options="option.title for option in dropdown" ng-value="selected" ng-model="selected">
		</select><span class="dropdown"><button type="button" class="btn btn-primary">Set</button><button type="button" class="btn btn-default">Refresh</button></span>
		<h3><b>{{selected.title}}</b></h3>
	</div>

	<h4>Stats</h4>
	<div class="container stats">
		<div class="row">
			<div class="col-md-6">
			<table class="table">
				<tr>
					<td><b class="tipsy-tooltip" title="Current number of bytes used to store items">bytes</b></td>
					<td>{{statsdata['bytes']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Total number of bytes read by this server from network">bytes_read</b></td>
					<td>{{statsdata['bytes_read']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Total number of bytes sent by this server to network">bytes_written</b></td>
					<td>{{statsdata['bytes_written']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Cumulative number of retrieval reqs">cmd_get</b></td>
					<td>{{statsdata['cmd_get']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Cumulative number of storage reqs">cmd_set</b></td>
					<td>{{statsdata['cmd_set']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of connection structures allocated by the server">connection_structures</b></td>
					<td>{{statsdata['connection_structures']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of open connections">curr_connections</b></td>
					<td>{{statsdata['curr_connections']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Current number of items stored">curr_items</b></td>
					<td>{{statsdata['curr_items']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of valid items removed from cache to free memory for new items">evictions</b></td>
					<td>{{statsdata['evictions']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of keys that have been requested and found present">get_hits</b></td>
					<td>{{statsdata['get_hits']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of items that have been requested and not found">get_misses</b></td>
					<td>{{statsdata['get_misses']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of bytes this server is allowed to use for storage.">limit_maxbytes</b></td>
					<td>{{statsdata['limit_maxbytes']}}</td>
				</tr>
			</table>
			</div>
			<div class="col-md-6">
			<table class="table">
				<tr>
					<td><b class="tipsy-tooltip" title="Process id of this server process">pid</b></td>
					<td>{{statsdata['pid']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Default size of pointers on the host OS (generally 32 or 64)">pointer_size</b></td>
					<td>{{statsdata['pointer_size']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Accumulated system time for this process (microseconds)">rusage_system_microseconds</b></td>
					<td>{{statsdata['rusage_system_microseconds']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Accumulated system time for this process (seconds)">rusage_system_seconds</b></td>
					<td>{{statsdata['rusage_system_seconds']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Accumulated user time for this process (microseconds)">rusage_user_microseconds</b></td>
					<td>{{statsdata['rusage_user_microseconds']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Accumulated user time for this process (seconds)">rusage_user_seconds</b></td>
					<td>{{statsdata['rusage_user_seconds']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of worker threads requested.">threads</b></td>
					<td>{{statsdata['threads']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="current UNIX time according to the server">time</b></td>
					<td>{{statsdata['time']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Total number of connections opened since the server started running">total_connections</b></td>
					<td>{{statsdata['total_connections']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Total number of items stored since the server startedn">total_items</b></td>
					<td>{{statsdata['total_items']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Number of secs since the server started">uptime</b></td>
					<td>{{statsdata['uptime']}}</td>
				</tr>
				<tr>
					<td><b class="tipsy-tooltip" title="Version string of this server">version</b></td>
					<td>{{statsdata['version']}}</td>
				</tr>

			</table>
			</div>
		</div>
	</div>
	<!--<h4>Slabs</h4>
	<table class="table table-striped">
		<tr>
			<th>Slab Name</th>
			<th>Slab Size</th>
		</tr>
		<tr ng-repeat="(key, value) in slabs">
			<td>{{key}}</td>
			<td>{{value.size}}</td>
		</tr>
	</table>-->
	<div class="slabdetail">
	<h4>Slab Detail</h4>
	<span class="floatright"><a>Expand</a></span>
	<table class="table table-striped">
		<tr>
			<th></th>
			<th>Name</th>
			<th>chunk_size</th>
			<th>chunks_per_page</th>
			<th>get_hits</th>
			<th>mem_requested</th>
			<th>total_chunks</th>
			<th>total_pages</th>
			<th>used_chunks</th>
		</tr>
		<tr ng-repeat="(key, value) in slabdata" ng-hide="value.name.substring(0,4)!='STAT'">
			<td><button type="button" class="btn btn-success" ng-click="getDump(value.id_num)">Dump</button></td>
			<td>{{value.id_num}}</td>
			<td>{{value.chunk_size}}</td>
			<td>{{value.chunks_per_page}}</td>
			<td>{{value.get_hits}}</td>
			<td>{{value.mem_requested}}</td>
			<td>{{value.total_chunks}}</td>
			<td>{{value.total_pages}}</td>
			<td>{{value.used_chunks}}</td>
		</tr>
	</table>
	</div>
	<div class="charts">
	<div class="piechart"></div>
	<div id="timerserieschart"></div>
	</div>
	<div>
	<form>
	<h4>Get Value from Key or Search by Key</h4>
	<div class="form-group">
	<label for="enterkey">Key: </label>
	<input name="enterkey" type="text" placeholder="Enter Key or Search Term" ng-model="key" ng-value="key"></input>
	<button type="button" class="btn btn-primary" ng-click="getValue()">Retrieve</button>
	<button type="button" class="btn btn-info" ng-click="searchKey()">Search</button>
	<button type="button" class="btn btn-warning" ng-click="clear()">Clear</button>
	</div>
	<h5>Selected Key:<b> {{key}}</b></h5>
	<pre ng-bind-html="valueData">
	</pre>
	<div class="floatright_container">
	<button type="button" class="btn" ng-show="valueData" ng-class="{btn-info: bautoRefresh_value_off, btn-primary: bautoRefresh_value_on}" ng-click="valueDataToggle()">Auto Refresh {{autoRefresh_value_text}}</button>
	<button type="button" class="btn btn-danger floatright">Delete From Cache</button>
	</div>
	</form>
	</div>

	<div ng-show="currentSlabDumpId!=""">
	<div class="floatright">
	<button type="button" class="btn btn-success" ng-click="getDump(currentSlabDumpId.substring(5))">Refresh</button>
	<button type="button" class="btn btn-info">Auto Refresh {{autoRefresh}}</button>
	</div>
	<h4>Dump for {{currentSlabDumpId}}</h4>
	<h5><i>Keys Stats</i></h5>
	<table class="table">
		<tr>
			<th>Trimmed Key Name</th>
			<th>Chunks</th>
			<th>Total Size</th>
			<th>Average Size</th>
		</tr>
		<tr ng-repeat="(key, value) in averageKeys">
			<td>{{key}}</td>
			<td>{{value.counter}}</td>
			<td>{{value.total}} B</td>
			<td>{{(value.total/value.counter)}} B</td>
		</tr>
		<tr><div>* Numerials were not used to uniquely identify these keys.</div></tr>
	</table>
	<table class="table">
		<tr>
			<td><b>Total Chunks</b></td>
			<td>{{totalOcc}}</td>
		</tr>
		<tr>
			<td><b>Total Size</b></td>
			<td>{{totalSize}} B</td>
		</tr>
	</table>
	<h5><i>Dump of Slab</i></h5>
	<table class="table">
		<tr>
			<th>Index</th>
			<th>Key</th>
			<th>Size</th>
			<th>Time</th>
		</tr>
		<tr ng-repeat-start="(key, value) in slabdump" >
			<td>{{key}}</td>
			<td><a ng-click="getValueFromDump(key)">{{value.key}}</a></td>
			<td>{{value.size}}</td>
			<td>{{value.time}}</td>
		</tr>
		<tr ng-repeat-end ng-show="value.html">
			<td colspan="4">
			<pre ng-bind-html="value.html">
			</pre>
			<div class="floatright_container">
			<button type="button" class="btn btn-warning floatright" ng-click="closeKey(key)">Close</button>
			<button type="button" class="btn btn-danger floatright">Delete From Cache</button>
			</div>
			<td>
		</tr>
	</table>
	</div>
	<script>
		var memcachedApp = angular.module('memcachedApp', []);
		memcachedApp.controller('mainCtrl', function($scope, $http, $sce) {
			$scope.statsdata = {};
			$scope.slabs = {};
			$scope.slabdata = {};

			$scope.slabdump = {};
			$scope.currentSlabDumpId = "";

			$scope.averageKeys = {};

			$scope.autoRefresh = "Off";

			$scope.totalOcc = 0;
			$scope.totalSize = 0;

			$scope.dropdown = [
				{title:"ModelMayhem PROD",ip:"98.158.196.55",port:11211},
				{title:"ModelMayhem stg1",ip:"99.999.999.99",port:11211},
				{title:"ModelMayhem stg2",ip:"99.999.999.99",port:11211},
				{title:"ModelMayhem dev-box SeanW",ip:"99.999.999.99",port:11211}
			];
			$scope.selected = $scope.dropdown[3];

			$scope.bautoRefresh_value_off = true;
			$scope.bautoRefresh_value_on = false;
			$scope.autoRefresh_value_text = "Off";
			$scope.valueDataToggle = function() {
				
				$scope.bautoRefresh_value_off = !$scope.bautoRefresh_value_off;
				$scope.bautoRefresh_value_on = !$scope.bautoRefresh_value_on;
				if($scope.bautoRefresh_value_off) $scope.autoRefresh_value_text = "Off";
				if($scope.bautoRefresh_value_on) $scope.autoRefresh_value_text = "On";
			}

			setInterval(function () {
				$http.get('memcachedadmin/getmemcacheddata.php').then(function(data) {
					$scope.statsdata = data.data['localhost:11211'];
				});
			}, 1000);
			/*$http.get('/getmemcachedslabs.php').then(function(data) {
				for(var key in data.data) {
					var item = {};
					var arrItem = data.data[key].split(":");
					item.name = arrItem[1];
					item.size = arrItem[2];
					$scope.slabs[key] = item;
				}
			});*/
			setInterval(function () {
				$http.get('memcachedadmin/getslabdata.php').then(function(data) {
					$scope.slabdata = {};
					for(var key in data.data) {
						
						var item = {};
						var arrItem = data.data[key].split(":");
						if (!$scope.slabdata[arrItem[0]]) {
							$scope.slabdata[arrItem[0]] = {};
						}
						if(arrItem[1]) {
							var fields = arrItem[1].split(" ");
							$scope.slabdata[arrItem[0]][fields[0]] = Number(fields[1]);
							$scope.slabdata[arrItem[0]]['name'] = arrItem[0];
							$scope.slabdata[arrItem[0]]['id_num'] = arrItem[0].substring(5);
						}
					}
					
					var odata = [];
					for(var key in $scope.slabdata) {
						if(Object.keys($scope.slabdata[key]).length > 0) {
							odata.push({"label":$scope.slabdata[key].id_num,"value":Number($scope.slabdata[key].chunk_size*$scope.slabdata[key].used_chunks)});
						}
					}

					    var w = 300,                        //width
					    h = 300,                            //height
					    r = 100,                            //radius
					    color = d3.scale.category20c();     //builtin range of colors

					    d3.select(".piechart").select("svg").remove();
					    var vis = d3.select(".piechart")
						.append("svg:svg")              //create the SVG element inside the <body>
						.data([odata])                   //associate our data with the document
						    .attr("width", w)           //set the width and height of our visualization (these will be attributes of the <svg> tag
						    .attr("height", h)
						.append("svg:g")                //make a group to hold our pie chart
						    .attr("transform", "translate(" + r + "," + r + ")")    //move the center of the pie chart from 0, 0 to radius, radius

					    var arc = d3.svg.arc()              //this will create <path> elements for us using arc data
						.outerRadius(r);

					    var pie = d3.layout.pie()           //this will create arc data for us given a list of values
						.value(function(d) { return d.value; });    //we must tell it out to access the value of each element in our data array

					    var arcs = vis.selectAll("g.slice")     //this selects all <g> elements with class slice (there aren't any yet)
						.data(pie)                          //associate the generated pie data (an array of arcs, each having startAngle, endAngle and value properties) 
						.enter()                            //this will create <g> elements for every "extra" data element that should be associated with a selection. The result is creating a <g> for every object in the data array
						    .append("svg:g")                //create a group to hold each slice (we will have a <path> and a <text> element associated with each slice)
							.attr("class", "slice");    //allow us to style things in the slices (like text)

						arcs.append("svg:path")
							.attr("fill", function(d, i) { return color(i); } ) //set the color for each slice to be chosen from the color function defined above
							.attr("d", arc);                                    //this creates the actual SVG path using the associated data (pie) with the arc drawing function

						arcs.append("svg:text")                                     //add a label to each slice
							.attr("transform", function(d) {                    //set the label's origin to the center of the arc
							//we have to make sure to set these before calling arc.centroid
							d.innerRadius = 0;
							d.outerRadius = r;
							return "translate(" + arc.centroid(d) + ")";        //this gives us a pair of coordinates like [50, 50]
						    })
						    .attr("text-anchor", "middle")                          //center the text on it's origin
						    .text(function(d, i) { return odata[i].label; });        //get the label from our original data array
				});
			}, 1000);
			$scope.getDump = function(id) {
				$scope.currentSlabDumpId = "Slab " + id;
				$scope.slabdump = {};
				$scope.averageKeys = {};
				$scope.totalOcc = 0;
				$scope.totalSize = 0;
				$http.get('memcachedadmin/dumpslab.php?slabid=' + id).then(function(data) {
					for(var key in data.data) {
						if (!$scope.slabdump[key]) {
							$scope.slabdump[key] = {};
						}
						var arrItem = data.data[key].split(" ");
						$scope.slabdump[key]['key'] = arrItem[1];

						// check for special cases
						var keyTemp = "";
						var bSpecial = false;
						if(arrItem[1].substring(0,7)=="credits") bSpecial = true;
						if(arrItem[1].substring(0,12)=="pic_comments") bSpecial = true;
						if(!bSpecial) {
							keyTemp = arrItem[1].replace(/\d+/g,'');
							if(!$scope.averageKeys[keyTemp]){
								$scope.averageKeys[keyTemp] = {};
								$scope.averageKeys[keyTemp].counter = 0;
								$scope.averageKeys[keyTemp].total = 0;
							
							}
						} else {
							if(arrItem[1].substring(0,7)=="credits") keyTemp = "*credits";
							if(arrItem[1].substring(0,12)=="pic_comments") keyTemp = "*pic_comments";
							if(!$scope.averageKeys[keyTemp]) {
								$scope.averageKeys[keyTemp] = {};
								$scope.averageKeys[keyTemp].counter = 0;
								$scope.averageKeys[keyTemp].total = 0;
							}
						}
						$scope.averageKeys[keyTemp].counter++;
						// stats index
						var fstIndex = data.data[key].indexOf("[");
						var sndIndex = data.data[key].indexOf("]");
						var len = sndIndex - fstIndex;

						var arrStat = data.data[key].substring(fstIndex+1,sndIndex).split(';');
						$scope.slabdump[key].size = arrStat[0];
						$scope.slabdump[key].time = arrStat[1];

						$scope.averageKeys[keyTemp].total += Number(arrStat[0].replace(/[b]/,'').trim());
					}

					for(var key in $scope.averageKeys) {
						$scope.totalOcc += $scope.averageKeys[key].counter;
						$scope.totalSize += $scope.averageKeys[key].total;
					}
				});
			}
			$scope.getValue = function() {
				$scope.valueData = "";
				$http.get('memcachedadmin/getValue.php?key=' + $scope.key).then(function(data) {
					$scope.valueData = $sce.trustAsHtml(data.data);
				});
			}
			$scope.searchKey = function() {
				alert($scope.key);
			}
			$scope.getValueFromDump = function(index) {
				/*$scope.valueData = "";
				$scope.key = key;*/
				if($scope.slabdump[index].html) {
					$scope.slabdump[index].html = null;
				} else {
					$http.get('memcachedadmin/getValue.php?key=' + $scope.slabdump[index].key).then(function(data) {
						$scope.slabdump[index].html = $sce.trustAsHtml(data.data);
					});
				}
			}
			$scope.clear = function() {
				$scope.valueData = "";
				$scope.key = "";
			}
			$scope.closeKey = function(index) {
				$scope.slabdump[index].html = null;
			}
		});
		var data = [
			{
			"year": "",
			"sightings": 0
			},
			{
			"year": "",
			"sightings": 1
			},
		];
		MG.data_graphic({
			title: "Slab Stats",
			description: "Slab Memory Usage Over Time",
			data: data,
			width: 650,
			height: 150,
			target: '#timerserieschart',
			x_accessor: 'year',
			y_accessor: 'sightings'
		    });
		 $('.tipsy-tooltip').tipsy({gravity: 'n'});
	</script>
	</div>
</body>
</html>
