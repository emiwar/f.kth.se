<?php
function get_mottagningen($startDate='2012-04-14', 
                                   $endDate='2012-04-22') 
{
	load_fadderiet_zend();
	$gdataCal = new Zend_Gdata_Calendar();
	$query = $gdataCal->newEventQuery();
	$query->setUser('f38rss95bnpb6hpcl4r72igb7o@group.calendar.google.com');
	$query->setVisibility('public');
	$query->setProjection('full');
	$query->setOrderby('starttime');
	$query->setStartMin($startDate);
    	$query->setStartMax($endDate);
	$query->maxResults = '100';
	$query->sortorder = 'ascending'; 
 
// Retrieve the event list from the calendar server
try {
    $eventFeed = $gdataCal->getCalendarEventFeed($query);
} catch (Zend_Gdata_App_Exception $e) {
    echo "Error: " . $e->getMessage();
}
 return $eventFeed;
}

function load_fadderiet_zend(){
	$optionarray_def = get_option('google_calendar_sync_options');
	$loader_path = $optionarray_def['zend_path'];
	require_once $loader_path;
	Zend_Loader::loadClass('Zend_Gdata');
	Zend_Loader::loadClass('Zend_Gdata_Calendar');
}
$klasser = array(
		array('pub'),
		array('matte'),
		array('lunch', 'framtiden', 'tiderna'),
		array('info'),
		array('fest','gasque','banquette','draque','efterkör'),
		array('tiderna'),
		array('obl','inskrivning','upprop'));
function get_mottagning_klass($titel){
	global $klasser;
	foreach ($klasser as $klass){
		foreach ($klass as $ord){
			if(strlen(stristr($titel,$ord))>0){
				return $klass[0];
			}
		}
	}
	return false;
}

$eventswalker = 0;
function get_mottagningen_day($events,$date){
	global $eventswalker;
	global $kal;
	while($events[$eventswalker]){
		
		$startstr = strtotime($events[$eventswalker]->when[0]->startTime);
		$endstr = strtotime($events[$eventswalker]->when[0]->endTime);
		$duration = ($endstr - $startstr)/3600;
		if(date('j',$startstr) != $date){//no events today or this event is not today
			break;
		}

		?>
		
		<div id="eventnr-<?php echo $eventswalker; ?>" style="top:<?php echo (date('G',$startstr)+date('i',$startstr)/60 - $kal['start'])*$kal['helh']; ?>px;" class="chip <?php echo get_mottagning_klass($events[$eventswalker]->title); ?>">
		<dl style="height:<?php echo ($duration*$kal['helh'] - 3); ?>px;" class="cbrd">
			<!--<dt><?php echo date('H:i',$startstr); ?> &ndash; <?php echo date('H:i',$endstr); ?></dt>-->
			<dd><span class="kal-beskrivning"><?php echo $events[$eventswalker]->title; ?></span><br /><span class="kal-plats"><?php echo $events[$eventswalker]->where[0]->valueString; ?></span></dd>
		</dl>
		<div class="bubble-info" style="display:none;">
			<div class="description"><?php echo $events[$eventswalker]->content; ?></div>
			<div class="full-date"><?php echo utf8_encode(strftime('%A %e %B %G, %H:%M',$startstr)).' &ndash; '.date('H:i',$endstr); ?></div>
		</div>
		</div><?php
		
		$eventswalker++;
	}
}

function get_mottagningen_text_day($events,$date,$utskrift){
	global $eventswalker;
	global $kal;
	$header_posted = false;
	while($events[$eventswalker]){
		
		$startstr = strtotime($events[$eventswalker]->when[0]->startTime);
		$endstr = strtotime($events[$eventswalker]->when[0]->endTime);
		$duration = ($endstr - $startstr)/3600;
		if(date('j',$startstr) != $date){//no events today or this event is not today
			if($header_posted){//we have a previously echoed an event, close the paragraph
				echo '</div>';
			}
			break;
		}
		
		if(!$header_posted){
			echo '<div class="text_day"><h2>'.utf8_encode(strftime('%A %e %b',$startstr)).'</h2>';
			$header_posted = true;
		}
			?>
			<div class="text_aktivitet <?php echo get_mottagning_klass($events[$eventswalker]->title); ?>">
			<span class="text_tid"><?php echo date('H:i',$startstr); ?> &ndash; <?php echo date('H:i',$endstr); ?></span>
			<span class="text_titel"><?php echo $events[$eventswalker]->title; ?></span>
			<span class="text_plats"><?php if($plats = $events[$eventswalker]->where[0]->valueString){ echo ' - '.$plats; } ?></span>
			<?php if($events[$eventswalker]->content != ''){?>
				<?php if(!$utskrift){ ?><span class="visa_beskrivning"><a href='javascript:void(0)' title="">(Visa mer)</a></span><?php } ?>
				<div class="text_beskrivning"><?php echo $events[$eventswalker]->content; ?></div>
			<?php } ?>
			</div>
		<?php		
		$eventswalker++;
	}
}

