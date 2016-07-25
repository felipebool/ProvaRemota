<?php

require_once 'vendor/autoload.php';
require_once 'UsefulTableTree/UsefulTableTree.php';
require_once 'twitter_credentials.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$twig_data = array();

if (isset($_POST['twitter_topic'])) {
   // retrieving tweets
   $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
   $result = $connection->get('search/tweets', array('q' => $_POST['twitter_topic'], 'count' => 100));

   if (!empty($result->statuses)) {
      // populating data structure
      $tabletree = array();
      foreach ($result->statuses as $result) {
         $entry = explode(' ', $result->created_at);
         $month = $entry[1];
         $day   = $entry[2];
         $hour  = $entry[3];

         if (array_key_exists($month, $tabletree)) {
            $tabletree[$month][$day][] = $hour;
         }
         else {
            $tabletree[$month] = array($day => array($hour));
         }
      }

      $usefultabletree = new UsefulTableTree($tabletree, array('Mês', 'Dia', 'Hora'));

      $twig_data['header']       = $usefultabletree->get_table_header();
      $twig_data['content']      = $usefultabletree->get_table_content();
   }
   else {
      $twig_data['query_result'] = false;
   }
}

Twig_Autoloader::register();

$loader   = new Twig_Loader_Filesystem('View/templates');
$twig     = new Twig_Environment($loader, array('cache' => 'View/cache'));
$template = $twig->loadTemplate('index.html');

echo $template->render($twig_data);

