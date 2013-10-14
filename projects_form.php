<?php
if (!function_exists('print_x')) { function print_x($value) { echo '<pre style="text-align:left;"><hr />'; print_r($value); echo '<hr /></pre>'; } }
if (!function_exists('clean')) { function clean($input, $length=16) { $input = (string)substr(trim(htmlentities(strip_tags($input))), 0, (int)$length); if (get_magic_quotes_gpc()) { $input = trim(stripslashes($input)); } $search  = array("\x00","\n","\r","\\","'","\"","\x1a"); $replace = array("\\x00","\\n","\\r","\\\\","\'","\\\"","\\x1a"); $input   = trim(str_replace($search, $replace, $input)); return $input; } }
if (!function_exists('selected')) { function selected($saved, $value) { if ($saved == $value) $selected = 'selected="selected"'; else $selected = ''; return $selected; } } //end function not exist

$envato_user   = trim(clean($_POST['user'], '16'));
$envato_market = trim(clean($_POST['market'], '16'));
$force_repsonsive = trim(clean($_POST['resp'], '1'));
$default_item  = ''; //default item

?>
<h1>Switcheroo product.js maker</h1>
<p>If your item is responsive, make sure it is tagged as such on your Envato listing!!</p>
<form action="" method="POST">
<input type="text" name="user" value="<?php if (!empty($envato_user)) echo $envato_user; ?>" placeholder="Envato User Name"/>
<select name="market">
	<option value="themeforest" <?php echo selected($envato_market,'themeforest'); ?>>ThemeForest</option>
	<option value="codecanyon" <?php echo selected($envato_market,'codecanyon'); ?>>CodeCanyon</option>
	<option value="activeden" <?php echo selected($envato_market,'activeden'); ?>>ActiveDen</option>
	<option value="audiojungle" <?php echo selected($envato_market,'audiojungle'); ?>>AudioJungle</option>
	<option value="cideohive" <?php echo selected($envato_market,'cideohive'); ?>>VideoHive</option>
	<option value="graphicriver" <?php echo selected($envato_market,'graphicriver'); ?>>GraphicRiver</option>
	<option value="3docean" <?php echo selected($envato_market,'3docean'); ?>>3D Ocean</option>
	<option value="photodune" <?php echo selected($envato_market,'photodune'); ?>>PhotoDune</option>
</select><br />
<label for="resp">Force All to be responsive? </label>
<input type="checkbox" name="resp" id="resp" value="1" /><br />
<input type="submit" value="Submit" />
</form>
<?php
if (empty($envato_user) || empty($envato_market)) die('Need Envato Username AND Marketplace!');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://marketplace.envato.com/api/edge/new-files-from-user:'.$envato_user.','.$envato_market.'.json');
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ch_data = curl_exec($ch);
curl_close($ch);
if (!empty($ch_data)) {
    $json_data  = json_decode($ch_data, true);
    $json_data  = $json_data['new-files-from-user'];
    $products = 'var $products, $current_product = "'.$default_item.'";'."\r\n".'$products = {'."\n";
    for ($i = 0; $i <= (count($json_data) - 1); $i++) {
        $url  = str_replace($json_data[$i]['id'], 'full_screen_preview/'.$json_data[$i]['id'], $json_data[$i]['url']);
        $page = file_get_contents($url);
        preg_match('#(?<=class="close" href=")(.*)(?=">X</a>)#', $page, $demo_url);
        $tag = ''; //set empty
        $tag = current(explode('/', $json_data[$i]['category']));
        if ($tag == 'wordpress') $tag = 'WP';
        if ($tag == 'php-scripts') $tag = 'PHP';
		if (strlen(strstr(strtolower($json_data[$i]['tags']),'responsive'))>0) {
			$responsive = 'true';
		} else {
			$responsive = 'false';
		}
		if ($force_repsonsive) $responsive = 'true';
        $name = preg_replace('/_+/', '_', preg_replace('/[^\da-z]/i', '_', strtolower($json_data[$i]['item'])));
        $products .= '
	'.$name.' : {
		name:"'.$json_data[$i]['item'].'",
		tag:"'.$tag.'",
		img:"'.$json_data[$i]['live_preview_url'].'",
		url:"'.$demo_url['0'].'",
		purchase:"'.$json_data[$i]['url'].'?ref='.$envato_user.'",
		responsive: '.$responsive.'
	},'."\n";
    } //end FOR loop
    $products .= "\n".'};';
}//not empty ch_data
print_x($products);
echo '<h3>Done</h3><p>Copy and paste above into your js/project.js file.</p>';