$kal = array('halvh' => '13', 'dagb' => '40', 'start' => '11','slut' => '23','startdatum' => 12);
$kaldays = array(0 => 14, 1 => 17, 2 => 19, 3 => 21);
$kalweekdays = array(14 => "Lördag", 17 => "Tisdag", 19 => "Torsdag", 21 => "Lördag");
$kal['perdag'] = $kal['slut'] - $kal['start'] + 1;
$kal['helh'] = 2*$kal['halvh'] + 2;
$kal['walker'] = $kal['startdatum'];
setlocale(LC_TIME,'sv_SE');
date_default_timezone_set('Europe/Stockholm');

function build_week($events,$weekwalker){ 
	global $kal;
	global $kaldays;
	global $kalweekdays; ?>
	<tr class="wk-daynames">
			<th scope="col" title="V.1" class="wk-weekhead"><div class="wk-weekname"><span class="wk-weeklink"></span></div></th>
			<?php for($i = 0; $i<4; $i++){ 
				$datum = strval($kaldays[$i]) . ' april'; // Leo är en hårdkodande #%!&@!#$! Jag finner inte ord.
				if($kaldays[$i] > 31){
					$datum = ($kaldays[$i] - 31) . ' maj';	
				}
				?><th scope="col" title="<?php echo $datum; ?>" class="wk-day"><div class="wk-dayname"><span class="wk-daylink"><?php echo $kalweekdays[$kaldays[$i]] . '&nbsp;' . $datum; ?></span></div></th>
			<?php } ?>
		</tr>
		<tr><?php //Timmarkeringar ?>
			<td class="tim-tabell"></td>
			<td colspan="<?php echo count($kaldays) ?>">
				<div class="tg-spanningwrapper">
					<div class="tg-hourmarkers">
						<?php for ($i = 1; $i <= $kal['perdag']; $i++) {
							?><div class="tg-markercell"><div class="tg-dualmarker"></div></div><?php
						} ?>
					</div>
				</div>
				<div id="tgspanningwrapper" class="tg-spanningwrapper tg-chipspanningwrapper"></div>
			</td>
		</tr>
		<tr>
			<td class="tg-times-pri"><?php //Timbeskrivningar ?>
				<?php for($i = $kal['start']; $i <= $kal['slut']; $i++){
					?><div class="tim-matare"><div class="tg-time-pri"><?php echo $i; ?>:00</div></div><?php
				} ?>
			</td>
			<?php for($i = 0; $i<count($kaldays); $i++)://Dagsinehåll ?>
			<td class="tg-col">
				<div style="" class="tg-col-eventwrapper" id="tgCol0">
					<div class="tg-gutter">
						<?php
							get_mottagningen_day($events,$kaldays[$i]);
							$kal['walker'] = $kaldays[$i];
						?>
					</div>
				</div>
				<div class="tg-col-overlaywrapper" id="tgOver0"></div>
			</td>
			<?php endfor; ?>
		</tr>
	<?php
}
function build_calendar($mode = 'grafiskt'){
	global $kal;
	global $eventswalker;
	$events = get_mottagningen('2012-04-'.$kal['startdatum']);
	switch($mode){
		case 'grafiskt':
			build_grafisk($events);
			break;
		case 'text':
			build_text($events);
			break;
		case 'utskrift-grafiskt':
			build_grafisk($events,'utskrift');
			break;
		case 'utskrift-text':
			build_text($events,'utskrift');
	}
}

function build_grafisk($events,$utskrift = false){
	if($utskrift){
		echo '<h2 class="utskrift-rubrik">Fadderiets Superfestliga Schema!</h2>';
	}
	?><div id="kalender_view"><table class="kalender">
		<tbody>
		<?php for( $i = 0 ; $i <= 0; $i++){
			build_week($events,$i);
		} ?>
	</tbody>
	</table>
	</div>
<?php
}

