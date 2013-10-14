<?php

$envato_user   = ''; //your envato username
$envato_market = ''; //codecanyon //themeforest
$default_item  = ''; //default item
$force_resp = FALSE; //force all items to be responsive

/* Stop Editing */

//because people tend to copy and paste and expect magic ...
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

	//displays on screen only
    echo '<h3>products.js now contains:</h3>';


    for ($i = 0; $i <= (count($json_data) - 1); $i++) {

        //turn product page url into preview page url
        $url  = str_replace($json_data[$i]['id'], 'full_screen_preview/'.$json_data[$i]['id'], $json_data[$i]['url']);

        //get live preview page html
        $page = file_get_contents($url);

        //get real demo url
        preg_match('#(?<=class="close" href=")(.*)(?=">X</a>)#', $page, $demo_url);

        $tag = ''; //set empty
        $tag = current(explode('/', $json_data[$i]['category']));
        if ($tag == 'wordpress') $tag = 'WP';
        if ($tag == 'php-scripts') $tag = 'PHP';

		//add un|responsive support
		if (strlen(strstr(strtolower($json_data[$i]['tags']),'responsive'))>0) {
			$responsive = 'true';
		} else {
			$responsive = 'false';
		}

		//force all to be responsive
		if ($force_resp) $responsive = 'true';

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

		//displays on screen item being added
        echo $json_data[$i]['item'].'<br />';

    }

    $products .= "\n".'};';
} //end FOR loop

//save to products.js
file_put_contents('js/products.js', $products);

//let you know it's done
echo '<h3>Done</h3><p>project.js has been updated and saved.</p>';
