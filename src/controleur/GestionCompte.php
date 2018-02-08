<?php
namespace garagesolidaire\controleur;

  use garagesolidaire\vue\VueAdministrateur as VueAdministrateur;
  use garagesolidaire\models\Authentication as Authentification;
  use garagesolidaire\models\UserInfo as UserInfo;
  use garagesolidaire\models as Model;

  /**
   * Controleur qui va gérer la gestion de compte
   * (formulaire de création de compte)
   */
  class GestionCompte{
      /**
       * Affichage formulaire inscription
       */
      public function afficheInscription(){
        $vue = new VueAdministrateur(null);
        $vue->render(VueAdministrateur::AFF_INSC);
      }

      /**
       * Affichage formulaire connexion
       */
      public function afficheConnexion(){
        $vue = new VueAdministrateur(null);
        $vue->render(VueAdministrateur::AFF_CO);
      }

      /**
       * Affichage avertissement accès interdit
       */
      public function afficheNonAccess(){
        // $vue = new VueAdministrateur(null, VueAdministrateur::AFF_NO_ACCES);
        // $vue->render();
      }


      /**
      * Ajoute un utlisateur dans la base de données
      */
      public function ajouterUtilisateur(){
        $app = \Slim\Slim::getInstance();
        if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['mdp']) && isset($_POST['mdp-conf'])){
          if (!filter_var( $tab['email'] , FILTER_VALIDATE_EMAIL)){
            $valueFiltred = $this->filterVar($_POST);
            $valueFiltred['error'] = "email";
            $vue = new VueAdministrateur( $valueFiltred);
            $vue->render(VueCreateur::AFF_INSC);

        }
          if($valueFiltred['mdp'] != $valueFiltred['mdp-conf']){
            $valueFiltred['error'] = 'mdpDiff';
            $vue = new VueAdministrateur($valueFiltred);
            $vue->render(VueAdministrateur::AFF_INSC);
          }else{
            $res = Authentification::createUser($valueFiltred['nom'], $valueFiltred['prenom'],$valueFiltred['email'], $valueFiltred['mdp'] );
            if($res == 0){
              $valueFiltred['error'] = "emailExist";
              $vue = new VueAdministrateur($valueFiltred);
              $vue->render(VueAdministrateur::AFF_INSC);
            }
            else
            $app->redirect( $app->urlFor("accueil"));
          }
        }else
          $app->redirect( $app->urlFor("accueil"));
      }

      /**
       *  Fonction permettant de filtrer les données venant d'un formulaire
       *  @return tab avec ses valeurs filtrée
       */
      public function filterVar($tab){
          $res = [];
          foreach ($tab as $key => $value) {
            $res[$key] = filter_var( $value , FILTER_SANITIZE_STRING);
          }

          return $res;
      }

      /**
       * Supprime définitivement un compte utilisateur
       * Et toute ses données entrées dans la base (reservation , items + image , listes)
       */
      public function supprimerCompte(){
          $app = \Slim\Slim::getInstance();
          //Redirection si l'utilisateur n'est pas connecté
          if(!isset($_SESSION["profile"])){
            $app->redirect( $app->urlFor("no-connection")  ) ;
          }

          $listes = Model\Liste::where('user_id',"=",$_SESSION['profile']['uid'])->get();
          foreach ($listes as $list) { //Parcours de toutes les listes une par une
            $items = Model\Item::where('liste_id','=',$list->no)->get();
            foreach ($items as $item) {   //Parcours de chaque item de la liste
                $reserv = Model\Reservation::where('id_item',"=",$item->id)->first(); //Prendre les reservation de chaque item
                if($reserv != null){
                  $reserv->delete();
                }
                if($item->img != null && $item->img != ''){ //retirer l'image de chaque item
                  $nomFichier = "img/".$item->img;
                  unlink($nomFichier);
                }
                $item->delete();
            }
            $list->delete();
          }
          //Suppression password
          $userPass = Model\UserPass::where('uid',"=",$_SESSION['profile']['uid'])->first();
          $userPass->delete();

          //Supression information utilisateurs
          $userInfo = Model\UserInfo::where('uid',"=",$_SESSION['profile']['uid'])->first();
          $userInfo->delete();

          //Déconnection de l'utilisateur
          $this->deconnecter();
      }


    /**
     * Déconnecte l'utilisateur
     */
    public function deconnecter(){
      $app = \Slim\Slim::getInstance();
      if(isset($_SESSION['profile'])){
        unset($_SESSION['profile']);
      }
      $app->redirect( $app->request->getRootUri() ) ;
    }

    /**
     * Connecte l'utilisateur
     */
    public function etablirConnection(){
      $app = \Slim\Slim::getInstance();

      //Filtrage du mail
      $email = filter_var($param['email'] , FILTER_SANITIZE_EMAIL);
      $mdp = $param['mdp'];

      $user = UserInfo::where("email","=",$email)->first(); //Récupération des Info

      try {
        if($user == null) //Test si l'utilisateur est présent dans la base ou non
          throw new \garagesolidaire\models\AuthException("User not exist");

        Authentification::authenticate($user->uid,$mdp); // Authentification
        Authentification::loadProfile($user->uid); //Chargement des données utilisateurs
        $app->redirect( $app->urlFor("liste") ) ; //Redirection à ses listes

      } catch (  \garagesolidaire\models\AuthException $ae ) { //Cas d'erreur
        $tab_report = ['email' => $email, 'error' => 'auth'];
        $vue = new VueAdministrateur($tab_report, VueAdministrateur::AFF_CO ); //Charge la page de connexion avec l'erreur correspondant
        $vue->render();
      }

    }
}