function build_text($events,$utskrift = false){
	if($utskrift){
		echo '<h2 class="utskrift-rubrik">Fadderiets Superfestliga Schema!</h2>';
	}
	echo '<div id="text_view">';
	global $kal;
	global $kaldays;
	$kal['walker'] = $kal['startdatum'];
	while($kal['walker']< 40){
		get_mottagningen_text_day($events,$kal['walker'],$utskrift);
		$kal['walker']++;
	}
	echo '</div></div>';//Oklart varför det behövs två här...
}
function build_bubble(){?>
	<div onmousedown="gcal$func$[1]()" class="bubble">
		<table cellspacing="0" cellpadding="0" class="bubble-table">
			<tbody>
				<tr>
					<td class="bubble-cell-side">
						<div id="tl:a" class="bubble-corner">
							<div class="bubble-sprite bubble-tl"></div>
						</div>
					</td>
					<td class="bubble-cell-main">
						<div class="bubble-top"></div>
					</td>
					<td class="bubble-cell-side">
						<div id="tr:a" class="bubble-corner">
							<div class="bubble-sprite bubble-tr"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="bubble-mid" colspan="3">
						<div id="bubbleContent:a" style="overflow:hidden">
							<div>
								<div class="eb-root">
									<div class="eb-header">
										<div class="eb-title goog-inline-block" id="mtb">&nbsp;</div>
										<div class="eb-date">ons, den 17 augusti, 16:00 &ndash; 20:00</div>
									</div>
									<table cellspacing="0" class="eb-data">
										<tbody>
											<tr class="var">
												<th>Var</th>
												<td>F2</td>
											</tr>
											<tr class="beskrivning">
												<th>Beskrivning</th>
												<td>Exempel</td>
											</tr>
										</tbody>
									</table>
									<div class="eb-popup" id="eventColorPopup">
										<div class="eb-popup-tip"></div>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div id="bl:a" class="bubble-corner">
							<div class="bubble-sprite bubble-bl"></div>
						</div>
					</td>
					<td>
						<div class="bubble-bottom"></div>
					</td>
					<td>
						<div id="br:a" class="bubble-corner">
							<div class="bubble-sprite bubble-br"></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="bubble-closebutton"></div>
		<div onclick="gcal$func$[0]()" id="prong:a" class="prong" style="display: block; left: 101px;"><div class="bubble-sprite"></div></div>
	</div>
<?php
}

function kalender_script(){?>
<script type="text/javascript">
	jQuery(document).ready( function($) {
		jQuery(".chip").click(function(e){
			 var eventid = jQuery(this).attr('id');
			 var eventtitle = jQuery('#' + eventid + ' .kal-beskrivning').html();
			 var eventtime = jQuery('#' + eventid + ' .full-date').html();
			 var eventdescr = jQuery('#' + eventid + ' .description').html();
			 if(eventdescr == ''){eventdescr = 'Beskrivning saknas.';}
			 var eventwhere = jQuery('#' + eventid + ' .kal-plats').html();
			 if(eventwhere == ''){eventwhere = 'Plats saknas.';}
			 
			 jQuery('.bubble .eb-title').html(eventtitle);
			 jQuery('.bubble .eb-date').html(eventtime);
			 jQuery('.bubble .eb-data .var td').html(eventwhere);
			 jQuery('.bubble .eb-data .beskrivning td').html(eventdescr);
			 
			 var bubbleheight = parseInt(jQuery('.bubble').css('height'));
			 jQuery('.bubble').css({'left': (e.pageX - 107), 'top': (e.pageY - bubbleheight - 75)}).show();
			 e.stopPropagation();
   		});
   		$(".chip").hover(
  			function () {
    			$(this).addClass("hover");
  			},
  			function () {
    			$(this).removeClass("hover");
  			}
		);
   		jQuery('.bubble-closebutton').click(function(){
   			jQuery('.bubble').hide();
   		});
   		

		jQuery('.bubble').click(function(event){
		     event.stopPropagation();
 		});
 		jQuery('html').click(function() {
 			jQuery('.bubble').hide();
 		});
 		
 		jQuery('.visa_beskrivning').click(function(){
 			jQuery('.open').slideToggle();
 			jQuery('.open').removeClass('open');
 			jQuery(this).next().slideToggle();
 			jQuery(this).next().addClass('open');
 		});
 		jQuery('.visa-importera').click(function(){
 			jQuery('.importera-kalender').slideToggle();
 		});

		});
</script>
<?php 
}
?>
