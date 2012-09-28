<?php 
$forced_parent = 1603; 
/*if using forced parent, be sure to declare all the colors on the level below. Like this:
$colors = (array) get_option("THS-colors");
$colors[<<PAGE ID OF LEVEL DOWN>>] = '<<THE COLOR HEX CODE WITHOUT #>>';

if no color defined, it uses the forced parent
*/
?>

<?php get_head(); ?>

<?php $here = get_bloginfo('url').'/templates/linken/'; ?>
<link rel="stylesheet" href="<?php echo $here; ?>style.css" type="text/css" media="screen" />
<style type="text/css">
body {
	background: url('<?php echo $here; ?>sol.png') no-repeat scroll top center #FEBD00;
}
#footer {
	background: url("<?php echo $here; ?>footer.jpg") scroll 0 0 transparent;
}
#fadderiet-logo {
    position: absolute;
}
#pratbubbla {
    background: url("<?php echo $here; ?>pratbubbla.png") repeat scroll 0 0 transparent;
    font-size: 14px;
    height: 162px;
    left: 591px;
    padding: 18px 16px 0 159px;
    position: absolute;
    top: 20px;
    width: 164px;
}
#meny li.current_page_item, #meny li.current-page-ancestor {
    height: 50px;
}
<?php if(get_root_id(get_the_ID()) == 1603){ ##FULHAXX, BORDE VARA FÖRBJUDET! GÖR INTE SÅHÄR =)?>
.page-item-1603 {
	background:<?php the_root_color($theID); ?> url(<?php bloginfo('stylesheet_directory'); ?>/images/btn_arrow.png) top center !important;
}
<?php ##INTE SÅ HÄR HELLER!!
}
if(is_single()){?>
#meny .page-item-1603 {
	background:#00FF2A url(<?php bloginfo('stylesheet_directory'); ?>/images/btn_arrow.png) top center !important;
}
  <?php }?>
.fotlama {
	background: url('<?php echo $here; ?>fotlama.png') no-repeat scroll right bottom transparent;
    bottom: 0;
    color: #A23C25;
    font-size: 15px;
    height: 92px;
    margin-bottom: -2px;
    padding: 6px 86px 0 0;
    position: absolute;
    right: -44px;
}
</style>
</head>

<body <?php body_class(); ?>>
<?php if(is_page('schema')){build_bubble();} ?>
<?php get_topbar(); ?>
<a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('title'); ?>" class="till-fysik"><img src="<?php echo $here; ?>tillbaka.png" alt="Till Fysiksektionen!" /></a>
<div id="page">


<div id="header" role="banner">
	<img id="vansterskruvar" alt="" src="<?php echo $here; ?>vansterskruvar.png" />
	<div id="fadderiet-logo">
		<img id="jslogo" src="<?php bloginfo('stylesheet_directory'); ?>/fysiklogo.png" width="121" height="167" alt="Fysiksektionen logotyp" title="Fysiksektionen(hem)" />
		<a href="<?php echo get_permalink(788); ?>" title="">
			<img id="fadderiet-link" width="502" height="98" src="<?php echo $here; ?>logo.png" alt="Fadderiet" title="Fadderiet (hem)" />
		</a>
	</div>
	<div id="header-norris"><img src="<?php echo $here; ?>norris.png" alt='San Norris' /></div>
	<div id="pratbubbla">Vi ses i Sommar nØllan!</div>
	<img id="hogerskruvar" alt="" src="<?php echo $here; ?>hogerskruvar.png" />
</div>

<?php
//////////////HÄR KAN DU ÄNDRA VAD SOM VISAS I MENYN GENOM ATT LÄGGA TILL RESPEKTIVE SIDAS ID I "INCLUDE" ?>
<div id="meny" role="navigation">
<?php 
$menu_args = array(	'include' => '1603,1613',
				'title_li' => '',
				'depth' => '-1'
				);
wp_list_pages($menu_args); ?>

</div>


 <br />
