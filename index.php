<?php
require_once 'vendor/autoload.php' ;
use \garagesolidaire\controleur\ControleurClient;
use \Illuminate\Database\Capsule\Manager as DB;


use \Slim\Slim;
use garagesolidaire\controleur\GestionAccueil;
use garagesolidaire\controleur\GestionCompte;

$db=new DB();
$db->addConnection(parse_ini_file('./conf/conf.ini'));
$db->setAsGlobal();
$db->bootEloquent();

session_start();
$app = new Slim() ;

//----------------------Pages-d-accueil--------------//
$app->get('/', function () {
  $c = new GestionAccueil();
  $c -> afficheAccueil();
})->name("accueil");

$app->get('/afficheritemscategorie/:num',function($num){
	$control=new ControleurClient();
	$control->afficheritemscategorie($num);
});

$app->get('/afficherplanninggraph/:num',function($num){
	$control=new ControleurClient();
	$control->afficherPlanningGraphique($num);
});

$app->get('/contact', function () {
  $c = new GestionAccueil();
  $c -> afficheContact();
})->name("contact");

$app->get('/about', function () {
  $c = new GestionAccueil();
  $c -> afficheAbout();
})->name("about");

$app->get('/help', function () {
  $c = new GestionAccueil();
  $c -> afficheHelp();
})->name("help");

// Redirection Erreur 404
$app->notFound(function () use ($app) {
  $c = new GestionAccueil();
  $c -> error404();
});

$app->get('/afficheritem/:id',function($id){
	$control = new ControleurClient();
	$control->afficherItem($id);
})->name('item');

$app->get('/affichercategories',function(){
	$control=new ControleurClient();
	$control->afficherCategories();
});

$app->get('/affichercreationreservation/:id',function($id){
	$control=new ControleurClient();
	$control->afficherCreationReservation($id);
});

//-----------------------------Formulaire-de-connexion-et-deconnexion-compte----------//
$app->get('/connexion', function () {
  $c = new GestionCompte();
  $c -> afficheConnexion();
})->name("connexion");

$app->post('/connexion', function () {
    $c = new GestionCompte();
    $c->etablirConnection();
});

$app->get('/deconnexion', function () {
  $c = new GestionCompte();
  $c -> deconnecter();
})->name("deconnexion");

//----------------------Formulaire-Inscription-Compte------//
$app->get('/inscription', function () {
  $c = new GestionCompte();
  $c -> afficheInscription();
})->name("inscription");

$app->post('/inscription', function () {

    $c = new GestionCompte();
    $c->ajouterUtilisateur();

});

$app->run();
