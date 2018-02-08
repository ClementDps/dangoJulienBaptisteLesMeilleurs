<?php

namespace garagesolidaire\vue;

use \garagesolidaire\models\User;
use \garagesolidaire\models\Item;

class VueAdministrateur{

  private $infos;

  const AFF_CO = 3;
  const AFF_INSC = 4;
  const AFF_USER = 5;
  const AFF_RESERV = 6;
  const AFF_CMDP = 7;
  const AFF_MODIF_COMPTE = 8;
  const AFF_NO_CO = 9;
  const AFF_NO_ACCES = 10;

  public function __construct($tab){
    $this->infos=$tab;
  }


  public function render($int){
    $code = "";
    $app = \Slim\Slim::getInstance();

  switch($int){
    case 1:{
      $content=$this->afficherAccueil();
      break;
    }
  case VueAdministrateur::AFF_CO :{
          $errorLogIn = "";
          $email = "";
          if(isset($this->infos)){ //Gestion du cas d'erreur
            if(isset($this->infos["email"]) && isset($this->infos['error']) && $this->infos['error'] == "auth"){
              $email = "value=\"".$this->infos['email']."\"";
              $errorLogIn = "<p>*** Mauvais email ou mot de passe ***</p>";
            }
          }
          $cheminCo = $app->urlFor("connexion");
          $cheminInsc =  $app->urlFor("inscription");
          $code = \garagesolidaire\vue\VueGeneral::genererHeader("formulaire");
          $code.= <<<END
                <header>CONNEXION</header>
                <form id="form" method="POST" action="${cheminCo}">
                  <label>EMAIL : </label> <input type="email" name="email" placeholder="EMAIL" $email required>
                  <label>MOT DE PASSE : </label><input type="password" name="mdp" placeholder="MOT DE PASSE" required>
                  $errorLogIn
                  <input id="submit" type="submit" name="connection" value="Connexion">
                  <div id="no_count">
                    <a>Pas de compte ? <a href="$cheminInsc" id="link">Inscrivez-vous !</a></a>
                  </div>
                </form>
                </div>
END;
    break;
    }
    case VueAdministrateur::AFF_INSC : {
      $cheminInsc =  $app->urlFor("inscription");

      //---------------------------------------------Gestion-du-cas-d'erreur
        $nom = "";
        $prenom = "";
        $email = "";
        $errorMdp = "";
        $errorEmail = "";
        if(isset($this->infos)){ // Gestion de l'affichage de l'erreur
          if ($this->infos["error"] === "mdpDiff"){
            $errorMdp = "<p>***Mot de passe invalide !***</p>";
          }else if ($this->infos["error"] === "email"){
            $errorEmail = "<p>***Email invalide !***</p>";
          }else if ($this->infos["error"] === "emailExist"){
            $errorEmail = "<p>***Email existe déjà dans la base !***</p> ";
          }
          $nom = "value=\"".$this->infos["nom"]."\""; //Affichage pré-rempli du formulaire en cas d'erreur
          $prenom = "value=\"".$this->infos["prenom"]."\"";
          $email = "value=\"".$this->infos["email"]."\"";
        }
//----------------------------------------------
        $code = \garagesolidaire\vue\VueGeneral::genererHeader("formulaire");
          $code.= <<<END
  <header>INSCRIPTION</header>
  <form id="form" method="POST" action="$cheminInsc">
    <label>Nom* : </label> <input type="text" name="nom" placeholder="Nom" $nom required>
    <label>Prénom* : </label><input type="text" name="prenom" placeholder="Prénom" $prenom required>
    $errorEmail
    <label id="email">Email* : </label><input type="email" name="email" placeholder="Email" $email required>
    $errorMdp
    <label>Mot de Passe* : </label> <input type="password" name="mdp" placeholder="Mot de passe" required>
    <label>Confirmation* : </label><input type="password" name="mdp-conf" placeholder="Mot de passe" required>
    <input id="submit" type="submit" name="valider-insc" value="S'inscrire"  placeholder="Mot de passe">
  </form>
END;
      break;
    }

    case VueAdministrateur::AFF_USER : {
      $cheminDelete = $app->urlFor('supprimer-compte');
      $cheminCompteInfo = $app->urlFor('modifier-compte');
      $cheminModifMdp = $app->urlFor('modifier-mdp');
      $code = \garagesolidaire\vue\VueGeneral::genererHeader("menu");
      $code .= <<<END
<div id="bouton">
  <a href="$cheminCompteInfo">Modifier son compte</a>
  <a href="$cheminModifMdp">Changer de mot de passe</a>
  <a href="#sup-compte">Supprimer son compte</a>
</div>
<div id="sup-compte" class="modal">
<div class="modal-dialog">
  <div class="modal-content">
    <header class="container">
      <a href="#" class="closebtn">×</a>
        <h4>Supprimer son compte</h4>
      </header>
      <div class="container">
        <p> Voulez-vous réellement supprimer votre compte ? </p><br>
        <form class="reservation" method="GET" action="$cheminDelete">
            <button class="suppr" type="submit" name="valid-reserv" value="valid_reserv">Supprimer</button>
            <a href="#">Annuler</a>
        </form>
      </div>
    </div>
  </div>
</div>
END;

      break;
    }
    case VueAdministrateur::AFF_RESERV : {
      $code = \garagesolidaire\vue\VueGeneral::genererHeader("menu");
      if (count($this->infos)>0) { //Affichage des items
        foreach ($this->infos as $value) {
            $rootDecline = $app->urlFor("reservation-decline", ["id" => $value['id']]) ;
            $rootAccept = $app->urlFor("reservation-accept", ["id" => $value['id']]) ;
            $id = $value['id'];
            $idItem = $value['idItem'];
            $idUser = $value['idUser'];
            $nomItem = Item::find($idItem)->nom;
            $nomUser = User::find($idUser)->nom;
            $prenomUser = User::find($idUser)->prenom;
      $code.= <<<END
      <div class="item">
          <div class="description">
            <h4>Utilisateur : $nomUser $prenomUser</h4>
          </div>
          <p class="etat">Nom Item : $nomItem</p>
          <a href="#id$id" class="supprimer" style="color:green">Valider</a>
          <a href="#id2$id" class="supprimer">Annuler</a>
      </div>
      <div id="id$id" class="modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <header class="container">
            <a href="#" class="closebtn">×</a>
              <h4>Validation Réservation</h4>
            </header>
            <div class="container">
              <p>Valider la reservation de l'item $nomItem ? </p><br>
              <form class="reservation" method="POST" action="$rootAccept">
                  <button class="suppr" type="submit"  >Valider</button>
                  <a href="#">Annuler</a>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div id="id2$id" class="modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <header class="container">
            <a href="#" class="closebtn">×</a>
              <h4>Annuler Réservation</h4>
            </header>
            <div class="container">
              <p>Annuler la réservation de l'item $nomItem par $nomUser $prenomUser? </p><br>
              <form class="reservation" method="POST" action="$rootDecline">
                  <button class="suppr" type="submit" >Annuler</button>
                  <a href="#">Retour</a>
              </form>
            </div>
          </div>
        </div>
      </div>
END;
    }
  } else {
    $code.= "<p>Aucune Reservations...</p>";
  }
      break;
    }
    case VueAdministrateur::AFF_CMDP:{
      $code = \garagesolidaire\vue\VueGeneral::genererHeader("formulaire");
      $cheminMdp = $app->urlFor('modifier-mdp');
      $errorLogIn = "";
      if(isset($this->infos) && $this->infos == 'error'){
        $errorLogIn = "<p>*** Mot de passe invalide ***</p>";
      }
      $code = \garagesolidaire\vue\VueGeneral::genererHeader("formulaire");
      $code .= <<<END
<header>CHANGER DE MOT DE PASSE</header>
<form id="form" method="POST" action="${cheminMdp}">
$errorLogIn
        <label>ANCIEN MOT DE PASSE* : </label> <input type="password" name="old-pass" placeholder="ANCIEN MOT DE PASSE"  required>
        <label>NOUVEAU MOT DE PASSE* : </label><input type="password" name="new-pass" placeholder="MOT DE PASSE" required>
        <label>CONFIRMATION* : </label><input type="password" name="conf-pass" placeholder="CONFIRMATION" required>
    <input id="submit" type="submit" name="connection" value="Changer de mot de passe">
</form>
</div>
END;
      break;
    }

    case VueAdministrateur::AFF_MODIF_COMPTE : {
      $cheminCompte = $app->urlFor('modifier-compte');
        $nom = "value=\"".$_SESSION['username']."\"";
        $prenom = "value=\"".$_SESSION['usernickname']."\"";


        $code = \garagesolidaire\vue\VueGeneral::genererHeader("formulaire");
        $code .= <<<END
        <header>MODIFIER SON COMPTE</header>
        <form id="form" method="POST" action="$cheminCompte">
          <label>Nom* : </label> <input type="text" name="nom" placeholder="Nom" $nom required>
          <label>Prénom* : </label><input type="text" name="prenom" placeholder="Prénom" $prenom required>
          <input id="submit" type="submit" name="valider-insc" value="Modifier">
        </form>
END;
    }
    case VueAdministrateur::AFF_NO_CO :  //-----------------------------------------------------------------------Erreur-Non-conecté
    $app = \Slim\Slim::getInstance();
    $cheminCo = $app->urlFor("connexion");
    $cheminInsc = $app->urlFor("inscription");
      $code = \garagesolidaire\vue\VueGeneral::genererHeader("erreur");
        $code .= "<h1> Oupss ! Il semble que vous n'&ecirc;tes pas connect&eacute ... :( </h1> \n";
        $code .= <<<END
        <p>Inscrivez-vous sur <a href="${cheminInsc}">ce lien</a></br>Ou si vous &ecirc;tes d&eacutej&agrave; inscrit c'est sur <a href="${cheminCo}">celui-ci</a> </p>
END;
    break;
case VueAdministrateur::AFF_NO_ACCES :  //------------------------------------------------------------------------interdiction-Acces
  $accueil = $app->urlFor('accueil');
  $code = \garagesolidaire\vue\VueGeneral::genererHeader("erreur403");
  $code .= <<<END
  <div class="message">Erreur 403 : Vous n&#39;avez pas le droit d'acc&egrave;s.</div>
    <div class="message2">Retour &agrave; la <a href="$accueil">page d&#39;accueil</a></div>
    <div class="container">
      <div class="neon">403</div>
      <div class="door-frame">
        <div class="door">
          <div class="rectangle">
        </div>
          <div class="handle">
            </div>
          <div class="window">
            <div class="eye">
            </div>
            <div class="eye eye2">
            </div>
            <div class="leaf">
            </div>
          </div>
        </div>
      </div>
    </div>
END;
break;
  }
  $code .= \garagesolidaire\vue\VueGeneral::genererFooter();
  echo $code;
}

}