<hr  class="clear" />


	<div id="content" class="narrowcolumn" role="main">

		<?php if (have_posts()) : while (have_posts()) : the_post();
		
		
		
		if(is_single()){ /// START OF SINGLE POST SECTION, SEE END BELOW
		$event = get_post_meta(get_the_ID(),'_isEvent',true);
	?>


		<div <?php post_class("realpost") ?> id="post-<?php the_ID(); ?>">
			<h2><?php the_title(); ?></h2>
			<?php global $wp_locale; ?>
			<div class="entry">
            <?php if($event) : ?>
					<div class="eventbig eventimage"><span class="monthname"><?php echo $wp_locale->get_month(date("m",$event)) ?></span><span class="monthday"><?php echo date("j",$event) ?></span><span class="eventtime"><?php echo date("H:i",$event) ?> - <?php echo @date("H:i",get_post_meta(get_the_ID(),'_endEvent',true)) ?></span></div>
					<?php endif; ?>
				<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				<?php the_tags( '<p>Tags: ', ', ', '</p>'); ?>
<div id="meta" style="overflow:auto;clear:both">
<div class="alignleft">
	
	<?php echo get_avatar(get_the_author_meta('user_email'), $size = '100', $default = '' ); 
	?> <br /><p>Skriven av <?php the_author(); ?></p></div>
			<p class="postmetadata alt" style="margin-left:150px; clear:none;">
					<small>
						Detta inlägg publicerades av <?php the_author(); ?> den
						<?php /* This is commented, because it requires a little adjusting sometimes.
							You'll need to download this plugin, and follow the instructions:
							http://binarybonsai.com/wordpress/time-since/ */
							/* $entry_datetime = abs(strtotime($post->post_date) - (60*120)); echo time_since($entry_datetime); echo ' ago'; */ ?>
					<strong class="postdate">	<?php the_time('d-m-Y') ?> klockan <?php the_time("H:i") ?></strong>
						och tillhör kategori/erna <?php the_category(', ') ?>.
						Du kan följa alla kommentarer till detta inlägg genom detta <?php post_comments_feed_link('RSS 2.0'); ?> flöde.

						<?php if ( comments_open() && pings_open() ) {
							// Both Comments and Pings are open ?>
							Du kan <a href="#respond">lämna en kommentar</a>, eller skicka en <a href="<?php trackback_url(); ?>" rel="trackback">trackback</a> från din egen site.

						<?php } elseif ( !comments_open() && pings_open() ) {
							// Only Pings are Open ?>
							Du kan skicka en <a href="<?php trackback_url(); ?>" rel="trackback">trackback</a> från din egen site.

						<?php } elseif ( comments_open() && !pings_open() ) {
							// Comments are open, Pings are not ?>
							Du kan <a href="#respond">lämna en kommentar nedan</a>

						<?php } elseif ( !comments_open() && !pings_open() ) {
							// Neither Comments, nor Pings are open ?>
							Både kommentarer och pings är för närvarande stängda för detta inlägg.

						<?php } edit_post_link('Redigera detta inlägg','','.'); ?>

					</small>
				</p>
</div>
			</div>
		</div>

		<?php }else{; // END OF SINGLE POST SECTION ?>
		
		
		
		<div class="post realpost page" id="post-<?php the_ID(); ?>">
		<h2><?php the_title(); ?></h2>
			<div class="entry">
				<?php if(is_front_page()) :  
					 $more = false;?>
				<div id="sneak" class="hide-if-no-js">	 <?php echo get_the_content("",true)."..."; 	$more = true; ?><a id="readmore" href="#">Läs mer</a></div>
               <div id="restofsneak"><?php the_content(); ?></div>
				<?php else : the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
    <?php endif; ?>
				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			</div>
		</div>
		<?php } // END PAGE SECTION ?>
		<?php endwhile; endif; ?>
        <?php if(function_exists("LoopThePage"))
									LoopThePage();			?>
	<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
	
	<?php comments_template(); ?>
	
	</div>

<div id="sidebar" role="complementary">
		<ul>
			<?php
			function mottagningen_countdown(){ ?>
				<li class="widget widget_countdown">
					<h2 class="widgettitle">Nu är det bara</h2>
					<div class="mottagningen-countdown"><?php 
						$dagar = ceil((strtotime('2011-08-14') - time())/(3600*24));
						echo $dagar;
						if($dagar < 3){
							echo '!!!';
						}elseif($dagar < 10){
							echo '!!';
						}elseif($dagar < 20){
							echo '!';
						}
					?></div>
					<h3 style='text-align:right;margin:0;'><?php if($dagar == 1){echo 'dag';}else{echo 'dagar';} ?> kvar till mottagningen!</h3>
				</li><?php
			}
			function fadderiet_args($name){
				return array('before_widget' => '<li class="widget widget_'.$name.'">','after_widget' => '</li>');
			}
			$facebook = array('title' => '','text' => '<iframe src="http://www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Ffysiksektionen&amp;width=270&amp;colorscheme=light&amp;show_faces=true&amp;stream=false&amp;header=false&amp;height=256" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:270px; height:256px;" allowTransparency="true"></iframe>');

			if(is_single()){$force_menu = array('force' => '10000000000');}else{$force_menu = false;}
			the_widget('THS_Submenu',$force_menu,fadderiet_args('ths_submenu'));
			mottagningen_countdown();
			the_widget('THS_Widget_Categories',array('title' => 'Nyheter från Fadderiet', 'cat' => '22'),fadderiet_args('widget_categories'));
			//the_widget('ThsAnnons',array('metod' => 'rand','antal'=>'2','kategori' => array('5')),fadderiet_args('thsannons'));
			the_widget('WP_Widget_Text',$facebook,fadderiet_args('text'));
			
			 ?>
		</ul>
	</div>
<div class="fotlama">Välkommen till Fysiksektionen nØllan!</div>
<?php get_footer(); ?>
