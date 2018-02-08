<?php
require_once 'vendor/autoload.php' ;
use \garagesolidaire\controleur\ControleurClient;
use \Illuminate\Database\Capsule\Manager as DB;


use \Slim\Slim;
use garagesolidaire\controleur\GestionAccueil;
use garagesolidaire\controleur\GestionCompte;
use garagesolidaire\controleur\ControleurAdministrateur;


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

$app->get('/afficher/items/categorie/:num',function($num){
	$control=new ControleurClient();
	$control->afficheritemscategorie($num);
})->name("afficher-item");

$app->get('/afficher/planning/graph/:num',function($num){
	$control=new ControleurClient();
	$control->afficherPlanningGraphique($num);
})->name("afficher-palanning-graph");

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

$app->get('/afficher/item/:id',function($id){
	$control = new ControleurClient();
	$control->afficherItem($id);
})->name('item');

$app->get('/afficher/categories',function(){
	$control=new ControleurClient();
	$control->afficherCategories();
})->name("aff-categorie");


$app->get('/afficher/creation/reservation/:id',function($id){
	$control=new ControleurClient();
	$control->afficherCreationReservation($id);
})->name("creation-reservation");


$app->get('/afficherlisteutilisateurs',function(){
	$control=new ControleurClient();
	$control->afficherListeUtilisateurs();
})->name("afficher-utilisateurs");

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

$app->get('/user',function () {
  $c = new GestionCompte();
  $c->afficherPanel();
})->name( 'aff-user' );

//----------------------Formulaire-Inscription-Compte------//
$app->get('/inscription', function () {
  $c = new GestionCompte();
  $c -> afficheInscription();
})->name("inscription");

$app->post('/inscription', function () {
  $c = new GestionCompte();
  $c -> ajouterUtilisateur();
});



$app->get('/user/delete', function () {
    $c = new GestionCompte();
    $c->supprimerCompte();
})->name('supprimer-compte');

$app->get('/user/modifier-compte', function () {
    $c = new GestionCompte();
    $c->afficherModifCompte();
})->name('modifier-compte');

$app->post('/user/modifier-compte', function () {
    $c = new GestionCompte();
    $c->modifCompte();
});

$app->get('/user/change-mdp', function () {
    $c = new GestionCompte();
    $c->afficherChangerMotDePasse();
})->name("modifier-mdp");

$app->post('/user/change-mdp', function () {
    $c = new GestionCompte();
    $c->changerMotDePasse();
});

$app->post('/validerreservation/:id', function($id) {
    $c = new ControleurClient();
    $c->validerReservation($_POST['jourdeb'],$_POST['jourfin'],$_POST['heuredeb'],$_POST['heurefin'],$id);
})->name("valid-reserv");


$app->get('/mesreservations', function () {
    $c = new ControleurClient();
    $c->mesReservations();
})->name("mes-reservations");

$app->get('/afficherplanningreservationitem/:id',function($id){
	$control=new ControleurClient();
	$control->afficherPlanningReservationItem($id);
})->name("reservationitem");


$app->get('/reservation/:id' , function ($id) {
  $control=new ControleurClient();
  $control->afficherReservation($id);
})->name("reservation");


$app->post('/ajoutercommentaire/:id',function($id){
	$control=new ControleurClient();
	$control->ajouterCommentaire($id,$_POST['message']);
})->name("ajouter-commentaire");


$app->get('/list/reservation/' , function () {
  $c = new ControleurAdministrateur();
  $c->afficherReservation();
})->name("reservation-list");

$app->post('/list/reservation/accept/:id' , function ($id) {
  $c = new ControleurAdministrateur();
  $c->acceptReservation($id);
})->name("reservation-accept");

$app->post('/list/reservation/decline/:id' , function ($id) {
  $c = new ControleurAdministrateur();
  $c->declineReservation($id);
})->name("reservation-decline");

$app->get('/afficherplanningreservationuser/:id',function($id){
	$control=new ControleurClient();
	$control->afficherPlanningUser($id);
})->name("reservation-user");

$app->post('/annulerreservation/:id',function($id){
	$control=new ControleurClient();
	$control->annulerReservation($id);
})->name("annuler-reservation");




$app->run();
