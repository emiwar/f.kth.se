<?php 
include 'enkat/submit.php';
global $print;
?>

<div id="enkat">

<?php if(isset($_POST["submit"])) { ?>
<h2 style="text-align:center;">Tack!</h2>
<p>Tack för att du tog dig tid att fylla i enkäten!</p>
<p>Hälsningar</p>
<p>Festliga Fadderiet</p>
<?php } else { ?>
<?php if(!$print) { ?>
<p><em>Här är mottagningsenkäten som du hemskt gärna får fylla i innan mottagningen, du kan göra det antingen här på nätet eller i den pappersversion som snart dimper ner i din brevlåda. Har du några frågor gällande enkäten, hör av dig till oss på <strong><a href='<?php echo antispambot('mailto:fadderiet@f.kth.se'); ?>' title=''><?php echo antispambot('fadderiet@f.kth.se'); ?></a></strong>.</em></p>
<?php }else{ ?>
<h2>Hej nØllan!</h2>
<?php } ?>
<form method="post" action="<?php echo get_permalink(); ?>">


<fieldset>
<legend>&Oslash;. Mottagningsenkäten</legend>

<p>Det här är Festliga Fadderiets helt egna typ-någonting-som-liknar-en-mina-vänner-bok! Det hela går ut på att du svarar på en massa snabba enkla frågor om dig själv, så att vi kan avgöra vilken n&Oslash;llegrupp som passar dig bäst. På det sättet tror vi att du kommer att uppskatta mottagningen ännu mer. Det finns gott om utrymme att meddela oss saker som du tror att vi behöver veta, men även saker som vi uppenbarligen inte behöver veta men som är genuint roliga/fyndiga/kreativa. Det som mest skiljer detta från en riktig mina-vänner-bok, är att det bara är vi Mottagningsansvariga som kommer att se enkäten. Den är alltså helt konfidentiell.</p>

<p>Enkäten är indelad i två delar, en med nödvändig information där vi vill att ni talar sanning och kanske tänker efter lite, och sedan en jätterolig del så att vi kan veta vad du är för filur innan du kommit till KTH!</p>

<?php if ($print) { ?>
<p>Den färdigifyllda enkäten postas till</p>
<pre>Caroline Magnusson
Pinnmovägen 5
187 50 Täby
</pre>
<p style="font-weight:bold;">OBS! Enkäten kan även fyllas i på nätet: 
<a href="http://f.kth.se/fadderiet/enkat" title="">http://f.kth.se/fadderiet/enkat</a></p>
<?php } ?>

<p>Enkäten kan fyllas i på nätet till och med den <b>13 augusti</b>. Om du tänker skicka enkäten med snigelpost så se till den är lagd på lådan senast onsdag den <b>10 augusti</b>. <?php if(!$print){ ?><a href="<?php echo get_permalink( get_page_by_path( 'fadderiet/enkat/utskriftsvanlig-version' ) ); ?>">Utskriftsvänlig version</a><?php } ?></p>

<p>Vi hoppas att du kommer att trivas med din n&Oslash;llegrupp och ha superduperkul under mottagningen. Vi ses den 14 augusti!</p>
<p>Hälsningar <br/><strong>Festliga Fadderiet</strong></p>

</fieldset>

<?php include('enkat/enkat_base.php'); ?>

<fieldset>
<legend>&#8734; Slutfasen</legend>
<?php if($print) { ?>
<p>Lägg pappersbunten i ett kuvert och skicka till adressen som stod längst upp på första sidan.</p>
<?php } else { ?>
<p><input name="submit" value="Skicka in mina svar!" type="submit" /></p>
<?php } ?>
</fieldset>

<h2 class="center">Kram och Tack!</h2>

</form>
<?php } ?>
</div>